<?php
$pageTitle = 'Contact Us';
$cssPath   = 'css/style.css';
require_once 'includes/header.php';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real app, this would send email via PHPMailer
    $success = true;
}
?>

<div style="background:var(--warm-white);border-bottom:1px solid var(--light-grey);padding:2rem 0;">
  <div class="container">
    <h1>Contact Us</h1>
    <p>We're here to help buyers and sellers. Reach us anytime.</p>
  </div>
</div>

<div class="container" style="padding:4rem 1.5rem;">
  <?php if ($success): ?>
  <div class="alert alert-success">Your message has been sent. We'll get back to you within 24 hours.</div>
  <?php endif; ?>

  <div class="grid-2" style="gap:3rem;">
    <!-- Form -->
    <div class="card card-body">
      <h3 style="margin-bottom:1.5rem;">Send a Message</h3>
      <form method="POST">
        <div class="form-group"><label>Full Name *</label><input type="text" name="name" class="form-control" required placeholder="Your full name"></div>
        <div class="form-group"><label>Email Address *</label><input type="email" name="email" class="form-control" required placeholder="you@example.co.za"></div>
        <div class="form-group">
          <label>Subject *</label>
          <select name="subject" class="form-control" required>
            <option value="">— Select Topic —</option>
            <option>Problem with an order</option>
            <option>Account issue</option>
            <option>Report a listing</option>
            <option>Authentication enquiry</option>
            <option>Other</option>
          </select>
        </div>
        <div class="form-group"><label>Your Message *</label><textarea name="message" class="form-control" rows="5" required placeholder="Describe your issue or question…"></textarea></div>
        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
      </form>
    </div>

    <!-- Info -->
    <div>
      <div class="card card-body mb-3">
        <h3 style="margin-bottom:1.25rem;">Support Information</h3>
        <div style="display:flex;flex-direction:column;gap:1.25rem;">
          <div><div style="font-weight:700;margin-bottom:.25rem;">📧 Email</div><div style="color:var(--mid-grey);">support@pastimes.co.za</div></div>
          <div>
            <div style="font-weight:700;margin-bottom:.25rem;">🕐 Hours</div>
            <div style="color:var(--mid-grey);line-height:1.8;">Mon–Fri: 08:00–17:00<br>Sat: 09:00–13:00<br>Sun &amp; Public Holidays: Closed</div>
          </div>
          <div><div style="font-weight:700;margin-bottom:.25rem;">💬 Live Chat</div><button class="btn btn-dark btn-sm" onclick="alert('Live chat would open here.')">Start Chat</button></div>
        </div>
      </div>

      <div class="card card-body">
        <h3 style="margin-bottom:1.25rem;">Frequently Asked Questions</h3>
        <?php
        $faqs = [
          'How do I track my order?' => 'Go to My Dashboard → My Purchases and click on your order for tracking info.',
          'How do I reset my password?' => 'Click "Forgot password?" on the login page and follow the email instructions.',
          'When will I receive my payout?' => 'Seller payouts are processed within 3–5 business days after order delivery is confirmed.',
          'How do I report a problem?' => 'Use this contact form or message us via live chat. We respond within 24 hours.',
        ];
        foreach ($faqs as $q => $a): ?>
        <details style="border-bottom:1px solid var(--light-grey);padding:.75rem 0;">
          <summary style="font-weight:600;cursor:pointer;font-size:.9rem;"><?= $q ?></summary>
          <p style="margin-top:.5rem;font-size:.875rem;"><?= $a ?></p>
        </details>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
