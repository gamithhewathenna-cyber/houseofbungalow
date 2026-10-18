<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('press_heading', 'Press & Media');
$page_meta_description = setting('press_lede');

include __DIR__ . '/includes/header.php';
?>

<!-- Header (solid colour band) ------------------------------------------ -->
<?php if (section_visible('press_intro')): ?>
<section class="maroon-band page-header-band reveal">
  <div class="container">
    <h1><?= e(setting('press_heading', 'Press & Media')) ?></h1>
    <?php if (setting('press_subheading')): ?><p class="subheading"><?= e(setting('press_subheading')) ?></p><?php endif; ?>
    <?php if (setting('press_lede')): ?><p class="lede"><?= enl(setting('press_lede')) ?></p><?php endif; ?>
    <?php if (setting('press_p1')): ?><p><?= enl(setting('press_p1')) ?></p><?php endif; ?>
    <?php if (setting('press_p2')): ?><p><?= enl(setting('press_p2')) ?></p><?php endif; ?>
    <?php if (setting('press_p3')): ?><p><?= enl(setting('press_p3')) ?></p><?php endif; ?>
    <?php if (setting('press_btn_label')): ?>
      <div class="btn-wrap-row">
        <a class="link-underline" href="#press-form"><?= e(setting('press_btn_label', 'MEDIA ENQUIRIES')) ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- Media Enquiry Form ------------------------------------------------------ -->
<?php if (section_visible('press_form')): ?>
<section class="career-form-section reveal" id="press-form">
  <div class="container">
    <div class="career-form-card">

      <div id="pressFormWrap">
        <?php if (setting('press_form_eyebrow')): ?><p class="career-eyebrow"><?= e(setting('press_form_eyebrow', 'Media Enquiry Form')) ?></p><?php endif; ?>
        <?php if (setting('press_form_intro')): ?><p class="career-form-intro"><?= enl(setting('press_form_intro')) ?></p><?php endif; ?>

        <form id="pressForm" novalidate>
          <div class="career-grid">
            <label class="career-field">
              <span>Full Name <em>*</em></span>
              <input type="text" name="full_name" placeholder="Your full name" required>
            </label>
            <label class="career-field">
              <span>Publication / Organisation <em>*</em></span>
              <input type="text" name="organisation" placeholder="Your publication or organisation" required>
            </label>
          </div>

          <div class="career-grid">
            <label class="career-field">
              <span>Email Address <em>*</em></span>
              <input type="email" name="email" placeholder="you@example.com" required>
            </label>
            <label class="career-field">
              <span>Contact Number</span>
              <input type="tel" name="phone" placeholder="0412 345 678 (optional)">
            </label>
          </div>

          <label class="career-field">
            <span>Nature of Enquiry <em>*</em></span>
            <select name="enquiry_type" required>
              <option value="">Select an option</option>
              <option value="Press / Editorial">Press / Editorial</option>
              <option value="Interview Request">Interview Request</option>
              <option value="Photography / Filming">Photography / Filming</option>
              <option value="Media Collaboration">Media Collaboration</option>
              <option value="Other">Other</option>
            </select>
          </label>

          <label class="career-field">
            <span>Your Enquiry <em>*</em></span>
            <textarea name="message" rows="5" placeholder="Tell us about your enquiry" required></textarea>
          </label>

          <p class="career-form-msg" id="pressFormMsg"></p>

          <button type="submit" class="career-submit" id="pressSubmitBtn">Send Enquiry &rarr;</button>
        </form>
      </div>

      <div class="career-success" id="pressSuccess" hidden>
        <h3>Thank You</h3>
        <p><?= enl(setting('press_success_message', 'Thank you for getting in touch. Our team will be in contact shortly.')) ?></p>
      </div>

    </div>
  </div>
</section>
<?php endif; ?>

<script>
(function () {
  var form = document.getElementById('pressForm');
  if (!form) return;
  var msgBox     = document.getElementById('pressFormMsg');
  var submitBtn  = document.getElementById('pressSubmitBtn');
  var formWrap   = document.getElementById('pressFormWrap');
  var successBox = document.getElementById('pressSuccess');

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    msgBox.textContent = '';
    msgBox.className = 'career-form-msg';

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';

    fetch('<?= e(url('press-enquiry.php')) ?>', { method: 'POST', body: new FormData(form) })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Enquiry →';
        if (data.ok) {
          formWrap.hidden = true;
          successBox.hidden = false;
        } else {
          msgBox.textContent = data.error || 'Something went wrong. Please try again.';
          msgBox.className = 'career-form-msg err';
        }
      })
      .catch(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Enquiry →';
        msgBox.textContent = 'Something went wrong. Please try again.';
        msgBox.className = 'career-form-msg err';
      });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
