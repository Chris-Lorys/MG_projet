<?php
// === client/move_detail.php ===
// 1) Logique (contrôles, POST) AVANT tout output
require __DIR__ . '/../config.php';

$u = current_user();
if (!$u || (($u['role'] ?? '') !== 'client')) {
  header('Location: ' . url('auth/login.php'));
  exit;
}

$moveId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($moveId <= 0) {
  header('Location: ' . url('client/my_moves.php?msg=action_invalide'));
  exit;
}

// Vérifier que l'annonce existe et appartient au client
$stmt = $pdo->prepare("SELECT * FROM moves WHERE id = ?");
$stmt->execute([$moveId]);
$move = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$move || (int)$move['client_id'] !== (int)$u['id']) {
  header('Location: ' . url('client/my_moves.php?msg=not_owner'));
  exit;
}

// --- POST : accepter ou refuser une offre ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

  $action = $_POST['action'];
  if (!in_array($action, ['accept','reject'], true)) {
    header('Location: ' . url('client/move_detail.php?id=' . $moveId . '&msg=action_invalide'));
    exit;
  }

  if (function_exists('check_csrf')) { check_csrf(); }

  $offerId = (int)($_POST['offer_id'] ?? 0);

  // Vérifier que l'offre est bien liée à ce move
  $check = $pdo->prepare("SELECT id, status FROM offers WHERE id = ? AND move_id = ?");
  $check->execute([$offerId, $moveId]);
  $offer = $check->fetch(PDO::FETCH_ASSOC);

  if (!$offer) {
    header('Location: ' . url('client/move_detail.php?id=' . $moveId . '&msg=action_invalide'));
    exit;
  }

  // Action : accepter
  if ($action === 'accept') {

    // Déjà un accepted ?
    $st = $pdo->prepare("SELECT COUNT(*) FROM offers WHERE move_id = ? AND status = 'accepted'");
    $st->execute([$moveId]);
    $acceptedCount = (int)$st->fetchColumn();

    if ($acceptedCount > 0) {
      header('Location: ' . url('client/move_detail.php?id=' . $moveId . '&msg=action_invalide'));
      exit;
    }

    // Transaction : accepter cette offre, rejeter les autres, mettre l'annonce en pause
    $pdo->beginTransaction();
    try {
      $pdo->prepare("UPDATE offers SET status='accepted' WHERE id=?")->execute([$offerId]);
      $pdo->prepare("UPDATE offers SET status='rejected' WHERE move_id=? AND id<>?")
          ->execute([$moveId, $offerId]);

      // Désactiver l'annonce (pause) une fois le déménageur choisi
      $pdo->prepare("UPDATE moves SET is_active = 0 WHERE id = ?")->execute([$moveId]);

      $pdo->commit();
      header('Location: ' . url('client/move_detail.php?id=' . $moveId . '&msg=offer_accepted'));
      exit;

    } catch (Exception $e) {
      $pdo->rollBack();
      header('Location: ' . url('client/move_detail.php?id=' . $moveId . '&msg=action_invalide'));
      exit;
    }
  }

  // Action : refuser (on passe juste cette offre à "rejected")
  if ($action === 'reject') {
    // On ne refuse que si elle n'est pas déjà acceptée / rejetée
    if ($offer['status'] !== 'pending') {
      header('Location: ' . url('client/move_detail.php?id=' . $moveId . '&msg=action_invalide'));
      exit;
    }

    $up = $pdo->prepare("UPDATE offers SET status='rejected' WHERE id = ? AND move_id = ? AND status='pending'");
    $up->execute([$offerId, $moveId]);

    header('Location: ' . url('client/move_detail.php?id=' . $moveId . '&msg=offer_rejected'));
    exit;
  }
}

// 2) Données d'affichage à jour

