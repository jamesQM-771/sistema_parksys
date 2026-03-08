<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

$action = strtolower(trim($_GET['action'] ?? 'get'));

if ($action === 'get') {
    try {
        $rows = $pdo->query('SELECT clave, valor FROM configuracion')->fetchAll();
        $cfg = [];
        foreach ($rows as $r) {
            $cfg[$r['clave']] = $r['valor'];
        }
        if (!isset($cfg['logo_url'])) {
            $cfg['logo_url'] = 'assets/img/logo-default.png';
        }
        if (!isset($cfg['modo_cobro'])) {
            $cfg['modo_cobro'] = 'POR_MINUTO';
        }
        if (!isset($cfg['minutos_gracia'])) {
            $cfg['minutos_gracia'] = '5';
        }
        json_response(['ok' => true, 'data' => $cfg]);
    } catch (Throwable $e) {
        json_response(['ok' => true, 'data' => ['logo_url' => 'assets/img/logo-default.png', 'modo_cobro' => 'POR_MINUTO', 'minutos_gracia' => '5']]);
    }
}

require_roles(['ADMIN', 'SUPERADMIN']);

if ($action === 'set') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['ok' => false, 'message' => 'Metodo no permitido'], 405);
    }
    $clave = trim($_POST['clave'] ?? '');
    $valor = trim($_POST['valor'] ?? '');
    if ($clave === '') {
        json_response(['ok' => false, 'message' => 'Clave requerida.'], 422);
    }

    $q = $pdo->prepare('INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)');
    $q->execute([$clave, $valor]);
    audit_log($pdo, 'CONFIG_SET', 'Clave: ' . $clave);
    json_response(['ok' => true, 'message' => 'Configuracion guardada.']);
}

json_response(['ok' => false, 'message' => 'Accion no soportada.'], 422);