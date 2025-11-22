
<?php
require __DIR__ . '/../include/header.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  check_csrf();
  $nom=trim($_POST['nom']??'');
  $prenom=trim($_POST['prenom']??'');
  $email=trim($_POST['email']??'');
  $pass=$_POST['password']??'';
  $role=$_POST['role']??'client';
  $pattern = '/^(?=.*[A-Z])(?=.*[!@#$%^&*.?]).{8,}$/';

  // Si le mot de passe ne respecte pas la regex → erreur unique
  

  if(!in_array($role,['client','demenageur'],true)) $role='client';
  $ex=$pdo->prepare("SELECT id FROM users WHERE email=?"); $ex->execute([$email]);
  if($ex->fetch()){ $err="Cet email est déjà utilisé."; }
  elseif (!preg_match($pattern, $pass)) {
        $errors="Mot de passe invalide : il doit contenir au moins 8 caractères, une majuscule et un caractère spécial.";
      }
  else{
    $hash=password_hash($pass,PASSWORD_BCRYPT);
    $pdo->prepare("INSERT INTO users(nom,prenom,email,password_hash,role,is_active,created_at) VALUES(?,?,?,?,?,1,NOW())")
        ->execute([$nom,$prenom,$email,$hash,$role]);
    $ok="Compte créé avec succès. Vous allez être redirigé vers la page de connexion dans 3 secondes !";
  }
}
?>

    
       <div class="container">
  <div class="auth-wrap">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6">
        <div class="auth-card">
          <h1 class="h4 mb-3">Inscription</h1>
        <?php if (!empty($ok)): ?><div class="alert alert-success"><?php echo htmlspecialchars($ok); ?></div><?php endif; ?>
        <?php if (!empty($err)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
        <?php if (!empty($errors)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($errors); ?></div><?php endif; ?>
        <form method="post" action="register.php" class="row g-3">
          <?php csrf_field(); ?>
          <div class="col-12">
            <label class="form-label">Nom</label>
            <input class="form-control" name="nom" value="<?= htmlspecialchars($nom ?? '') ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Prenom</label>
            <input class="form-control" name="prenom" value="<?= htmlspecialchars($prenom ?? '') ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Mot de passe</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Rôle</label>
            <select class="form-select" name="role">
              <option value="client">Client</option>
              <option value="demenageur">Déménageur</option>
            </select>
          </div>
          <div class="col-12">
            <button class="btn btn-primary w-100 fw-semibold">Créer mon compte</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php  if(!empty($ok)):?> <meta http-equiv="refresh" content="3;url=login.php"><?php endif;?>

<?php require __DIR__ . '/../include/footer.php'; ?>

