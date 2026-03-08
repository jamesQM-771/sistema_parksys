<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

$u = require_roles(['ADMIN', 'SUPERADMIN']);
$action = strtolower(trim($_REQUEST['action'] ?? 'list'));

try {
    if ($action === 'list') {
        $rows = $pdo->query('SELECT id, codigo, zona, estado, observacion FROM ubicaciones ORDER BY zona, codigo')->fetchAll();
        json_response(['ok' => true, 'data' => $rows]);
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
        $zona = strtoupper(trim($_POST['zona'] ?? 'A'));
        $estado = strtoupper(trim($_POST['estado'] ?? 'LIBRE'));
        $obs = trim($_POST['observacion'] ?? '');
        if ($codigo === '') {
            json_response(['ok' => false, 'message' => 'Codigo obligatorio.'], 422);
        }
        if (!in_array($estado, ['LIBRE','OCUPADO','MANTENIMIENTO'], true)) {
            json_response(['ok' => false, 'message' => 'Estado invalido.'], 422);
        }

        if ($id > 0) {
            $pdo->prepare('UPDATE ubicaciones SET codigo=?, zona=?, estado=?, observacion=? WHERE id=?')->execute([$codigo, $zona, $estado, $obs !== '' ? $obs : null, $id]);
        } else {
            $pdo->prepare('INSERT INTO ubicaciones (codigo, zona, estado, observacion) VALUES (?, ?, ?, ?)')->execute([$codigo, $zona, $estado, $obs !== '' ? $obs : null]);
        }
        audit_log($pdo, 'UBICACION_GUARDAR', 'Codigo: ' . $codigo, (int)$u['id']);
        json_response(['ok' => true, 'message' => 'Ubicacion guardada.']);
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM ubicaciones WHERE id=?')->execute([$id]);
        audit_log($pdo, 'UBICACION_ELIMINAR', 'ID: ' . $id, (int)$u['id']);
        json_response(['ok' => true, 'message' => 'Ubicacion eliminada.']);
    }

    json_response(['ok' => false, 'message' => 'Accion no soportada.'], 422);
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => 'Error en ubicaciones.', 'error' => $e->getMessage()], 500);
}
