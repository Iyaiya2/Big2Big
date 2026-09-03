<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Méthode non autorisée.'], 405);
}

$in = json_input();
$name     = trim((string)($in['name'] ?? ''));
$phone    = trim((string)($in['phone'] ?? ''));
$email    = trim(strtolower((string)($in['email'] ?? '')));
$password = (string)($in['password'] ?? '');

if ($name === '' || $phone === '' || $email === '' || $password === '') {
    respond(['error' => 'Tous les champs sont obligatoires.'], 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(['error' => 'Adresse email invalide.'], 400);
}
if (strlen($password) < 6) {
    respond(['error' => 'Le mot de passe doit contenir au moins 6 caractères.'], 400);
}

$pdo = get_db();

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    respond(['error' => 'Un compte existe déjà avec cet email. Connectez-vous.'], 409);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (name, phone, email, password_hash, created_at) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$name, $phone, $email, $hash, date('c')]);

$_SESSION['user_id']    = $pdo->lastInsertId();
$_SESSION['user_name']  = $name;
$_SESSION['user_email'] = $email;
$_SESSION['user_phone'] = $phone;

respond(['success' => true, 'user' => ['name' => $name, 'email' => $email, 'phone' => $phone]]);
