<?php
require_once __DIR__ . '/helpers.php';

$user = auth_user();
if (!$user) {
    json_response(['ok' => false, 'message' => 'No autenticado.'], 401);
}

json_response(['ok' => true, 'data' => $user]);
