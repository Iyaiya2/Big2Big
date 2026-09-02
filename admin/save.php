<?php
require_once __DIR__ . '/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
$badge = trim($_POST['badge'] ?? '');
$meta = trim($_POST['meta'] ?? '');
$id = trim($_POST['id'] ?? '');

if ($name === '' || $category === '' || !isset($CATEGORIES[$category])) {
    header('Location: form.php' . ($id ? '?id=' . urlencode($id) : '') . '&error=' . urlencode('Veuillez remplir le nom et choisir une catégorie.'));
    exit;
}

$products = load_products();
$error = '';
$isEdit = $id !== '';

if ($isEdit) {
    $index = null;
    foreach ($products as $i => $p) {
        if ($p['id'] === $id) { $index = $i; break; }
    }
    if ($index === null) {
        header('Location: index.php?error=' . urlencode('Produit introuvable.'));
        exit;
    }
    $product = $products[$index];
} else {
    $slug = slugify($name);
    $baseSlug = $slug;
    $n = 1;
    $existingIds = array_column($products, 'id');
    while (in_array($slug, $existingIds, true)) {
        $slug = $baseSlug . '-' . (++$n);
    }
    $product = ['id' => $slug, 'image' => '', 'fiche' => ''];
}

$product['name'] = $name;
$product['category'] = $category;
$product['cat_label'] = $CATEGORIES[$category];
$product['badge'] = $badge;
$product['meta'] = $meta;

$imgUrl = handle_upload('image', IMG_DIR, IMG_URL, $product['id'], ['jpg', 'jpeg', 'png', 'webp'], MAX_IMAGE_SIZE, $error);
if ($error) {
    header('Location: form.php' . ($isEdit ? '?id=' . urlencode($id) : '') . '&error=' . urlencode($error));
    exit;
}
if ($imgUrl) $product['image'] = ltrim($imgUrl, '/');

$ficheUrl = handle_upload('fiche', FICHE_DIR, FICHE_URL, $product['id'], ['pdf'], MAX_FICHE_SIZE, $error);
if ($error) {
    header('Location: form.php' . ($isEdit ? '?id=' . urlencode($id) : '') . '&error=' . urlencode($error));
    exit;
}
if ($ficheUrl) $product['fiche'] = ltrim($ficheUrl, '/');

if ($isEdit) {
    $products[$index] = $product;
} else {
    $products[] = $product;
}

save_products($products);
header('Location: index.php?ok=' . ($isEdit ? 'updated' : 'added'));
exit;
