<?php
// === client/move.php ===
// Page publique (aperçu d'une annonce)
require __DIR__ . '/../config.php';

// ID obligatoire
$moveId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($moveId <= 0) {
  http_response_code(404);
  echo "Annonce introuvable.";
  exit;
}

// Charger les infos de l'annonce
$stmt = $pdo->prepare("
  SELECT m.*, u.prenom, u.nom
  FROM moves m
  JOIN users u ON u.id = m.client_id
  WHERE m.id = ?
");
$stmt->execute([$moveId]);
$move = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$move) {
  http_response_code(404);
  echo "Annonce introuvable ou supprimée.";
  exit;
}

// Charger les images
$imgStmt = $pdo->prepare("SELECT filename FROM move_images WHERE move_id = ?");
$imgStmt->execute([$moveId]);
$images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

// Variables utiles
$title       = $move['title'] ?? '';
$desc        = $move['description'] ?? '';
$from        = $move['city_from'] ?? '';
$to          = $move['city_to'] ?? '';
$date        = $move['date_start'] ?? '';
$volume      = $move['volume_m3'] ?? '';
$housingFrom = $move['housing_from'] ?? '';
$housingTo   = $move['housing_to'] ?? '';
$isActive    = (int)($move['is_active'] ?? 0);

// Inclure header standard (pas forcément le header client)
require __DIR__ . '/../include/header.php';
?>

<div class="container container-narrow py-4">
  <div class="section-head d-flex justify-content-between align-items-center mb-4">
    <h1 class="title"><?= htmlspecialchars($title ?: 'Annonce de déménagement') ?></h1>
    <a href="<?= url('client/my_moves.php') ?>" class="btn btn-outline-secondary">← Retour</a>
  </div>

  <div class="card-move mb-4">
    <div class="badge-soft mb-2"><?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?></div>
    <div class="small-muted mb-2">
      <?= $date ? 'Prévu le ' . htmlspecialchars($date) : '' ?>
      <?= $volume ? ' • ' . htmlspecialchars($volume) . ' m³' : '' ?>
    </div>

    <div class="mb-2"><strong>Client :</strong> <?= htmlspecialchars($move['prenom'] . ' ' . $move['nom']) ?></div>

    <?php if ($housingFrom || $housingTo): ?>
      <div class="mt-3 small-muted">
        <?php if ($housingFrom): ?><div><strong>Départ :</strong> <?= htmlspecialchars($housingFrom) ?></div><?php endif; ?>
        <?php if ($housingTo): ?><div><strong>Arrivée :</strong> <?= htmlspecialchars($housingTo) ?></div><?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($desc): ?>
      <div class="mt-3">
        <strong>Description :</strong>
        <p><?= nl2br(htmlspecialchars($desc)) ?></p>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($images): ?>
    <h5>Photos</h5>
    <div class="row g-3 mb-4">
      <?php foreach ($images as $img): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <img src="<?= url('uploads/moves/' . htmlspecialchars($img)) ?>"
               alt="Photo" class="img-fluid rounded shadow-sm">
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!$isActive): ?>
    <div class="alert alert-warning">⚠️ Cette annonce est actuellement mise en pause et n’est plus visible publiquement.</div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../include/footer.php'; ?>
