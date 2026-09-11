<?php
$bp_heading    = setting('popup_heading', 'One Address. Every Mood');
$bp_subheading = setting('popup_subheading', 'Book Your Table Today');
$bp_btn1_label = setting('popup_btn1_label', 'Below');
$bp_btn1_url   = setting('popup_btn1_url', 'below.php');
$bp_btn2_label = setting('popup_btn2_label', 'Restaurant');
$bp_btn2_url   = setting('popup_btn2_url', 'restaurant.php');
?>
<div class="booking-popup" id="bookingPopup" aria-hidden="true">
  <button type="button" class="booking-popup-close" id="bookingPopupClose" aria-label="Close">&times;</button>
  <div class="booking-popup-inner">
    <h2><?= e($bp_heading) ?></h2>
    <p class="booking-popup-sub"><?= e($bp_subheading) ?></p>
    <div class="booking-popup-actions">
      <?php if ($bp_btn1_label): ?>
        <a class="link-underline" href="<?= e(url($bp_btn1_url ?: '#')) ?>"><?= e($bp_btn1_label) ?></a>
      <?php endif; ?>
      <?php if ($bp_btn2_label): ?>
        <a class="link-underline" href="<?= e(url($bp_btn2_url ?: '#')) ?>"><?= e($bp_btn2_label) ?></a>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
(function () {
  var popup = document.getElementById('bookingPopup');
  if (!popup) return;
  var closeBtn = document.getElementById('bookingPopupClose');

  function openPopup(e) {
    if (e) e.preventDefault();
    popup.classList.add('open');
    popup.setAttribute('aria-hidden', 'false');
    document.body.classList.add('popup-open');
  }
  function closePopup() {
    popup.classList.remove('open');
    popup.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('popup-open');
  }

  document.querySelectorAll('a').forEach(function (a) {
    if (popup.contains(a)) return;
    var label = (a.textContent || '').trim().toUpperCase();
    if (label === 'BOOK A TABLE') {
      a.addEventListener('click', openPopup);
    }
  });

  if (closeBtn) closeBtn.addEventListener('click', closePopup);
  popup.addEventListener('click', function (e) {
    if (e.target === popup) closePopup();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closePopup();
  });
})();
</script>
