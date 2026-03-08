<?php
const TOTAL_CUPOS = 50;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function json_response(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function normalizar_placa(string $placa): string {
    return strtoupper(trim($placa));
}

function placa_valida(string $placa): bool {
    return (bool) preg_match('/^[A-Z0-9-]{5,10}$/', $placa);
}

function horas_cobrables_desde_minutos(int $minutos): int {
    if ($minutos <= 0) {
        return 0;
    }
    return (int) ceil($minutos / 60);
}

function minutos_entre_fechas(DateTime $inicio, DateTime $fin): int {
    $segundos = $fin->getTimestamp() - $inicio->getTimestamp();
    if ($segundos <= 0) {
        return 0;
    }
    return (int) ceil($segundos / 60);
}

function generar_codigo(string $prefijo = '', int $longitud = 8): string {
    $pool = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $out = '';
    for ($i = 0; $i < $longitud; $i++) {
        $out .= $pool[random_int(0, strlen($pool) - 1)];
    }
    return $prefijo . $out;
}

function auth_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_auth(): array {
    $u = auth_user();
    if (!$u) {
        json_response(['ok' => false, 'message' => 'No autenticado.'], 401);
    }
    return $u;
}

function require_roles(array $roles): array {
    $u = require_auth();
    if (!in_array($u['rol'], $roles, true)) {
        json_response(['ok' => false, 'message' => 'No autorizado para esta accion.'], 403);
    }
    return $u;
}

function audit_log(PDO $pdo, string $accion, string $detalle = '', ?int $usuarioId = null): void {
    try {
        $userId = $usuarioId;
        if ($userId === null) {
            $user = auth_user();
            $userId = $user ? (int) $user['id'] : null;
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $q = $pdo->prepare('INSERT INTO auditoria (usuario_id, accion, detalle, ip) VALUES (?, ?, ?, ?)');
        $q->execute([$userId, $accion, $detalle !== '' ? $detalle : null, $ip]);
    } catch (Throwable $e) {
    }
}

function obtener_config(PDO $pdo, string $clave, ?string $default = null): ?string {
    try {
        $q = $pdo->prepare('SELECT valor FROM configuracion WHERE clave = ? LIMIT 1');
        $q->execute([$clave]);
        $row = $q->fetch();
        if ($row && array_key_exists('valor', $row)) {
            return (string)$row['valor'];
        }
    } catch (Throwable $e) {
    }
    return $default;
}

function obtener_modo_cobro(PDO $pdo): string {
    $modo = strtoupper((string)obtener_config($pdo, 'modo_cobro', 'POR_MINUTO'));
    return in_array($modo, ['POR_HORA', 'POR_MINUTO'], true) ? $modo : 'POR_MINUTO';
}

function obtener_minutos_gracia(PDO $pdo): int {
    $v = (int)obtener_config($pdo, 'minutos_gracia', '5');
    if ($v < 0) return 0;
    if ($v > 120) return 120;
    return $v;
}

function calcular_valor_cobro(int $minutosReales, float $tarifaHora, string $modo, int $minutosGracia = 0): array {
    $minutosCobrables = max(0, $minutosReales - max(0, $minutosGracia));

    if ($minutosCobrables <= 0) {
        return [
            'minutos_reales' => $minutosReales,
            'minutos_gracia' => $minutosGracia,
            'minutos_cobrables' => 0,
            'horas_cobrables' => 0,
            'valor_total' => 0,
            'descripcion' => 'Dentro del tiempo de gracia',
        ];
    }

    if ($modo === 'POR_MINUTO') {
        $horasEq = round($minutosCobrables / 60, 2);
        $valor = round(($minutosCobrables / 60) * $tarifaHora, 2);
        return [
            'minutos_reales' => $minutosReales,
            'minutos_gracia' => $minutosGracia,
            'minutos_cobrables' => $minutosCobrables,
            'horas_cobrables' => $horasEq,
            'valor_total' => $valor,
            'descripcion' => 'Cobro proporcional por minuto',
        ];
    }

    $horas = horas_cobrables_desde_minutos($minutosCobrables);
    return [
        'minutos_reales' => $minutosReales,
        'minutos_gracia' => $minutosGracia,
        'minutos_cobrables' => $minutosCobrables,
        'horas_cobrables' => $horas,
        'valor_total' => $horas * $tarifaHora,
        'descripcion' => 'Cobro por hora (redondeo hacia arriba)',
    ];
}
