<?php
// includes/header.php
// Rule: always require session_check BEFORE this file is included
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/session_check.php';
}
$user       = getCurrentUser();
$isLoggedIn = isLoggedIn();
$root       = $rootPath ?? '';
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