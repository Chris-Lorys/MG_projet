<footer class="mg-footer mt-5">
  <div class="container py-3 d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">

    <!-- Colonne gauche : logo + nom -->
    <div class="d-flex align-items-center gap-2">
      <img src="<?= asset('assets/img/logo.png') ?>" alt="Move & Go" width="90" height="90"
           class="footer-logo">
      <div class="d-flex flex-column">
        <span class="fw-semibold">Move & Go</span>
        <span class="footer-muted small">&copy; <?= date('Y') ?> • Tous droits réservés</span>
      </div>
    </div>

    <!-- Colonne centre : liens -->
    <div class="d-flex flex-wrap justify-content-center gap-3">
      <a href="#" class="footer-link small">
        <i class="bi bi-file-earmark-text me-1"></i> Mentions légales
      </a>
      <a href="#" class="footer-link small">
        <i class="bi bi-shield-check me-1"></i> Politique de confidentialité
      </a>
      <a href="#" class="footer-link small">
        <i class="bi bi-envelope-open me-1"></i> Contact
      </a>
    </div>

    <!-- Colonne droite : badge projet -->
    <div class="text-md-end text-center">
      <span class="footer-badge small">
        <i class="bi bi-mortarboard-fill me-1"></i>
        Un projet de Daren & Marvin
      </span>
    </div>

  </div>
</footer>


<!-- Scripts Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</main>
</body>
</html>
