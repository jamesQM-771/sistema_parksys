<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Metodo no permitido'], 405);
}

$user = auth_user();
if ($user) {
    audit_log($pdo, 'LOGOUT', 'Cierre de sesion', (int)$user['id']);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

json_response(['ok' => true, 'message' => 'Sesion cerrada.']);
