<?php
// mover/offer_new.php — proposer un prix pour une annonce

// 1) AUCUN HTML AVANT LES header()
//    On charge seulement la config ici.
require __DIR__ . '/../config.php';

// Sécurité : utilisateur connecté + rôle déménageur
$u = current_user();
if (!$u || (($u['role'] ?? '') !== 'demenageur')) {
    header('Location: ' . url('auth/login.php'));
    exit;
}

$moverId = (int)$u['id'];

// 2) Récupération de l’ID de l’annonce
$moveId = (int)($_GET['move_id'] ?? $_POST['move_id'] ?? 0);
if ($moveId <= 0) {
    header('Location: ' . url('mover/dashboard.php?msg=move_not_found'));
    exit;
}

// 3) Récupération de l’annonce + client
$st = $pdo->prepare("
    SELECT m.id, m.title, m.city_from, m.city_to, m.date_start,
           m.volume_m3, m.is_active,
           u.prenom, u.nom
    FROM moves m
    JOIN users u ON u.id = m.client_id
    WHERE m.id = ?
");
$st->execute([$moveId]);
$mv = $st->fetch(PDO::FETCH_ASSOC);

if (!$mv) {
    header('Location: ' . url('mover/dashboard.php?msg=move_not_found'));
    exit;
}

// 4) Vérifier si ce déménageur a DÉJÀ une offre sur cette annonce
$st2 = $pdo->prepare("
    SELECT id, price, message, status, created_at
    FROM offers
    WHERE move_id = ? AND mover_id = ?
    LIMIT 1
");
$st2->execute([$moveId, $moverId]);
$existing = $st2->fetch(PDO::FETCH_ASSOC);

// 5) Traitement POST : on n’autorise l’envoi que si AUCUNE offre n’existe encore
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (function_exists('check_csrf')) { check_csrf(); }

    // a) Il a déjà une offre → redirection, PAS de modification possible
    if ($existing) {
        header('Location: ' . url('mover/move_preview.php?id=' . $moveId) . '&msg=' . urlencode('offer_already_exists'));
        exit;
    }

    // b) Annonce inactive
    if ((int)$mv['is_active'] !== 1) {
        header('Location: ' . url('mover/move_preview.php?id=' . $moveId) . '&msg=' . urlencode('move_not_active'));
        exit;
    }

    // c) Lecture du formulaire
    $price   = (float)($_POST['price'] ?? 0);
    $message = trim((string)($_POST['message'] ?? ''));

    if ($price <= 0) {
        // On ne redirige pas ici pour pouvoir afficher l’erreur dans la page
        $error = "Le prix proposé doit être un montant positif.";
    } else {
        // d) Insertion de l’offre (toujours status = pending)
        $ins = $pdo->prepare("
            INSERT INTO offers (move_id, mover_id, price, message, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        $ins->execute([$moveId, $moverId, $price, $message]);

        // Redirection vers l’aperçu d’annonce, sans erreur de header
        header('Location: ' . url('mover/move_preview.php?id=' . $moveId) . '&msg=' . urlencode('offer_saved'));
        exit;
    }
}

// 6) À partir d’ici SEULEMENT on inclut le header (HTML)
require __DIR__ . '/../include/header_mover.php';

// Petites variables pour l’affichage
$from   = $mv['city_from'] ?? '';
$to     = $mv['city_to'] ?? '';
$title  = $mv['title'] ?? 'Sans titre';
$date   = $mv['date_start'] ?? null;
$vol    = (int)($mv['volume_m3'] ?? 0);
$client = trim(($mv['prenom'] ?? '') . ' ' . ($mv['nom'] ?? ''));
?>

<div class="container container-narrow py-4">

  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Proposer un prix</div>
    <div class="cta">
      <a class="btn btn-outline-secondary"
         href="<?= url('mover/move_preview.php?id=' . (int)$moveId) ?>">
        ← Retour à l’annonce
      </a>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
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

  <?php if ($existing): ?>
    <!-- Il a déjà proposé : on n’autorise PAS la modification -->
    <div class="card-move">
      <div class="h6 mb-2">Votre offre est déjà enregistrée</div>
      <div class="small mb-1">
        Prix proposé : <strong><?= number_format((float)$existing['price'], 0, ',', ' ') ?> €</strong>
      </div>
      <?php if ($existing['message']): ?>
        <div class="small-muted mb-2">
          Message : <?= nl2br(htmlspecialchars($existing['message'])) ?>
        </div>
      <?php endif; ?>
      <div class="small-muted">
        Statut : <strong><?= htmlspecialchars($existing['status']) ?></strong>
      </div>

      <div class="mt-3 d-flex gap-2 flex-wrap">
        <a class="btn btn-primary"
           href="<?= url('mover/move_preview.php?id=' . (int)$moveId) ?>">
          Voir l’annonce
        </a>
        <a class="btn btn-outline-secondary"
           href="<?= url('mover/my_offers.php') ?>">
          Mes offres
        </a>
      </div>
    </div>

  <?php elseif ((int)$mv['is_active'] !== 1): ?>

    <div class="alert alert-info">
      Cette annonce n’est plus active, vous ne pouvez plus proposer de prix.
    </div>

  <?php else: ?>
    <!-- Formulaire uniquement si AUCUNE offre encore envoyée -->
    <form method="post" class="card-move">
      <?php if (function_exists('csrf_field')) { csrf_field(); } ?>
      <input type="hidden" name="move_id" value="<?= (int)$moveId ?>">

      <div class="mb-3">
        <label class="form-label fw-semibold">Votre prix (EUR)</label>
        <input
          type="number"
          min="1"
          step="1"
          required
          class="form-control"
          name="price"
          placeholder="Ex : 450">
        <div class="form-text small-muted">Montant total proposé au client.</div>
      </div>


      <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">
          Envoyer ma proposition
        </button>
        <a class="btn btn-outline-secondary"
           href="<?= url('mover/move_preview.php?id=' . (int)$moveId) ?>">
          Annuler
        </a>
      </div>
    </form>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/../include/footer.php'; ?>
