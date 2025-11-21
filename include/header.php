<?php
require_once __DIR__ . '/../config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Move & Go</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>

<body class="bg-surface">

<header class="mg-header">
  <div class="container d-flex justify-content-between align-items-center mg-header-inner">
    <!-- Logo -->
    <a class="d-flex align-items-center text-decoration-none" href="<?= url('index.php') ?>">
      <img src="<?= asset('assets/img/logo.png') ?>" class="mg-logo" alt="Move & Go Logo">
    </a>

    <?php if (empty($HIDE_PUBLIC_NAV)): ?>

      <!-- Menu DESKTOP (>= md) -->
      <nav class="d-none d-md-flex align-items-center gap-3">
        <a class="mg-nav-link" href="<?= url('index.php') ?>">Accueil</a>
        <a class="mg-nav-link" href="<?= url('about.php') ?>">Infos</a>
        <a class="mg-nav-cta" href="<?= url('auth/login.php') ?>">Connexion</a>
        <a class="mg-nav-cta" href="<?= url('auth/register.php') ?>">Inscription</a>
      </nav>

      <!-- Bouton HAMBURGER (mobile seulement) -->
      <button class="btn mg-nav-toggle d-md-none"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#mgMobileNav"
              aria-controls="mgMobileNav"
              aria-expanded="false"
              aria-label="Ouvrir le menu">
        <i class="bi bi-list"></i>
      </button>

    <?php endif; ?>
  </div>

  <?php if (empty($HIDE_PUBLIC_NAV)): ?>
    <!-- Panneau latéral MOBILE (lié au hamburger) -->
    <div class="collapse d-md-none" id="mgMobileNav">
      <aside class="mg-sidebar">
        <div class="mg-sidebar-header d-flex align-items-center justify-content-between">
          <span class="mg-sidebar-brand">MOVE &amp; GO</span>

          <!-- Bouton fermer : utilise aussi le collapse Bootstrap -->
          <button class="btn mg-sidebar-close"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#mgMobileNav"
                  aria-controls="mgMobileNav"
                  aria-expanded="true"
                  aria-label="Fermer le menu">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <nav class="mg-sidebar-nav mt-3">
          <a class="mg-sidebar-btn" href="<?= url('index.php') ?>">Accueil</a>
          <a class="mg-sidebar-btn" href="<?= url('about.php') ?>">Infos</a>
          <a class="mg-sidebar-btn" href="<?= url('auth/login.php') ?>">Connexion</a>
          <a class="mg-sidebar-btn" href="<?= url('auth/register.php') ?>">Inscription</a>
        </nav>
      </aside>
    </div>
  <?php endif; ?>
</header>

<main>
