<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

require_auth();

$codigo = strtoupper(trim($_GET['codigo'] ?? ''));
if ($codigo === '') {
    json_response(['ok' => false, 'message' => 'Codigo de factura obligatorio.'], 422);
}

$sql = "
    SELECT rp.id, rp.ticket_codigo, rp.factura_codigo, rp.hora_entrada, rp.hora_salida, rp.total_minutos, rp.valor_total,
           v.placa, v.color, c.nombre AS categoria, u.codigo AS ubicacion,
           p.metodo, p.fecha_pago,
           ue.nombre AS atendio_entrada, us.nombre AS atendio_salida
    FROM registros_parqueo rp
    INNER JOIN vehiculos v ON v.id = rp.vehiculo_id
    INNER JOIN categorias_vehiculo c ON c.id = v.categoria_id
    INNER JOIN ubicaciones u ON u.id = rp.ubicacion_id
    LEFT JOIN pagos p ON p.registro_id = rp.id
    LEFT JOIN usuarios ue ON ue.id = rp.usuario_entrada_id
    LEFT JOIN usuarios us ON us.id = rp.usuario_salida_id
    WHERE rp.factura_codigo = ?
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$codigo]);
$row = $stmt->fetch();

if (!$row) {
    json_response(['ok' => false, 'message' => 'Factura no encontrada.'], 404);
}

json_response(['ok' => true, 'data' => $row]);
