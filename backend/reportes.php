<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

$user = require_auth();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $action = strtolower(trim($_POST['action'] ?? ''));

    if (!in_array($user['rol'], ['ADMIN', 'SUPERADMIN'], true)) {
        json_response(['ok' => false, 'message' => 'No autorizado para CRUD de reportes.'], 403);
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            json_response(['ok' => false, 'message' => 'ID invalido.'], 422);
        }

        $pdo->beginTransaction();
        try {
            $q = $pdo->prepare('SELECT estado, ubicacion_id FROM registros_parqueo WHERE id = ? FOR UPDATE');
            $q->execute([$id]);
            $r = $q->fetch();
            if (!$r) {
                $pdo->rollBack();
                json_response(['ok' => false, 'message' => 'Registro no encontrado.'], 404);
            }

            $pdo->prepare('DELETE FROM pagos WHERE registro_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM registros_parqueo WHERE id = ?')->execute([$id]);

            if ($r['estado'] === 'ACTIVO') {
                $pdo->prepare("UPDATE ubicaciones SET estado='LIBRE' WHERE id = ?")->execute([$r['ubicacion_id']]);
            }

            $pdo->commit();
            audit_log($pdo, 'REPORTE_DELETE', 'Registro ID: ' . $id, (int)$user['id']);
            json_response(['ok' => true, 'message' => 'Registro eliminado.']);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            json_response(['ok' => false, 'message' => 'No fue posible eliminar registro.', 'error' => $e->getMessage()], 500);
        }
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $horaEntrada = trim($_POST['hora_entrada'] ?? '');
        $horaSalida = trim($_POST['hora_salida'] ?? '');
        $valorTotal = ($_POST['valor_total'] ?? '') !== '' ? (float)$_POST['valor_total'] : null;

        if ($id <= 0 || $horaEntrada === '') {
            json_response(['ok' => false, 'message' => 'Datos insuficientes para actualizar.'], 422);
        }

        try {
            $estado = $horaSalida === '' ? 'ACTIVO' : 'FINALIZADO';
            $totalMin = null;
            if ($horaSalida !== '') {
                $ent = new DateTime($horaEntrada);
                $sal = new DateTime($horaSalida);
                $totalMin = minutos_entre_fechas($ent, $sal);
            }

            $pdo->prepare('UPDATE registros_parqueo SET hora_entrada=?, hora_salida=?, total_minutos=?, valor_total=?, estado=? WHERE id=?')
                ->execute([$horaEntrada, $horaSalida !== '' ? $horaSalida : null, $totalMin, $valorTotal, $estado, $id]);

            audit_log($pdo, 'REPORTE_UPDATE', 'Registro ID: ' . $id, (int)$user['id']);
            json_response(['ok' => true, 'message' => 'Registro actualizado.']);
        } catch (Throwable $e) {
            json_response(['ok' => false, 'message' => 'No fue posible actualizar registro.', 'error' => $e->getMessage()], 500);
        }
    }

    json_response(['ok' => false, 'message' => 'Accion no soportada.'], 422);
}

if ($method !== 'GET') {
    json_response(['ok' => false, 'message' => 'Metodo no permitido'], 405);
}

$tipo = strtolower(trim($_GET['tipo'] ?? 'diario'));
$validos = ['diario', 'semanal', 'mensual', 'activos', 'dashboard', 'operativo'];
if (!in_array($tipo, $validos, true)) {
    json_response(['ok' => false, 'message' => 'Tipo de reporte invalido.'], 422);
}

