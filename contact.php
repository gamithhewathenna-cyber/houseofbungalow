<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('contact_heading', 'Contact Us');
$page_meta_description = setting('contact_lede');

include __DIR__ . '/includes/header.php';

$contact_hero_image = asset(setting('contact_hero_image', 'assets/img/hero.jpg'));
$contact_hero_video = setting('contact_hero_video');
$contact_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($contact_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$map_address = setting('contact_map_address', '65-67 Foveaux St, Surry Hills NSW 2010');
$map_embed_url = 'https://www.google.com/maps?q=' . rawurlencode($map_address) . '&output=embed';
?>

<!-- Hero ------------------------------------------------------------- -->
<?php if (section_visible('contact_hero')): ?>
<section class="hero"<?php if (!$contact_hero_video): ?> style="background-image:url('<?= e($contact_hero_image) ?>');"<?php endif; ?>>
  <?php if ($contact_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($contact_hero_video)) ?>" type="<?= e($contact_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- Intro -------------------------------------------------------------- -->
<?php if (section_visible('contact_intro')): ?>
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('contact_heading', 'Contact Us')) ?></h1>
    <?php if (setting('contact_subheading')): ?><p class="subheading"><?= e(setting('contact_subheading')) ?></p><?php endif; ?>
    <?php if (setting('contact_lede')): ?><p class="lede"><?= enl(setting('contact_lede')) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- Contact Details + Map ------------------------------------------------ -->
<?php if (section_visible('contact_details')): ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <h2 class="contact-name"><?= e(setting('contact_name', 'House of Bungalow')) ?></h2>
        <?php if (setting('contact_address_line1') || setting('contact_address_line2')): ?>
          <p>
            <?php if (setting('contact_address_line1')): ?><?= e(setting('contact_address_line1')) ?><br><?php endif; ?>
            <?= e(setting('contact_address_line2')) ?>
          </p>
        <?php endif; ?>
        <?php if (setting('contact_email')): ?>
          <p><a class="link-underline" href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a></p>
        <?php endif; ?>
        <div class="social contact-social">
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
      </div>
      <div class="split-media">
        <iframe src="<?= e($map_embed_url) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Map to <?= e(setting('contact_name', 'House of Bungalow')) ?>"></iframe>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
