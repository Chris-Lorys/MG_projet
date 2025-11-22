<?php
// mover/question.php — Chat déménageur ↔ client pour une offre
require __DIR__ . '/../include/header_mover.php'; // charge $pdo + vérifie role déménageur

$u = current_user();
$moverId = (int)($u['id'] ?? 0);

// 1) Récupération et vérification de l'offre
$offerId = (int)($_GET['offer_id'] ?? 0);
if ($offerId <= 0) {
    echo '<div class="container py-5"><div class="alert alert-danger">Offre introuvable.</div></div>';
    require __DIR__ . '/../include/footer.php';
    exit;
}

$st = $pdo->prepare("
    SELECT 
      o.id, o.move_id, o.mover_id, o.price,
      m.title, m.city_from, m.city_to,
      u.id AS client_id, u.prenom, u.nom
    FROM offers o
    JOIN moves m ON m.id = o.move_id
    JOIN users u ON u.id = m.client_id
    WHERE o.id = ? AND o.mover_id = ?
");
$st->execute([$offerId, $moverId]);
$offer = $st->fetch(PDO::FETCH_ASSOC);

if (!$offer) {
    echo '<div class="container py-5"><div class="alert alert-danger">Offre introuvable ou non autorisée.</div></div>';
    require __DIR__ . '/../include/footer.php';
    exit;
}

$moveId     = (int)$offer['move_id'];
$clientId   = (int)$offer['client_id'];
$clientName = trim(($offer['prenom'] ?? '') . ' ' . ($offer['nom'] ?? ''));

// 2) Traitement de l'envoi de message (POST)
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (function_exists('check_csrf')) check_csrf();

    $body = trim($_POST['message'] ?? '');
    if ($body === '') {
        $error = "Le message ne peut pas être vide.";
    } else {
        $ins = $pdo->prepare("
            INSERT INTO move_messages (offer_id, sender_role, sender_id, body, created_at)
            VALUES (?, 'mover', ?, ?, NOW())
        ");
        $ins->execute([$offerId, $moverId, $body]);
        // Pas de redirect : on laisse la page se recharger et on verra le message dans la liste
    }
}

// 3) Récupération de l'historique des messages
$msgSt = $pdo->prepare("
    SELECT sender_role, sender_id, body, created_at
    FROM move_messages
    WHERE offer_id = ?
    ORDER BY created_at ASC, id ASC
");
$msgSt->execute([$offerId]);
$messages = $msgSt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container container-narrow py-4">
  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Discussion avec le client</div>
    <a class="btn btn-outline-secondary" href="<?= url('mover/my_offers.php') ?>">← Mes offres</a>
  </div>

  <!-- Rappel de l'annonce -->
  <div class="card-move mb-3">
    <div class="fw-semibold mb-1"><?= htmlspecialchars($offer['title']) ?></div>
    <div class="small-muted mb-1">
      <?= htmlspecialchars($offer['city_from'] . ' → ' . $offer['city_to']) ?>
    </div>
    <div class="small-muted">Client : <strong><?= htmlspecialchars($clientName) ?></strong></div>
    <div class="small-muted">Prix proposé : <strong><?= number_format((float)$offer['price'], 0, ',', ' ') ?> €</strong></div>
  </div>

  <!-- Historique -->
  <div class="card-move mb-3" style="max-height: 380px; overflow-y: auto;">
    <?php if (!$messages): ?>
      <div class="small-muted">Aucun message pour l’instant. Posez votre première question.</div>
    <?php else: ?>
      <?php foreach ($messages as $m): ?>
        <?php
          $isMe   = ($m['sender_role'] === 'mover' && (int)$m['sender_id'] === $moverId);
          $who    = $isMe ? 'Vous' : $clientName;
          $align  = $isMe ? 'text-end' : 'text-start';
          $bg     = $isMe ? 'background:#0b7076;color:#fff;' : 'background:#f3f6f7;';
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

  <!-- Erreur éventuelle -->
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Formulaire d'envoi -->
  <form method="post" class="card-move">
    <?php if (function_exists('csrf_field')) csrf_field(); ?>
    <div class="mb-3">
      <label class="form-label fw-semibold">Nouveau message</label>
      <textarea name="message" rows="3" class="form-control"
                placeholder="Écrivez votre message au client..."></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Envoyer</button>
  </form>
</div>

<?php require __DIR__ . '/../include/footer.php'; ?>
