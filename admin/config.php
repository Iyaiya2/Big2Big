<?php
// ============================================================
// CONFIGURATION DE L'ESPACE ADMINISTRATEUR
// ============================================================
// Changez simplement le mot de passe ci-dessous (entre guillemets).
// N'utilisez pas d'accents ni de guillemets dans le mot de passe.
$ADMIN_PASSWORD = "Big2Big2026!";

// Chemins (ne pas modifier sauf si vous savez ce que vous faites)
define('ROOT_DIR', dirname(__DIR__));
define('PRODUCTS_FILE', ROOT_DIR . '/products.json');
define('IMG_DIR', ROOT_DIR . '/img/produits');
define('FICHE_DIR', ROOT_DIR . '/fiches');
define('IMG_URL', '/img/produits');
define('FICHE_URL', '/fiches');

define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);   // 5 Mo
define('MAX_FICHE_SIZE', 15 * 1024 * 1024);  // 15 Mo
