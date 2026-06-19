<?php
$pageTitle = 'My Wishlist';
require_once 'includes/session_check.php';
requireLogin();
require_once 'includes/header.php';
?>
<div class="container" style="padding:3rem 1.5rem 5rem;max-width:960px;">
  <a href="dashboard.php" style="font-size:.85rem;color:var(--mid-grey);">← Back to Dashboard</a>
  <h1 style="margin-top:.75rem;margin-bottom:.5rem;">My Wishlist</h1>
  <p style="margin-bottom:2rem;">Items you've saved for later.</p>
  <div class="card card-body text-center" style="padding:4rem;">
    <div style="font-size:3rem;margin-bottom:1rem;">🤍</div>
    <h3>Your wishlist is empty</h3>
    <p style="margin-bottom:1.5rem;">Browse our listings and tap the heart icon to save items you love.</p>
    <a href="browse.php" class="btn btn-primary">Start Browsing</a>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
