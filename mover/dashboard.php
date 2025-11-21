<?php
// mover/dashboard.php
require __DIR__ . '/../include/header_mover.php';

// $u vient de header_mover.php
$moverId   = (int)$u['id'];
$firstName = trim((string)($u['prenom'] ?? ''));
$lastName  = trim((string)($u['nom'] ?? ''));
$fullName  = trim($firstName . ' ' . $lastName);

// Stats offres
$stats = $pdo->query("
  SELECT 
    SUM(status='pending')   AS pending,
    SUM(status='accepted')  AS accepted,
    SUM(status='rejected')  AS rejected,
    SUM(status='withdrawn') AS withdrawn
  FROM offers 
  WHERE mover_id = {$moverId}
")->fetch(PDO::FETCH_ASSOC) ?: [
  'pending'   => 0,
  'accepted'  => 0,
  'rejected'  => 0,
  'withdrawn' => 0,
];

// Dernières annonces ouvertes
$stmt = $pdo->prepare("
  SELECT 
    m.id, 
    m.title, 
    m.city_from, 
    m.city_to, 
    DATE_FORMAT(m.date_start,'%d/%m/%Y %H:%i') AS d, 
    m.volume_m3,
    (
      SELECT o.status 
      FROM offers o 
      WHERE o.move_id = m.id AND o.mover_id = ?
      ORDER BY o.id DESC 
      LIMIT 1
    ) AS my_status
  FROM moves m
  WHERE m.is_active = 1
  ORDER BY m.created_at DESC
  LIMIT 12
");
$stmt->execute([$moverId]);
$moves = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container container-narrow">
  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="welcome-banner">
       <h1>👋 Bienvenue <span class="username"><?= htmlspecialchars($fullName ?: 'déménageur') ?></span> !</h1>
       <p>Heureux de vous revoir sur votre espace Move & Go. Consultez les annonces récentes et formulez des propositions aux clients dès maintenant.</p>
    </div>
  </div>

  <div class="row g-3 stats-row">
    <div class="col-6 col-lg-3">
      <div class="stat-card h-100">
        <div class="stat-label">En attente</div>
        <div class="stat-value"><?= (int)$stats['pending'] ?></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card h-100">
        <div class="stat-label">Acceptées</div>
        <div class="stat-value"><?= (int)$stats['accepted'] ?></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card h-100">
        <div class="stat-label">Rejetées</div>
        <div class="stat-value"><?= (int)$stats['rejected'] ?></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card h-100">
        <div class="stat-label">Retirées</div>
        <div class="stat-value"><?= (int)$stats['withdrawn'] ?></div>
      </div>
    </div>
  </div>

  <h2 class="h6 mt-4 mb-2">Annonces récentes</h2>
  <div class="row g-3">
    <?php foreach ($moves as $mv): ?>
      <div class="col-12 col-lg-6">
        <article class="card-move h-100">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="badge-soft">
              <?= htmlspecialchars($mv['city_from']) ?> → <?= htmlspecialchars($mv['city_to']) ?>
            </span>
            <span class="small-muted"><?= htmlspecialchars($mv['d']) ?></span>
          </div>
          <h3 class="h6 mb-1"><?= htmlspecialchars($mv['title'] ?: 'Sans titre') ?></h3>
          <div class="small-muted mb-2"><?= (int)$mv['volume_m3'] ?> m³</div>

          <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-secondary"
               target="_blank" rel="noopener"
               href="<?= url('mover/move_preview.php') ?>?id=<?= (int)$mv['id'] ?>">
              Voir plus
            </a>
            <!-- On ne montre plus "Proposer un prix" ici -->
          </div>
        </article>
      </div>
    <?php endforeach; ?>
    <?php if (empty($moves)): ?>
      <div class="col-12">
        <div class="card-move empty-hero">
          <div class="content">
            <h2>Aucune annonce disponible pour le moment</h2>
            <p>Revenez un peu plus tard pour voir de nouvelles demandes de déménagement.</p>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>



<?php require __DIR__ . '/../include/footer.php'; ?>
