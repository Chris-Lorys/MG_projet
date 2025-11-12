<?php
// mover/offer_new.php — créer / modifier une offre pour une annonce
require __DIR__ . '/../include/header_mover.php'; // Sécurise (role=demenageur) + charge $pdo, helpers

$mover = current_user();
$moverId = (int)($mover['id'] ?? 0);

// 1) Paramètres
$moveId = (int)($_GET['move_id'] ?? $_POST['move_id'] ?? 0);
if ($moveId <= 0) {
  echo '<div class="container py-5"><div class="alert alert-danger">Annonce introuvable.</div></div>';
  require __DIR__ . '/../include/footer.php'; exit;
}

// 2) Récup annonce
$st = $pdo->prepare("
  SELECT m.id, m.title, m.city_from, m.city_to, m.date_start, m.volume_m3, m.is_active,
         u.prenom, u.nom
  FROM moves m
  JOIN users u ON u.id = m.client_id
  WHERE m.id = ?
");
$st->execute([$moveId]);
$mv = $st->fetch(PDO::FETCH_ASSOC);

if (!$mv) {
  echo '<div class="container py-5"><div class="alert alert-danger">Annonce introuvable.</div></div>';
  require __DIR__ . '/../include/footer.php'; exit;
}

// 3) Récup offre existante (si le déménageur a déjà proposé)
$st2 = $pdo->prepare("
  SELECT id, price, message, status
  FROM offers
  WHERE move_id = ? AND mover_id = ?
  LIMIT 1
");
$st2->execute([$moveId, $moverId]);
$existing = $st2->fetch(PDO::FETCH_ASSOC);

// 4) POST -> insert / update
$okMsg = $err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('check_csrf')) { check_csrf(); }

  $price   = (float)($_POST['price'] ?? 0);
  $message = trim((string)($_POST['message'] ?? ''));

  if ($price <= 0) {
    $err = "Le prix proposé doit être un montant positif.";
  }

  if (!$err) {
    try {
      if ($existing) {
        // Mise à jour de l'offre
        $up = $pdo->prepare("
          UPDATE offers
          SET price = ?, message = ?, updated_at = NOW()
          WHERE id = ? AND mover_id = ?
        ");
        $up->execute([$price, $message, (int)$existing['id'], $moverId]);
        $okMsg = "Votre proposition a été mise à jour.";
      } else {
        // Nouvelle offre (status = pending)
        $ins = $pdo->prepare("
          INSERT INTO offers (move_id, mover_id, price, message, status, created_at)
          VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        $ins->execute([$moveId, $moverId, $price, $message]);
        $okMsg = "Votre proposition a été envoyée.";
      }

      // Redirection douce vers l’aperçu de l’annonce (évite le repost)
      header('Location: ' . url('mover/move_preview.php?id=' . $moveId) . '&msg=' . urlencode('offer_saved'));
      exit;

    } catch (Exception $e) {
      $err = "Erreur lors de l’enregistrement : " . htmlspecialchars($e->getMessage());
    }
  }
}

// 5) Valeurs par défaut pour le formulaire
$prefPrice   = $existing ? (float)$existing['price'] : '';
$prefMessage = $existing ? (string)$existing['message'] : '';

$from   = $mv['city_from'] ?? '';
$to     = $mv['city_to'] ?? '';
$title  = $mv['title'] ?? 'Sans titre';
$date   = $mv['date_start'] ?? null;
$vol    = (int)($mv['volume_m3'] ?? 0);
$client = trim(($mv['prenom'] ?? '') . ' ' . ($mv['nom'] ?? ''));
?>
<div class="container container-narrow py-4">

  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title"><?= $existing ? 'Modifier ma proposition' : 'Proposer un prix' ?></div>
    <div class="cta">
      <a class="btn btn-outline-secondary" href="<?= url('mover/move_preview.php?id=' . (int)$moveId) ?>">← Retour à l’annonce</a>
    </div>
  </div>

  <?php if ($err): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php elseif ($okMsg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($okMsg) ?></div>
  <?php endif; ?>

  <!-- Rappel de l’annonce -->
  <div class="card-move mb-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <span class="badge-soft"><?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?></span>
      <span class="small-muted"><?= $date ? htmlspecialchars($date) : '' ?></span>
    </div>
    <div class="fw-semibold"><?= htmlspecialchars($title) ?></div>
    <div class="small-muted">
      <?= $vol ? ($vol . ' m³') : '' ?> • Client : <?= htmlspecialchars($client ?: '—') ?>
    </div>
  </div>

  <!-- Formulaire offre -->
  <form method="post" class="card-move">
    <?php if (function_exists('csrf_field')) { csrf_field(); } ?>
    <input type="hidden" name="move_id" value="<?= (int)$moveId ?>">

    <div class="mb-3">
      <label class="form-label fw-semibold">Votre prix (EUR)</label>
      <input type="number" min="1" step="1" required
             class="form-control"
             name="price"
             value="<?= htmlspecialchars($prefPrice) ?>"
             placeholder="Ex : 450">
      <div class="form-text small-muted">Montant total proposé au client.</div>
    </div>

    <div class="mb-3">
      <label class="form-label fw-semibold">Message au client (optionnel)</label>
      <textarea class="form-control" rows="4" name="message"
                placeholder="Bonjour, nous pouvons intervenir à cette date. Détails..."><?= htmlspecialchars($prefMessage) ?></textarea>
      <div class="form-text small-muted">Présentez brièvement l’équipe, le matériel, ou précisez des conditions.</div>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">
        <?= $existing ? 'Mettre à jour ma proposition' : 'Envoyer ma proposition' ?>
      </button>
      <a class="btn btn-outline-secondary" href="<?= url('mover/move_preview.php?id=' . (int)$moveId) ?>">Annuler</a>
    </div>
  </form>

  <div class="small-muted mt-3">
    Statut actuel :
    <strong>
      <?= $existing ? htmlspecialchars($existing['status']) : 'Aucune proposition — statut à venir : pending' ?>
    </strong>
  </div>
</div>

<?php require __DIR__ . '/../include/footer.php'; ?>
