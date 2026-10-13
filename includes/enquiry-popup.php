<?php
/**
 * Small line-icon set for the enquiry form (same stroke style as the
 * admin sidebar icons) - inlined here since this partial is the only
 * place that uses them.
 */
function enquiry_icon(string $key): void
{
    $icons = [
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/>',
        'phone'     => '<path d="M4 4h4l2 5-2.5 2.5a12 12 0 0 0 6 6L16 15l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 2 6a2 2 0 0 1 2-2Z"/>',
        'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M4 6.5l8 6.5 8-6.5"/>',
        'calendar'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'users'     => '<circle cx="8.5" cy="8" r="3"/><path d="M2.5 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.5"/><path d="M14.5 20c.3-2.6 2.2-4.6 4.7-4.9"/>',
        'message'   => '<path d="M21 11.5a8.4 8.4 0 0 1-8.9 8.4 9 9 0 0 1-3.6-.8L3 20l1-5.2a8.4 8.4 0 0 1-.9-3.8 8.4 8.4 0 0 1 8.9-8.4A8.6 8.6 0 0 1 21 11.5Z"/>',
        'chevron'   => '<path d="M6 9l6 6 6-6"/>',
        'gift'      => '<rect x="3" y="9" width="18" height="4" rx="1"/><rect x="4" y="13" width="16" height="8" rx="1"/><path d="M12 9v12M12 9C9.5 9 8 7.5 8 6a2.5 2.5 0 0 1 4-2c1 1.2 1 3 0 5ZM12 9c2.5 0 4-1.5 4-3a2.5 2.5 0 0 0-4-2c-1 1.2-1 3 0 5Z"/>',
        'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><path d="M2 13h20"/>',
        'glass'     => '<path d="M5 3h14l-2.2 9.2a4.8 4.8 0 0 1-9.6 0L5 3Z"/><path d="M12 15v6M8 21h8"/>',
        'megaphone' => '<path d="M3 11v2a2 2 0 0 0 2 2h1l5 4V5L6 9H5a2 2 0 0 0-2 2Z"/><path d="M14.5 8.5a4 4 0 0 1 0 7"/><path d="M17.5 5.5a8.5 8.5 0 0 1 0 13"/>',
        'cocktail'  => '<path d="M4 4h16l-8 9-8-9Z"/><path d="M12 13v7M8 20h8"/>',
        'dots'      => '<circle cx="5" cy="12" r="1.6" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none"/><circle cx="19" cy="12" r="1.6" fill="currentColor" stroke="none"/>',
        'fork'      => '<path d="M6 2v8a2 2 0 0 0 4 0V2M8 2v20M18 2c-2 1.5-2 4-2 6 0 2.5 1 3 2 3.5V22"/>',
        'moon'      => '<path d="M20 14.5A8.5 8.5 0 1 1 9.5 4a7 7 0 0 0 10.5 10.5Z"/>',
        'question'  => '<circle cx="12" cy="12" r="9"/><path d="M9.2 9.3a2.8 2.8 0 0 1 5.4.9c0 1.8-2.6 2-2.6 3.8"/><circle cx="12" cy="17" r=".2" fill="currentColor"/>',
    ];
    echo '<svg class="enquiry-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . ($icons[$key] ?? '') . '</svg>';
}
?>
<div class="enquiry-popup" id="enquiryPopup" aria-hidden="true">
  <button type="button" class="enquiry-popup-close" id="enquiryPopupClose" aria-label="Close">&times;</button>
  <div class="enquiry-popup-card">
    <div class="enquiry-popup-body">

      <form id="enquiryForm" novalidate>
        <input type="hidden" name="source" id="enquirySource" value="">

        <p class="enquiry-eyebrow">Your Details</p>

        <div class="enquiry-grid">
          <label class="enquiry-field">
            <span>Full name <em>*</em></span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('user'); ?>
              <input type="text" name="full_name" placeholder="Your name" required>
            </span>
          </label>
          <label class="enquiry-field">
            <span>Phone number <em>*</em></span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('phone'); ?>
              <input type="tel" name="phone" placeholder="0412 345 678" required>
            </span>
          </label>
        </div>

        <div class="enquiry-grid">
          <label class="enquiry-field">
            <span>Email address <em>*</em></span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('mail'); ?>
              <input type="email" name="email" placeholder="you@example.com" required>
            </span>
          </label>
          <label class="enquiry-field">
            <span>Company</span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('briefcase'); ?>
              <input type="text" name="company" placeholder="Company name (optional)">
            </span>
          </label>
        </div>

        <p class="enquiry-eyebrow enquiry-eyebrow-spaced">Your Event</p>

        <div class="enquiry-field">
          <span>Type of event <em>*</em></span>
          <input type="hidden" name="event_type">
          <div class="enquiry-options" data-group="event_type">
            <button type="button" class="enquiry-option" data-value="Birthday / Celebration"><?php enquiry_icon('gift'); ?><span>Birthday / Celebration</span></button>
            <button type="button" class="enquiry-option" data-value="Corporate Event"><?php enquiry_icon('briefcase'); ?><span>Corporate Event</span></button>
            <button type="button" class="enquiry-option" data-value="Private Dining"><?php enquiry_icon('glass'); ?><span>Private Dining</span></button>
            <button type="button" class="enquiry-option" data-value="Brand Launch / Activation"><?php enquiry_icon('megaphone'); ?><span>Brand Launch / Activation</span></button>
            <button type="button" class="enquiry-option" data-value="Cocktail Reception"><?php enquiry_icon('cocktail'); ?><span>Cocktail Reception</span></button>
            <button type="button" class="enquiry-option" data-value="Other"><?php enquiry_icon('dots'); ?><span>Other</span></button>
          </div>
        </div>

        <div class="enquiry-field">
          <span>Preferred space <em>*</em></span>
          <input type="hidden" name="preferred_space">
          <div class="enquiry-options" data-group="preferred_space">
            <button type="button" class="enquiry-option" data-value="Restaurant"><?php enquiry_icon('fork'); ?><span>Restaurant</span></button>
            <button type="button" class="enquiry-option" data-value="Below"><?php enquiry_icon('moon'); ?><span>Below</span></button>
            <button type="button" class="enquiry-option" data-value="Multiple Spaces / Exclusive Hire"><?php enquiry_icon('users'); ?><span>Multiple Spaces / Exclusive Hire</span></button>
            <button type="button" class="enquiry-option" data-value="Not sure yet"><?php enquiry_icon('question'); ?><span>Not sure yet</span></button>
          </div>
        </div>

        <div class="enquiry-grid">
          <label class="enquiry-field">
            <span>Preferred date <em>*</em></span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('calendar'); ?>
              <input type="date" name="preferred_date" required>
            </span>
          </label>
          <label class="enquiry-field">
            <span>Preferred start time</span>
            <span class="enquiry-input-icon">
              <?php enquiry_icon('clock'); ?>
              <input type="text" name="preferred_time" placeholder="e.g. 7:00pm">
            </span>
          </label>
        </div>

        <label class="enquiry-field">
          <span>Estimated guests <em>*</em></span>
          <span class="enquiry-input-icon enquiry-select-wrap">
            <?php enquiry_icon('users'); ?>
            <select name="guests" required>
              <option value="">Select number of guests</option>
              <option value="1 - 10">1 - 10</option>
              <option value="11 - 20">11 - 20</option>
              <option value="21 - 40">21 - 40</option>
              <option value="41 - 80">41 - 80</option>
              <option value="80+">80+</option>
            </select>
            <span class="enquiry-select-chevron"><?php enquiry_icon('chevron'); ?></span>
          </span>
        </label>

        <label class="enquiry-field">
          <span>Tell us about your occasion</span>
          <span class="enquiry-input-icon enquiry-textarea-wrap">
            <?php enquiry_icon('message'); ?>
            <textarea name="message" placeholder="Anything you'd like us to know? Food, drinks, music, special requests..."></textarea>
          </span>
        </label>

        <p class="enquiry-form-msg" id="enquiryFormMsg"></p>

        <button type="submit" class="enquiry-submit" id="enquirySubmitBtn">Send Enquiry</button>
        <p class="enquiry-fineprint">By submitting this form, you agree to be contacted about your enquiry.</p>
      </form>

      <div class="enquiry-success" id="enquirySuccess" hidden>
        <h3>Thank you</h3>
        <p>We've received your enquiry and will be in touch shortly.</p>
        <button type="button" class="enquiry-submit" id="enquirySuccessClose">Close</button>
      </div>

    </div>
  </div>
