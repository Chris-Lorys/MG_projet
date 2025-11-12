<?php
// client/move_toggle.php
require __DIR__ . '/../config.php';   // ⚠️ pas header_client.php (pour éviter d'envoyer du HTML)
// Helpers attendus : current_user(), url(), $pdo (PDO)

$u = current_user();
if (!$u || (($u['role'] ?? '') !== 'client')) {
  header('Location: ' . url('auth/login.php'));
  exit;
}

$moveId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($moveId <= 0) {
  header('Location: ' . url('client/my_moves.php?toggle=invalid'));
  exit;
}

// 1) Vérifier que l'annonce existe et appartient au client
$stmt = $pdo->prepare("SELECT COUNT(*) FROM moves WHERE id = ? AND client_id = ?");
$stmt->execute([$moveId, (int)$u['id']]);
$exists = (int)$stmt->fetchColumn();   // ✅ on appelle fetchColumn() sur $stmt, pas sur $pdo

if ($exists === 0) {
  header('Location: ' . url('client/my_moves.php?toggle=notfound'));
  exit;
}

// 2) Récupérer l'état actuel
$stmt = $pdo->prepare("SELECT is_active FROM moves WHERE id = ? AND client_id = ?");
$stmt->execute([$moveId, (int)$u['id']]);
$current = (int)$stmt->fetchColumn();
$new = $current ? 0 : 1;   // toggle

// 3) Mettre à jour
$stmt = $pdo->prepare("UPDATE moves SET is_active = ? WHERE id = ? AND client_id = ?");
$stmt->execute([$new, $moveId, (int)$u['id']]);

// 4) Rediriger
header('Location: ' . url('client/my_moves.php?toggle=' . ($new ? 'on' : 'off')));
exit;
