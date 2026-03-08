<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['ok' => false, 'message' => 'Metodo no permitido'], 405);
}

$placa = isset($_GET['placa']) ? normalizar_placa($_GET['placa']) : '';
if (!placa_valida($placa)) {
    json_response(['ok' => false, 'message' => 'Placa invalida.'], 422);
}

try {
    $sql = "
        SELECT rp.id, rp.hora_entrada, rp.ticket_codigo, v.placa, c.nombre AS categoria_nombre, c.valor_hora
        FROM registros_parqueo rp
        INNER JOIN vehiculos v ON v.id = rp.vehiculo_id
        INNER JOIN categorias_vehiculo c ON c.id = v.categoria_id
        WHERE v.placa = ? AND rp.estado = 'ACTIVO'
        ORDER BY rp.hora_entrada DESC
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa]);
    $registro = $stmt->fetch();

    if (!$registro) {
        json_response(['ok' => false, 'message' => 'No hay un registro activo para esta placa.'], 404);
    }

    $entrada = new DateTime($registro['hora_entrada']);
    $ahora = new DateTime('now');
    $minutos = minutos_entre_fechas($entrada, $ahora);
    $modoCobro = obtener_modo_cobro($pdo);
    $gracia = obtener_minutos_gracia($pdo);
    $calc = calcular_valor_cobro($minutos, (float)$registro['valor_hora'], $modoCobro, $gracia);

    json_response([
        'ok' => true,
        'data' => [
            'placa' => $registro['placa'],
            'categoria' => $registro['categoria_nombre'],
            'ticket_codigo' => $registro['ticket_codigo'],
            'hora_entrada' => $registro['hora_entrada'],
            'minutos' => $calc['minutos_reales'],
            'minutos_gracia' => $calc['minutos_gracia'],
            'minutos_cobrables' => $calc['minutos_cobrables'],
            'horas_cobrables' => $calc['horas_cobrables'],
            'tarifa_hora' => (float)$registro['valor_hora'],
            'modo_cobro' => $modoCobro,
            'descripcion_cobro' => $calc['descripcion'],
            'valor_total' => $calc['valor_total'],
        ],
    ]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => 'Error interno al calcular pago.', 'error' => $e->getMessage()], 500);
}
