<?php
// === auth/logout.php ===
// Déconnexion utilisateur

require __DIR__ . '/../config.php';

// Si une session est active, on la détruit
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Suppression des variables de session
$_SESSION = [];

// Suppression du cookie de session (si défini)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruction complète de la session
session_destroy();

// Redirection vers la page de login avec message
header('Location: ' . url('auth/login.php?msg=logout_success'));
exit;
