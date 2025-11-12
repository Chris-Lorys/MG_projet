<?php
/* ============================================================
   config.php — Configuration globale & helpers
   ============================================================ */

/* ---------- Base de données ---------- */
$DB_HOST = 'localhost';
$DB_NAME = 'move_and_go';
$DB_USER = 'root';
$DB_PASS = 'root';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('DB error: ' . htmlspecialchars($e->getMessage()));
}

/* ---------- Session ---------- */
if (session_status() === PHP_SESSION_NONE) {
    @session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ---------- CSRF ---------- */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}
function check_csrf() {
    if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(400);
        die('CSRF invalide');
    }
}

/* ============================================================
   Helpers d’URL — fonctionnement en sous-dossier inclus
   ============================================================ */
// Normalise les chemins Windows \ -> /
$__DOC_ROOT = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$__APP_ROOT = str_replace('\\', '/', realpath(__DIR__));

// Chemin web de base du projet, ex: "/MoveAndGo_project_v2.2"
$BASE_URL = rtrim('/' . ltrim(str_replace($__DOC_ROOT, '', $__APP_ROOT), '/'), '/');

function url($path) {
    // URL interne (page)
    global $BASE_URL;
    return $BASE_URL . '/' . ltrim($path, '/');
}

function asset($path) {
    // Fichiers statiques (css/js/img)
    global $BASE_URL;
    return $BASE_URL . '/' . ltrim($path, '/');
}
/* ============================================================
   Auth helpers
   ============================================================ */
function is_logged_in() {
    return !empty($_SESSION['user']);
}

function current_user() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function require_login() {
    if (!is_logged_in()) {
        $redir = urlencode(parse_url(current_url(), PHP_URL_PATH) . ((isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
        header('Location: ' . url('auth/login.php') . '?redirect=' . $redir);
        exit;
    }
}

function require_role($role) {
    require_login();
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== $role) {
        $_SESSION['flash_error'] = "Accès refusé.";
        header('Location: ' . url('index.php'));
        exit;
    }
}
?>
