<?php
$pageTitle = 'My Purchases';
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';
requireLogin();

$user = getCurrentUser();
$conn = getDBConnection();

$orders = [];
$res = $conn->query("SELECT o.*, l.title, l.category, l.price, l.condition_grade,
    u.first_name AS seller_fname, u.last_name AS seller_lname, u.shop_name
    FROM tblAorder o
    JOIN tblListing l ON o.listing_id = l.listing_id
    JOIN tblUser u ON o.seller_id = u.user_id
    WHERE o.buyer_id = {$user['user_id']}
    ORDER BY o.created_at DESC");
while ($row = $res->fetch_assoc()) $orders[] = $row;
$conn->close();

require_once 'includes/header.php';
?>

<div class="container" style="padding:3rem 1.5rem 5rem;max-width:960px;">
  <a href="dashboard.php" style="font-size:.85rem;color:var(--mid-grey);">← Back to Dashboard</a>
  <h1 style="margin-top:.75rem;margin-bottom:.5rem;">My Purchases</h1>
  <p style="margin-bottom:2rem;"><?= count($orders) ?> order<?= count($orders) !== 1 ? 's' : '' ?> found.</p>

  <?php if (empty($orders)): ?>
  <div class="card card-body text-center" style="padding:4rem;">
    <div style="font-size:3rem;margin-bottom:1rem;">🛍️</div>
    <h3>No purchases yet</h3>
    <p style="margin-bottom:1.5rem;">When you buy an item it will appear here.</p>
    <a href="browse.php" class="btn btn-primary">Start Shopping</a>
  </div>
  <?php else: ?>
  <div style="display:flex;flex-direction:column;gap:1rem;">
    <?php foreach ($orders as $o):
      $statusColor = match($o['order_status']) {
        'delivered' => 'badge-green',
        'shipped'   => 'badge-gold',
        'cancelled' => 'badge-red',
        default     => 'badge-orange'
      };
    ?>
    <div class="card card-body" style="display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap;">
      <div style="width:60px;height:60px;background:var(--light-grey);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:1.75rem;flex-shrink:0;">👗</div>
      <div style="flex:1;">
        <div style="font-weight:700;"><?= htmlspecialchars($o['title']) ?></div>
        <div style="font-size:.8rem;color:var(--mid-grey);">
          From <?= htmlspecialchars($o['shop_name'] ?: $o['seller_fname'].' '.$o['seller_lname']) ?> &nbsp;·&nbsp;
          <?= date('d M Y', strtotime($o['created_at'])) ?>
        </div>
      </div>
      <div style="text-align:right;">
        <div style="font-weight:700;font-size:1.1rem;font-family:var(--font-display);">R<?= number_format($o['price_paid'],2) ?></div>
        <span class="badge <?= $statusColor ?>"><?= ucfirst($o['order_status']) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
