<?php
// === client/create_move.php ===
// Logique serveur (POST) avant tout output
require __DIR__ . '/../config.php';

$u = current_user();
if (!$u || (($u['role'] ?? '') !== 'client')) {
  header('Location: ' . url('auth/login.php'));
  exit;
}

$errors = [];

// Répertoire d'upload : /uploads/moves (à la racine du projet)
$rootDir   = realpath(__DIR__ . '/..'); // parent de /client
$uploadDir = $rootDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'moves';
if (!is_dir($uploadDir)) {
  @mkdir($uploadDir, 0775, true);
}

// POST: création d’annonce
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (function_exists('check_csrf')) { check_csrf(); }

  // Champs simples
  $title       = trim((string)($_POST['title'] ?? ''));
  $description = trim((string)($_POST['description'] ?? ''));
  $city_from   = trim((string)($_POST['city_from'] ?? ''));
  $city_to     = trim((string)($_POST['city_to'] ?? ''));
  $date_start  = trim((string)($_POST['date_start'] ?? ''));  // HTML5 datetime-local
  $volume_m3   = (int)($_POST['volume_m3'] ?? 0);
  $needed      = (int)($_POST['needed'] ?? 0);

  // Logement départ/arrivée -> stocké en texte (housing_from / housing_to)
  $from_floor    = trim((string)($_POST['from_floor'] ?? ''));
  $from_has_lift = !empty($_POST['from_has_lift']) ? 'Ascenseur: oui' : 'Ascenseur: non';
  $from_detail   = trim((string)($_POST['from_detail'] ?? ''));
  $housing_from  = 'Étage: ' . ($from_floor === '' ? 'NC' : $from_floor) . ', ' . $from_has_lift
                   . ($from_detail !== '' ? ', ' . $from_detail : '');

  $to_floor      = trim((string)($_POST['to_floor'] ?? ''));
  $to_has_lift   = !empty($_POST['to_has_lift']) ? 'Ascenseur: oui' : 'Ascenseur: non';
  $to_detail     = trim((string)($_POST['to_detail'] ?? ''));
  $housing_to    = 'Étage: ' . ($to_floor === '' ? 'NC' : $to_floor) . ', ' . $to_has_lift
                   . ($to_detail !== '' ? ', ' . $to_detail : '');

  // Validations
  if ($title === '')       $errors[] = "Le titre est requis.";
  if ($city_from === '')   $errors[] = "La ville de départ est requise.";
  if ($city_to === '')     $errors[] = "La ville d’arrivée est requise.";
  if ($date_start === '')  $errors[] = "La date et l’horaire de début sont requis.";
  if ($volume_m3 < 0)      $errors[] = "Le volume doit être un nombre positif.";
  if ($needed < 0)         $errors[] = "Le nombre de déménageurs doit être un nombre positif.";

  // Pré-validation des images
  // Table attendue :
  //   CREATE TABLE move_images (
  //     id INT AUTO_INCREMENT PRIMARY KEY,
  //     move_id INT NOT NULL,
  //     filename VARCHAR(255) NOT NULL,
  //     created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  //     FOREIGN KEY (move_id) REFERENCES moves(id) ON DELETE CASCADE
  //   );
  $filesOk = [];
  if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $maxCount    = 10;                 // max 10 images
    $maxSize     = 5 * 1024 * 1024;    // 5 Mo
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

    $count = count($_FILES['images']['name']);
    if ($count > $maxCount) {
      $errors[] = "Tu peux téléverser au maximum $maxCount images.";
    } else {
      for ($i = 0; $i < $count; $i++) {
        $err  = (int)$_FILES['images']['error'][$i];
        $size = (int)$_FILES['images']['size'][$i];
        $tmp  = (string)$_FILES['images']['tmp_name'][$i];
        $name = (string)$_FILES['images']['name'][$i];

        if ($err === UPLOAD_ERR_NO_FILE) continue; // aucun fichier à cet index

        if ($err !== UPLOAD_ERR_OK) {
          $errors[] = "Erreur à l’upload du fichier « $name » (code $err).";
          continue;
        }
        if ($size <= 0 || $size > $maxSize) {
          $errors[] = "Fichier « $name » trop volumineux (max 5 Mo).";
          continue;
        }

        // Détection du mime via finfo si dispo
        $mime = null;
        if (function_exists('finfo_open')) {
          $fi = @finfo_open(FILEINFO_MIME_TYPE);
          if ($fi) {
            $mime = @finfo_file($fi, $tmp);
            @finfo_close($fi);
          }
        }
        if (!$mime) {
          // fallback basique par extension
          $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
          $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg'
                : ($ext === 'png' ? 'image/png'
                : ($ext === 'webp' ? 'image/webp' : ''));
        }
        if (!in_array($mime, $allowedMime, true)) {
          $errors[] = "Format non supporté pour « $name » (JPEG, PNG, WEBP).";
          continue;
        }

        $filesOk[] = ['tmp' => $tmp, 'orig' => $name, 'mime' => $mime, 'size' => $size];
      }
    }
  }

  // Si tout est bon -> Insert + upload
  if (!$errors) {
    // Convertir "YYYY-MM-DDTHH:MM" -> "YYYY-MM-DD HH:MM:SS"
    $date_sql = str_replace('T', ' ', $date_start) . ':00';

    $pdo->beginTransaction();
    try {
      // Insertion de l’annonce
      $ins = $pdo->prepare("
        INSERT INTO moves
          (client_id, title, description, city_from, city_to, date_start, volume_m3, needed,
           housing_from, housing_to, is_active)
        VALUES
          (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
      ");
      $ins->execute([
        (int)$u['id'], $title, $description, $city_from, $city_to, $date_sql,
        $volume_m3, $needed, $housing_from, $housing_to
      ]);
      $moveId = (int)$pdo->lastInsertId();

      // Sauvegarde des images
      if ($moveId && $filesOk) {
        $imgIns = $pdo->prepare("INSERT INTO move_images (move_id, filename) VALUES (?, ?)");
        foreach ($filesOk as $f) {
          $ext = strtolower(pathinfo($f['orig'], PATHINFO_EXTENSION));
          if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            // normaliser extension selon mime
            $ext = $f['mime'] === 'image/png' ? 'png'
                 : ($f['mime'] === 'image/webp' ? 'webp' : 'jpg');
          }
          $newName = 'move_' . $moveId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
          $dest    = $uploadDir . DIRECTORY_SEPARATOR . $newName;

          if (!@move_uploaded_file($f['tmp'], $dest)) {
            throw new RuntimeException("Échec du déplacement de « {$f['orig']} ».");
          }

          $imgIns->execute([$moveId, $newName]);
        }
      }

      $pdo->commit();
      // ✅ Message correct après création
      header('Location: ' . url('client/my_moves.php?msg=annonce_creee'));
      exit;

    } catch (Exception $e) {
      $pdo->rollBack();
      $errors[] = "Erreur lors de l’enregistrement : " . $e->getMessage();
    }
  }
}