// Offres (avec infos déménageur)
$offersSt = $pdo->prepare("
  SELECT o.id, o.price, o.message, o.status, o.created_at,
         u.id AS mover_id, u.nom, u.prenom, u.email
  FROM offers o
  JOIN users u ON u.id = o.mover_id
  WHERE o.move_id = ?
  ORDER BY 
    CASE WHEN o.status='accepted' THEN 0 ELSE 1 END,
    o.created_at ASC
");
$offersSt->execute([$moveId]);
$offers = $offersSt->fetchAll(PDO::FETCH_ASSOC);

$offersCount = count($offers);
$accSt = $pdo->prepare("SELECT COUNT(*) FROM offers WHERE move_id = ? AND status='accepted'");
$accSt->execute([$moveId]);
$acceptedAny = (int)$accSt->fetchColumn();

// Images liées à l'annonce
$imgSt = $pdo->prepare("SELECT filename FROM move_images WHERE move_id = ? ORDER BY id ASC");
$imgSt->execute([$moveId]);
$images = $imgSt->fetchAll(PDO::FETCH_COLUMN);

// Données d'affichage
$title         = $move['title'] ?? '';
$from          = $move['city_from'] ?? '';
$to            = $move['city_to'] ?? '';
$dateStart     = $move['date_start'] ?? '';
$volume        = (int)($move['volume_m3'] ?? 0);
$isActive      = (int)($move['is_active'] ?? 1);
$description   = trim((string)($move['description'] ?? ''));
$housingFrom   = trim((string)($move['housing_from'] ?? ''));
$housingTo     = trim((string)($move['housing_to'] ?? ''));

$statusLabel = $isActive ? 'open' : 'paused';
$statusClass = $isActive
  ? 'badge bg-success-subtle text-success-emphasis border'
  : 'badge bg-secondary-subtle text-secondary-emphasis border';

// 3) Output
require __DIR__ . '/../include/header_client.php';

// Flashs
$flashKey = $_GET['msg'] ?? null;
$flashMap = [
  'offer_accepted' => ['type' => 'success', 'text' => "Déménageur choisi avec succès. L'annonce est maintenant en pause."],
  'offer_rejected' => ['type' => 'info',    'text' => "L’offre de ce déménageur a été refusée."],
  'images_ok'      => ['type' => 'success', 'text' => "Images ajoutées avec succès."],
  'action_invalide'=> ['type' => 'warning', 'text' => "Action invalide ou refusée."],
];
?>
<?php if ($flashKey && isset($flashMap[$flashKey])): ?>
  <div class="container" style="max-width:980px;">
    <div class="alert alert-<?= $flashMap[$flashKey]['type'] ?> movego-alert shadow-sm d-flex align-items-center justify-content-between" role="alert">
      <div class="me-3"><?= $flashMap[$flashKey]['text'] ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
  </div>
<?php endif; ?>

<div class="container container-narrow py-4">
  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Annonce</div>
    <div class="cta">
      <a class="btn btn-outline-secondary" href="<?= url('client/my_moves.php') ?>">← Retour</a>
    </div>
  </div>

  <!-- Carte récap de l'annonce -->
  <div class="card-move mb-3">
    <div class="d-flex flex-wrap justify-content-between gap-2">
      <div class="d-flex flex-column">
        <div class="badge-soft mb-2"><?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?></div>
        <h2 class="h6 m-0"><?= htmlspecialchars($title ?: 'Sans titre') ?></h2>
        <div class="small-muted">
          <?= $dateStart ? 'Le ' . htmlspecialchars($dateStart) : '' ?>
          <?= $volume ? ' • ' . (int)$volume . ' m³' : '' ?>
        </div>

        <?php if ($housingFrom || $housingTo): ?>
          <div class="mt-2 small-muted">
            <?php if ($housingFrom): ?>
              <div><strong>Départ :</strong> <?= htmlspecialchars($housingFrom) ?></div>
            <?php endif; ?>
            <?php if ($housingTo): ?>
              <div><strong>Arrivée :</strong> <?= htmlspecialchars($housingTo) ?></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if ($description !== ''): ?>
          <div class="mt-2">
            <div class="small-muted mb-1" style="font-weight:600;">Description</div>
            <p class="mb-0"><?= nl2br(htmlspecialchars($description)) ?></p>
          </div>
        <?php endif; ?>
      </div>

      <div class="text-end">
        <div class="small mb-1">Propositions : <strong><?= $offersCount ?></strong></div>
        <span class="<?= $statusClass ?>"><?= $statusLabel ?></span>
        <?php if ($acceptedAny > 0): ?>
          <span class="badge bg-teal text-white ms-1" style="background:#118a96;">choisi</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Galerie d’images -->
  <?php if ($images): ?>
    <div class="mb-4">
      <div class="small-muted mb-2" style="font-weight:600;">Photos</div>
      <div class="row g-3">
        <?php foreach ($images as $fn): 
          $src = asset('uploads/moves/' . $fn);
        ?>
          <div class="col-6 col-md-4 col-lg-3">
            <div class="ratio ratio-4x3">
              <img src="<?= $src ?>" alt="Photo" class="img-fluid rounded-3 border" style="object-fit:cover;">
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Liste des propositions -->
  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Propositions reçues</div>
    <?php if ($offersCount === 0): ?>
      <span class="small-muted">Aucune proposition pour l’instant.</span>
    <?php endif; ?>
  </div>

  <div class="row g-3">
    <?php foreach ($offers as $of):
      $isAccepted = ($of['status'] === 'accepted');
      $isRejected = ($of['status'] === 'rejected');
      $badge      = $isAccepted ? 'badge bg-success' : ($isRejected ? 'badge bg-secondary' : 'badge bg-info');
      $badgeText  = $isAccepted ? 'acceptée' : ($isRejected ? 'rejetée' : 'nouvelle');
    ?>
      <div class="col-12">
        <div class="card-move">
          <div class="d-flex flex-wrap justify-content-between gap-2">
            <div class="d-flex flex-column">
              <div class="badge-soft mb-2">
                <?= htmlspecialchars(($of['prenom'] ?? '').' '.($of['nom'] ?? '')) ?>
                <span class="small-muted">• <?= htmlspecialchars($of['email'] ?? '') ?></span>
              </div>
              <div class="small-muted"><?= htmlspecialchars($of['created_at'] ?? '') ?></div>
              <?php if (!empty($of['message'])): ?>
                <div class="mt-2"><?= nl2br(htmlspecialchars($of['message'])) ?></div>
              <?php endif; ?>
            </div>

            <div class="text-end">
              <div class="h5 m-0">
                <?= isset($of['price']) ? number_format((float)$of['price'], 0, ',', ' ') . ' €' : '-' ?>
              </div>
              <span class="<?= $badge ?>"><?= $badgeText ?></span>
            </div>
          </div>

          <div class="mt-3 d-flex gap-2 flex-wrap">

            <!-- Bouton de messagerie -->
            <a class="btn btn-sm btn-outline-secondary"
               href="<?= url('client/questions.php?offer_id=' . (int)$of['id']) ?>">
              Messagerie
            </a>

            <!-- Boutons accepter / refuser (uniquement si aucune offre encore acceptée et si celle-ci n'est pas rejetée) -->
            <?php if ($acceptedAny === 0 && !$isRejected && !$isAccepted): ?>
              <form method="post" action="<?= url('client/move_detail.php?id=' . $moveId) ?>" class="d-inline">
                <?php if (function_exists('csrf_field')) { csrf_field(); } ?>
                <input type="hidden" name="action" value="accept">
                <input type="hidden" name="offer_id" value="<?= (int)$of['id'] ?>">
                <button class="btn btn-sm btn-primary" type="submit">Choisir ce déménageur</button>
              </form>

              <form method="post" action="<?= url('client/move_detail.php?id=' . $moveId) ?>" class="d-inline">
                <?php if (function_exists('csrf_field')) { csrf_field(); } ?>
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="offer_id" value="<?= (int)$of['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit">Refuser ce déménageur</button>
              </form>
            <?php endif; ?>

          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/../include/footer.php'; ?>
