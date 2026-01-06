<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header_public.php';
?>

<section class="hero" aria-labelledby="home-hero-title">
 <div class="hero-inner ff-glass">


    <img src="assets/images/logo.jpg" alt="ÉtudeSync" class="logo-center" />
    <h1 id="home-hero-title">ÉtudeSync</h1>
    <p class="tagline">Study • Connect • Grow — All in one space.</p>
    <p><a class="btn primary" href="login.php">Get Started</a></p>
  </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.body.classList.add('index-page');
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
