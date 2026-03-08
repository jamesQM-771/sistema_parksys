<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

require_roles(['ADMIN', 'SUPERADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Metodo no permitido'], 405);
}

$categoriaId = (int)($_POST['categoria_id'] ?? 0);
$minutos = (int)($_POST['minutos'] ?? 0);

if ($categoriaId <= 0 || $minutos <= 0) {
    json_response(['ok' => false, 'message' => 'Categoria y minutos son obligatorios.'], 422);
}

$stmt = $pdo->prepare('SELECT id, nombre, valor_hora FROM categorias_vehiculo WHERE id = ? AND activo = 1 LIMIT 1');
$stmt->execute([$categoriaId]);
$categoria = $stmt->fetch();

if (!$categoria) {
    json_response(['ok' => false, 'message' => 'Categoria no encontrada o inactiva.'], 404);
}

$modoCobro = obtener_modo_cobro($pdo);
$gracia = obtener_minutos_gracia($pdo);
$calc = calcular_valor_cobro($minutos, (float)$categoria['valor_hora'], $modoCobro, $gracia);

audit_log($pdo, 'SIMULAR_COBRO', 'Categoria: ' . $categoria['nombre'] . ', minutos: ' . $minutos);

json_response([
    'ok' => true,
    'data' => [
        'categoria' => $categoria['nombre'],
        'minutos' => $minutos,
        'tarifa_hora' => (float)$categoria['valor_hora'],
        'modo_cobro' => $modoCobro,
        'descripcion_cobro' => $calc['descripcion'],
        'minutos_gracia' => $calc['minutos_gracia'],
        'minutos_cobrables' => $calc['minutos_cobrables'],
        'horas_cobrables' => $calc['horas_cobrables'],
        'valor_total' => $calc['valor_total'],
    ],
]);