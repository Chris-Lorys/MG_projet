<?php
// how_it_works.php — Page publique "Comment ça marche ?"
require __DIR__ . '/include/header_client.php';   // ✅ header PUBLIC

// Savoir si un client est déjà connecté pour adapter les CTA
$isClient = is_logged_in() && ((current_user()['role'] ?? '') === 'client');
?>
<section class="hero">
  <div class="container">
    <h1 class="display-6 mb-2">Comment ça marche ?</h1>
    <p class="small-muted m-0">
      Publiez une annonce claire, recevez des propositions de déménageurs, comparez et choisissez en un clic.
    </p>
    <div class="cta d-flex gap-2 justify-content-center">
      <?php if ($isClient): ?>
        <a class="btn btn-primary" href="<?= url('client/create_move.php') ?>">Créer une annonce</a>
        <a class="btn btn-outline-secondary" href="<?= url('client/my_moves.php') ?>">Mes annonces</a>
      <?php else: ?>
        <a class="btn btn-primary" href="<?= url('auth/register.php') ?>">Créer un compte</a>
        <a class="btn btn-outline-secondary" href="<?= url('auth/login.php') ?>">Se connecter</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="pt-3 pb-4">
  <div class="container container-narrow">

    <!-- Étapes principales -->
    <h2 class="h6 text-uppercase small-muted mb-2">Les 4 étapes</h2>
    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="d-flex align-items-start gap-3">
            <div class="circle"><img src="<?= asset('assets/img/add.svg') ?>" width="36" height="36" alt="Créer l’annonce"></div>
            <div>
              <div class="h6 mb-1">1) Créer l’annonce</div>
              <p class="small-muted mb-2">
                Renseignez le titre, la ville de départ et d’arrivée, la date/heure, le volume estimé (m³),
                les contraintes d’accès (étage, ascenseur, portail…) et <strong>ajoutez des photos</strong>.
              </p>
              <?php if ($isClient): ?>
                <a class="btn btn-sm btn-primary" href="<?= url('client/create_move.php') ?>">Créer ma première annonce</a>
              <?php else: ?>
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('auth/login.php') ?>">Se connecter pour créer</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="d-flex align-items-start gap-3">
            <div class="circle"><img src="<?= asset('assets/img/mail.svg') ?>" width="36" height="36" alt="Recevoir des propositions"></div>
            <div>
              <div class="h6 mb-1">2) Recevoir des propositions</div>
              <p class="small-muted mb-0">
                Les déménageurs voient votre annonce et proposent un <strong>prix</strong> accompagné d’un message.
                Vous retrouvez toutes les offres dans l’onglet <em>Mes annonces</em>.
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="d-flex align-items-start gap-3">
            <div class="circle"><img src="<?= asset('assets/img/people.svg') ?>" width="36" height="36" alt="Comparer & choisir"></div>
            <div>
              <div class="h6 mb-1">3) Comparer & choisir</div>
              <p class="small-muted mb-0">
                Comparez les offres (prix + message). Lorsque vous cliquez sur
                <strong>« Choisir ce déménageur »</strong>, l’annonce est mise en pause et
                les autres offres passent en “rejetée”.
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="d-flex align-items-start gap-3">
            <div class="circle"><img src="<?= asset('assets/img/truck.svg') ?>" width="36" height="36" alt="Déménager sereinement"></div>
            <div>
              <div class="h6 mb-1">4) Déménager sereinement</div>
              <p class="small-muted mb-0">
                Le jour J, tout est clair : itinéraire, volume, contraintes et timing.
                Les photos aident les déménageurs à venir avec le matériel adapté.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Ce que voit le public -->
    <div class="card-move mt-3">
      <h3 class="h6 mb-2">Ce que voit un visiteur (public)</h3>
      <p class="mb-2">
        Côté public, les annonces affichent uniquement les <strong>informations essentielles</strong> : 
        <em>titre, villes, date, volume et photos</em>. Le nom du client n’est <strong>jamais</strong> visible publiquement.
      </p>
      <ul class="small-muted mb-0">
        <li>Respect de la confidentialité (pas de nom, ni d’e-mail côté public)</li>
        <li>Annonce claire pour estimer l’intervention</li>
        <li>Les détails privés restent dans l’espace client sécurisé</li>
      </ul>
    </div>

    <!-- Conseils -->
    <h2 class="h6 text-uppercase small-muted mt-4 mb-2">Bien préparer son annonce</h2>
    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="h6 mb-1">Descriptions utiles</div>
          <p class="small-muted mb-0">
            Indiquez si certains meubles sont <em>démontables</em>, s’il y a des objets <em>fragiles</em>,
            si le stationnement est facile, ou si un <em>badge/porte</em> est nécessaire.
          </p>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="h6 mb-1">Photos parlantes</div>
          <p class="small-muted mb-0">
            Ajoutez quelques photos des pièces, couloirs, escaliers/ascenseur et objets volumineux.
            Ça aide à éviter les imprévus et à avoir des offres plus justes.
          </p>
        </div>
      </div>
    </div>

    <!-- FAQ -->
    <h2 class="h6 text-uppercase small-muted mt-4 mb-2">FAQ</h2>
    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="h6 mb-1">Puis-je mettre en pause une annonce ?</div>
          <p class="small-muted mb-0">
            Oui. Depuis <em>Mes annonces</em>, utilisez le bouton <strong>Mettre en pause</strong>.
            Vous pourrez la <strong>réactiver</strong> quand vous voulez.
          </p>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="h6 mb-1">Combien d’images sont acceptées ?</div>
          <p class="small-muted mb-0">
            Jusqu’à 10 images (JPEG/PNG/WEBP), 5&nbsp;Mo max par fichier.
          </p>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="h6 mb-1">Le nom du client est-il public ?</div>
          <p class="small-muted mb-0">
            Non. Le nom n’apparaît <strong>jamais</strong> dans les pages publiques.
          </p>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="h6 mb-1">Que se passe-t-il quand je choisis un déménageur ?</div>
          <p class="small-muted mb-0">
            L’offre sélectionnée passe en <em>acceptée</em>, les autres en <em>rejetée</em> et l’annonce est
            automatiquement <strong>mise en pause</strong>.
          </p>
        </div>
      </div>
    </div>

    <!-- Appel à l'action -->
    <div class="card-move mt-3">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
          <div class="h6 m-0">Prêt à vous lancer ?</div>
          <div class="small-muted">Créez votre annonce en 2 minutes et recevez vos premières propositions.</div>
        </div>
        <div class="d-flex gap-2">
          <?php if ($isClient): ?>
            <a class="btn btn-primary" href="<?= url('client/create_move.php') ?>">Créer une annonce</a>
            <a class="btn btn-outline-secondary" href="<?= url('client/my_moves.php') ?>">Mes annonces</a>
          <?php else: ?>
            <a class="btn btn-primary" href="<?= url('auth/register.php') ?>">Créer un compte</a>
            <a class="btn btn-outline-secondary" href="<?= url('auth/login.php') ?>">Se connecter</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/include/footer.php'; ?>

<style>
/* Étapes : pastilles d'icônes */
/* Cercles d’icônes (uniformes avec la home) */
.card-move .circle,
.step .circle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #bfe7e6; /* fond pastel clair */
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 8px rgba(0, 0, 0, .1);
  flex-shrink: 0;
}

.card-move .circle img,
.step .circle img {
  width: 32px;
  height: 32px;
  filter: none; /* ✅ important : pas d’inversion */
  opacity: 0.95;
}



</style>
