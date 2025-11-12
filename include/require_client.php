<?php
// /include/require_client.php

// Monte d'un niveau depuis /include vers la racine du projet
$root = dirname(__DIR__);

// Charge la conf + helpers ($pdo, url(), current_user(), etc.)
require_once $root . '/config.php';

// Au cas où la session n'est pas démarrée par config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Protection : uniquement pour les clients
$u = current_user();
if (!$u || ($u['role'] ?? '') !== 'client') {
    $_SESSION['flash_error'] = "Vous devez être connecté(e) en tant que client pour accéder à cette page.";
    $return = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: ' . url('auth/login.php?redirect=' . rawurlencode($return)));
    exit;
}