</div>

<script>
(function () {
  var popup = document.getElementById('enquiryPopup');
  if (!popup) return;
  var closeBtn   = document.getElementById('enquiryPopupClose');
  var form       = document.getElementById('enquiryForm');
  var msgBox     = document.getElementById('enquiryFormMsg');
  var submitBtn  = document.getElementById('enquirySubmitBtn');
  var successBox = document.getElementById('enquirySuccess');
  var successClose = document.getElementById('enquirySuccessClose');
  var sourceField = document.getElementById('enquirySource');

  function openPopup(e) {
    if (e) e.preventDefault();
    var trigger = e && e.currentTarget;
    sourceField.value = (trigger && trigger.getAttribute('data-enquiry-source')) || '';
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
    document.querySelectorAll('.enquiry-option.active').forEach(function (b) { b.classList.remove('active'); });
  }

  document.querySelectorAll('.js-enquiry-popup-trigger').forEach(function (a) {
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

  // Single-select pill groups (Type of event / Preferred space)
  document.querySelectorAll('.enquiry-options').forEach(function (group) {
    var hidden = group.previousElementSibling;
    group.querySelectorAll('.enquiry-option').forEach(function (btn) {
      btn.addEventListener('click', function () {
        group.querySelectorAll('.enquiry-option').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        hidden.value = btn.getAttribute('data-value');
      });
    });
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    msgBox.textContent = '';
    msgBox.className = 'enquiry-form-msg';

    var eventType = form.querySelector('[name="event_type"]').value;
    var space = form.querySelector('[name="preferred_space"]').value;
    if (!eventType || !space) {
      msgBox.textContent = 'Please choose a type of event and a preferred space.';
      msgBox.className = 'enquiry-form-msg err';
      return;
    }
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';

    fetch('<?= e(url('enquiry.php')) ?>', { method: 'POST', body: new FormData(form) })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Enquiry';
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
        submitBtn.textContent = 'Send Enquiry';
        msgBox.textContent = 'Something went wrong. Please try again.';
        msgBox.className = 'enquiry-form-msg err';
      });
  });
})();
</script>
