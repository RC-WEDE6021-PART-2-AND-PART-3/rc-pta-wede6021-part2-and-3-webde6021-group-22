<?php
// includes/header.php
// Rule: always require session_check BEFORE this file is included
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/session_check.php';
}
// Load cart functions
require_once __DIR__ . '/cart.php';
$user       = getCurrentUser();
$isLoggedIn = isLoggedIn();
$root       = $rootPath ?? '';
$cartCount  = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Pastimes') ?> — Pastimes SA</title>
  <link rel="stylesheet" href="<?= $root ?>css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
  <div class="container">
    <a href="<?= $root ?>index.php" class="navbar-brand">
      Pastimes<span>/ SA Pre-Owned Fashion</span>
    </a>

    <ul class="nav-links">
      <li><a href="<?= $root ?>browse.php">Shop</a></li>
      <li><a href="<?= $root ?>browse.php?verified=1">Verified Luxury</a></li>
      <li><a href="<?= $root ?>contact.php">Contact</a></li>

            <!-- Cart -->
      <li>
        <a href="<?= $root ?>cart.php" style="position:relative;display:flex;align-items:center;gap:.3rem;">
          🛒
          <?php if ($cartCount > 0): ?>
          <span style="position:absolute;top:-8px;right:-12px;background:var(--gold);color:var(--dark);font-size:.6rem;font-weight:700;padding:1px 6px;border-radius:50%;">
            <?= $cartCount ?>
          </span>
          <?php endif; ?>
        </a>
      </li>

      <?php if ($isLoggedIn): ?>
        <?php if ($user['role'] === 'admin'): ?>
          <li><a href="<?= $root ?>admin/dashboard.php">Admin Panel</a></li>
        <?php else: ?>
          <li><a href="<?= $root ?>sell.php">Sell</a></li>
          <li><a href="<?= $root ?>dashboard.php">My Account</a></li>
        <?php endif; ?>
        <li>
          <div class="user-pill">
            <div class="avatar"><?= strtoupper(substr($user['first_name'], 0, 1)) ?></div>
            <?= htmlspecialchars($user['first_name']) ?>
          </div>
        </li>
        <li><a href="<?= $root ?>logout.php" class="btn btn-outline btn-sm">Logout</a></li>
      <?php else: ?>
        <li><a href="<?= $root ?>login.php">Sign In</a></li>
        <li><a href="<?= $root ?>register.php" class="btn btn-primary btn-sm nav-cta">Join Free</a></li>
      <?php endif; ?>
    </ul>

    <!-- Mobile hamburger -->
    <button class="hamburger" id="hamburger" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>

  <!-- Mobile menu -->
  <div class="mobile-menu" id="mobileMenu">
    <a href="<?= $root ?>browse.php">🛍 Shop All Items</a>
    <a href="<?= $root ?>browse.php?verified=1">✓ Verified Luxury</a>
    <a href="<?= $root ?>contact.php">📬 Contact Us</a>
    <a href="<?= $root ?>cart.php">🛒 Cart <?= $cartCount > 0 ? '(' . $cartCount . ')' : '' ?></a>
    <?php if ($isLoggedIn): ?>
      <?php if ($user['role'] !== 'admin'): ?>
        <a href="<?= $root ?>sell.php">➕ Sell an Item</a>
        <a href="<?= $root ?>dashboard.php">👤 My Account</a>
      <?php else: ?>
        <a href="<?= $root ?>admin/dashboard.php">🔐 Admin Panel</a>
      <?php endif; ?>
      <a href="<?= $root ?>logout.php">🚪 Logout</a>
    <?php else: ?>
      <a href="<?= $root ?>login.php">Sign In</a>
      <a href="<?= $root ?>register.php">Join Free</a>
    <?php endif; ?>
  </div>
</nav>
<?php
// Check for seller request notifications
if (isset($_SESSION['seller_request_pending'])): ?>
    <div class="alert alert-warning" style="margin:1rem 0;">
        ⏳ Your seller request is pending admin review. You'll be notified once approved.
    </div>
<?php unset($_SESSION['seller_request_pending']); endif; ?>

<?php if (isset($_SESSION['seller_request_approved'])): ?>
    <div class="alert alert-success" style="margin:1rem 0;">
        🎉 Congratulations! Your seller request has been approved. You can now list items for sale.
    </div>
<?php unset($_SESSION['seller_request_approved']); endif; ?>

<?php if (isset($_SESSION['seller_request_rejected'])): ?>
    <div class="alert alert-error" style="margin:1rem 0;">
        ❌ Your seller request was not approved. 
        <?php if (!empty($_SESSION['seller_request_notes'])): ?>
            <br><strong>Admin note:</strong> <?= htmlspecialchars($_SESSION['seller_request_notes']) ?>
        <?php endif; ?>
        <br><a href="seller_request.php">Submit a new request</a>
    </div>
<?php unset($_SESSION['seller_request_rejected']); unset($_SESSION['seller_request_notes']); endif; ?>

<style>
.hamburger { display:none; flex-direction:column; gap:5px; background:none; border:none; cursor:pointer; padding:.4rem; }
.hamburger span { display:block; width:24px; height:2px; background:var(--light-grey); border-radius:2px; transition:.3s; }
.mobile-menu { display:none; flex-direction:column; background:var(--dark); border-top:1px solid rgba(255,255,255,.08); }
.mobile-menu a { padding:1rem 1.5rem; color:#ccc; font-size:.95rem; border-bottom:1px solid rgba(255,255,255,.05); }
.mobile-menu a:hover { color:var(--gold); background:rgba(200,169,110,.05); }
.mobile-menu.open { display:flex; }
@media(max-width:900px){
  .navbar .nav-links { display:none; }
  .hamburger { display:flex; }
}
</style>
<script>
document.getElementById('hamburger').addEventListener('click', function(){
  document.getElementById('mobileMenu').classList.toggle('open');
});
</script>