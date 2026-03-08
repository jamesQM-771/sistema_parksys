<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

$user = require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Metodo no permitido'], 405);
}

$placa = isset($_POST['placa']) ? normalizar_placa($_POST['placa']) : '';
$ticket = strtoupper(trim($_POST['ticket_codigo'] ?? ''));
$metodo = strtoupper(trim($_POST['metodo'] ?? 'EFECTIVO'));
$metodos = ['EFECTIVO', 'TRANSFERENCIA', 'TARJETA', 'QR'];

if (!placa_valida($placa) || $ticket === '') {
    json_response(['ok' => false, 'message' => 'Placa y ticket son obligatorios.'], 422);
}
if (!in_array($metodo, $metodos, true)) {
    json_response(['ok' => false, 'message' => 'Metodo de pago invalido.'], 422);
}

try {
    $pdo->beginTransaction();

    $sql = "
        SELECT rp.id, rp.hora_entrada, rp.ticket_codigo, rp.ubicacion_id,
               v.placa, c.nombre AS categoria_nombre, c.valor_hora
        FROM registros_parqueo rp
        INNER JOIN vehiculos v ON v.id = rp.vehiculo_id
        INNER JOIN categorias_vehiculo c ON c.id = v.categoria_id
        WHERE v.placa = ? AND rp.estado = 'ACTIVO'
        ORDER BY rp.hora_entrada DESC
        LIMIT 1
        FOR UPDATE
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa]);
    $registro = $stmt->fetch();

    if (!$registro) {
        $pdo->rollBack();
        json_response(['ok' => false, 'message' => 'No existe entrada activa para esta placa.'], 404);
    }
    if ($registro['ticket_codigo'] !== $ticket) {
        $pdo->rollBack();
        json_response(['ok' => false, 'message' => 'El codigo de ticket no coincide.'], 403);
    }

    $entrada = new DateTime($registro['hora_entrada']);
    $salida = new DateTime('now');
    $minutos = minutos_entre_fechas($entrada, $salida);
    $modoCobro = obtener_modo_cobro($pdo);
    $gracia = obtener_minutos_gracia($pdo);
    $calc = calcular_valor_cobro($minutos, (float)$registro['valor_hora'], $modoCobro, $gracia);
    $valor = $calc['valor_total'];
    $factura = generar_codigo('FC', 8);

    $checkF = $pdo->prepare('SELECT id FROM registros_parqueo WHERE factura_codigo = ? LIMIT 1');
    while (true) {
        $checkF->execute([$factura]);
        if (!$checkF->fetch()) {
            break;
        }
        $factura = generar_codigo('FC', 8);
    }

    $upd = $pdo->prepare('UPDATE registros_parqueo SET hora_salida = NOW(), total_minutos = ?, valor_total = ?, estado = "FINALIZADO", usuario_salida_id = ?, factura_codigo = ? WHERE id = ?');
    $upd->execute([$calc['minutos_reales'], $valor, $user['id'], $factura, $registro['id']]);

    $pago = $pdo->prepare('INSERT INTO pagos (registro_id, usuario_id, fecha_pago, valor_pagado, metodo, estado) VALUES (?, ?, NOW(), ?, ?, "PAGADO")');
    $pago->execute([$registro['id'], $user['id'], $valor, $metodo]);

    $liberar = $pdo->prepare("UPDATE ubicaciones SET estado='LIBRE' WHERE id = ?");
    $liberar->execute([$registro['ubicacion_id']]);

    $activos = (int)$pdo->query("SELECT COUNT(*) AS c FROM registros_parqueo WHERE estado='ACTIVO'")->fetch()['c'];
    $pdo->commit();
    audit_log($pdo, 'SALIDA_REGISTRADA', 'Placa: ' . $placa . ', Factura: ' . $factura, (int)$user['id']);

    json_response([
        'ok' => true,
        'message' => 'Salida registrada y pago generado.',
        'data' => [
            'placa' => $registro['placa'],
            'categoria' => $registro['categoria_nombre'],
            'hora_entrada' => $registro['hora_entrada'],
            'hora_salida' => $salida->format('Y-m-d H:i:s'),
            'ticket_codigo' => $registro['ticket_codigo'],
            'factura_codigo' => $factura,
            'minutos' => $calc['minutos_reales'],
            'minutos_gracia' => $calc['minutos_gracia'],
            'minutos_cobrables' => $calc['minutos_cobrables'],
            'horas_cobrables' => $calc['horas_cobrables'],
            'tarifa_hora' => (float)$registro['valor_hora'],
            'modo_cobro' => $modoCobro,
            'descripcion_cobro' => $calc['descripcion'],
            'valor_total' => $valor,
            'cupos_disponibles' => TOTAL_CUPOS - $activos,
        ],
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(['ok' => false, 'message' => 'Error interno al registrar salida.', 'error' => $e->getMessage()], 500);
}
