<?php
// mover/dashboard.php
require __DIR__ . '/../include/header_mover.php';

$moverId = (int)$u['id'];

// Stats offres
$stats = $pdo->query("
  SELECT 
    SUM(status='pending')   AS pending,
    SUM(status='accepted')  AS accepted,
    SUM(status='rejected')  AS rejected,
    SUM(status='withdrawn') AS withdrawn
  FROM offers WHERE mover_id={$moverId}
")->fetch(PDO::FETCH_ASSOC) ?: ['pending'=>0,'accepted'=>0,'rejected'=>0,'withdrawn'=>0];

// Dernières annonces ouvertes (et non à lui si jamais il a aussi un compte client)
$stmt = $pdo->prepare("
  SELECT m.id, m.title, m.city_from, m.city_to, DATE_FORMAT(m.date_start,'%d/%m/%Y %H:%i') AS d, m.volume_m3,
         (SELECT o.status FROM offers o WHERE o.move_id=m.id AND o.mover_id=? ORDER BY o.id DESC LIMIT 1) AS my_status
  FROM moves m
  WHERE m.is_active=1
  ORDER BY m.created_at DESC
  LIMIT 12
");
$stmt->execute([$moverId]);
$moves = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container container-narrow">
  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Dashboard</div>
  </div>

  <div class="row g-3">
    <div class="col-6 col-lg-3"><div class="stat-card h-100"><div class="stat-label">En attente</div><div class="stat-value"><?= (int)$stats['pending'] ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card h-100"><div class="stat-label">Acceptées</div><div class="stat-value"><?= (int)$stats['accepted'] ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card h-100"><div class="stat-label">Rejetées</div><div class="stat-value"><?= (int)$stats['rejected'] ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card h-100"><div class="stat-label">Retirées</div><div class="stat-value"><?= (int)$stats['withdrawn'] ?></div></div></div>
  </div>

  <h2 class="h6 mt-4 mb-2">Annonces récentes</h2>
  <div class="row g-3">
    <?php foreach ($moves as $mv): ?>
      <div class="col-12 col-lg-6">
        <article class="card-move h-100">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="badge-soft"><?= htmlspecialchars($mv['city_from']) ?> → <?= htmlspecialchars($mv['city_to']) ?></span>
            <span class="small-muted"><?= htmlspecialchars($mv['d']) ?></span>
          </div>
          <h3 class="h6 mb-1"><?= htmlspecialchars($mv['title'] ?: 'Sans titre') ?></h3>
          <div class="small-muted mb-2"><?= (int)$mv['volume_m3'] ?> m³</div>

          <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
               href="<?= url('mover/move_preview.php') ?>?id=<?= (int)$mv['id'] ?>">Aperçu public</a>
            <a class="btn btn-sm btn-primary" href="<?= url('mover/offer_new.php') ?>?id=<?= (int)$mv['id'] ?>">
              <?= $mv['my_status'] ? 'Voir mon offre' : 'Proposer un prix' ?>
            </a>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/../include/footer.php'; ?>
