<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Metodo no permitido'], 405);
}

$email = strtolower(trim($_POST['email'] ?? ''));
if ($email === '') {
    json_response(['ok' => false, 'message' => 'Email obligatorio.'], 422);
}

$stmt = $pdo->prepare('SELECT id, email, activo FROM usuarios WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || (int)$user['activo'] !== 1) {
    audit_log($pdo, 'RECOVERY_SOLICITUD_FALLIDA', 'Email no encontrado: ' . $email);
    json_response(['ok' => false, 'message' => 'No existe un usuario activo con ese email.'], 404);
}

$token = bin2hex(random_bytes(24));
$expira = (new DateTime('now'))->modify('+30 minutes')->format('Y-m-d H:i:s');

$pdo->prepare('UPDATE password_resets SET usado = 1 WHERE usuario_id = ? AND usado = 0')->execute([(int)$user['id']]);
$ins = $pdo->prepare('INSERT INTO password_resets (usuario_id, token, expira_en, usado) VALUES (?, ?, ?, 0)');
$ins->execute([(int)$user['id'], $token, $expira]);

audit_log($pdo, 'RECOVERY_SOLICITUD_OK', 'Token generado', (int)$user['id']);

json_response([
    'ok' => true,
    'message' => 'Token de recuperacion generado. Es valido por 30 minutos.',
    'data' => ['token' => $token, 'expira_en' => $expira],
]);
