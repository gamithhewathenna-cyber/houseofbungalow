<div class="enquiry-popup" id="vipBottlePopup" aria-hidden="true">
  <button type="button" class="enquiry-popup-close" id="vipBottlePopupClose" aria-label="Close">&times;</button>
  <div class="enquiry-popup-card">
    <div class="enquiry-popup-body">

      <div class="vip-popup-intro">
        <p class="vip-popup-eyebrow1"><?= e(setting('below_vipbottle_popup_eyebrow1', 'Below')) ?></p>
        <p class="vip-popup-eyebrow2"><?= e(setting('below_vipbottle_popup_eyebrow2', 'VIP Bottle Service')) ?></p>
        <h2 class="vip-popup-heading"><?= e(setting('below_vipbottle_popup_heading', 'Your Table. Your Experience.')) ?></h2>
        <p class="vip-popup-subheading"><?= e(setting('below_vipbottle_popup_subheading', "Make yourself at home. We'll take care of the rest.")) ?></p>
        <?php if (setting('below_vipbottle_popup_p1')): ?><p class="vip-popup-lede"><?= enl(setting('below_vipbottle_popup_p1')) ?></p><?php endif; ?>
        <?php if (setting('below_vipbottle_popup_p2')): ?><p class="vip-popup-lede"><?= enl(setting('below_vipbottle_popup_p2')) ?></p><?php endif; ?>
      </div>

      <form id="vipBottleForm" novalidate>
        <div class="enquiry-grid">
          <label class="enquiry-field">
            <span>Full Name <em>*</em></span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('user'); ?>
              <input type="text" name="full_name" placeholder="Your full name" required>
            </span>
          </label>
          <label class="enquiry-field">
            <span>Contact Number <em>*</em></span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('phone'); ?>
              <input type="tel" name="phone" placeholder="0412 345 678" required>
            </span>
          </label>
        </div>

        <div class="enquiry-grid">
          <label class="enquiry-field">
            <span>Company</span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('briefcase'); ?>
              <input type="text" name="company" placeholder="Your company name (optional)">
            </span>
          </label>
          <label class="enquiry-field">
            <span>Occasion</span>
            <span class="enquiry-input-icon enquiry-select-wrap">
              <?php enquiry_icon('gift'); ?>
              <select name="occasion">
                <option value="">Select occasion</option>
                <option value="Birthday">Birthday</option>
                <option value="Celebration">Celebration</option>
                <option value="Corporate">Corporate</option>
                <option value="Night Out">Night Out</option>
                <option value="Other">Other</option>
              </select>
              <span class="enquiry-select-chevron"><?php enquiry_icon('chevron'); ?></span>
            </span>
          </label>
        </div>

        <div class="enquiry-grid">
          <label class="enquiry-field">
            <span>Number of Guests (PAX) <em>*</em></span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('users'); ?>
              <input type="number" name="guests" min="1" placeholder="e.g. 10" required>
            </span>
          </label>
          <label class="enquiry-field">
            <span>Preferred Date <em>*</em></span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('calendar'); ?>
              <input type="date" name="preferred_date" required>
            </span>
          </label>
        </div>

        <p class="enquiry-form-msg" id="vipBottleFormMsg"></p>

        <button type="submit" class="enquiry-submit" id="vipBottleSubmitBtn">Enquire About VIP Tables &rarr;</button>
        <p class="enquiry-fineprint">Fields marked * are required.</p>
      </form>

      <div class="enquiry-success" id="vipBottleSuccess" hidden>
        <h3>Thank You</h3>
        <p><?= enl(setting('below_vipbottle_popup_success', 'Thank you for your enquiry. Our VIP team will be in touch to discuss table availability and bottle-service options.')) ?></p>
        <button type="button" class="enquiry-submit" id="vipBottleSuccessClose">Close</button>
      </div>

    </div>
  </div>
</div>

<script>
(function () {
  var popup = document.getElementById('vipBottlePopup');
  if (!popup) return;
  var closeBtn   = document.getElementById('vipBottlePopupClose');
  var form       = document.getElementById('vipBottleForm');
  var msgBox     = document.getElementById('vipBottleFormMsg');
  var submitBtn  = document.getElementById('vipBottleSubmitBtn');
  var successBox = document.getElementById('vipBottleSuccess');
  var successClose = document.getElementById('vipBottleSuccessClose');

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

  document.querySelectorAll('.js-vipbottle-popup-trigger').forEach(function (a) {
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

    fetch('<?= e(url('vip-bottle-enquiry.php')) ?>', { method: 'POST', body: new FormData(form) })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Enquire About VIP Tables →';
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
        submitBtn.textContent = 'Enquire About VIP Tables →';
        msgBox.textContent = 'Something went wrong. Please try again.';
        msgBox.className = 'enquiry-form-msg err';
      });
  });
})();
</script>
