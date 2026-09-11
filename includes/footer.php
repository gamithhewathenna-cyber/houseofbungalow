<?php
$footer_nav = blocks('footer_nav');
$brands     = blocks('brand');
$msg        = $_GET['sub'] ?? '';
?>
<footer class="site-footer reveal">
  <div class="container">

    <hr class="footer-rule">

    <div class="social">
      <a href="<?= e(url(setting('social_instagram','#'))) ?>" aria-label="Instagram">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
      </a>
      <a href="<?= e(url(setting('social_spotify','#'))) ?>" aria-label="Spotify">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm4.6 14.4a.7.7 0 01-1 .24c-2.6-1.6-5.9-1.96-9.8-1.07a.7.7 0 11-.3-1.36c4.24-.97 7.9-.56 10.8 1.2.34.2.44.65.3 1zm1.23-2.74a.87.87 0 01-1.2.29c-3-1.83-7.55-2.36-11.1-1.29a.87.87 0 11-.5-1.66c4.05-1.22 9.06-.63 12.5 1.47.42.26.55.8.3 1.19zm.1-2.85C14.44 8.72 8.9 8.52 5.5 9.55a1.04 1.04 0 11-.6-2C8.8 6.38 14.9 6.6 18.85 8.94a1.04 1.04 0 01-1.08 1.77z"/></svg>
      </a>
      <a href="<?= e(url(setting('social_tiktok','#'))) ?>" aria-label="TikTok">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 3c.3 2 1.5 3.5 3.5 3.8v2.4c-1.3.1-2.5-.3-3.5-1v6.1c0 3.2-2.5 5.7-5.6 5.7A5.6 5.6 0 015.3 14a5.6 5.6 0 016.4-5.6v2.5a3.1 3.1 0 00-.9-.1 3.1 3.1 0 103.1 3.1V3h2.6z"/></svg>
      </a>
    </div>

    <?php if ($footer_nav): ?>
    <nav class="footer-nav">
      <?php foreach ($footer_nav as $n): ?>
        <a href="<?= e(url($n['link_url'] ?: '#')) ?>"><?= e($n['title']) ?></a>
      <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <p class="footer-address"><?= e(setting('footer_address')) ?></p>

    <?php if ($brands): ?>
    <div class="brands">
      <?php foreach ($brands as $b): ?>
        <?php if (!empty($b['image'])): ?>
          <img src="<?= e(asset($b['image'])) ?>" alt="<?= e($b['title']) ?>">
        <?php else: ?>
          <span class="brand"><?= e($b['title']) ?></span>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="footer-logo">
      <img src="<?= e(asset(setting('logo_colour', 'assets/img/logo-maroon.png'))) ?>" alt="<?= e(SITE_NAME) ?>">
    </div>

    <h3 class="newsletter-heading"><?= e(setting('newsletter_heading')) ?></h3>
    <p class="newsletter-sub"><?= e(setting('newsletter_sub')) ?></p>

    <form class="newsletter-form" method="post" action="<?= e(url('subscribe.php')) ?>">
      <div class="row">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit" class="btn-maroon"><?= e(setting('newsletter_btn')) ?></button>
      </div>
      <?php if ($msg === 'ok'): ?>
        <p class="form-msg ok">Thank you — you're on the list.</p>
      <?php elseif ($msg === 'dupe'): ?>
        <p class="form-msg ok">You're already subscribed.</p>
      <?php elseif ($msg === 'err'): ?>
        <p class="form-msg err">Please enter a valid email address.</p>
      <?php endif; ?>
    </form>

    <hr class="footer-divider">
    <p class="copyright"><?= e(setting('copyright')) ?></p>
    <?php if (setting('footer_disclaimer')): ?>
      <p class="footer-disclaimer"><?= e(setting('footer_disclaimer')) ?></p>
    <?php endif; ?>
  </div>
</footer>

<?php include __DIR__ . '/booking-popup.php'; ?>

<script>
(function () {
  var items = document.querySelectorAll('.reveal');
  if (!items.length) return;
  if (!('IntersectionObserver' in window)) {
    items.forEach(function (el) { el.classList.add('is-visible'); });
    return;
  }
  var observer = new IntersectionObserver(function (entries, obs) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });
  items.forEach(function (el) { observer.observe(el); });
})();
</script>

</body>
</html>
