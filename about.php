<?php
// about.php — Page publique "À propos"
require __DIR__ . '/include/header.php';
?>
<section class="hero">
  <div class="container">
    <h1 class="display-6 mb-2">À propos de Move & Go</h1>
    <p class="small-muted m-0">
      La plateforme qui met en relation des clients et des déménageurs vérifiés, en toute simplicité.
    </p>
    <div class="cta d-flex gap-2 justify-content-center">
      <a class="btn btn-primary" href="<?= url('auth/register.php') ?>">Créer un compte</a>
      <a class="btn btn-outline-secondary" href="<?= url('auth/login.php') ?>">Se connecter</a>
    </div>
  </div>
</section>

<section class="pt-3 pb-4">
  <div class="container container-narrow">
    <div class="card-move mb-3">
      <h2 class="h5 mb-2">Notre mission</h2>
      <p class="mb-2">
        Rendre le déménagement plus <strong>clair</strong>, <strong>rapide</strong> et <strong>équitable</strong> pour tout le monde.
        Move & Go vous permet de publier une annonce détaillée, de recevoir des propositions de déménageurs, puis
        de <strong>choisir en confiance</strong> selon le prix et les informations fournies.
      </p>
      <ul class="small-muted mb-0">
        <li>Des annonces structurées (trajet, volume, contraintes d’accès, photos…)</li>
        <li>Des déménageurs identifiés et notifiés instantanément</li>
        <li>Un suivi simple de vos propositions et de votre choix final</li>
      </ul>
    </div>

    <h2 class="h6 text-uppercase small-muted mb-2">Comment ça marche ?</h2>
    <div class="row g-3">
      <div class="col-6 col-lg-3">
        <div class="step">
          <div class="circle">
            <img src="<?= asset('assets/img/add.svg') ?>" width="36" height="36" alt="">
          </div>
          <div class="step-title">Créer une annonce</div>
          <div class="small-muted">Titre, villes, date, volume, contraintes, photos.</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="step">
          <div class="circle">
            <img src="<?= asset('assets/img/mail.svg') ?>" width="36" height="36" alt="">
          </div>
          <div class="step-title">Recevoir des offres</div>
          <div class="small-muted">Les déménageurs proposent un prix et un message.</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="step">
          <div class="circle">
            <img src="<?= asset('assets/img/people.svg') ?>" width="36" height="36" alt="">
          </div>
          <div class="step-title">Comparer & choisir</div>
          <div class="small-muted">Vous retenez le meilleur profil selon vos critères.</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="step">
          <div class="circle">
            <img src="<?= asset('assets/img/truck.svg') ?>" width="36" height="36" alt="">
          </div>
          <div class="step-title">Déménager</div>
          <div class="small-muted">Le jour J, tout est prêt — en avant !</div>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-1">
      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <h3 class="h6 mb-2">Transparence & sécurité</h3>
          <p class="mb-2">
            Move & Go ne partage pas de données sensibles côté public : un visiteur voit uniquement
            les <strong>informations essentielles</strong> (titre, villes, date, volume, photos).
            Le nom du client n’est <strong>jamais</strong> affiché publiquement.
          </p>
          <ul class="small-muted mb-0">
            <li>Annonces publiques limitées aux détails nécessaires</li>
            <li>Accès complet côté client depuis l’espace sécurisé</li>
            <li>Suivi des offres et validation en un clic</li>
          </ul>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <h3 class="h6 mb-2">Pour les déménageurs</h3>
          <p class="mb-2">
            Recevez des demandes ciblées et <strong>répondez directement</strong> avec votre prix et un message.
            Lorsque le client accepte votre proposition, l’annonce passe en pause et vous êtes notifié.
          </p>
          <ul class="small-muted mb-0">
            <li>Flux d’annonces actif et lisible</li>
            <li>Historique de vos propositions</li>
            <li>Process clair d’acceptation</li>
          </ul>
        </div>
      </div>
    </div>

    <h2 class="h6 text-uppercase small-muted mt-4 mb-2">Questions fréquentes</h2>
    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="h6 mb-1">Qui peut voir mon annonce ?</div>
          <p class="small-muted mb-0">
            Tout visiteur voit les détails essentiels (trajet, date, volume, photos).
            Les informations personnelles (nom, e-mail) ne sont visibles que dans l’espace client.
          </p>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="h6 mb-1">Puis-je modifier ou mettre en pause ?</div>
          <p class="small-muted mb-0">
            Oui. Depuis <em>Mes annonces</em>, vous pouvez <strong>mettre en pause</strong> une annonce,
            la <strong>réactiver</strong>, ou en créer une nouvelle en quelques secondes.
          </p>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="h6 mb-1">Combien d’images puis-je ajouter ?</div>
          <p class="small-muted mb-0">
            Jusqu’à 10 images par annonce (JPEG, PNG, WEBP — 5&nbsp;Mo max par fichier) pour aider
            les déménageurs à mieux estimer leur intervention.
          </p>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card-move h-100">
          <div class="h6 mb-1">Comment choisir un déménageur ?</div>
          <p class="small-muted mb-0">
            Dans <em>Mes annonces</em>, ouvrez l’annonce puis comparez les offres reçues.
            Cliquez sur <strong>Choisir ce déménageur</strong> pour valider : les autres offres passent en “rejetée”
            et l’annonce est automatiquement mise en pause.
          </p>
        </div>
      </div>
    </div>

    <div class="card-move mt-3">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
          <div class="h6 m-0">Prêt à démarrer ?</div>
          <div class="small-muted">Créez votre compte pour publier votre première annonce.</div>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-primary" href="<?= url('auth/register.php') ?>">Créer un compte</a>
          <a class="btn btn-outline-secondary" href="<?= url('auth/login.php') ?>">Se connecter</a>
        </div>
      </div>
    </div>

    <div class="text-center small-muted mt-3">
      Besoin d’aide ? <a href="<?= url('auth/login.php') ?>">Connectez-vous</a> et envoyez-nous un message depuis votre espace.
    </div>
  </div>
</section>

<?php require __DIR__ . '/include/footer.php'; ?>
