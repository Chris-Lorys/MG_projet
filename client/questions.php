<?php
// client/questions.php — Chat client ↔ déménageur pour une offre
require __DIR__ . '/../config.php';

$u = current_user();
if (!$u || ($u['role'] ?? '') !== 'client') {
    header('Location: ' . url('auth/login.php'));
    exit;
}
$clientId = (int)$u['id'];

// 1) Récupération et vérification de l'offre
$offerId = (int)($_GET['offer_id'] ?? 0);
if ($offerId <= 0) {
    header('Location: ' . url('client/my_moves.php'));
    exit;
}

$st = $pdo->prepare("
    SELECT 
      o.id, o.move_id, o.mover_id, o.price,
      m.title, m.city_from, m.city_to, m.client_id,
      u.prenom, u.nom
    FROM offers o
    JOIN moves m ON m.id = o.move_id
    JOIN users u ON u.id = o.mover_id
    WHERE o.id = ? AND m.client_id = ?
");
$st->execute([$offerId, $clientId]);
$offer = $st->fetch(PDO::FETCH_ASSOC);

if (!$offer) {
    header('Location: ' . url('client/my_moves.php'));
    exit;
}

$moveId    = (int)$offer['move_id'];
$moverId   = (int)$offer['mover_id'];
$moverName = trim(($offer['prenom'] ?? '') . ' ' . ($offer['nom'] ?? ''));

// 2) Traitement envoi message (POST)
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (function_exists('check_csrf')) check_csrf();

    $body = trim($_POST['message'] ?? '');
    if ($body === '') {
        $error = "Le message ne peut pas être vide.";
    } else {
        $ins = $pdo->prepare("
            INSERT INTO move_messages (offer_id, sender_role, sender_id, body, created_at)
            VALUES (?, 'client', ?, ?, NOW())
        ");
        $ins->execute([$offerId, $clientId, $body]);
    }
}

// 3) Récupération historique messages
$msgSt = $pdo->prepare("
    SELECT sender_role, sender_id, body, created_at
    FROM move_messages
    WHERE offer_id = ?
    ORDER BY created_at ASC, id ASC
");
$msgSt->execute([$offerId]);
$messages = $msgSt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../include/header_client.php';
?>

<div class="container container-narrow py-4">
  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Discussion avec le déménageur</div>
    <a class="btn btn-outline-secondary" href="<?= url('client/move_detail.php?id=' . (int)$moveId) ?>">← Retour à l’annonce</a>
  </div>

  <!-- Rappel de l'annonce -->
  <div class="card-move mb-3">
    <div class="fw-semibold mb-1"><?= htmlspecialchars($offer['title']) ?></div>
    <div class="small-muted mb-1">
      <?= htmlspecialchars($offer['city_from'] . ' → ' . $offer['city_to']) ?>
    </div>
    <div class="small-muted">Déménageur : <strong><?= htmlspecialchars($moverName) ?></strong></div>
    <div class="small-muted">Prix proposé : <strong><?= number_format((float)$offer['price'], 0, ',', ' ') ?> €</strong></div>
  </div>

  <!-- Historique -->
  <div class="card-move mb-3" style="max-height: 380px; overflow-y: auto;">
    <?php if (!$messages): ?>
      <div class="small-muted">Pas encore de messages.</div>
    <?php else: ?>
      <?php foreach ($messages as $m): ?>
        <?php
          $isMe  = ($m['sender_role'] === 'client' && (int)$m['sender_id'] === $clientId);
          $who   = $isMe ? 'Vous' : $moverName;
          $align = $isMe ? 'text-end' : 'text-start';
          $bg    = $isMe ? 'background:#0b7076;color:#fff;' : 'background:#f3f6f7;';
        ?>
        <div class="mb-2 <?= $align ?>">
          <div class="small-muted mb-1"><?= htmlspecialchars($who) ?> — <?= htmlspecialchars($m['created_at']) ?></div>
          <div class="d-inline-block px-3 py-2 rounded-3" style="<?= $bg ?> max-width: 80%;">
            <?= nl2br(htmlspecialchars($m['body'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Formulaire d'envoi -->
  <form method="post" class="card-move">
    <?php if (function_exists('csrf_field')) csrf_field(); ?>
    <div class="mb-3">
      <label class="form-label fw-semibold">Nouveau message</label>
      <textarea name="message" rows="3" class="form-control"
                placeholder="Écrivez votre message au déménageur..."></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Envoyer</button>
  </form>
</div>

<?php require __DIR__ . '/../include/footer.php'; ?>