try {
    if ($tipo === 'activos') {
        $sql = "
            SELECT rp.id, v.placa, c.nombre AS categoria, ma.nombre AS marca, mo.nombre AS modelo,
                   v.color, u.codigo AS ubicacion, rp.hora_entrada, rp.ticket_codigo
            FROM registros_parqueo rp
            INNER JOIN vehiculos v ON v.id = rp.vehiculo_id
            INNER JOIN categorias_vehiculo c ON c.id = v.categoria_id
            LEFT JOIN marcas_vehiculo ma ON ma.id = v.marca_id
            LEFT JOIN modelos_vehiculo mo ON mo.id = v.modelo_id
            INNER JOIN ubicaciones u ON u.id = rp.ubicacion_id
            WHERE rp.estado='ACTIVO'
            ORDER BY rp.hora_entrada ASC
        ";
        json_response(['ok' => true, 'data' => $pdo->query($sql)->fetchAll()]);
    }

    if ($tipo === 'dashboard') {
        $activos = (int)$pdo->query("SELECT COUNT(*) c FROM registros_parqueo WHERE estado='ACTIVO'")->fetch()['c'];
        $ingresosHoy = $pdo->query("SELECT COUNT(*) servicios, COALESCE(SUM(valor_total),0) ingresos FROM registros_parqueo WHERE estado='FINALIZADO' AND DATE(hora_salida)=CURDATE()")->fetch();
        $ocupacionZona = $pdo->query("SELECT zona, SUM(CASE WHEN estado='OCUPADO' THEN 1 ELSE 0 END) AS ocupados, COUNT(*) total FROM ubicaciones GROUP BY zona ORDER BY zona")->fetchAll();
        $porCategoria = $pdo->query("SELECT c.nombre categoria, COUNT(*) cantidad FROM registros_parqueo rp INNER JOIN vehiculos v ON v.id=rp.vehiculo_id INNER JOIN categorias_vehiculo c ON c.id=v.categoria_id WHERE rp.estado='ACTIVO' GROUP BY c.nombre ORDER BY cantidad DESC")->fetchAll();

        json_response([
            'ok' => true,
            'data' => [
                'total_cupos' => TOTAL_CUPOS,
                'ocupados' => $activos,
                'disponibles' => TOTAL_CUPOS - $activos,
                'salidas_hoy' => (int)$ingresosHoy['servicios'],
                'ingresos_hoy' => (float)$ingresosHoy['ingresos'],
                'ocupacion_zona' => $ocupacionZona,
                'activos_por_categoria' => $porCategoria,
            ],
        ]);
    }

    if ($tipo === 'diario') {
        $filtro = 'DATE(rp.hora_entrada) = CURDATE() OR DATE(rp.hora_salida) = CURDATE()';
    } elseif ($tipo === 'semanal') {
        $filtro = 'YEARWEEK(rp.hora_entrada, 1) = YEARWEEK(CURDATE(), 1) OR YEARWEEK(rp.hora_salida, 1) = YEARWEEK(CURDATE(), 1)';
    } elseif ($tipo === 'mensual') {
        $filtro = '(YEAR(rp.hora_entrada)=YEAR(CURDATE()) AND MONTH(rp.hora_entrada)=MONTH(CURDATE())) OR (YEAR(rp.hora_salida)=YEAR(CURDATE()) AND MONTH(rp.hora_salida)=MONTH(CURDATE()))';
    } else {
        $filtro = '1=1';
    }

    $sql = "
        SELECT rp.id, rp.estado, rp.ticket_codigo, rp.factura_codigo,
               v.placa, v.color, c.nombre categoria, ma.nombre marca, mo.nombre modelo,
               u.codigo ubicacion,
               rp.hora_entrada, rp.hora_salida,
               COALESCE(rp.total_minutos, TIMESTAMPDIFF(MINUTE, rp.hora_entrada, COALESCE(rp.hora_salida, NOW()))) AS total_minutos,
               rp.valor_total,
               p.metodo
        FROM registros_parqueo rp
        INNER JOIN vehiculos v ON v.id = rp.vehiculo_id
        INNER JOIN categorias_vehiculo c ON c.id = v.categoria_id
        LEFT JOIN marcas_vehiculo ma ON ma.id = v.marca_id
        LEFT JOIN modelos_vehiculo mo ON mo.id = v.modelo_id
        INNER JOIN ubicaciones u ON u.id = rp.ubicacion_id
        LEFT JOIN pagos p ON p.registro_id = rp.id
        WHERE $filtro
        ORDER BY rp.hora_entrada DESC
    ";
    $rows = $pdo->query($sql)->fetchAll();

    $resumen = [
        'total_servicios' => count($rows),
        'total_ingresos' => array_reduce($rows, fn($c, $i) => $c + (float)($i['valor_total'] ?? 0), 0.0),
    ];

    json_response(['ok' => true, 'data' => ['tipo' => $tipo, 'resumen' => $resumen, 'registros' => $rows, 'can_crud' => in_array($user['rol'], ['ADMIN','SUPERADMIN'], true)]]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => 'Error interno al generar reporte.', 'error' => $e->getMessage()], 500);
}
