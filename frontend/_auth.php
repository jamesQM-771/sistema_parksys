<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function page_user() {
    return $_SESSION['user'] ?? null;
}

function require_login_page(): array {
    $u = page_user();
    if (!$u) {
        header('Location: login.php');
        exit;
    }
    return $u;
}

function allow_roles_page(array $roles): array {
    $u = require_login_page();
    if (!in_array($u['rol'], $roles, true)) {
        http_response_code(403);
        echo 'No autorizado';
        exit;
    }
    return $u;
}

function nav_link(string $href, string $label, string $current): string {
    $active = $href === $current ? 'active' : '';
    return '<a href="' . $href . '" class="' . $active . '">' . $label . '</a>';
}
