<?php
$host = 'localhost';
$db   = 'sistema_parksys';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

date_default_timezone_set('America/Bogota');

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("SET time_zone = '-05:00'");
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => false,
        'message' => 'No fue posible conectar con la base de datos.',
        'error' => $e->getMessage(),
    ]);
    exit;
}