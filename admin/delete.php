<?php
require_once __DIR__ . '/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $products = load_products();
    $products = array_filter($products, function ($p) {
        return $p['id'] !== $_POST['id'];
    });
    save_products($products);
    header('Location: index.php?ok=deleted');
    exit;
}

header('Location: index.php');
exit;
