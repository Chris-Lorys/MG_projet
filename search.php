<?php
require __DIR__ . '/include/header.php';
$from = trim($_GET['from'] ?? '');
$to   = trim($_GET['to'] ?? '');
$date = trim($_GET['date'] ?? '');

$sql = "SELECT id, title, city_from, city_to, DATE_FORMAT(date_start, '%d/%m/%Y') as d, volume_m3 FROM moves WHERE is_active=1";
$p=[];
if ($from!==''){ $sql.=" AND city_from LIKE ?"; $p[]="%$from%"; }
if ($to!==''){ $sql.=" AND city_to LIKE ?"; $p[]="%$to%"; }
if ($date!==''){ $sql.=" AND DATE(date_start)=?"; $p[]=$date; }
$sql.=" ORDER BY date_start DESC";

$st = $pdo->prepare($sql); $st->execute($p); $moves = $st->fetchAll();
?>
<div class="container py-5">
  <h1 class="h5 mb-3">Résultats</h1>
  <div class="row g-3">
    <?php foreach ($moves as $mv): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <article class="card-move">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="badge-soft"><?php echo htmlspecialchars($mv['city_from']) . " → " . htmlspecialchars($mv['city_to']); ?></span>
            <span class="small-muted"><?php echo htmlspecialchars($mv['d']); ?></span>
          </div>
          <h3 class="h6 mb-1"><?php echo htmlspecialchars($mv['title']); ?></h3>
          <div class="small-muted mb-2"><?php echo (int)$mv['volume_m3']; ?> m³</div>
          <a class="btn btn-sm btn-primary" href="<?= url('visitor/move_preview_visitor.php') ?>?id=<?php echo (int)$mv['id']; ?>">Voir</a>
        </article>
      </div>
    <?php endforeach; ?>
    <?php if (empty($moves)): ?>
      <div class="col-12"><div class="alert alert-warning">Aucune annonce trouvée.</div></div>
    <?php endif; ?>
  </div>
</div>
