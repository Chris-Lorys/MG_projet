<?php
// mover/move_preview.php — aperçu d'une annonce côté déménageur
require __DIR__ . '/../include/header_mover.php';   // Inclut la navbar + sécurité déménageur

$mover = current_user();
$moverId = (int)($mover['id'] ?? 0);

// 1) Récupération de l'ID d'annonce
$moveId = (int)($_GET['id'] ?? 0);
if ($moveId <= 0) {
  echo '<div class="container py-5"><div class="alert alert-danger">Annonce introuvable.</div></div>';
  require __DIR__ . '/../include/footer.php';
  exit;
}

// 2) Récupération de l'annonce + infos client
$st = $pdo->prepare("
  SELECT m.*, u.prenom, u.nom, u.email
  FROM moves m
  JOIN users u ON u.id = m.client_id
  WHERE m.id = ?
");
$st->execute([$moveId]);
$mv = $st->fetch(PDO::FETCH_ASSOC);

if (!$mv) {
  echo '<div class="container py-5"><div class="alert alert-danger">Annonce introuvable.</div></div>';
  require __DIR__ . '/../include/footer.php';
  exit;
}

// 3) Récupération des images liées
$sti = $pdo->prepare("SELECT filename FROM move_images WHERE move_id = ? ORDER BY id ASC");
$sti->execute([$moveId]);
$imgs = $sti->fetchAll(PDO::FETCH_ASSOC);

// 4) Vérifier si ce déménageur a déjà fait une offre sur cette annonce
$stOffer = $pdo->prepare("
  SELECT id, status, price, created_at
  FROM offers
  WHERE move_id = ? AND mover_id = ?
  ORDER BY id DESC
  LIMIT 1
");
$stOffer->execute([$moveId, $moverId]);
$myOffer = $stOffer->fetch(PDO::FETCH_ASSOC);

// 5) Variables utiles pour l'affichage
$from    = $mv['city_from'] ?? '';
$to      = $mv['city_to'] ?? '';
$title   = $mv['title'] ?? 'Sans titre';
$desc    = trim((string)($mv['description'] ?? ''));
$vol     = (int)($mv['volume_m3'] ?? 0);
$date    = $mv['date_start'] ?? null;
$needed  = (int)($mv['needed'] ?? 0);
$isActive = (int)($mv['is_active'] ?? 1);

$housing_from = $mv['housing_from'] ?? '';
$housing_to   = $mv['housing_to'] ?? '';
$client_name  = trim(($mv['prenom'] ?? '') . ' ' . ($mv['nom'] ?? ''));
$client_email = $mv['email'] ?? '';

// Message éventuel passé en GET (ex: ?msg=offer_saved)
$flashMsg = $_GET['msg'] ?? null;
?>
<div class="container container-narrow py-4">
  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Aperçu de l’annonce</div>
   
  </div>

  <?php if ($flashMsg === 'offer_saved'): ?>
    <div class="alert alert-success movego-alert shadow-sm d-flex align-items-center justify-content-between">
      <div class="me-3">Votre proposition a bien été enregistrée.</div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
  <?php endif; ?>

  <article class="card-move">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <span class="badge-soft">
        <?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?>
      </span>
      <span class="small-muted"><?= $date ? htmlspecialchars($date) : '' ?></span>
    </div>

    <h1 class="h5 mb-1"><?= htmlspecialchars($title) ?></h1>

    <div class="small-muted mb-2">
      <?= $vol ? ($vol . ' m³') : '' ?>
      <?= $needed ? (' • ' . $needed . ' déménageur(s)') : '' ?>
    </div>

    <!-- Nom + email du client visibles par le déménageur -->
    <div class="alert alert-info py-2 px-3 mb-3" style="background:#e6f7f7;border:1px solid #9ed8d7;">
      <strong>Client :</strong> <?= htmlspecialchars($client_name ?: 'Non renseigné') ?><br>
      <span class="small-muted"><?= htmlspecialchars($client_email) ?></span>
    </div>

    <?php if ($desc !== ''): ?>
      <p class="move-desc mb-3"><?= nl2br(htmlspecialchars($desc)) ?></p>
    <?php endif; ?>

    <div class="row g-3 mb-3">
      <div class="col-12 col-lg-6">
        <div class="p-3 rounded-3" style="background:#f9fbfb;border:1px solid rgba(0,0,0,.06)">
          <div class="fw-semibold mb-1">Logement au départ</div>
          <div class="small-muted"><?= htmlspecialchars($housing_from ?: '—') ?></div>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="p-3 rounded-3" style="background:#f9fbfb;border:1px solid rgba(0,0,0,.06)">
          <div class="fw-semibold mb-1">Logement à l’arrivée</div>
          <div class="small-muted"><?= htmlspecialchars($housing_to ?: '—') ?></div>
        </div>
      </div>
    </div>

    <?php if ($imgs): ?>
      <hr class="my-4" />
      <div class="h6 mb-2">Photos</div>
      <div class="row g-2">
        <?php foreach ($imgs as $im): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <img class="img-fluid rounded" style="border:1px solid rgba(0,0,0,.06)"
                 src="<?= asset('uploads/moves/' . $im['filename']) ?>"
                 alt="Photo annonce">
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="mt-4 d-flex flex-wrap gap-2 align-items-center">
      <?php if ($myOffer): ?>
        <?php
          $label = 'Offre envoyée';
          if ($myOffer['status'] === 'accepted')  $label = 'Offre acceptée';
          if ($myOffer['status'] === 'rejected')  $label = 'Offre rejetée';
          if ($myOffer['status'] === 'withdrawn') $label = 'Offre retirée';
        ?>
        <span class="badge bg-secondary-subtle text-secondary-emphasis border me-2">
          <?= htmlspecialchars($label) ?>
        </span>
        <span class="small-muted">
          Vous avez déjà proposé un prix pour cette annonce.
          Consultez le détail dans <a href="<?= url('mover/my_offers.php') ?>">Mes offres</a>.
        </span>
      <?php elseif (!$isActive): ?>
        <span class="badge bg-secondary-subtle text-secondary-emphasis border me-2">
          Annonce en pause
        </span>
        <span class="small-muted">
          Cette annonce n’est plus active, vous ne pouvez plus proposer de prix.
        </span>
      <?php else: ?>
        <!-- ✅ Bouton visible uniquement si AUCUNE offre envoyée par ce déménageur -->
        <a class="btn btn-primary"
           href="<?= url('mover/offer_new.php?move_id=' . (int)$moveId) ?>">
          Proposer un prix
        </a>
      <?php endif; ?>
    </div>
  </article>
</div>


<?php require __DIR__ . '/../include/footer.php'; ?>
