<?php
require __DIR__ . '/include/header.php';
$id=(int)($_GET['id']??0);
$st=$pdo->prepare("SELECT m.*,u.name AS client_name FROM moves m JOIN users u ON u.id=m.client_id WHERE m.id=?");
$st->execute([$id]); $mv=$st->fetch();
if(!$mv){ echo '<div class="container py-5"><div class="alert alert-danger">Annonce introuvable.</div></div>'; require __DIR__ . '/include/footer.php'; exit; }
$imgs=$pdo->prepare("SELECT path FROM move_images WHERE move_id=?"); $imgs->execute([$id]); $images=$imgs->fetchAll();
?>
<div class="container py-5">
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card-move">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="badge-soft"><?php echo htmlspecialchars($mv['city_from'])." → ".htmlspecialchars($mv['city_to']); ?></span>
          <span class="small-muted"><?php echo htmlspecialchars($mv['date_start']); ?></span>
        </div>
        <h1 class="h5 mb-1"><?php echo htmlspecialchars($mv['title']); ?></h1>
        <p class="small-muted"><?php echo nl2br(htmlspecialchars($mv['description'])); ?></p>
        <ul class="small-muted">
          <li>Volume : <?php echo (int)$mv['volume_m3']; ?> m³</li>
          <li>Départ : <?php echo htmlspecialchars($mv['housing_from']); ?> — Arrivée : <?php echo htmlspecialchars($mv['housing_to']); ?></li>
          <li>Besoin : <?php echo (int)$mv['needed']; ?> déménageur(s)</li>
          <li>Client : <?php echo htmlspecialchars($mv['client_name']); ?></li>
        </ul>
        <?php if($images): ?>
          <div class="row g-2">
            <?php foreach($images as $im): ?>
              <div class="col-6 col-md-4"><img class="img-fluid rounded" src="<?= asset(htmlspecialchars($im['path'])) ?>" alt=""></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card-move">
        <h2 class="h6">Proposer mes services</h2>
        <?php if(!is_logged_in()): ?>
          <div class="alert alert-warning small">Connectez-vous comme <strong>déménageur</strong> pour proposer un prix.</div>
        <?php elseif(current_user()['role']!=='demenageur'): ?>
          <div class="alert alert-info small">Seuls les comptes <strong>déménageur</strong> peuvent proposer un prix.</div>
        <?php else: ?>
          <?php
          if($_SERVER['REQUEST_METHOD']==='POST'){
            check_csrf();
            $price=(float)($_POST['price']??0);
            $pdo->prepare("INSERT INTO offers(move_id,mover_id,price,status,created_at) VALUES(?,?,?,?,NOW())")->execute([$mv['id'], current_user()['id'], $price, 'pending']);
            echo '<div class="alert alert-success small">Proposition envoyée.</div>';
          }
          ?>
          <form method="post" class="mt-2">
            <?php csrf_field(); ?>
            <label class="form-label">Votre prix (€)</label>
            <input class="form-control mb-2" type="number" name="price" min="0" step="1" required>
            <button class="btn btn-primary w-100">Envoyer</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/include/footer.php'; ?>
