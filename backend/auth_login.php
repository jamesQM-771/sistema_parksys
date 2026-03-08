<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Metodo no permitido'], 405);
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    json_response(['ok' => false, 'message' => 'Email y password son obligatorios.'], 422);
}

$stmt = $pdo->prepare('SELECT id, nombre, email, password_hash, rol, activo FROM usuarios WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

$validPassword = false;
if ($user) {
    $validPassword = password_verify($password, $user['password_hash']) || hash_equals((string)$user['password_hash'], $password);
}

if (!$user || (int)$user['activo'] !== 1 || !$validPassword) {
    audit_log($pdo, 'LOGIN_FALLIDO', 'Intento con email: ' . $email, $user ? (int)$user['id'] : null);
    json_response(['ok' => false, 'message' => 'Credenciales invalidas.'], 401);
}

$_SESSION['user'] = [
    'id' => (int)$user['id'],
    'nombre' => $user['nombre'],
    'email' => $user['email'],
    'rol' => $user['rol'],
];

audit_log($pdo, 'LOGIN_OK', 'Inicio de sesion');
json_response(['ok' => true, 'message' => 'Sesion iniciada.', 'data' => $_SESSION['user']]);
