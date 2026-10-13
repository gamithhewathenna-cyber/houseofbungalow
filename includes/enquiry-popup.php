<div class="enquiry-popup" id="enquiryPopup" aria-hidden="true">
  <div class="enquiry-popup-card">
    <button type="button" class="enquiry-popup-close" id="enquiryPopupClose" aria-label="Close">&times;</button>
    <div class="enquiry-popup-body">

      <form id="enquiryForm" novalidate>
        <input type="hidden" name="source" id="enquirySource" value="">

        <p class="enquiry-eyebrow">Your Details</p>

        <div class="enquiry-grid">
          <label class="enquiry-field">
            <span>Full name <em>*</em></span>
            <input type="text" name="full_name" placeholder="Your name" required>
          </label>
          <label class="enquiry-field">
            <span>Phone number <em>*</em></span>
            <input type="tel" name="phone" placeholder="0412 345 678" required>
          </label>
        </div>

        <label class="enquiry-field">
          <span>Email address <em>*</em></span>
          <input type="email" name="email" placeholder="you@example.com" required>
        </label>

        <hr class="enquiry-divider">

        <p class="enquiry-eyebrow">Your Event</p>

        <div class="enquiry-field">
          <span>Type of event <em>*</em></span>
          <input type="hidden" name="event_type">
          <div class="enquiry-options" data-group="event_type">
            <button type="button" class="enquiry-option" data-value="Birthday / Celebration">Birthday / Celebration</button>
            <button type="button" class="enquiry-option" data-value="Corporate Event">Corporate Event</button>
            <button type="button" class="enquiry-option" data-value="Private Dining">Private Dining</button>
            <button type="button" class="enquiry-option" data-value="Brand Launch / Activation">Brand Launch / Activation</button>
            <button type="button" class="enquiry-option" data-value="Cocktail Reception">Cocktail Reception</button>
            <button type="button" class="enquiry-option" data-value="Other">Other</button>
          </div>
        </div>

        <div class="enquiry-field">
          <span>Preferred space <em>*</em></span>
          <input type="hidden" name="preferred_space">
          <div class="enquiry-options" data-group="preferred_space">
            <button type="button" class="enquiry-option" data-value="Restaurant">Restaurant</button>
            <button type="button" class="enquiry-option" data-value="Below">Below</button>
            <button type="button" class="enquiry-option" data-value="Multiple Spaces / Exclusive Hire">Multiple Spaces / Exclusive Hire</button>
            <button type="button" class="enquiry-option" data-value="Not sure yet">Not sure yet</button>
          </div>
        </div>

        <div class="enquiry-grid">
          <label class="enquiry-field">
            <span>Preferred date <em>*</em></span>
            <input type="date" name="preferred_date" required>
          </label>
          <label class="enquiry-field">
            <span>Preferred start time</span>
            <input type="text" name="preferred_time" placeholder="e.g. 7:00pm">
          </label>
        </div>

        <div class="enquiry-grid">
          <label class="enquiry-field">
            <span>Estimated guests <em>*</em></span>
            <select name="guests" required>
              <option value="">Select number of guests</option>
              <option value="1 - 10">1 - 10</option>
              <option value="11 - 20">11 - 20</option>
              <option value="21 - 40">21 - 40</option>
              <option value="41 - 80">41 - 80</option>
              <option value="80+">80+</option>
            </select>
          </label>
          <label class="enquiry-field">
            <span>Estimated total budget</span>
            <select name="budget">
              <option value="">Select budget range</option>
              <option value="Under $1,000">Under $1,000</option>
              <option value="$1,000 - $2,500">$1,000 - $2,500</option>
              <option value="$2,500 - $5,000">$2,500 - $5,000</option>
              <option value="$5,000 - $10,000">$5,000 - $10,000</option>
              <option value="$10,000+">$10,000+</option>
            </select>
          </label>
        </div>

        <label class="enquiry-field">
          <span>Tell us about your occasion</span>
          <textarea name="message" placeholder="Anything you'd like us to know? Food, drinks, music, special requests..."></textarea>
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
