<?php
// include/header_mover.php — Header dédié aux pages DÉMÉNAGEUR
require_once __DIR__ . '/../config.php';

$u = current_user();
if (!$u || ($u['role'] ?? '') !== 'demenageur') {
  header('Location: ' . url('auth/login.php'));
  exit;
}

// Affichages
$displayName  = trim(($u['name'] ?? '') ?: (($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')));
$displayEmail = $u['email'] ?? '';
$displayId    = (int)($u['id'] ?? 0);

// Initiales (si tu veux afficher un avatar plus tard)
function mg_initials($name, $prenom = null, $nom = null){
  $src = trim($name ?: trim(($prenom ?? '').' '.($nom ?? '')));
  $parts = preg_split('/\s+/', $src);
  $ini = '';
  foreach ($parts as $p) { if ($p !== '') { $ini .= mb_strtoupper(mb_substr($p,0,1)); } }
  return mb_substr($ini,0,2) ?: 'MG';
}
$initials = mg_initials($displayName, $u['prenom'] ?? null, $u['nom'] ?? null);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Move & Go — Espace déménageur</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
  <link rel="icon" href="<?= asset('assets/img/logo.png') ?>" type="image/png">

  
   
    
 
</head>

<body class="bg-surface">

<header class="mover-topbar py-3">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand d-flex align-items-center gap-2" href="">
      <img src="<?= asset('assets/img/logo.png') ?>" alt="Move & Go" width="150" height="150">
     
    </a>

    <div class="d-flex align-items-center gap-2">
      <!-- Hamburger -->
      <button class="hamburger-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#moverMenu" aria-controls="moverMenu" aria-label="Menu">
        <span style="display:block;width:22px;height:2px;background:#0b2f37"></span>
        <span style="display:block;width:22px;height:2px;background:#0b2f37;margin-top:4px"></span>
        <span style="display:block;width:22px;height:2px;background:#0b2f37;margin-top:4px"></span>
      </button>
    </div>
  </div>
</header>

<!-- Panneau latéral -->
<div class="offcanvas offcanvas-end offcanvas-mover" tabindex="-1" id="moverMenu" aria-labelledby="moverMenuLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="moverMenuLabel">Mon compte</h5>
    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>
  </div>

  <div class="offcanvas-body">
    <!-- Infos utilisateur -->
    <div class="p-3 mb-3 rounded-3" style="background:rgba(255,255,255,.65);border:1px solid rgba(0,0,0,.08)">
      <div class="d-flex align-items-center gap-2">
        <div>
          <div class="user-name"><?= htmlspecialchars($displayName ?: 'Déménageur') ?></div>
          <div class="muted small">ID : <?= $displayId ?></div>
          <?php if ($displayEmail): ?><div class="muted small"><?= htmlspecialchars($displayEmail) ?></div><?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Raccourcis -->
    <div class="list-group mb-4">
      <a href="<?= url('mover/dashboard.php') ?>" class="list-group-item list-group-item-action">Dashboard</a>
      <a href="<?= url('mover/my_offers.php') ?>" class="list-group-item list-group-item-action">Mes offres</a>
    </div>

    <!-- Déconnexion -->
    <a href="<?= url('auth/logout.php') ?>" class="btn btn-logout w-100">Se déconnecter</a>
  </div>
</div>

<main>
