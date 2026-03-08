<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';
require_auth();

$action = strtolower(trim($_GET['action'] ?? 'makes'));
$base = 'https://www.carqueryapi.com/api/0.3/';

function decode_carquery_payload(string $raw): ?array {
    $raw = trim($raw);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return $decoded;
    }

    if (preg_match('/^[\w$\.]+\((.*)\);?$/s', $raw, $m)) {
        $decoded = json_decode($m[1], true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    return null;
}

function http_get_carquery(string $url): ?string {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_USERAGENT => 'ParkSys/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (is_string($raw) && $raw !== '' && $status >= 200 && $status < 400) {
            return $raw;
        }
    }

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 12,
            'user_agent' => 'ParkSys/1.0',
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false || $raw === '') {
        return null;
    }

    return $raw;
}

function fetch_carquery(string $url): array {
    $raw = http_get_carquery($url);
    if (!is_string($raw)) {
        return ['ok' => false, 'message' => 'No fue posible conectar con CarQuery API.'];
    }

    $data = decode_carquery_payload($raw);
    if (!is_array($data)) {
        return ['ok' => false, 'message' => 'Respuesta invalida de CarQuery API.'];
    }

    return ['ok' => true, 'data' => $data];
}

function static_makes(): array {
    $brands = [
        'TOYOTA', 'CHEVROLET', 'RENAULT', 'KIA', 'NISSAN', 'MAZDA', 'HYUNDAI', 'SUZUKI', 'YAMAHA',
        'HONDA', 'BMW', 'MERCEDES-BENZ', 'AUDI', 'VOLKSWAGEN', 'FORD', 'MITSUBISHI', 'FIAT',
        'PEUGEOT', 'ISUZU', 'HINO', 'VOLVO', 'SCANIA', 'DUCATI', 'KAWASAKI'
    ];
    $out = [];
    foreach ($brands as $b) {
        $out[] = ['id' => $b, 'nombre' => $b];
    }
    return $out;
}

function static_models_by_make(string $make): array {
    $m = strtoupper(trim($make));
    $map = [
        'TOYOTA' => ['COROLLA', 'YARIS', 'HILUX', 'PRADO', 'FORTUNER'],
        'CHEVROLET' => ['SPARK GT', 'ONIX', 'TRACKER', 'D-MAX'],
        'RENAULT' => ['LOGAN', 'SANDERO', 'DUSTER', 'KWID'],
        'KIA' => ['RIO', 'PICANTO', 'SPORTAGE'],
        'MAZDA' => ['MAZDA 2', 'MAZDA 3', 'CX-30', 'CX-5'],
        'YAMAHA' => ['FZ', 'MT-03', 'NMAX', 'XTZ'],
        'SUZUKI' => ['GN125', 'GSX-R150', 'V-STROM'],
        'HONDA' => ['CB 125F', 'XR 190L', 'CIVIC', 'CR-V'],
    ];

    if (!isset($map[$m])) {
        return [];
    }

    $out = [];
    foreach ($map[$m] as $model) {
        $out[] = ['id' => $model, 'nombre' => $model];
    }
    return $out;
}

if ($action === 'makes') {
    $res = fetch_carquery($base . '?cmd=getMakes&sold_in_us=1');
    if ($res['ok']) {
        $rows = array_map(
            fn($r) => ['id' => strtoupper((string)($r['make_id'] ?? '')), 'nombre' => strtoupper((string)($r['make_display'] ?? ''))],
            $res['data']['Makes'] ?? []
        );
        $rows = array_values(array_filter($rows, fn($x) => $x['nombre'] !== ''));
        if (count($rows) > 0) {
            json_response(['ok' => true, 'data' => $rows, 'source' => 'carquery']);
        }
    }

    $local = $pdo->query('SELECT id, nombre FROM marcas_vehiculo WHERE activo = 1 ORDER BY nombre')->fetchAll();
    $rows = array_map(fn($r) => ['id' => (string)$r['id'], 'nombre' => strtoupper((string)$r['nombre'])], $local);
    if (count($rows) > 0) {
        json_response(['ok' => true, 'data' => $rows, 'source' => 'local', 'message' => 'CarQuery no disponible. Mostrando marcas locales.']);
    }

    json_response(['ok' => true, 'data' => static_makes(), 'source' => 'static', 'message' => 'CarQuery no disponible. Mostrando marcas base.']);
}

if ($action === 'models') {
    $make = trim($_GET['make'] ?? '');
    if ($make === '') {
        json_response(['ok' => false, 'message' => 'Parametro make requerido.'], 422);
    }

    $res = fetch_carquery($base . '?cmd=getModels&make=' . rawurlencode($make));
    if ($res['ok']) {
        $rows = array_map(
            fn($r) => ['id' => strtoupper((string)($r['model_name'] ?? '')), 'nombre' => strtoupper((string)($r['model_name'] ?? ''))],
            $res['data']['Models'] ?? []
        );
        $uniq = [];
        foreach ($rows as $row) {
            if ($row['nombre'] !== '') {
                $uniq[$row['nombre']] = $row;
            }
        }
        $rows = array_values($uniq);
        if (count($rows) > 0) {
            json_response(['ok' => true, 'data' => $rows, 'source' => 'carquery']);
        }
    }

    $q = $pdo->prepare('SELECT id, nombre FROM marcas_vehiculo WHERE UPPER(nombre)=UPPER(?) LIMIT 1');
    $q->execute([$make]);
    $marca = $q->fetch();
    if ($marca) {
        $q2 = $pdo->prepare('SELECT id, nombre FROM modelos_vehiculo WHERE marca_id = ? AND activo = 1 ORDER BY nombre');
        $q2->execute([(int)$marca['id']]);
        $rows = array_map(fn($r) => ['id' => (string)$r['id'], 'nombre' => strtoupper((string)$r['nombre'])], $q2->fetchAll());
        if (count($rows) > 0) {
            json_response(['ok' => true, 'data' => $rows, 'source' => 'local', 'message' => 'CarQuery no disponible. Mostrando modelos locales.']);
        }
    }

    $staticModels = static_models_by_make($make);
    if (count($staticModels) > 0) {
        json_response(['ok' => true, 'data' => $staticModels, 'source' => 'static', 'message' => 'CarQuery no disponible. Mostrando modelos base.']);
    }

    json_response(['ok' => true, 'data' => [], 'source' => 'none', 'message' => 'No se encontraron modelos para esa marca.']);
}

json_response(['ok' => false, 'message' => 'Accion no soportada.'], 422);