<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

$u = require_roles(['SUPERADMIN']);
$action = strtolower(trim($_REQUEST['action'] ?? 'list'));

try {
    if ($action === 'list') {
        $rows = $pdo->query('SELECT id, nombre, email, rol, activo, creado_en FROM usuarios ORDER BY id DESC')->fetchAll();
        json_response(['ok' => true, 'data' => $rows]);
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $rol = strtoupper(trim($_POST['rol'] ?? 'OPERADOR'));
        $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;
        $password = $_POST['password'] ?? '';
        if ($nombre === '' || $email === '' || !in_array($rol, ['SUPERADMIN','ADMIN','OPERADOR'], true)) {
            json_response(['ok' => false, 'message' => 'Nombre, email y rol son obligatorios.'], 422);
        }

        if ($id > 0) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare('UPDATE usuarios SET nombre=?, email=?, rol=?, activo=?, password_hash=? WHERE id=?')->execute([$nombre, $email, $rol, $activo, $hash, $id]);
            } else {
                $pdo->prepare('UPDATE usuarios SET nombre=?, email=?, rol=?, activo=? WHERE id=?')->execute([$nombre, $email, $rol, $activo, $id]);
            }
        } else {
            if ($password === '') {
                json_response(['ok' => false, 'message' => 'Password obligatorio para nuevo usuario.'], 422);
            }
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $pdo->prepare('INSERT INTO usuarios (nombre, email, password_hash, rol, activo) VALUES (?, ?, ?, ?, ?)')->execute([$nombre, $email, $hash, $rol, $activo]);
        }

        audit_log($pdo, 'USUARIO_GUARDAR', 'Email: ' . $email, (int)$u['id']);
        json_response(['ok' => true, 'message' => 'Usuario guardado.']);
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM usuarios WHERE id=?')->execute([$id]);
        audit_log($pdo, 'USUARIO_ELIMINAR', 'ID: ' . $id, (int)$u['id']);
        json_response(['ok' => true, 'message' => 'Usuario eliminado.']);
    }

    json_response(['ok' => false, 'message' => 'Accion no soportada.'], 422);
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => 'Error en usuarios.', 'error' => $e->getMessage()], 500);
}
