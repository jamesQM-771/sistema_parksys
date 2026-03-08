<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

require_roles(['ADMIN', 'SUPERADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Metodo no permitido'], 405);
}

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    json_response(['ok' => false, 'message' => 'Archivo no recibido.'], 422);
}

$allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];
$mime = mime_content_type($_FILES['logo']['tmp_name']);
if (!isset($allowed[$mime])) {
    json_response(['ok' => false, 'message' => 'Formato no permitido (png/jpg/webp/svg).'], 422);
}

$ext = $allowed[$mime];
$targetDir = realpath(__DIR__ . '/../frontend/assets/img');
if ($targetDir === false) {
    json_response(['ok' => false, 'message' => 'Directorio de imagenes no encontrado.'], 500);
}
$targetFile = $targetDir . DIRECTORY_SEPARATOR . 'logo-custom.' . $ext;

if (!move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
    json_response(['ok' => false, 'message' => 'No se pudo guardar el archivo.'], 500);
}

$logoUrl = 'assets/img/' . basename($targetFile) . '?v=' . time();
$q = $pdo->prepare('INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)');
$q->execute(['logo_url', $logoUrl]);
audit_log($pdo, 'LOGO_UPLOAD', 'Logo actualizado');

json_response(['ok' => true, 'message' => 'Logo actualizado.', 'data' => ['logo_url' => $logoUrl]]);
