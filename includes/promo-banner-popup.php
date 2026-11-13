<?php
/**
 * Promo banner popup (Home Page only, admin > Home Page > Promo Banner).
 * Appears automatically once the visitor has scrolled 20% down the page,
 * then stays dismissed for the rest of that browser session once closed.
 */
if (!section_visible('banner')) {
    return;
}
?>
<div class="promo-banner-popup" id="promoBannerPopup" aria-hidden="true">
  <div class="promo-banner-popup-backdrop" id="promoBannerPopupBackdrop"></div>
  <div class="promo-banner-popup-card">
    <button type="button" class="promo-banner-popup-close" id="promoBannerPopupClose" aria-label="Close">&times;</button>
    <div class="promo-banner-card">
      <div class="promo-banner-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('banner_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('banner_heading')) ?>">
      </div>
      <div class="promo-banner-copy">
        <h2><?= enl(setting('banner_heading')) ?></h2>
        <?php if (setting('banner_body')): ?><p><?= enl(setting('banner_body')) ?></p><?php endif; ?>
        <?php if (setting('banner_btn_label')): ?>
          <a class="promo-banner-btn" href="<?= e(url(setting('banner_btn_url', '#'))) ?>"><?= e(setting('banner_btn_label')) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var popup = document.getElementById('promoBannerPopup');
  if (!popup) return;
  var backdrop = document.getElementById('promoBannerPopupBackdrop');
  var closeBtn = document.getElementById('promoBannerPopupClose');
  var dismissKey = 'hob_promo_banner_dismissed';
  var shown = false;

  try {
    if (sessionStorage.getItem(dismissKey) === '1') return;
  } catch (e) {}

  function openPopup() {
    if (shown) return;
    shown = true;
    popup.classList.add('open');
    popup.setAttribute('aria-hidden', 'false');
    window.__hobScrollY = window.scrollY || window.pageYOffset || 0;
    document.body.style.top = (-window.__hobScrollY) + 'px';
    document.body.classList.add('scroll-locked');
  }
  function closePopup() {
    popup.classList.remove('open');
    popup.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('scroll-locked');
    document.body.style.top = '';
    window.scrollTo(0, window.__hobScrollY || 0);
    try { sessionStorage.setItem(dismissKey, '1'); } catch (e) {}
  }

  function checkScroll() {
    if (shown) return;
    var scrollable = document.documentElement.scrollHeight - window.innerHeight;
    if (scrollable <= 0) return;
    var pct = (window.scrollY || window.pageYOffset || 0) / scrollable;
    if (pct >= 0.2) {
      openPopup();
      window.removeEventListener('scroll', checkScroll);
    }
  }
  window.addEventListener('scroll', checkScroll, { passive: true });
  checkScroll();

  if (closeBtn) closeBtn.addEventListener('click', closePopup);
  if (backdrop) backdrop.addEventListener('click', closePopup);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && popup.classList.contains('open')) closePopup();
  });
})();
</script>
