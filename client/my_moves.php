<?php
// === client/my_moves.php ===
require __DIR__ . '/../include/header_client.php';

// Sécurité
$u = current_user();
if (!$u || (($u['role'] ?? '') !== 'client')) {
  header('Location: ' . url('auth/login.php'));
  exit;
}

// Récupérer annonces + métriques
$stmt = $pdo->prepare("
  SELECT m.id, m.title, m.description, m.city_from, m.city_to, m.date_start, m.volume_m3,
         m.is_active,
         (SELECT COUNT(*) FROM offers o WHERE o.move_id = m.id) AS offers_count,
         (SELECT COUNT(*) FROM offers o WHERE o.move_id = m.id AND o.status = 'accepted') AS accepted_count
  FROM moves m
  WHERE m.client_id = ?
  ORDER BY m.created_at DESC
");
$stmt->execute([$u['id']]);
$moves = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats synthétiques
$totalMoves  = count($moves);
$totalOffers = 0;
$acceptedAny = 0;
foreach ($moves as $mv) {
  $totalOffers += (int)($mv['offers_count'] ?? 0);
  if ((int)($mv['accepted_count'] ?? 0) > 0) $acceptedAny++;
}

// Flash
$flashKey = $_GET['msg'] ?? null;
$flashMap = [
  // ✅ ajouté pour l’affichage après création
  'annonce_creee'      => ['type' => 'success', 'text' => "Annonce créée avec succès. Elle est maintenant visible pour les déménageurs."],
  'annonce_activee'    => ['type' => 'success', 'text' => "Annonce réactivée et visible pour les déménageurs."],
  'annonce_desactivee' => ['type' => 'info',    'text' => "Annonce mise en pause. Elle n'est plus visible publiquement."],
  'images_ok'          => ['type' => 'success', 'text' => "Images ajoutées avec succès."],
  'action_invalide'    => ['type' => 'warning', 'text' => "Action invalide ou incomplète."],
  'not_owner'          => ['type' => 'danger',  'text' => "Accès refusé : cette annonce ne vous appartient pas."]
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

<!-- Bienvenue -->
<div class="welcome-banner">
  <h1>👋 Bienvenue <span class="username"><?= htmlspecialchars(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')) ?></span> !</h1>
  <p>Heureux de vous revoir sur votre espace Move & Go. Consultez vos annonces ou créez-en une nouvelle dès maintenant.</p>
</div>

<div class="container container-narrow py-4">
  <!-- Titre + CTA -->
  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Mes annonces</div>
    <div class="cta">
      <a class="btn btn-primary fw-semibold" href="<?= url('client/create_move.php') ?>">Créer une annonce</a>
    </div>
  </div>

  <!-- Indicateurs -->
  <div class="row g-3 stats-row align-items-stretch">
    <div class="col-12 col-md-6 col-lg-4">
      <div class="stat-card h-100">
        <div class="stat-label">Annonces</div>
        <div class="stat-value"><?= (int)$totalMoves ?></div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
      <div class="stat-card h-100">
        <div class="stat-label">Propositions reçues</div>
        <div class="stat-value"><?= (int)$totalOffers ?></div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
      <div class="stat-card h-100">
        <div class="stat-label">Déménageur choisi</div>
        <div class="stat-value"><?= (int)$acceptedAny ?></div>
      </div>
    </div>
  </div>

  <?php if (!$moves): ?>
    <!-- État vide -->
    <div class="empty-hero mt-3">
      <div class="illu" aria-hidden="true">
        <svg width="140" height="100" viewBox="0 0 200 140" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="14" y="48" width="120" height="62" rx="10" fill="#E8F8F7" stroke="#9ed8d7"/>
          <rect x="134" y="63" width="52" height="47" rx="8" fill="#CDEEEE" stroke="#9ed8d7"/>
          <rect x="26" y="62" width="32" height="20" rx="4" fill="#bfe7e6"/>
          <circle cx="50" cy="114" r="11" fill="#bfe7e6" stroke="#8ecfd0"/>
          <circle cx="158" cy="114" r="11" fill="#bfe7e6" stroke="#8ecfd0"/>
          <path d="M120 48 L146 32" stroke="#9ed8d7" stroke-width="4" stroke-linecap="round"/>
          <path d="M146 32 L178 44" stroke="#9ed8d7" stroke-width="4" stroke-linecap="round"/>
        </svg>
      </div>
      <div class="content">
        <h2>Tu n’as pas encore d’annonce</h2>
        <p>Crée ta première demande en 2 minutes pour recevoir des propositions de déménageurs vérifiés.</p>
        <div class="d-flex gap-2 flex-wrap">
          <a href="<?= url('client/create_move.php') ?>" class="btn btn-primary">+ Nouvelle annonce</a>
          <a href="<?= url('about_client.php') ?>" class="btn btn-outline-secondary">Comment ça marche ?</a>
        </div>
      </div>
    </div>

  <?php else: ?>
    <!-- Liste des annonces -->
    <div class="row g-3 mt-1">
      <?php foreach ($moves as $mv): ?>
        <?php
          $id           = (int)$mv['id'];
          $from         = $mv['city_from'] ?? '';
          $to           = $mv['city_to'] ?? '';
          $title        = $mv['title'] ?? '';
          $dateStart    = $mv['date_start'] ?? '';
          $volume       = (int)($mv['volume_m3'] ?? 0);
          $offersCount  = (int)($mv['offers_count'] ?? 0);
          $acceptedCnt  = (int)($mv['accepted_count'] ?? 0);
          $isActive     = (int)($mv['is_active'] ?? 1);
          $desc         = trim((string)($mv['description'] ?? ''));

          $statusLabel  = $isActive ? 'open' : 'paused';
          $statusClass  = $isActive ? 'badge bg-success-subtle text-success-emphasis border'
                                    : 'badge bg-secondary-subtle text-secondary-emphasis border';
        ?>
        <div class="col-12">
          <div class="card-move">
            <div class="d-flex flex-wrap justify-content-between gap-2">
              <div class="d-flex flex-column">
                <div class="badge-soft mb-2">
                  <?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?>
                </div>
                <h2 class="h6 m-0"><?= htmlspecialchars($title ?: 'Sans titre') ?></h2>
                <div class="small-muted">
                  <?= $dateStart ? 'Le ' . htmlspecialchars($dateStart) : '' ?>
                  <?= $volume ? ' • ' . (int)$volume . ' m³' : '' ?>
                </div>
                <?php if ($desc !== ''): ?>
                  <p class="move-desc mt-2 mb-0">
                    <?= nl2br(htmlspecialchars($desc)) ?>
                  </p>
                <?php endif; ?>
              </div>

              <div class="text-end">
                <div class="small mb-1">Propositions : <strong><?= $offersCount ?></strong></div>
                <span class="<?= $statusClass ?>"><?= $statusLabel ?></span>
                <?php if ($acceptedCnt > 0): ?>
                  <span class="badge bg-teal text-white ms-1" style="background:#118a96;">choisi</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="mt-3 d-flex flex-wrap gap-2">
              <a class="btn btn-sm btn-primary"
                 href="<?= url('client/move_detail.php?id=' . $id) ?>">
                Voir les propositions
              </a>

              <a class="btn btn-sm btn-outline-secondary"
                 href="<?= url('client/move_preview.php?id=' . $id) ?>"
                 target="_blank" rel="noopener">
                 Aperçu public
              </a>

              <?php if ($acceptedCnt === 0): ?>
                <?php if ($isActive): ?>
                  <a class="btn btn-sm btn-outline-danger"
                     href="<?= url('client/move_toggle.php?id=' . $id . '&state=0') ?>">
                    Mettre en pause
                  </a>
                <?php else: ?>
                  <a class="btn btn-sm btn-outline-success"
                     href="<?= url('client/move_toggle.php?id=' . $id . '&state=1') ?>">
                    Réactiver
                  </a>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<!-- Bouton flottant -->
<a href="<?= url('client/create_move.php') ?>" class="fab" title="Créer une annonce">+</a>

<?php require __DIR__ . '/../include/footer.php'; ?>
