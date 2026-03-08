<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

$u = require_roles(['ADMIN', 'SUPERADMIN']);
$action = strtolower(trim($_REQUEST['action'] ?? 'list'));

try {
    if ($action === 'list') {
        $cats = $pdo->query('SELECT id, nombre, valor_hora, activo FROM categorias_vehiculo ORDER BY nombre')->fetchAll();
        $marcas = $pdo->query('SELECT id, nombre, activo FROM marcas_vehiculo ORDER BY nombre')->fetchAll();
        $modelos = $pdo->query('SELECT mo.id, mo.nombre, mo.activo, ma.nombre marca, c.nombre categoria, mo.marca_id, mo.categoria_id FROM modelos_vehiculo mo INNER JOIN marcas_vehiculo ma ON ma.id=mo.marca_id LEFT JOIN categorias_vehiculo c ON c.id=mo.categoria_id ORDER BY ma.nombre, mo.nombre')->fetchAll();
        json_response(['ok' => true, 'data' => ['categorias' => $cats, 'marcas' => $marcas, 'modelos' => $modelos]]);
    }

    if ($action === 'save_categoria') {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
        $valor = (float)($_POST['valor_hora'] ?? 0);
        $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;
        if ($nombre === '' || $valor <= 0) {
            json_response(['ok' => false, 'message' => 'Nombre y valor/hora son requeridos.'], 422);
        }
        if ($id > 0) {
            $q = $pdo->prepare('UPDATE categorias_vehiculo SET nombre=?, valor_hora=?, activo=? WHERE id=?');
            $q->execute([$nombre, $valor, $activo, $id]);
        } else {
            $q = $pdo->prepare('INSERT INTO categorias_vehiculo (nombre, valor_hora, activo) VALUES (?, ?, ?)');
            $q->execute([$nombre, $valor, $activo]);
        }
        audit_log($pdo, 'CATALOGO_CATEGORIA_GUARDAR', 'Categoria: ' . $nombre, (int)$u['id']);
        json_response(['ok' => true, 'message' => 'Categoria guardada.']);
    }

    if ($action === 'delete_categoria') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM categorias_vehiculo WHERE id=?')->execute([$id]);
        audit_log($pdo, 'CATALOGO_CATEGORIA_ELIMINAR', 'ID: ' . $id, (int)$u['id']);
        json_response(['ok' => true, 'message' => 'Categoria eliminada.']);
    }

    if ($action === 'save_marca') {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
        $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;
        if ($nombre === '') {
            json_response(['ok' => false, 'message' => 'Nombre de marca requerido.'], 422);
        }
        if ($id > 0) {
            $pdo->prepare('UPDATE marcas_vehiculo SET nombre=?, activo=? WHERE id=?')->execute([$nombre, $activo, $id]);
        } else {
            $pdo->prepare('INSERT INTO marcas_vehiculo (nombre, activo) VALUES (?, ?)')->execute([$nombre, $activo]);
        }
        audit_log($pdo, 'CATALOGO_MARCA_GUARDAR', 'Marca: ' . $nombre, (int)$u['id']);
        json_response(['ok' => true, 'message' => 'Marca guardada.']);
    }

    if ($action === 'delete_marca') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM marcas_vehiculo WHERE id=?')->execute([$id]);
        audit_log($pdo, 'CATALOGO_MARCA_ELIMINAR', 'ID: ' . $id, (int)$u['id']);
        json_response(['ok' => true, 'message' => 'Marca eliminada.']);
    }

    if ($action === 'save_modelo') {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
        $marcaId = (int)($_POST['marca_id'] ?? 0);
        $categoriaId = ($_POST['categoria_id'] ?? '') !== '' ? (int)$_POST['categoria_id'] : null;
        $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;
        if ($nombre === '' || $marcaId <= 0) {
            json_response(['ok' => false, 'message' => 'Modelo y marca son obligatorios.'], 422);
        }
        if ($id > 0) {
            $pdo->prepare('UPDATE modelos_vehiculo SET nombre=?, marca_id=?, categoria_id=?, activo=? WHERE id=?')->execute([$nombre, $marcaId, $categoriaId, $activo, $id]);
        } else {
            $pdo->prepare('INSERT INTO modelos_vehiculo (nombre, marca_id, categoria_id, activo) VALUES (?, ?, ?, ?)')->execute([$nombre, $marcaId, $categoriaId, $activo]);
        }
        audit_log($pdo, 'CATALOGO_MODELO_GUARDAR', 'Modelo: ' . $nombre, (int)$u['id']);
        json_response(['ok' => true, 'message' => 'Modelo guardado.']);
    }

    if ($action === 'delete_modelo') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM modelos_vehiculo WHERE id=?')->execute([$id]);
        audit_log($pdo, 'CATALOGO_MODELO_ELIMINAR', 'ID: ' . $id, (int)$u['id']);
        json_response(['ok' => true, 'message' => 'Modelo eliminado.']);
    }

    json_response(['ok' => false, 'message' => 'Accion no soportada.'], 422);
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => 'Error en catalogo.', 'error' => $e->getMessage()], 500);
}
