<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Méthode non autorisée.'], 405);
}

$in       = json_input();
$email    = trim(strtolower((string)($in['email'] ?? '')));
$password = (string)($in['password'] ?? '');

if ($email === '' || $password === '') {
    respond(['error' => 'Email et mot de passe requis.'], 400);
}

$pdo  = get_db();
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password_hash'])) {
    respond(['error' => 'Email ou mot de passe incorrect.'], 401);
}

$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_phone'] = $user['phone'];

respond(['success' => true, 'user' => ['name' => $user['name'], 'email' => $user['email'], 'phone' => $user['phone']]]);
