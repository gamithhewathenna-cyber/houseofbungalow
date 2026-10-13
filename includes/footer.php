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
      <a href="<?= e(url(setting('social_facebook','#'))) ?>" aria-label="Facebook">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 9.5V7.2c0-.9.6-1.1 1-1.1h2.5V2.5L14.5 2.5c-3.3 0-4 2.5-4 4.1v2.9H8v4h2.5V22h4V13.5h2.9l.4-4H14.5Z"/></svg>
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

    <?php if ($brands && section_visible('brands')): ?>
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
        <p class="form-msg ok">Thank you - you're on the list.</p>
      <?php elseif ($msg === 'dupe'): ?>
        <p class="form-msg ok">You're already subscribed.</p>
      <?php elseif ($msg === 'err'): ?>
        <p class="form-msg err">Please enter a valid email address.</p>
      <?php endif; ?>
    </form>

    <hr class="footer-divider">
    <p class="copyright"><?= e(setting('copyright')) ?></p>
    <?php if (setting('footer_disclaimer')): ?>
      <p class="footer-disclaimer"><?= enl(setting('footer_disclaimer')) ?></p>
    <?php endif; ?>
    <p class="footer-privacy"><a href="<?= e(url('privacy.php')) ?>">View our Privacy Policy.</a></p>
  </div>
</footer>

<?php include __DIR__ . '/booking-popup.php'; ?>
<?php include __DIR__ . '/enquiry-popup.php'; ?>
<?php include __DIR__ . '/vip-bottle-popup.php'; ?>

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

<script>
(function () {
  // Only EXTERNAL CTA "link buttons" (.link-underline) open in a new tab -
  // links to other pages on this same site (e.g. whatson.php) stay in-tab,
  // same as same-page anchors (href="#...") and mailto:/tel: links. Site
  // navigation (header/mobile/footer menus) doesn't use this class at all.
  document.querySelectorAll('a.link-underline').forEach(function (a) {
    var href = a.getAttribute('href') || '';
    if (href.charAt(0) === '#' || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return;
    if (a.hostname && a.hostname !== window.location.hostname) {
      a.setAttribute('target', '_blank');
      a.setAttribute('rel', 'noopener');
    }
  });
})();
</script>

</body>
</html>
