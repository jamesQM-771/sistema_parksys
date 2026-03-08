<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Metodo no permitido'], 405);
}

$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';

if ($token === '' || strlen($password) < 6) {
    json_response(['ok' => false, 'message' => 'Token y nueva clave (min 6) son obligatorios.'], 422);
}

$sql = 'SELECT id, usuario_id, expira_en, usado FROM password_resets WHERE token = ? LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    audit_log($pdo, 'RECOVERY_RESET_FALLIDO', 'Token no existe');
    json_response(['ok' => false, 'message' => 'Token invalido.'], 404);
}
if ((int)$reset['usado'] === 1) {
    json_response(['ok' => false, 'message' => 'Token ya usado.'], 409);
}
if (new DateTime($reset['expira_en']) < new DateTime('now')) {
    json_response(['ok' => false, 'message' => 'Token expirado.'], 410);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$pdo->beginTransaction();
try {
    $pdo->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?')->execute([$hash, (int)$reset['usuario_id']]);
    $pdo->prepare('UPDATE password_resets SET usado = 1 WHERE id = ?')->execute([(int)$reset['id']]);
    audit_log($pdo, 'RECOVERY_RESET_OK', 'Clave actualizada por token', (int)$reset['usuario_id']);
    $pdo->commit();
    json_response(['ok' => true, 'message' => 'Contrasena actualizada correctamente.']);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(['ok' => false, 'message' => 'Error al restablecer clave.', 'error' => $e->getMessage()], 500);
}
