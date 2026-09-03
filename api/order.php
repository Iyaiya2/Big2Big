<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Méthode non autorisée.'], 405);
}

if (empty($_SESSION['user_id'])) {
    respond(['error' => 'Vous devez être connecté pour valider une commande.'], 401);
}

$in   = json_input();
$cart = $in['cart'] ?? [];

if (!is_array($cart) || count($cart) === 0) {
    respond(['error' => 'Votre panier est vide.'], 400);
}

$name  = $_SESSION['user_name'];
$phone = $_SESSION['user_phone'];
$email = $_SESSION['user_email'];

// ---- Construction de l'email ----
$lines   = [];
$lines[] = 'Nouvelle demande de commande — ' . SITE_NAME;
$lines[] = '';
$lines[] = 'Client   : ' . $name;
$lines[] = 'Téléphone: ' . $phone;
$lines[] = 'Email    : ' . $email;
$lines[] = 'Date     : ' . date('d/m/Y H:i');
$lines[] = '';
$lines[] = 'Produits demandés :';
$lines[] = str_repeat('-', 40);

$itemCount = 0;
foreach ($cart as $item) {
    $itemName = isset($item['name']) ? trim((string)$item['name']) : '';
    if ($itemName === '') continue;
    $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
    if ($qty < 1) $qty = 1;
    $meta = isset($item['meta']) ? trim((string)$item['meta']) : '';
    $lines[] = '- ' . $itemName . '  x' . $qty . ($meta !== '' ? '  (' . $meta . ')' : '');
    $itemCount++;
}

if ($itemCount === 0) {
    respond(['error' => 'Votre panier est vide.'], 400);
}

$lines[] = str_repeat('-', 40);
$body = implode("\n", $lines);

$subject = 'Nouvelle commande de ' . $name . ' — ' . SITE_NAME;

$headers   = [];
$headers[] = 'From: ' . SITE_NAME . ' <' . SITE_FROM_EMAIL . '>';
$headers[] = 'Reply-To: ' . $email;
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$sent = @mail(ADMIN_ORDER_EMAIL, $subject, $body, implode("\r\n", $headers));

if (!$sent) {
    respond(['error' => "La commande n'a pas pu être envoyée par email. Merci de réessayer ou de nous contacter directement."], 500);
}

respond(['success' => true]);
