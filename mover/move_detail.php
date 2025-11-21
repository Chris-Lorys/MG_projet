<?php
// mover/move_detail.php — Détail d'une annonce côté déménageur
require __DIR__ . '/../include/header_mover.php';

$u = current_user();
$moverId = (int)($u['id'] ?? 0);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo '<div class="container py-5"><div class="alert alert-danger">Annonce introuvable.</div></div>';
    require __DIR__ . '/../include/footer.php';
    exit;
}

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

// Récupération de la dernière offre du déménageur (s'il y en a une)
$my = $pdo->prepare("
    SELECT *
    FROM offers
    WHERE move_id = ? AND mover_id = ?
    ORDER BY id DESC
    LIMIT 1
");
$my->execute([$id, $moverId]);
$myOffer = $my->fetch(PDO::FETCH_ASSOC);

// Petit helper pour le badge de statut
function offer_status_badge_class(string $status): string {
    switch ($status) {
        case 'accepted':  return 'badge bg-success';
        case 'rejected':  return 'badge bg-danger';
        case 'withdrawn': return 'badge bg-secondary';
        case 'pending':
        default:          return 'badge bg-warning text-dark';
    }
}
?>
<div class="container container-narrow">
  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Détails de l'annonce</div>
    <a class="btn btn-outline-secondary" href="<?= url('mover/dashboard.php') ?>">← Retour</a>
  </div>

  <div class="card-move mb-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <span class="badge-soft">
        <?= htmlspecialchars($mv['city_from']) ?> → <?= htmlspecialchars($mv['city_to']) ?>
      </span>
      <span class="small-muted"><?= htmlspecialchars($mv['d']) ?></span>
    </div>

    <h1 class="h6 mb-1"><?= htmlspecialchars($mv['title'] ?: 'Sans titre') ?></h1>
    <div class="small-muted mb-2"><?= (int)$mv['volume_m3'] ?> m³</div>
    
    <?php if (!empty($mv['description'])): ?>
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
    <!-- L'utilisateur a déjà une offre pour cette annonce -->
    <div class="card-move">
      <div class="h6 mb-2">Votre offre</div>

      <div class="small mb-2">
        Prix : 
        <strong><?= number_format((float)$myOffer['price'], 2, ',', ' ') ?> €</strong>
      </div>

      <?php if (!empty($myOffer['message'])): ?>
        <div class="small-muted mb-2">
          Message : <?= nl2br(htmlspecialchars($myOffer['message'])) ?>
        </div>
      <?php endif; ?>

      <?php
        $stLabel = htmlspecialchars($myOffer['status']);
        $stClass = offer_status_badge_class($myOffer['status']);
      ?>
      <span class="<?= $stClass ?>"><?= $stLabel ?></span>

      <?php if ($myOffer['status'] === 'pending'): ?>
        <a class="btn btn-sm btn-outline-danger mt-2" 
           href="<?= url('mover/offer_toggle.php') ?>?id=<?= (int)$myOffer['id'] ?>&action=withdrawn"
           onclick="return confirm('Retirer cette offre ?');">
          Retirer l’offre
        </a>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <!-- Aucune offre encore envoyée par ce déménageur -->
    <?php if ((int)$mv['is_active'] === 1): ?>
      <div class="card-move">
        <div class="h6 mb-2">Proposer un prix</div>
        <p class="small-muted mb-3">
          Vous n'avez pas encore fait de proposition pour cette annonce.
        </p>
        <a class="btn btn-primary"
           href="<?= url('mover/offer_new.php?move_id=' . (int)$mv['id']) ?>">
          Proposer un prix
        </a>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        Cette annonce n’est plus active, vous ne pouvez plus proposer de prix.
      </div>
    <?php endif; ?>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/../include/footer.php'; ?>
