<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

// ---- Construction du contenu de l'email ----
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
$bodyText = implode("\n", $lines);
$bodyHtml = '<pre style="font-family: Arial, sans-serif; font-size: 14px;">'
          . htmlspecialchars($bodyText, ENT_QUOTES, 'UTF-8')
          . '</pre>';

$subject = 'Nouvelle commande de ' . $name . ' — ' . SITE_NAME;

// ---- Envoi via PHPMailer / SMTP authentifié ----
$mail = new PHPMailer(true);

try {
    // Config serveur SMTP
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    // Expéditeur : DOIT correspondre exactement au compte SMTP pour aligner SPF/DKIM/DMARC
    $mail->setFrom(SITE_FROM_EMAIL, SITE_NAME);
    $mail->addAddress(ADMIN_ORDER_EMAIL);

    // Reply-To pointant vers le client, pratique pour répondre directement
    if ($email) {
        $mail->addReplyTo($email, $name);
    }

    $mail->Subject = $subject;
    $mail->isHTML(true);
    $mail->Body    = $bodyHtml;
    $mail->AltBody = $bodyText; // version texte brut, recommandée contre le spam

    $mail->send();
} catch (Exception $e) {
    respond(['error' => "La commande n'a pas pu être envoyée par email. Merci de réessayer ou de nous contacter directement."], 500);
}

respond(['success' => true]);