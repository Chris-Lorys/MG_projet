<?php
// === move_preview.php — vue publique de l'annonce ===
require __DIR__ . '/../include/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  echo '<div class="container py-5"><div class="alert alert-danger">Annonce invalide.</div></div>';
  require __DIR__ . '/include/footer.php';
  exit;
}

// On récupère les infos de l’annonce (sans nom du client)
$st = $pdo->prepare("
  SELECT
    id, title, description, city_from, city_to,
    date_start, volume_m3, needed, housing_from, housing_to, is_active
  FROM moves
  WHERE id = ?
  -- AND is_active = 1   -- (décommente si tu veux cacher les annonces inactives)
  LIMIT 1
");
$st->execute([$id]);
$mv = $st->fetch(PDO::FETCH_ASSOC);

if (!$mv) {
  echo '<div class="container py-5"><div class="alert alert-danger">Annonce introuvable.</div></div>';
  require __DIR__ . '/../include/footer.php';
  exit;
}

// Récupération des images associées
$imgs = $pdo->prepare("SELECT filename FROM move_images WHERE move_id = ? ORDER BY id ASC");
$imgs->execute([$mv['id']]);
$images = $imgs->fetchAll(PDO::FETCH_ASSOC);

// Formatage
$title       = htmlspecialchars($mv['title'] ?? 'Sans titre');
$from        = htmlspecialchars($mv['city_from'] ?? '');
$to          = htmlspecialchars($mv['city_to'] ?? '');
$date        = htmlspecialchars($mv['date_start'] ?? '');
$volume      = (int)($mv['volume_m3'] ?? 0);
$needed      = (int)($mv['needed'] ?? 0);
$housingFrom = htmlspecialchars($mv['housing_from'] ?? '');
$housingTo   = htmlspecialchars($mv['housing_to'] ?? '');
$desc        = nl2br(htmlspecialchars($mv['description'] ?? ''));
?>
<div class="container py-5">
  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Détails de l’annonce</div>
    <a href="<?= url('index.php') ?>" class="btn btn-outline-secondary">← Retour</a>
  </div>

  <article class="card-move mt-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="badge-soft"><?= $from ?> → <?= $to ?></span>
      <?php if ($date): ?><span class="small-muted"><?= $date ?></span><?php endif; ?>
    </div>

    <h1 class="h5 mb-2"><?= $title ?></h1>

    <?php if ($desc): ?>
      <p class="small-muted"><?= $desc ?></p>
    <?php endif; ?>

    <ul class="small-muted mb-3">
      <li><strong>Volume :</strong> <?= $volume ?> m³</li>
      <li><strong>Logement départ :</strong> <?= $housingFrom ?: '—' ?></li>
      <li><strong>Logement arrivée :</strong> <?= $housingTo ?: '—' ?></li>
      <li><strong>Besoin :</strong> <?= $needed ?> déménageur(s)</li>
    </ul>

    <?php if ($images): ?>
      <div class="row g-2">
        <?php foreach ($images as $im): ?>
          <div class="col-6 col-md-4">
            <img class="img-fluid rounded border"
                 src="<?= asset('uploads/moves/' . rawurlencode($im['filename'])) ?>"
                 alt="Image de l'annonce">
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </article>
</div>

<?php require __DIR__ . '/../include/footer.php'; ?>
