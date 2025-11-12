<?php
require __DIR__ . '/../include/header.php'; require_role('demenageur');
$m=$pdo->query("SELECT m.id,m.title,m.description,m.city_from,m.city_to,DATE_FORMAT(m.date_start,'%d/%m/%Y %H:%i') AS d,m.volume_m3 FROM moves m WHERE m.is_active=1 ORDER BY m.created_at DESC")->fetchAll();
?>
<div class="container py-5">
  <h1 class="h5 mb-3">Annonces clients</h1>
  <div class="row g-3">
    <?php foreach($m as $mv): ?>
      <div class="col-12 col-md-6">
        <article class="card-move">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="badge-soft"><?php echo htmlspecialchars($mv['city_from'])." → ".htmlspecialchars($mv['city_to']); ?></span>
            <span class="small-muted"><?php echo htmlspecialchars($mv['d']); ?></span>
          </div>
          <h3 class="h6 mb-1"><?php echo htmlspecialchars($mv['title']); ?></h3>
          <p class="small-muted mb-2"><?php echo nl2br(htmlspecialchars($mv['description'])); ?></p>
          <a class="btn btn-sm btn-primary" href="<?= url('move.php') ?>?id=<?php echo (int)$mv['id']; ?>">Détails</a>
        </article>
      </div>
    <?php endforeach; ?>
    <?php if(empty($m)): ?>
      <div class="col-12"><div class="alert alert-warning">Aucune annonce disponible.</div></div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../include/footer.php'; ?>
