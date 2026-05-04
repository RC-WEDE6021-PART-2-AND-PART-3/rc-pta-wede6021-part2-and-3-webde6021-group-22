<?php
$pageTitle = 'My Dashboard';
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';
requireLogin();

$user = getCurrentUser();
$conn = getDBConnection();

// Fetch full user record
$stmt = $conn->prepare("SELECT u.*, w.buyer_balance, w.seller_balance FROM tblUser u 
    LEFT JOIN tblWallet w ON u.user_id = w.user_id WHERE u.user_id = ?");
$stmt->bind_param('i', $user['user_id']);
$stmt->execute();
$fullUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch user's listings (if seller)
$listings = [];
if ($fullUser['role'] === 'seller') {
    $res = $conn->query("SELECT * FROM tblListing WHERE seller_id = {$user['user_id']} ORDER BY created_at DESC LIMIT 10");
    while ($row = $res->fetch_assoc()) $listings[] = $row;
}

// Fetch user's purchases
$orders = [];
$res = $conn->query("SELECT o.*, l.title, l.price, u.first_name AS seller_name 
    FROM tblAorder o 
    JOIN tblListing l ON o.listing_id = l.listing_id 
    JOIN tblUser u ON o.seller_id = u.user_id 
    WHERE o.buyer_id = {$user['user_id']} 
    ORDER BY o.created_at DESC LIMIT 5");
while ($row = $res->fetch_assoc()) $orders[] = $row;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Dashboard — Pastimes</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="dashboard">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <a href="index.php">Pastimes</a>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-label">Account</div>
      <a href="dashboard.php" class="sidebar-link active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>
      <a href="browse.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Browse Shop
      </a>

      <?php if ($fullUser['role'] === 'seller'): ?>
      <div class="sidebar-label">Selling</div>
      <a href="sell.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        List an Item
      </a>
      <a href="my-listings.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        My Listings
      </a>
      <?php endif; ?>

      <div class="sidebar-label">Buying</div>
      <a href="wishlist.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z"/></svg>
        Wishlist
      </a>
      <a href="orders.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
        My Purchases
      </a>

      <div class="sidebar-label">Settings</div>
      <a href="profile.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profile
      </a>
      <a href="logout.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Logout
      </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="dashboard-content">
    <!-- Login Banner -->
    <div class="alert alert-success" style="margin-bottom:1.5rem;">
      User <?= htmlspecialchars($fullUser['first_name'] . ' ' . $fullUser['last_name']) ?> is logged in
    </div>

    <div class="page-header">
      <h1>Welcome back, <?= htmlspecialchars($fullUser['first_name']) ?> 👋</h1>
      <p>Here's what's happening with your Pastimes account.</p>
    </div>

    <!-- Stat Cards -->
    <div class="stat-cards">
      <div class="stat-card">
        <div class="label">Buyer Balance</div>
        <div class="value gold">R<?= number_format($fullUser['buyer_balance'] ?? 0, 2) ?></div>
      </div>
      <?php if ($fullUser['role'] === 'seller'): ?>
      <div class="stat-card">
        <div class="label">Seller Earnings</div>
        <div class="value">R<?= number_format($fullUser['seller_balance'] ?? 0, 2) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Active Listings</div>
        <div class="value"><?= count(array_filter($listings, fn($l) => $l['listing_status'] === 'active')) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Reputation Score</div>
        <div class="value"><?= number_format($fullUser['reputation_score'], 1) ?> ⭐</div>
      </div>
      <?php else: ?>
      <div class="stat-card">
        <div class="label">Purchases</div>
        <div class="value"><?= count($orders) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Account Status</div>
        <div class="value" style="font-size:1rem;"><?= ucfirst($fullUser['account_status']) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Profile Card -->
    <div class="card mb-3">
      <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h3>My Profile</h3>
        <a href="profile.php" class="btn btn-outline btn-sm">Edit Profile</a>
      </div>
      <div class="card-body">
        <div class="grid-2" style="gap:1.5rem;">
          <?php
          $fields = [
            'Full Name'    => $fullUser['first_name'].' '.$fullUser['last_name'],
            'Email'        => $fullUser['email'],
            'Phone'        => $fullUser['phone_number'] ?: '—',
            'Role'         => ucfirst($fullUser['role']),
            'Province'     => $fullUser['province'] ?: '—',
            'City'         => $fullUser['city'] ?: '—',
            'Member Since' => date('d M Y', strtotime($fullUser['created_at'])),
            'Status'       => ucfirst($fullUser['account_status']),
          ];
          foreach ($fields as $label => $val): ?>
          <div>
            <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--mid-grey);margin-bottom:.2rem;"><?= $label ?></div>
            <div style="font-weight:500;"><?= htmlspecialchars($val) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Listings (sellers) -->
    <?php if ($fullUser['role'] === 'seller'): ?>
    <div class="card mb-3">
      <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h3>My Listings</h3>
        <a href="sell.php" class="btn btn-primary btn-sm">+ New Listing</a>
      </div>
      <?php if (empty($listings)): ?>
      <div class="card-body text-center" style="padding:3rem;">
        <p style="margin-bottom:1rem;">You haven't listed anything yet.</p>
        <a href="sell.php" class="btn btn-primary">List Your First Item</a>
      </div>
      <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Title</th><th>Category</th><th>Price</th><th>Status</th><th>Listed</th></tr></thead>
          <tbody>
            <?php foreach ($listings as $l): ?>
            <tr>
              <td><strong><?= htmlspecialchars($l['title']) ?></strong></td>
              <td><?= htmlspecialchars($l['category']) ?></td>
              <td>R<?= number_format($l['price'],2) ?></td>
              <td>
                <span class="badge <?= $l['listing_status']==='active' ? 'badge-green' : 'badge-grey' ?>">
                  <?= ucfirst($l['listing_status']) ?>
                </span>
              </td>
              <td><?= date('d M Y', strtotime($l['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Recent Purchases -->
    <div class="card">
      <div class="card-header"><h3>Recent Purchases</h3></div>
      <?php if (empty($orders)): ?>
      <div class="card-body text-center" style="padding:3rem;">
        <p style="margin-bottom:1rem;">You haven't made any purchases yet.</p>
        <a href="browse.php" class="btn btn-primary">Start Shopping</a>
      </div>
      <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Item</th><th>Seller</th><th>Amount Paid</th><th>Status</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
              <td><strong><?= htmlspecialchars($o['title']) ?></strong></td>
              <td><?= htmlspecialchars($o['seller_name']) ?></td>
              <td>R<?= number_format($o['price_paid'],2) ?></td>
              <td><span class="badge badge-green"><?= ucfirst($o['order_status']) ?></span></td>
              <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </main>
</div>
</body>
</html>
