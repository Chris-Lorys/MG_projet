<?php
require __DIR__ . '/include/header.php';

$stmt = $pdo->query("
  SELECT m.id, m.title, m.city_from, m.city_to,
         DATE_FORMAT(m.date_start, '%d/%m/%Y') AS d, m.volume_m3
  FROM moves m
  WHERE m.is_active = 1
  ORDER BY m.created_at DESC
  LIMIT 6
");
$moves = $stmt->fetchAll();
?>
<div class="hero">
  <div class="container">
    <h1 class="display-5 mb-3">Déménager ensemble,<br> c'est plus simple !</h1>
    <div class="cta">
      <!-- ❗️ Utiliser url() au lieu de chemins absolus -->
      <a
        href="<?= (is_logged_in() && current_user()['role'] === 'client')
                  ? url('client/create_move.php')
                  : url('auth/login.php'); ?>"
        class="btn btn-primary"
      >
        Créer une annonce
      </a>
    </div>
  </div>
</div>

<div class="pb-4">
  <div class="container">
    <div class="search-strip">
      <form class="row g-2 align-items-center" method="get" action="<?= url('search.php') ?>">
        <div class="col-12 col-md-3">
          <span class="small-muted">Annonces Rapides</span>
        </div>
        <div class="col-12 col-md">
          <input name="from" class="form-control" placeholder="Ville de départ">
        </div>
        <div class="col-12 col-md">
          <input name="to" class="form-control" placeholder="Ville d'arrivée">
        </div>
        <div class="col-12 col-md-2">
          <input name="date" type="date" class="form-control" >
        </div>
        <div class="col-12 col-md-auto d-grid">
          <button class="btn btn-primary" type="submit">Rechercher</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="pt-4">
  <div class="container">
    <div class="row g-3">
      <?php foreach ($moves as $mv): ?>
        <div class="col-12 col-md-6 col-lg-4">
          <article class="card-move">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="badge-soft">
                <?= htmlspecialchars($mv['city_from']) ?> → <?= htmlspecialchars($mv['city_to']) ?>
              </span>
              <span class="small-muted"><?= htmlspecialchars($mv['d']) ?></span>
            </div>
            <h3 class="h6 mb-1"><?= htmlspecialchars($mv['title']) ?></h3>
            <div class="small-muted mb-2"><?= (int)$mv['volume_m3'] ?> m³</div>
            <!-- ❗️ Utiliser url() (et pas /client/…) -->
            <a class="btn btn-sm btn-primary"
               href="<?= url('visitor/move_preview_visitor.php') ?>?id=<?= (int)$mv['id'] ?>">
               Voir
            </a>
          </article>
        </div>
      <?php endforeach; ?>
      <?php if (empty($moves)): ?>
        <div class="col-12">
          <div class="alert alert-warning">Aucune annonce pour le moment.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
      </div>

<div class="py-5">
  <div class="container">
    <h2 class="h5 mb-3">Comment ça marche ?</h2>
    <div class="row g-4">
      <div class="col-6 col-lg-3">
        <div class="step">
          <div class="circle"><img src="<?= asset('assets/img/add.svg') ?>" width="40" alt="Add"></div>
          <div class="step-title">Créer une annonce</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="step">
          <div class="circle"><img src="<?= asset('assets/img/mail.svg') ?>" width="40" alt="Mail"></div>
          <div class="step-title">Recevoir des propositions</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="step">
          <div class="circle"><img src="<?= asset('assets/img/people.svg') ?>" width="40" alt="People"></div>
          <div class="step-title">Choisir les déménageurs</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="step">
          <div class="circle"><img src="<?= asset('assets/img/truck.svg') ?>" width="40" alt="Truck"></div>
          <div class="step-title">Déménager</div>
        </div>
      </div>
    </div>
  </div>
      </div>

<?php require __DIR__ . '/include/footer.php'; ?>
