<?php
/**
 * Shared "See This Week's Line-Up" section — used on both below.php and
 * whatson.php. Content (heading, image, paragraph, DJ cards) is managed
 * in one place in the admin (Week's Line-Up), so it stays identical and
 * in sync on every page that includes this file.
 */
$djs = blocks('below_dj');
?>
<section id="lineup-section" class="lineup-section reveal" style="background-image:url('<?= e(asset(setting('below_lineup_image', 'assets/img/below.jpg'))) ?>');">
  <div class="lineup-overlay">
    <div class="container">
      <h2><?= e(setting('below_lineup_heading', "See This Week's Line-Up")) ?></h2>
      <p><?= e(setting('below_lineup_p1')) ?></p>
    </div>

    <?php if ($djs): ?>
      <div class="dj-carousel">
        <button type="button" class="dj-arrow dj-arrow-prev" aria-label="Previous">&#8249;</button>
        <div class="dj-scroller" id="djScroller">
          <div class="dj-row">
            <?php foreach ($djs as $dj): ?>
              <div class="dj-card">
                <div class="dj-card-media">
                  <img src="<?= e(asset($dj['image'])) ?>" alt="<?= e($dj['title']) ?>">
                  <span class="dj-card-tint"></span>
                </div>
                <div class="dj-card-info">
                  <div class="dj-card-name"><?= e($dj['title']) ?></div>
                  <?php if ($dj['subtitle']): ?><div class="dj-card-day"><?= e($dj['subtitle']) ?></div><?php endif; ?>
                  <?php if ($dj['body']): ?><div class="dj-card-time"><?= e($dj['body']) ?></div><?php endif; ?>
                  <?php if ($dj['link_url'] || $dj['link_url2']): ?>
                    <div class="dj-card-actions">
                      <?php if ($dj['link_url']): ?><a class="dj-card-link link-underline" href="<?= e(url($dj['link_url'])) ?>">Book Now</a><?php endif; ?>
                      <?php if ($dj['link_url2']): ?><a class="dj-card-link link-underline" href="<?= e(url($dj['link_url2'])) ?>">Buy Tickets</a><?php endif; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <button type="button" class="dj-arrow dj-arrow-next" aria-label="Next">&#8250;</button>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
(function () {
  document.querySelectorAll('#djScroller').forEach(function (scroller) {
    var carousel = scroller.closest('.dj-carousel');
    var prev = carousel.querySelector('.dj-arrow-prev');
    var next = carousel.querySelector('.dj-arrow-next');
    function step() {
      var card = scroller.querySelector('.dj-card');
      return card ? card.getBoundingClientRect().width + 22 : 300;
    }
    if (prev) prev.addEventListener('click', function () { scroller.scrollBy({ left: -step(), behavior: 'smooth' }); });
    if (next) next.addEventListener('click', function () { scroller.scrollBy({ left: step(), behavior: 'smooth' }); });
  });
})();
</script>
