<?php
// auth/login.php
require __DIR__ . '/../include/header.php';

$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (function_exists('check_csrf')) { check_csrf(); }

    // Normalisation basique
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("
        SELECT id, nom, prenom, email, password_hash, role, is_active
        FROM users
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $validCreds = $user
        && (int)$user['is_active'] === 1
        && password_verify($pass, $user['password_hash']);

    if ($validCreds) {
        $_SESSION['user'] = [
            'id'     => (int)$user['id'],
            'email'  => $user['email'],
            'role'   => $user['role'],
            'nom'    => $user['nom'],
            'prenom' => $user['prenom'],
            'name'   => trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')),
        ];

        // Redirection prioritaire si "redirect" est fourni et interne
        if ($redirect && preg_match('#^/[a-zA-Z0-9/_\-.]*$#', $redirect)) {
            header('Location: ' . $redirect);
            exit;
        }

        // Sinon, redirection selon le rôle
        switch ($user['role']) {
            case 'client':
                header('Location: ' . url('client/my_moves.php')); exit;
            case 'demenageur':
                header('Location: ' . url('mover/dashboard.php')); exit;
            case 'admin':
                header('Location: ' . url('admin/index.php')); exit;
            default:
                header('Location: ' . url('index.php')); exit;
        }
    } else {
        $err = "Identifiants invalides.";
        if ($user && (int)$user['is_active'] !== 1) {
            $err = "Compte inactif. Contactez l’administrateur.";
        }
    }
}
?>
<div class="container auth-wrap">
  <div class="row justify-content-center w-100">
    <div class="col-sm-10 col-md-7 col-lg-5">
      <div class="auth-card">
        <h1 class="h4 mb-3">Connexion</h1>

        <?php if (!empty($_SESSION['flash_error'])): ?>
          <div class="alert alert-warning">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
          </div>
          <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if (!empty($err)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= url('auth/login.php') ?>">
          <?php if (function_exists('csrf_field')) { csrf_field(); } ?>
          <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect ?? '') ?>">

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" placeholder="Entrez votre adresse e-mail" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" placeholder="Entrez votre mot de passe" class="form-control" required>
          </div>

          <button class="btn btn-primary w-100 fw-semibold">Se connecter</button>
        </form>

        <p class="small text-muted mt-3 mb-0">
          Pas encore de compte ?
          <a href="<?= url('auth/register.php'); ?>">Inscription</a>
        </p>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../include/footer.php'; ?>
