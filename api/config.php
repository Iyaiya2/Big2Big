<?php
// =====================================================================
// CONFIGURATION — à adapter si besoin
// =====================================================================

// Adresse email qui recevra les commandes des clients
define('ADMIN_ORDER_EMAIL', 'contact@big2big.fr');

// Adresse "expéditeur" utilisée dans les emails envoyés par le site
// (même boîte que ADMIN_ORDER_EMAIL car tu n'as accès qu'à celle-ci)
define('SITE_FROM_EMAIL', 'contact@big2big.fr');

define('SITE_NAME', 'Big2Big');

// ---- Identifiants SMTP (boîte contact@big2big.fr) ----
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);         // 465 = SSL
define('SMTP_SECURE', 'ssl');
define('SMTP_USER', 'contact@big2big.fr');
define('SMTP_PASS', 'Contact92*!');

// Emplacement du fichier de base de données (comptes clients)
define('DB_PATH', __DIR__ . '/data/users.db');

// =====================================================================
// Session PHP (garde le client connecté 30 jours)
// =====================================================================
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 30,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function respond($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}