// À partir d’ici on peut afficher
require __DIR__ . '/../include/header_client.php';
?>
<div class="container container-narrow py-4">

  <div class="d-flex align-items-center justify-content-between section-head">
    <div class="title">Créer une annonce</div>
    <div class="cta">
      <a href="<?= url('client/my_moves.php') ?>" class="btn btn-outline-secondary">← Retour à mes annonces</a>
    </div>
  </div>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form action="" method="post" enctype="multipart/form-data" class="card-move">
    <?php if (function_exists('csrf_field')) { csrf_field(); } ?>

    <div class="row g-3">
      <div class="col-12">
        <label class="form-label fw-semibold">Titre *</label>
        <input type="text" name="title" class="form-control" required
               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
               placeholder="Ex: Bordeaux → Marseille - Studio">
      </div>

      <div class="col-12">
        <label class="form-label fw-semibold">Description rapide</label>
        <textarea name="description" class="form-control" rows="4"
                  placeholder="Détaille ce qu’il faut savoir (fragile, démontage, stationnement, …)"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Ville de départ *</label>
        <input type="text" name="city_from" class="form-control" required
               value="<?= htmlspecialchars($_POST['city_from'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Ville d’arrivée *</label>
        <input type="text" name="city_to" class="form-control" required
               value="<?= htmlspecialchars($_POST['city_to'] ?? '') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Date & heure de début *</label>
        <input type="datetime-local" name="date_start" class="form-control" required
               value="<?= htmlspecialchars($_POST['date_start'] ?? '') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Volume (m³)</label>
        <input type="number" name="volume_m3" class="form-control" min="0" step="1"
               value="<?= htmlspecialchars($_POST['volume_m3'] ?? '0') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Nb de déménageurs</label>
        <input type="number" name="needed" class="form-control" min="0" step="1"
               value="<?= htmlspecialchars($_POST['needed'] ?? '0') ?>">
      </div>
    </div>

    <hr class="my-4">

    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="small-muted mb-2" style="font-weight:700;">Logement au départ</div>
        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label fw-semibold">Étage</label>
            <input type="number" name="from_floor" class="form-control"
                   placeholder="0 = RDC" value="<?= htmlspecialchars($_POST['from_floor'] ?? '') ?>">
          </div>
          <div class="col-sm-6 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="from_has_lift" id="from_lift"
                     <?= !empty($_POST['from_has_lift']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="from_lift">Ascenseur</label>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Détail</label>
            <input type="text" name="from_detail" class="form-control"
                   placeholder="Porte étroite, cave, etc."
                   value="<?= htmlspecialchars($_POST['from_detail'] ?? '') ?>">
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="small-muted mb-2" style="font-weight:700;">Logement à l’arrivée</div>
        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label fw-semibold">Étage</label>
            <input type="number" name="to_floor" class="form-control"
                   placeholder="0 = RDC" value="<?= htmlspecialchars($_POST['to_floor'] ?? '') ?>">
          </div>
          <div class="col-sm-6 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="to_has_lift" id="to_lift"
                     <?= !empty($_POST['to_has_lift']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="to_lift">Ascenseur</label>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Détail</label>
            <input type="text" name="to_detail" class="form-control"
                   placeholder="Accès camion, portail, etc."
                   value="<?= htmlspecialchars($_POST['to_detail'] ?? '') ?>">
          </div>
        </div>
      </div>
    </div>

    <hr class="my-4">

    <!-- Upload images -->
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label fw-semibold">Images (JPEG, PNG, WEBP) — facultatif</label>
        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
        <div class="small-muted mt-1">Jusqu’à 10 images, 5 Mo par fichier.</div>
      </div>
    </div>

    <div class="mt-4 d-flex gap-2">
      <button class="btn btn-primary" type="submit">Créer l’annonce</button>
      <a href="<?= url('client/my_moves.php') ?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../include/footer.php'; ?>
