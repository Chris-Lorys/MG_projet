<?php
// mover/move_detail.php — Détail annonce + proposer un prix
require __DIR__ . '/../include/header_mover.php';

$moverId = (int)$u['id'];
$id = (int)($_GET['id'] ?? 0);

// Récupération de l'annonce + infos client
$st = $pdo->prepare("
  SELECT m.id, m.title, m.description, m.city_from, m.city_to, 
         DATE_FORMAT(m.date_start,'%d/%m/%Y %H:%i') AS d, m.volume_m3, 
         m.housing_from, m.housing_to, m.is_active,
         u.nom AS client_nom, u.prenom AS client_prenom, u.email AS client_email
  FROM moves m
  JOIN users u ON m.client_id = u.id
  WHERE m.id = ?
  LIMIT 1
");
$st->execute([$id]);
$mv = $st->fetch(PDO::FETCH_ASSOC);

if (!$mv) {
  echo '<div class="container py-5"><div class="alert alert-danger">Annonce introuvable.</div></div>';
  require __DIR__ . '/../include/footer.php';
  exit;
}

// Récupération de la dernière offre du déménageur
$my = $pdo->prepare("SELECT * FROM offers WHERE move_id=? AND mover_id=? ORDER BY id DESC LIMIT 1");
$my->execute([$id, $moverId]);
$myOffer = $my->fetch(PDO::FETCH_ASSOC);

$ok = null; 
$err = null;

// Envoi d'une nouvelle offre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$myOffer) {
  if (function_exists('check_csrf')) { check_csrf(); }

  if ((int)$mv['is_active'] !== 1) {
    $err = "L’annonce n’est plus active.";
  } else {
    $price   = (float)($_POST['price'] ?? 0);
    $message = trim((string)($_POST['message'] ?? ''));

    if ($price <= 0) {
      $err = "Prix invalide.";
    } else {
      $ins = $pdo->prepare("INSERT INTO offers (move_id, mover_id, price, message, status) VALUES (?, ?, ?, ?, 'pending')");
      $ins->execute([$id, $moverId, $price, $message]);
      $ok = "Proposition envoyée.";
      $my->execute([$id, $moverId]);
      $myOffer = $my->fetch(PDO::FETCH_ASSOC);
    }
  }
}
?>
<div class="container container-narrow">
  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Détails de l'annonce</div>
    <a class="btn btn-outline-secondary" href="<?= url('mover/dashboard.php') ?>">← Retour</a>
  </div>

  <?php if ($ok): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="card-move mb-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <span class="badge-soft"><?= htmlspecialchars($mv['city_from']) ?> → <?= htmlspecialchars($mv['city_to']) ?></span>
      <span class="small-muted"><?= htmlspecialchars($mv['d']) ?></span>
    </div>
    <h1 class="h6 mb-1"><?= htmlspecialchars($mv['title'] ?: 'Sans titre') ?></h1>
    <div class="small-muted mb-2"><?= (int)$mv['volume_m3'] ?> m³</div>
    
    <?php if ($mv['description']): ?>
      <p class="small-muted mb-2"><?= nl2br(htmlspecialchars($mv['description'])) ?></p>
    <?php endif; ?>

    <ul class="small-muted mb-3">
      <li><strong>Départ :</strong> <?= htmlspecialchars($mv['housing_from'] ?? 'NC') ?></li>
      <li><strong>Arrivée :</strong> <?= htmlspecialchars($mv['housing_to'] ?? 'NC') ?></li>
    </ul>

    <!-- Informations sur le client -->
    <div class="p-3 rounded-3" style="background:rgba(17,138,150,0.07); border:1px solid rgba(0,0,0,0.08);">
      <div class="fw-semibold mb-1">👤 Client :</div>
      <div><?= htmlspecialchars(($mv['client_prenom'] ?? '') . ' ' . ($mv['client_nom'] ?? '')) ?></div>
      <div class="small-muted"><?= htmlspecialchars($mv['client_email'] ?? '') ?></div>
    </div>
  </div>

  <?php if ($myOffer): ?>
    <div class="card-move">
      <div class="h6 mb-2">Votre offre</div>
      <div class="small mb-2">Prix : <strong><?= number_format((float)$myOffer['price'], 2, ',', ' ') ?> €</strong></div>
      <?php if ($myOffer['message']): ?>
        <div class="small-muted mb-2">Message : <?= nl2br(htmlspecialchars($myOffer['message'])) ?></div>
      <?php endif; ?>
      <span class="badge 
        <?= $myOffer['status']=='accepted'?'bg-success':
            ($myOffer['status']=='rejected'?'bg-danger':
             ($myOffer['status']=='withdrawn'?'bg-secondary':'bg-warning text-dark')) ?>">
        <?= htmlspecialchars($myOffer['status']) ?>
      </span>

      <?php if ($myOffer['status'] === 'pending'): ?>
        <a class="btn btn-sm btn-outline-danger mt-2" 
           href="<?= url('mover/offer_toggle.php') ?>?id=<?= (int)$myOffer['id'] ?>&action=withdrawn"
           onclick="return confirm('Retirer cette offre ?');">Retirer l’offre</a>
      <?php endif; ?>
    </div>
  <?php elseif ((int)$mv['is_active'] === 1): ?>
    <div class="card-move">
      <div class="h6 mb-2">Proposer un prix</div>
      <form method="post">
        <?php if (function_exists('csrf_field')) { csrf_field(); } ?>
        <div class="row g-2">
          <div class="col-12 col-md-4">
            <label class="form-label">Prix (€)</label>
            <input class="form-control" type="number" min="0" step="1" name="price" required>
          </div>
          <div class="col-12 col-md-8">
            <label class="form-label">Message (optionnel)</label>
            <input class="form-control" name="message" placeholder="Détails, disponibilité…">
          </div>
        </div>
        <button class="btn btn-primary mt-3">Envoyer</button>
      </form>
    </div>
  <?php else: ?>
    <div class="alert alert-info">Annonce inactive.</div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../include/footer.php'; ?>
