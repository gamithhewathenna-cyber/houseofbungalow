<div class="enquiry-popup" id="pressPopup" aria-hidden="true">
  <button type="button" class="enquiry-popup-close" id="pressPopupClose" aria-label="Close">&times;</button>
  <div class="enquiry-popup-card">
    <div class="enquiry-popup-body">

      <div class="vip-popup-intro">
        <p class="vip-popup-eyebrow2"><?= e(setting('press_form_eyebrow', 'Media Enquiry Form')) ?></p>
        <?php if (setting('press_form_intro')): ?><p class="vip-popup-lede"><?= enl(setting('press_form_intro')) ?></p><?php endif; ?>
      </div>

      <form id="pressForm" novalidate>
        <div class="enquiry-grid">
          <label class="enquiry-field">
            <span>Full Name <em>*</em></span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('user'); ?>
              <input type="text" name="full_name" placeholder="Your full name" required>
            </span>
          </label>
          <label class="enquiry-field">
            <span>Publication / Organisation <em>*</em></span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('briefcase'); ?>
              <input type="text" name="organisation" placeholder="Your publication or organisation" required>
            </span>
          </label>
        </div>

        <div class="enquiry-grid">
          <label class="enquiry-field">
            <span>Email Address <em>*</em></span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('mail'); ?>
              <input type="email" name="email" placeholder="you@example.com" required>
            </span>
          </label>
          <label class="enquiry-field">
            <span>Contact Number</span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('phone'); ?>
              <input type="tel" name="phone" placeholder="0412 345 678 (optional)">
            </span>
          </label>
        </div>

        <label class="enquiry-field">
          <span>Nature of Enquiry <em>*</em></span>
          <span class="enquiry-input-icon enquiry-select-wrap">
            <?php enquiry_icon('question'); ?>
            <select name="enquiry_type" required>
              <option value="">Select an option</option>
              <option value="Press / Editorial">Press / Editorial</option>
              <option value="Interview Request">Interview Request</option>
              <option value="Photography / Filming">Photography / Filming</option>
              <option value="Media Collaboration">Media Collaboration</option>
              <option value="Other">Other</option>
            </select>
            <span class="enquiry-select-chevron"><?php enquiry_icon('chevron'); ?></span>
          </span>
        </label>

        <label class="enquiry-field">
          <span>Your Enquiry <em>*</em></span>
          <span class="enquiry-input-icon enquiry-textarea-wrap">
            <?php enquiry_icon('message'); ?>
            <textarea name="message" placeholder="Tell us about your enquiry" required></textarea>
          </span>
        </label>

        <p class="enquiry-form-msg" id="pressFormMsg"></p>

        <button type="submit" class="enquiry-submit" id="pressSubmitBtn">Send Enquiry &rarr;</button>
        <p class="enquiry-fineprint">Fields marked * are required.</p>
      </form>

      <div class="enquiry-success" id="pressSuccess" hidden>
        <h3>Thank You</h3>
        <p><?= enl(setting('press_success_message', 'Thank you for getting in touch. Our team will be in contact shortly.')) ?></p>
        <button type="button" class="enquiry-submit" id="pressSuccessClose">Close</button>
      </div>

    </div>
  </div>
</div>

<script>
(function () {
  var popup = document.getElementById('pressPopup');
  if (!popup) return;
  var closeBtn   = document.getElementById('pressPopupClose');
  var form       = document.getElementById('pressForm');
  var msgBox     = document.getElementById('pressFormMsg');
  var submitBtn  = document.getElementById('pressSubmitBtn');
  var successBox = document.getElementById('pressSuccess');
  var successClose = document.getElementById('pressSuccessClose');

  function openPopup(e) {
    if (e) e.preventDefault();
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
  }
  function resetPopup() {
    form.reset();
    form.hidden = false;
    successBox.hidden = true;
    msgBox.textContent = '';
    msgBox.className = 'enquiry-form-msg';
  }

  document.querySelectorAll('.js-press-popup-trigger').forEach(function (a) {
    a.addEventListener('click', openPopup);
  });
  if (closeBtn) closeBtn.addEventListener('click', function () { closePopup(); setTimeout(resetPopup, 250); });
  if (successClose) successClose.addEventListener('click', function () { closePopup(); setTimeout(resetPopup, 250); });
  popup.addEventListener('click', function (e) {
    if (e.target === popup) { closePopup(); setTimeout(resetPopup, 250); }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && popup.classList.contains('open')) { closePopup(); setTimeout(resetPopup, 250); }
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    msgBox.textContent = '';
    msgBox.className = 'enquiry-form-msg';

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
          form.hidden = true;
          successBox.hidden = false;
        } else {
          msgBox.textContent = data.error || 'Something went wrong. Please try again.';
          msgBox.className = 'enquiry-form-msg err';
        }
      })
      .catch(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Enquiry →';
        msgBox.textContent = 'Something went wrong. Please try again.';
        msgBox.className = 'enquiry-form-msg err';
      });
  });
})();
</script>
