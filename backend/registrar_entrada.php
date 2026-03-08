<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

$user = require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Metodo no permitido'], 405);
}

$placa = isset($_POST['placa']) ? normalizar_placa($_POST['placa']) : '';
$categoriaId = (int)($_POST['categoria_id'] ?? 0);
$marcaNombre = strtoupper(trim($_POST['marca_nombre'] ?? ''));
$modeloNombre = strtoupper(trim($_POST['modelo_nombre'] ?? ''));
$color = trim($_POST['color'] ?? '');
$ubicacionId = (int)($_POST['ubicacion_id'] ?? 0);

if (!placa_valida($placa)) {
    json_response(['ok' => false, 'message' => 'Placa invalida. Use 5 a 10 caracteres alfanumericos.'], 422);
}
if ($categoriaId <= 0 || $ubicacionId <= 0 || $marcaNombre === '' || $modeloNombre === '' || $color === '') {
    json_response(['ok' => false, 'message' => 'Categoria, marca, modelo, color y ubicacion son obligatorios.'], 422);
}

try {
    $pdo->beginTransaction();

    $categoriaStmt = $pdo->prepare('SELECT id, nombre, valor_hora FROM categorias_vehiculo WHERE id = ? AND activo = 1 LIMIT 1');
    $categoriaStmt->execute([$categoriaId]);
    $categoria = $categoriaStmt->fetch();
    if (!$categoria) {
        $pdo->rollBack();
        json_response(['ok' => false, 'message' => 'Categoria no valida.'], 422);
    }

    $ubicacionStmt = $pdo->prepare("SELECT id, codigo, estado FROM ubicaciones WHERE id = ? LIMIT 1 FOR UPDATE");
    $ubicacionStmt->execute([$ubicacionId]);
    $ubicacion = $ubicacionStmt->fetch();
    if (!$ubicacion || $ubicacion['estado'] !== 'LIBRE') {
        $pdo->rollBack();
        json_response(['ok' => false, 'message' => 'La ubicacion no esta disponible.'], 409);
    }

    $activos = (int)$pdo->query("SELECT COUNT(*) AS c FROM registros_parqueo WHERE estado='ACTIVO'")->fetch()['c'];
    if ($activos >= TOTAL_CUPOS) {
        $pdo->rollBack();
        json_response(['ok' => false, 'message' => 'No hay cupos globales disponibles.'], 409);
    }

    $getMarca = $pdo->prepare('SELECT id FROM marcas_vehiculo WHERE nombre = ? LIMIT 1');
    $getMarca->execute([$marcaNombre]);
    $m = $getMarca->fetch();
    if ($m) {
        $marcaId = (int)$m['id'];
    } else {
        $pdo->prepare('INSERT INTO marcas_vehiculo (nombre, activo) VALUES (?, 1)')->execute([$marcaNombre]);
        $marcaId = (int)$pdo->lastInsertId();
    }

    $getModelo = $pdo->prepare('SELECT id FROM modelos_vehiculo WHERE nombre = ? AND marca_id = ? LIMIT 1');
    $getModelo->execute([$modeloNombre, $marcaId]);
    $mo = $getModelo->fetch();
    if ($mo) {
        $modeloId = (int)$mo['id'];
    } else {
        $pdo->prepare('INSERT INTO modelos_vehiculo (nombre, marca_id, categoria_id, activo) VALUES (?, ?, ?, 1)')->execute([$modeloNombre, $marcaId, $categoriaId]);
        $modeloId = (int)$pdo->lastInsertId();
    }

    $vehStmt = $pdo->prepare('SELECT id FROM vehiculos WHERE placa = ? LIMIT 1');
    $vehStmt->execute([$placa]);
    $vehiculo = $vehStmt->fetch();

    if (!$vehiculo) {
        $insV = $pdo->prepare('INSERT INTO vehiculos (placa, categoria_id, marca_id, modelo_id, color) VALUES (?, ?, ?, ?, ?)');
        $insV->execute([$placa, $categoriaId, $marcaId, $modeloId, $color]);
        $vehiculoId = (int)$pdo->lastInsertId();
    } else {
        $vehiculoId = (int)$vehiculo['id'];
        $updV = $pdo->prepare('UPDATE vehiculos SET categoria_id = ?, marca_id = ?, modelo_id = ?, color = ? WHERE id = ?');
        $updV->execute([$categoriaId, $marcaId, $modeloId, $color, $vehiculoId]);
    }

    $activeVeh = $pdo->prepare("SELECT id FROM registros_parqueo WHERE vehiculo_id = ? AND estado='ACTIVO' LIMIT 1");
    $activeVeh->execute([$vehiculoId]);
    if ($activeVeh->fetch()) {
        $pdo->rollBack();
        json_response(['ok' => false, 'message' => 'Este vehiculo ya tiene entrada activa.'], 409);
    }

    $ticket = generar_codigo('TK', 8);
    $checkTicket = $pdo->prepare('SELECT id FROM registros_parqueo WHERE ticket_codigo = ? LIMIT 1');
    while (true) {
        $checkTicket->execute([$ticket]);
        if (!$checkTicket->fetch()) {
            break;
        }
        $ticket = generar_codigo('TK', 8);
    }

    $insR = $pdo->prepare('INSERT INTO registros_parqueo (vehiculo_id, ubicacion_id, usuario_entrada_id, hora_entrada, ticket_codigo, estado) VALUES (?, ?, ?, NOW(), ?, "ACTIVO")');
    $insR->execute([$vehiculoId, $ubicacionId, $user['id'], $ticket]);

    $pdo->prepare("UPDATE ubicaciones SET estado='OCUPADO' WHERE id = ?")->execute([$ubicacionId]);

    $horaEntrada = $pdo->query("SELECT DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s') h")->fetch()['h'];

    $pdo->commit();
    audit_log($pdo, 'ENTRADA_REGISTRADA', 'Placa: ' . $placa . ', Ticket: ' . $ticket, (int)$user['id']);

    json_response([
        'ok' => true,
        'message' => 'Entrada registrada correctamente.',
        'data' => [
            'placa' => $placa,
            'categoria' => $categoria['nombre'],
            'ticket_codigo' => $ticket,
            'tarifa_hora' => (float)$categoria['valor_hora'],
            'hora_entrada' => $horaEntrada,
            'ubicacion_codigo' => $ubicacion['codigo'],
            'cupos_disponibles' => TOTAL_CUPOS - ($activos + 1),
        ],
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(['ok' => false, 'message' => 'Error al registrar entrada.', 'error' => $e->getMessage()], 500);
}
