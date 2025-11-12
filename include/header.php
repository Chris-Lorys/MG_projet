<?php
require_once __DIR__ . '/../config.php';
// Pas de echo / HTML ici, ne pas fermer le tag PHP avec "?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Move & Go</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Style principal (pas de slash initial) -->
  <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">

  <!-- Favicon -->
  <link rel="icon" href="<?= asset('assets/img/logo.png') ?>" type="image/png">

  <?php /* 
  Optionnel : si tu veux forcer une base pour tous les liens relatifs
  <base href="<?= rtrim(url(''), '/') ?>/">
  */ ?>
</head>
<body class="bg-surface">

<header class="navbar navbar-expand-lg navbar-light bg-transparent pt-3">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('index.php') ?>">
      <img src="<?= asset('assets/img/logo.png') ?>" alt="Move & Go" width="150" height="150">
      <span class="fw-bold fs-5">Move&Go</span>
    </a>

    <?php if (empty($HIDE_PUBLIC_NAV)): ?>
      <nav class="d-flex gap-4">
        <a class="nav-link fw-semibold" href="<?= url('index.php') ?>">Accueil</a>
        <a class="nav-link fw-semibold" href="<?= url('about.php') ?>">À propos</a>
        <a class="nav-link fw-semibold" href="<?= url('auth/login.php') ?>">Connexion/Inscription</a>
      </nav>
    <?php endif; ?>
  </div>
</header>

<main>
