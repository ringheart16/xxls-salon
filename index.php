<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Welcome';
$services = db()->query("SELECT * FROM services WHERE is_active=1 ORDER BY id DESC LIMIT 3")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="hero-copy">
    <span class="eyebrow">WELCOME TO XXL’S SALON</span>
    <h1>Your beauty.<br><em>Your moment.</em></h1>
    <p>Refresh your look, relax, and feel confident. Discover salon services made to help you look and feel your best.</p>
    <div class="hero-actions"><a class="btn btn-primary" href="<?= e(BASE_URL) ?>/services.php">Explore Services</a><a class="btn btn-light" href="<?= e(BASE_URL) ?>/book.php">Book an Appointment</a></div>
    <div class="hero-note">A little self-care goes a long way. ♡</div>
  </div>
  <div class="hero-art"><div class="hero-circle"><div class="hero-sparkle">✦</div><div class="hero-monogram">XXL</div><span>BEAUTY STUDIO</span></div><div class="floating-card">♡ &nbsp; Your time to shine</div></div>
</section>
<section class="section">
  <div class="section-heading"><div><span class="eyebrow">WHAT WE OFFER</span><h2>Our popular services</h2></div><a class="text-link" href="<?= e(BASE_URL) ?>/services.php">View all services →</a></div>
  <div class="service-grid">
  <?php foreach ($services as $s): ?>
    <article class="service-card"><div class="service-icon">✿</div><h3><?= e($s['name']) ?></h3><p><?= e($s['description']) ?></p><div class="service-bottom"><strong><?= money($s['price']) ?></strong><span><?= (int)$s['duration_minutes'] ?> min</span></div></article>
  <?php endforeach; ?>
  <?php if (!$services): ?><div class="empty-state">Our services are being prepared. Please check back soon.</div><?php endif; ?>
  </div>
</section>
<section class="booking-banner"><div><span class="eyebrow">READY FOR A REFRESH?</span><h2>Make time for yourself.</h2><p>Choose your service and preferred schedule in just a few steps.</p></div><a class="btn btn-white" href="<?= e(BASE_URL) ?>/book.php">Book your visit →</a></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
