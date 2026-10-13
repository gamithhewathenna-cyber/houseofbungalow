<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('career_heading', 'Join Our Team');
$page_meta_description = setting('career_lede');

include __DIR__ . '/includes/header.php';
?>

<!-- Intro -------------------------------------------------------------- -->
<?php if (section_visible('career_intro')): ?>
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('career_heading', 'Join Our Team')) ?></h1>
    <?php if (setting('career_lede')): ?><p class="lede"><?= enl(setting('career_lede')) ?></p><?php endif; ?>
    <?php if (setting('career_p1')): ?><p><?= enl(setting('career_p1')) ?></p><?php endif; ?>
    <?php if (setting('career_p2')): ?><p><?= enl(setting('career_p2')) ?></p><?php endif; ?>
    <?php if (setting('career_p3')): ?><p><?= enl(setting('career_p3')) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- Application Form ------------------------------------------------------ -->
<?php if (section_visible('career_form')): ?>
<section class="career-form-section reveal">
  <div class="container">
    <div class="career-form-card">

      <div id="careerFormWrap">
        <?php if (setting('career_form_eyebrow')): ?><p class="career-eyebrow"><?= e(setting('career_form_eyebrow', 'Join The House')) ?></p><?php endif; ?>
        <?php if (setting('career_form_intro')): ?><p class="career-form-intro"><?= enl(setting('career_form_intro')) ?></p><?php endif; ?>

        <form id="careerForm" novalidate enctype="multipart/form-data">
          <div class="career-grid">
            <label class="career-field">
              <span>Full Name <em>*</em></span>
              <input type="text" name="full_name" placeholder="Your full name" required>
            </label>
            <label class="career-field">
              <span>Email Address <em>*</em></span>
              <input type="email" name="email" placeholder="you@example.com" required>
            </label>
          </div>

          <div class="career-grid">
            <label class="career-field">
              <span>Contact Number <em>*</em></span>
              <input type="tel" name="phone" placeholder="0412 345 678" required>
            </label>
            <label class="career-field">
              <span>Area of Interest <em>*</em></span>
              <select name="area_of_interest" required>
                <option value="">Select an area</option>
                <option value="Café">Café</option>
                <option value="Restaurant - Front of House">Restaurant - Front of House</option>
                <option value="Restaurant - Kitchen">Restaurant - Kitchen</option>
                <option value="Below - Bar & Service">Below - Bar &amp; Service</option>
                <option value="Management">Management</option>
                <option value="Other">Other</option>
              </select>
            </label>
          </div>

          <label class="career-field">
            <span>Position You're Interested In</span>
            <input type="text" name="position" placeholder="e.g. Barista, Waiter, Bartender (optional)">
          </label>

          <label class="career-field">
            <span>Upload Your CV <em>*</em></span>
            <input type="file" name="cv" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
            <span class="career-field-help">PDF, DOC or DOCX, max 8&nbsp;MB.</span>
          </label>

          <p class="career-form-msg" id="careerFormMsg"></p>

          <button type="submit" class="career-submit" id="careerSubmitBtn">Join Our Team &rarr;</button>
        </form>
      </div>

      <div class="career-success" id="careerSuccess" hidden>
        <h3>Thank You</h3>
        <p><?= enl(setting('career_success_message', "Thanks for your interest in House of Bungalow. We've received your application and will be in touch if a suitable opportunity comes up.")) ?></p>
      </div>

    </div>
  </div>
</section>
<?php endif; ?>

<script>
(function () {
  var form = document.getElementById('careerForm');
  if (!form) return;
  var msgBox     = document.getElementById('careerFormMsg');
  var submitBtn  = document.getElementById('careerSubmitBtn');
  var formWrap   = document.getElementById('careerFormWrap');
  var successBox = document.getElementById('careerSuccess');

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

    fetch('<?= e(url('careers-application.php')) ?>', { method: 'POST', body: new FormData(form) })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Join Our Team →';
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
        submitBtn.textContent = 'Join Our Team →';
        msgBox.textContent = 'Something went wrong. Please try again.';
        msgBox.className = 'career-form-msg err';
      });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
