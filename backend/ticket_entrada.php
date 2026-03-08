<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

require_auth();

$ticket = strtoupper(trim($_GET['ticket'] ?? ''));
if ($ticket === '') {
    json_response(['ok' => false, 'message' => 'Ticket requerido.'], 422);
}

$sql = "
    SELECT rp.ticket_codigo, rp.hora_entrada,
           v.placa, v.color,
           c.nombre AS categoria, c.valor_hora AS tarifa_hora,
           ma.nombre AS marca,
           mo.nombre AS modelo,
           u.codigo AS ubicacion,
           ue.nombre AS operador
    FROM registros_parqueo rp
    INNER JOIN vehiculos v ON v.id = rp.vehiculo_id
    INNER JOIN categorias_vehiculo c ON c.id = v.categoria_id
    LEFT JOIN marcas_vehiculo ma ON ma.id = v.marca_id
    LEFT JOIN modelos_vehiculo mo ON mo.id = v.modelo_id
    INNER JOIN ubicaciones u ON u.id = rp.ubicacion_id
    LEFT JOIN usuarios ue ON ue.id = rp.usuario_entrada_id
    WHERE rp.ticket_codigo = ?
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$ticket]);
$row = $stmt->fetch();

if (!$row) {
    json_response(['ok' => false, 'message' => 'Ticket no encontrado.'], 404);
}

json_response(['ok' => true, 'data' => $row]);

