<?php
require_once __DIR__ . '/functions.php';
require_login();

$products = load_products();
$editing = null;
if (!empty($_GET['id'])) {
    foreach ($products as $p) {
        if ($p['id'] === $_GET['id']) { $editing = $p; break; }
    }
}
$isEdit = $editing !== null;
$title = $isEdit ? 'Modifier le produit' : 'Ajouter un produit';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — Administration Big2Big</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="admin-header">
    <div class="admin-header__brand">BIG<span>2</span>BIG <small>Administration</small></div>
    <nav class="admin-header__nav">
        <a href="../produits.html" target="_blank">Voir la page produits ↗</a>
        <a href="logout.php">Se déconnecter</a>
    </nav>
</header>

<main class="admin-main admin-main--narrow">
    <a href="index.php" class="back-link">← Retour à la liste</a>
    <h1><?= htmlspecialchars($title) ?></h1>

    <form method="post" action="save.php" enctype="multipart/form-data" class="product-form">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($editing['id']) ?>">
        <?php endif; ?>

        <label for="name">Nom du produit *</label>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($editing['name'] ?? '') ?>" placeholder="Ex : Escalope Viennoise Poulet">

        <label for="badge">Marque</label>
        <input type="text" id="badge" name="badge" value="<?= htmlspecialchars($editing['badge'] ?? '') ?>" placeholder="Ex : The Farmer">

        <label for="category">Catégorie *</label>
        <select id="category" name="category" required>
            <?php foreach ($CATEGORIES as $key => $label): ?>
                <option value="<?= htmlspecialchars($key) ?>" <?= (($editing['category'] ?? '') === $key) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="meta">Format / conditionnement</label>
        <input type="text" id="meta" name="meta" value="<?= htmlspecialchars($editing['meta'] ?? '') ?>" placeholder="Ex : 800g · Halal">

        <label for="image">Photo du produit <?= $isEdit ? '(laisser vide pour garder la photo actuelle)' : '' ?></label>
        <?php if ($isEdit && !empty($editing['image'])): ?>
            <div class="current-file">
                <img src="../<?= htmlspecialchars($editing['image']) ?>" alt="">
                <span>Photo actuelle</span>
            </div>
        <?php endif; ?>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">

        <label for="fiche">Fiche technique (PDF) <?= $isEdit ? '(laisser vide pour garder la fiche actuelle)' : '' ?></label>
        <?php if ($isEdit && !empty($editing['fiche'])): ?>
            <div class="current-file">
                <span>📄 <a href="../<?= htmlspecialchars($editing['fiche']) ?>" target="_blank">Fiche actuelle</a></span>
            </div>
        <?php endif; ?>
        <input type="file" id="fiche" name="fiche" accept=".pdf">

        <div class="form-actions">
            <button type="submit" class="btn"><?= $isEdit ? 'Enregistrer les modifications' : 'Ajouter le produit' ?></button>
            <a href="index.php" class="btn btn--outline">Annuler</a>
        </div>
    </form>
</main>
</body>
</html>
