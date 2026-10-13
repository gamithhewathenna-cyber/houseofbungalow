<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('privacy_heading', 'Privacy & Terms');
$page_meta_description = setting('privacy_intro');

include __DIR__ . '/includes/header.php';

$privacy_email = setting('contact_email', 'hello@houseofbungalow.com.au');
?>

<!-- Intro -------------------------------------------------------------- -->
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('privacy_heading', 'Privacy & Terms')) ?></h1>
    <p class="legal-updated">Last updated: <?= e(setting('privacy_updated', 'October 2026')) ?></p>
    <?php if (setting('privacy_intro')): ?><p class="lede"><?= enl(setting('privacy_intro')) ?></p><?php endif; ?>
  </div>
</section>

<!-- Privacy Policy & Terms content ---------------------------------------- -->
<?php if (section_visible('privacy_content')): ?>
<section class="legal-section reveal">
  <div class="container">
    <div class="legal-content">

      <h2>Privacy Policy</h2>

      <h3>Information we collect</h3>
      <p>When you interact with House of Bungalow, we may collect information including your:</p>
      <ul>
        <li>Name</li>
        <li>Email address</li>
        <li>Mobile number</li>
        <li>Postcode</li>
        <li>Date of birth, where voluntarily provided</li>
        <li>Reservation and dining information</li>
        <li>Event and private function enquiries</li>
        <li>Guest preferences and interests</li>
        <li>Transaction and purchase information</li>
        <li>Marketing preferences</li>
        <li>Information submitted through forms, competitions or promotions</li>
        <li>Website usage information, including through cookies and analytics tools</li>
      </ul>

      <h3>How we use your information</h3>
      <p>We may use your information to:</p>
      <ul>
        <li>Manage reservations, enquiries and event bookings</li>
        <li>Provide our services and communicate with you about your booking</li>
        <li>Personalise your experience with House of Bungalow</li>
        <li>Manage guest preferences and visit history</li>
        <li>Process payments and transactions</li>
        <li>Respond to feedback or enquiries</li>
        <li>Operate competitions, promotions and events</li>
        <li>Improve our website, services and guest experience</li>
        <li>Send news, events, What's On, invitations and marketing communications where you have opted in</li>
        <li>Meet legal, regulatory and operational requirements</li>
      </ul>

      <h3>Our service providers</h3>
      <p>We use third-party platforms and service providers to operate parts of our business, including reservation systems, point-of-sale and payment providers, CRM and marketing platforms, website hosting, analytics and event-management services.</p>
      <p>This may include services such as OpenTable and Square, together with other technology providers used by House of Bungalow from time to time.</p>
      <p>These providers may process personal information on our behalf in accordance with their own privacy practices and our arrangements with them.</p>

      <h3>Marketing communications</h3>
      <p>If you subscribe to House of Bungalow communications, we may send you information about Restaurant, Below, the Café, events, new menus, programming, invitations and other House of Bungalow news.</p>
      <p>You can unsubscribe from marketing emails at any time using the unsubscribe link contained in the email.</p>
      <p>Where SMS marketing is offered, you may opt out using the instructions provided in the message.</p>

      <h3>Cookies and analytics</h3>
      <p>Our website may use cookies and similar technologies to understand how visitors use the site, improve performance, measure marketing activity and deliver relevant advertising.</p>
      <p>You may be able to manage cookies through your browser settings.</p>

      <h3>Access and correction</h3>
      <p>You may contact us if you would like to request access to personal information we hold about you or ask us to correct information that is inaccurate or out of date.</p>

      <h3>Security</h3>
      <p>We take reasonable steps to protect personal information from misuse, interference, loss and unauthorised access, modification or disclosure.</p>
      <p>No online system is completely secure and we cannot guarantee the security of information transmitted over the internet.</p>

      <h3>Privacy enquiries</h3>
      <p>For questions about this Privacy Policy or the way House of Bungalow handles personal information, please contact:</p>
      <p class="legal-address">
        House of Bungalow<br>
        65&ndash;67 Foveaux Street<br>
        Surry Hills NSW 2010<br>
        Email: <a class="link-underline" href="mailto:<?= e($privacy_email) ?>"><?= e($privacy_email) ?></a>
      </p>

      <h2>Website Terms &amp; Conditions</h2>
      <p>By accessing or using the House of Bungalow website, you agree to these terms.</p>

      <h3>Website information</h3>
      <p>We aim to keep information on this website accurate and current. However, menus, prices, opening hours, entertainment, events, packages and other information may change without notice.</p>
      <p>Images and descriptions are provided for general information and may not always represent the exact product, dish, event or experience available at a particular time.</p>

      <h3>Reservations</h3>
      <p>Restaurant and Below reservations may be managed through third-party reservation platforms.</p>
      <p>Bookings are subject to availability and any booking terms presented at the time of reservation.</p>
      <p>Certain reservations may require a credit card guarantee, deposit or prepayment.</p>
      <p>Cancellation, amendment and no-show conditions will be displayed during the booking process and may vary depending on the booking type, event, date or party size.</p>

      <h3>Private events and group bookings</h3>
      <p>Private events, functions and larger group bookings are subject to separate terms provided as part of the event proposal or booking agreement.</p>
      <p>These may include minimum spends, deposits, cancellation conditions, final guest numbers and other event-specific requirements.</p>

      <h3>Alcohol and responsible service</h3>
      <p>House of Bungalow supports the responsible service of alcohol.</p>
      <p>Service of alcohol is subject to applicable liquor licensing requirements and NSW Responsible Service of Alcohol obligations.</p>
      <p>Management reserves the right to refuse service or entry where required by law or where reasonably necessary for the safety and enjoyment of guests and staff.</p>
      <p>Entry to Below is restricted to guests aged 18 years and over unless otherwise expressly permitted by law and the venue.</p>
      <p>Valid identification may be required.</p>

      <h3>Venue entry</h3>
      <p>Entry to House of Bungalow and Below remains subject to venue capacity, licensing conditions, dress standards, behaviour and management discretion.</p>
      <p>A reservation, event ticket or guest-list registration does not override legal entry requirements.</p>

      <h3>Gift cards</h3>
      <p>Gift cards are subject to the terms provided at the time of purchase.</p>
      <p>Gift cards cannot generally be exchanged for cash except where required by law.</p>
      <p>Expiry periods and redemption conditions will be displayed when purchasing the gift card.</p>

      <h3>Promotions and events</h3>
      <p>Promotions, competitions, ticketed events and special offers may be subject to additional terms.</p>
      <p>Where specific terms are published for a promotion or event, those terms will apply in addition to these general terms.</p>

      <h3>Intellectual property</h3>
      <p>Unless otherwise stated, all content on this website &mdash; including branding, logos, text, graphics, menus, photography, video and design elements &mdash; belongs to or is licensed to House of Bungalow.</p>
      <p>Content may not be reproduced, distributed or commercially used without prior written permission.</p>

      <h3>Third-party websites</h3>
      <p>Our website may contain links to third-party websites or platforms including reservation, payment, ticketing and social media services.</p>
      <p>House of Bungalow is not responsible for the content, availability or privacy practices of third-party websites.</p>

      <h3>Liability</h3>
      <p>To the extent permitted by law, House of Bungalow is not responsible for loss or damage arising from the use of this website or reliance on information contained on it.</p>
      <p>Nothing in these terms excludes any rights or remedies that cannot legally be excluded under Australian law.</p>

      <h3>Changes to these terms</h3>
      <p>We may update this Privacy Policy or these Terms &amp; Conditions from time to time.</p>
      <p>The current version will always be published on this page with the date of the latest update.</p>

      <h3>Governing law</h3>
      <p>These terms are governed by the laws of New South Wales, Australia.</p>

    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
