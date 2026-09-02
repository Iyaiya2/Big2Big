<?php
require_once __DIR__ . '/functions.php';

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['big2big_admin'] = true;
        header('Location: index.php');
        exit;
    } else {
        $loginError = 'Mot de passe incorrect.';
    }
}

if (!is_logged_in()) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connexion — Administration Big2Big</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body class="login-page">
        <form class="login-box" method="post">
            <div class="login-box__logo">BIG<span>2</span>BIG</div>
            <h1>Espace administrateur</h1>
            <p>Connectez-vous pour gérer les produits du catalogue.</p>
            <?php if ($loginError): ?><div class="alert alert--error"><?= htmlspecialchars($loginError) ?></div><?php endif; ?>
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required autofocus>
            <button type="submit" class="btn">Se connecter</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

$products = load_products();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration — Nos Produits — Big2Big</title>
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

<main class="admin-main">

    <?php if (!empty($_GET['ok'])): ?>
        <div class="alert alert--ok">
            <?php
            $messages = [
                'added'   => 'Produit ajouté avec succès.',
                'updated' => 'Produit mis à jour avec succès.',
                'deleted' => 'Produit supprimé.',
            ];
            echo htmlspecialchars($messages[$_GET['ok']] ?? 'Opération réussie.');
            ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert--error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="admin-toolbar">
        <h1>Produits <span class="count"><?= count($products) ?></span></h1>
        <a href="form.php" class="btn">+ Ajouter un produit</a>
    </div>

    <div class="admin-grid">
        <?php if (empty($products)): ?>
            <p class="empty-msg">Aucun produit pour le moment. Cliquez sur "Ajouter un produit" pour commencer.</p>
        <?php endif; ?>
        <?php foreach ($products as $p): ?>
            <div class="admin-card">
                <div class="admin-card__img">
                    <?php if (!empty($p['image'])): ?>
                        <img src="../<?= htmlspecialchars($p['image']) ?>" alt="">
                    <?php else: ?>
                        <div class="admin-card__placeholder">🛒</div>
                    <?php endif; ?>
                </div>
                <div class="admin-card__body">
                    <span class="admin-card__cat"><?= htmlspecialchars($p['cat_label'] ?? $p['category'] ?? '') ?></span>
                    <h3><?= htmlspecialchars($p['name'] ?? '') ?></h3>
                    <span class="admin-card__meta"><?= htmlspecialchars($p['meta'] ?? '') ?></span>
                    <span class="admin-card__fiche <?= empty($p['fiche']) ? 'is-missing' : '' ?>">
                        <?= empty($p['fiche']) ? '⚠ Pas de fiche technique' : '📄 Fiche technique jointe' ?>
                    </span>
                </div>
                <div class="admin-card__actions">
                    <a href="form.php?id=<?= urlencode($p['id']) ?>" class="btn btn--sm">Modifier</a>
                    <form method="post" action="delete.php" onsubmit="return confirm('Supprimer ce produit définitivement ?');">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                        <button type="submit" class="btn btn--sm btn--danger">Supprimer</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
