<?php
// include/header_client.php — Header dédié aux pages CLIENT
require_once __DIR__ . '/../config.php';

$u = current_user();
if (!$u || ($u['role'] ?? '') !== 'client') {
  header('Location: ' . url('auth/login.php'));
  exit;
}

// Affichages
$displayName  = trim(($u['name'] ?? '') ?: (($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')));
$displayEmail = $u['email'] ?? '';
$displayId    = (int)($u['id'] ?? 0);

// Initiales pour l’avatar
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
  <title>Move & Go — Espace client</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
  <link rel="icon" href="<?= asset('assets/img/logo.png') ?>" type="image/png">
  <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
  <link rel="icon" href="<?= asset('assets/img/logo.png') ?>" type="image/png">

  <style>
  <main style="padding-top: 64px;"> /* Topbar identique à l’initial + verre givré */ .client-topbar{ position: fixed; /* au lieu de sticky */ top: 0; left: 0; right: 0; z-index: 1030; background: #ffffff; /* couleur pleine */ /* backdrop-filter: blur(8px); <- SUPPRIMER */ border-bottom: 1px solid rgba(0,0,0,.06); box-shadow: 0 6px 18px rgba(0,0,0,.06); }
    .client-topbar .brand{font-weight:800;color:#103a40;letter-spacing:.2px}
    .hamburger-btn{border:1px solid rgba(0,0,0,.15);border-radius:.7rem;background:rgba(255,255,255,.65);padding:.45rem .7rem}
    .hamburger-btn:focus{box-shadow:0 0 0 .2rem rgba(17,138,150,.15)}

    /* Offcanvas */
    .offcanvas-client .user-name{font-weight:800;color:#0b2f37}
    .offcanvas-client .muted{color:#2e5b61}
    .offcanvas-client .list-group-item{border:none;border-radius:.7rem;margin-bottom:.35rem}
    .offcanvas-client .list-group-item:hover{background:rgba(16,58,64,.08)}
    .btn-logout{font-weight:700;border-radius:.8rem;border:1px solid #b42b2b;color:#b42b2b}
    .btn-logout:hover{background:#b42b2b;color:#fff}

    /* Avatar initiales */
    .avatar{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;
      font-weight:800;letter-spacing:.5px;color:#0b2f37;border:1px solid rgba(0,0,0,.08);
      background: radial-gradient(circle at 30% 30%, #ffffff, #bfe7e6 70%, #9ed8d7 100%);
      box-shadow:0 8px 16px rgba(0,0,0,.08)}
    /* Toggle clair/sombre */
    .theme-toggle{border:none;background:transparent;padding:.25rem .5rem;border-radius:.6rem}
    .theme-toggle:hover{background:rgba(0,0,0,.06)}
    /* Mode sombre du panneau */
    .theme-dark .offcanvas-client{background:#0f2c31;color:#e9f3f3}
    .theme-dark .offcanvas-client .user-name{color:#e9f3f3}
    .theme-dark .offcanvas-client .muted{color:#b7d2d6}
    .theme-dark .offcanvas-client .list-group-item{background:rgba(255,255,255,.05);color:#e9f3f3}
    .theme-dark .offcanvas-client .list-group-item:hover{background:rgba(255,255,255,.12)}
    .theme-dark .btn-logout{border-color:#ff7777;color:#ff7777}
    .theme-dark .btn-logout:hover{background:#ff7777;color:#092227}
  </style>
</head>

<body class="bg-surface">

<header class="client-topbar py-3">
 <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('client/my_moves.php') ?>">
      <img src="<?= asset('assets/img/logo.png') ?>" alt="Move & Go" width="100" height="100">   <!-- ✔ -->
      <span class="brand fs-5">Move&Go</span>
    </a>

    <div class="d-flex align-items-center gap-2">
     

      <!-- Hamburger -->
      <button class="hamburger-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#clientMenu" aria-controls="clientMenu" aria-label="Menu">
        <span style="display:block;width:22px;height:2px;background:#0b2f37"></span>
        <span style="display:block;width:22px;height:2px;background:#0b2f37;margin-top:4px"></span>
        <span style="display:block;width:22px;height:2px;background:#0b2f37;margin-top:4px"></span>
      </button>
    </div>
  </div>
</header>

<!-- Panneau latéral -->
<div class="offcanvas offcanvas-end offcanvas-client" tabindex="-1" id="clientMenu" aria-labelledby="clientMenuLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="clientMenuLabel">Mon compte</h5>
    <div class="d-flex align-items-center gap-2">
     
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>
  </div>

  <div class="offcanvas-body">
    <!-- Infos utilisateur -->
    <div class="p-3 mb-3 rounded-3" style="background:rgba(255,255,255,.65);border:1px solid rgba(0,0,0,.08)">
      <div class="d-flex align-items-center gap-2">
        <div>
          <div class="user-name"><?= htmlspecialchars($displayName ?: 'Client') ?></div>
          <div class="muted small">ID : <?= $displayId ?></div>
          <?php if ($displayEmail): ?><div class="muted small"><?= htmlspecialchars($displayEmail) ?></div><?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Raccourcis -->
    <div class="list-group mb-4">
      <a href="<?= url('client/my_moves.php') ?>" class="list-group-item list-group-item-action">Mes annonces</a>
      <a href="<?= url('client/create_move.php') ?>" class="list-group-item list-group-item-action">Créer une annonce</a>
    </div>

    <!-- Déconnexion -->
   <a href="<?= url('auth/logout.php') ?>" class="btn btn-logout w-100">Se déconnecter</a>
  </div>
</div>

<main>
