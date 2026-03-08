<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

require_roles(['ADMIN', 'SUPERADMIN']);

$limit = max(1, min(500, (int)($_GET['limit'] ?? 100)));

$sql = "
    SELECT a.id, a.usuario_id, a.accion, a.detalle, a.ip, a.creado_en, u.nombre AS usuario_nombre, u.email
    FROM auditoria a
    LEFT JOIN usuarios u ON u.id = a.usuario_id
    ORDER BY a.id DESC
    LIMIT $limit
";

$rows = $pdo->query($sql)->fetchAll();
json_response(['ok' => true, 'data' => $rows]);
