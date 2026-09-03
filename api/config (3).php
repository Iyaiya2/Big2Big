<?php
// =====================================================================
// CONFIGURATION — à adapter si besoin
// =====================================================================

// Adresse email qui recevra les commandes des clients
define('ADMIN_ORDER_EMAIL', 'contact@big2big.fr');

// Adresse "expéditeur" utilisée dans les emails envoyés par le site
// (idéalement une adresse sur le même nom de domaine que le site,
// ex: noreply@big2big.fr, pour éviter que l'email parte en spam)
define('SITE_FROM_EMAIL', 'noreply@big2big.fr');

define('SITE_NAME', 'Big2Big');

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

// Autorise les appels fetch() en JSON depuis les pages du site
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
