<?php
require_once __DIR__ . '/config.php';
session_start();

function is_logged_in() {
    return !empty($_SESSION['big2big_admin']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function load_products() {
    if (!file_exists(PRODUCTS_FILE)) return [];
    $json = file_get_contents(PRODUCTS_FILE);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function save_products($products) {
    file_put_contents(
        PRODUCTS_FILE,
        json_encode(array_values($products), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
}

function slugify($text) {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $text));
    return trim($text, '-') ?: 'produit';
}

// Déplace un fichier uploadé (image ou fiche PDF) et renvoie son URL relative, ou null.
function handle_upload($field, $destDir, $destUrlBase, $slug, $allowedExts, $maxSize, &$error) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // rien d'envoyé
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Erreur lors de l'envoi du fichier.";
        return null;
    }
    if ($file['size'] > $maxSize) {
        $error = "Le fichier est trop volumineux.";
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) {
        $error = "Format de fichier non autorisé (" . implode(', ', $allowedExts) . " uniquement).";
        return null;
    }
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    $filename = $slug . '-' . substr(md5(uniqid('', true)), 0, 6) . '.' . $ext;
    $destPath = $destDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $error = "Impossible d'enregistrer le fichier sur le serveur.";
        return null;
    }
    return $destUrlBase . '/' . $filename;
}

$CATEGORIES = [
    'volaille'   => '🍗 Volaille',
    'boeuf'      => '🥩 Bœuf',
    'kebab'      => '🌯 Kebab',
    'frites'     => '🍟 Frites & Apéritifs',
    'sauces'     => '🥣 Sauces & Condiments',
    'pains'      => '🥖 Pains',
    'fromages'   => '🧀 Fromages',
    'desserts'   => '🍰 Desserts',
    'pizza'      => '🍕 Pizza',
    'condiments' => '🫙 Condiments',
    'entretien'  => '🧴 Entretien',
    'emballage'  => '📦 Emballage',
];
