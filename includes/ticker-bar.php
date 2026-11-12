<?php
/**
 * Sitewide notification ticker (admin > Notification Ticker). Shown at
 * the very top of every page, above the header, when enabled. Visitors
 * can close it with the X - that choice is remembered per browser until
 * the message content changes, at which point it shows again.
 */
$__ticker_messages = setting('ticker_enabled', '0') === '1' ? blocks('notification_ticker') : [];
if ($__ticker_messages):
    $__ticker_bg      = setting('ticker_bg_color', '#620E15');
    $__ticker_fg      = setting('ticker_text_color', '#FFFFFF');
    $__ticker_speed   = max(2, (int) setting('ticker_speed', '6'));
    $__ticker_version = substr(md5(json_encode(array_map(
        fn($m) => [$m['id'], $m['title'], $m['link_url']],
        $__ticker_messages
    ))), 0, 10);
?>
<div class="notice-ticker" id="noticeTicker" style="--ticker-bg:<?= e($__ticker_bg) ?>;--ticker-fg:<?= e($__ticker_fg) ?>;" data-ticker-speed="<?= (int) $__ticker_speed ?>" data-ticker-version="<?= e($__ticker_version) ?>">
  <div class="notice-ticker-track">
    <?php foreach ($__ticker_messages as $i => $m): ?>
      <div class="notice-ticker-msg<?= $i === 0 ? ' active' : '' ?>">
        <?php if ($m['link_url']): ?>
          <a href="<?= e(url($m['link_url'])) ?>"><?= e($m['title']) ?></a>
        <?php else: ?>
          <span><?= e($m['title']) ?></span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <button type="button" class="notice-ticker-close" id="noticeTickerClose" aria-label="Close notification">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
  </button>
</div>
<script>
(function () {
  var el = document.getElementById('noticeTicker');
  if (!el) return;
  var dismissKey = 'hob_ticker_dismissed_' + el.dataset.tickerVersion;
  try {
    if (localStorage.getItem(dismissKey) === '1') {
      el.style.display = 'none';
      return;
    }
  } catch (e) {}

  document.documentElement.style.setProperty('--ticker-h', el.offsetHeight + 'px');
  document.body.classList.add('has-ticker');

  var msgs = el.querySelectorAll('.notice-ticker-msg');
  if (msgs.length > 1) {
    var idx = 0;
    var speed = Math.max(2, parseInt(el.dataset.tickerSpeed, 10) || 6) * 1000;
    setInterval(function () {
      msgs[idx].classList.remove('active');
      idx = (idx + 1) % msgs.length;
      msgs[idx].classList.add('active');
    }, speed);
  }

  var closeBtn = document.getElementById('noticeTickerClose');
  if (closeBtn) {
    closeBtn.addEventListener('click', function () {
      el.style.display = 'none';
      document.documentElement.style.setProperty('--ticker-h', '0px');
      document.body.classList.remove('has-ticker');
      try { localStorage.setItem(dismissKey, '1'); } catch (e) {}
    });
  }
})();
</script>
<?php endif; ?>
