<?php
// mover/my_offers.php — Mes offres (déménageur)
require __DIR__ . '/../include/header_mover.php'; // même style que le header client, adapté déménageur

$u = current_user();
$moverId = (int)($u['id'] ?? 0);

// Filtre de statut optionnel: all (par défaut), pending, accepted, rejected, withdrawn
$allowed = ['all','pending','accepted','rejected','withdrawn'];
$status  = $_GET['status'] ?? 'all';
if (!in_array($status, $allowed, true)) $status = 'all';

// Compteurs par statut
$cntStmt = $pdo->prepare("
  SELECT status, COUNT(*) AS c
  FROM offers
  WHERE mover_id = ?
  GROUP BY status
");
$cntStmt->execute([$moverId]);
$rawCounts = ['pending'=>0,'accepted'=>0,'rejected'=>0,'withdrawn'=>0];
foreach ($cntStmt as $row) {
  $st = $row['status'];
  if (isset($rawCounts[$st])) $rawCounts[$st] = (int)$row['c'];
}
$totalAll = array_sum($rawCounts);

// Requête principale des offres
$sql = "
  SELECT 
    o.id, o.move_id, o.price, o.message, o.status, o.created_at,
    m.title, m.city_from, m.city_to, m.date_start, m.volume_m3, m.client_id,
    u.nom  AS client_nom, u.prenom AS client_prenom
  FROM offers o
  JOIN moves  m ON m.id = o.move_id
  JOIN users  u ON u.id = m.client_id
  WHERE o.mover_id = ?
";
$params = [$moverId];

if ($status !== 'all') {
  $sql .= " AND o.status = ? ";
  $params[] = $status;
}
$sql .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

function status_badge(string $s): array {
  // return [label, class]
  switch ($s) {
    case 'pending':   return ['En attente',   'badge bg-warning-subtle text-warning-emphasis border'];
    case 'accepted':  return ['Acceptée',     'badge bg-success-subtle text-success-emphasis border'];
    case 'rejected':  return ['Rejetée',      'badge bg-secondary-subtle text-secondary-emphasis border'];
    case 'withdrawn': return ['Retirée',      'badge bg-dark-subtle text-dark-emphasis border'];
    default:          return [ucfirst($s),    'badge bg-secondary-subtle text-secondary-emphasis border'];
  }
}

?>
<div class="container container-narrow py-4">
  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Mes offres</div>
  </div>

  <!-- Filtres -->
  <div class="card-move mb-3">
    <div class="d-flex flex-wrap gap-2">
      <?php
        // helper pour activer le filtre
        function f_link($key, $label, $count, $active) {
          $href = url('mover/my_offers.php') . '?status=' . $key;
          $cls  = 'btn btn-sm ' . ($active ? 'btn-primary' : 'btn-outline-secondary');
          echo '<a class="'.$cls.'" href="'.$href.'">'.$label.' <span class="badge text-bg-light ms-1">'.$count.'</span></a>';
        }
        f_link('all',       'Toutes',     $totalAll,              $status==='all');
        f_link('pending',   'En attente', $rawCounts['pending'],  $status==='pending');
        f_link('accepted',  'Acceptées',  $rawCounts['accepted'], $status==='accepted');
        f_link('rejected',  'Rejetées',   $rawCounts['rejected'], $status==='rejected');
        f_link('withdrawn', 'Retirées',   $rawCounts['withdrawn'],$status==='withdrawn');
      ?>
    </div>
  </div>

  <?php if (!$offers): ?>
    <div class="card-move">
      <div class="small-muted">Aucune offre à afficher pour ce filtre.</div>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($offers as $o): ?>
        <?php
          [$label, $badgeClass] == status_badge((string)$o['status']);
          $route  = trim(($o['city_from'] ?? '').' → '.($o['city_to'] ?? ''));
          $title  = trim($o['title'] ?? '');
          $date   = $o['date_start'] ?? '';
          $vol    = (int)($o['volume_m3'] ?? 0);
          $price  = (float)$o['price'];
          $msg    = trim((string)($o['message'] ?? ''));
          $client = trim(($o['client_prenom'] ?? '').' '.($o['client_nom'] ?? ''));
          $moveId = (int)$o['move_id'];
        ?>
        <div class="col-12">
          <div class="card-move h-100">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div>
                <div class="badge-soft mb-2"><?= htmlspecialchars($route) ?></div>
                <div class="h6 m-0"><?= htmlspecialchars($title ?: 'Sans titre') ?></div>
                <div class="small-muted">
                  <?= $date ? 'Le ' . htmlspecialchars($date) : '' ?>
                  <?= $vol ? ' • ' . (int)$vol . ' m³' : '' ?>
                </div>
                <div class="small mt-1">
                  <strong>Client :</strong> <?= htmlspecialchars($client ?: 'n/d') ?>
                </div>
              </div>

              <div class="text-end">
                <div class="small mb-1">Prix proposé</div>
                <div class="h5 m-0" style="color:#0b7076"><?= number_format($price, 0, ',', ' ') ?> €</div>
                <span class="<?= $badgeClass ?> mt-1"><?= $label ?></span>
              </div>
            </div>

            <?php if ($msg !== ''): ?>
              <hr class="my-3">
              <div class="small-muted">
                <strong>Message :</strong><br>
                <?= nl2br(htmlspecialchars(mb_strimwidth($msg, 0, 350, '…'))) ?>
              </div>
            <?php endif; ?>

            <div class="mt-3 d-flex flex-wrap gap-2">
              <a class="btn btn-sm btn-outline-secondary"
                 href="<?= url('mover/move_preview.php?id=' . $moveId) ?>">
                Voir l’annonce
              </a>
              <a class="btn btn-sm btn-primary"
                 href="<?= url('mover/message_new.php?move_id=' . $moveId) ?>">
                Contacter le client
              </a>
              <?php if ($o['status'] === 'pending'): ?>
                <!-- Exemple : bouton (optionnel) pour retirer l’offre si tu crées le handler -->
                <!-- <a class="btn btn-sm btn-outline-danger" href="<?= url('mover/offer_withdraw.php?id='.(int)$o['id']) ?>">Retirer l’offre</a> -->
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../include/footer.php'; ?>
