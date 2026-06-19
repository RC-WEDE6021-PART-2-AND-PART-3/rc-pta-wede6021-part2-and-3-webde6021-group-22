<?php
$pageTitle = 'Admin Orders';
require_once '../includes/session_check.php';
require_once '../includes/DBConn.php';
requireAdmin('../admin/login.php');

$conn = getDBConnection();
$admin = getCurrentUser();
$msg = '';
$msgType = 'success';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['order_status'] ?? '';
    $validStatuses = ['placed', 'confirmed', 'shipped', 'delivered', 'cancelled', 'disputed'];
    
    if (in_array($status, $validStatuses)) {
        $stmt = $conn->prepare("UPDATE tblAorder SET order_status = ? WHERE order_id = ?");
        $stmt->bind_param('si', $status, $orderId);
        if ($stmt->execute()) {
            $msg = "Order #$orderId updated to $status.";
        } else {
            $msg = 'Error updating order.';
            $msgType = 'error';
        }
        $stmt->close();
    }
}

// Fetch orders with details
$orders = [];
$res = $conn->query("
    SELECT o.*, 
           l.title AS listing_title, l.price AS listing_price,
           u1.first_name AS buyer_fname, u1.last_name AS buyer_lname,
           u2.first_name AS seller_fname, u2.last_name AS seller_lname
    FROM tblAorder o
    JOIN tblListing l ON o.listing_id = l.listing_id
    JOIN tblUser u1 ON o.buyer_id = u1.user_id
    JOIN tblUser u2 ON o.seller_id = u2.user_id
    ORDER BY o.created_at DESC
");
while ($row = $res->fetch_assoc()) $orders[] = $row;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Orders — Pastimes</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="dashboard">
  <aside class="sidebar">
    <div class="sidebar-brand"><a href="../index.php">Pastimes</a></div>
    <nav class="sidebar-nav">
      <div class="sidebar-label">Admin</div>
      <a href="dashboard.php" class="sidebar-link">Dashboard</a>
      <a href="messages.php" class="sidebar-link">📬 Messages</a>
      <a href="orders.php" class="sidebar-link active">📦 Orders</a>
      <div class="sidebar-label">Site</div>
      <a href="../browse.php" class="sidebar-link">View Site</a>
      <a href="../logout.php" class="sidebar-link">Logout</a>
    </nav>
  </aside>

  <main class="dashboard-content">
    <div class="page-header">
      <h1>Order Management</h1>
      <p>Manage all orders across the platform.</p>
    </div>

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header"><h3>All Orders (<?= count($orders) ?>)</h3></div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Order ID</th><th>Item</th><th>Buyer</th><th>Seller</th>
              <th>Price Paid</th><th>Payment</th><th>Status</th><th>Date</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
            <tr><td colspan="9" style="text-align:center;padding:3rem;color:var(--mid-grey);">No orders found.</td></tr>
            <?php endif; ?>
            <?php foreach ($orders as $o): 
                $statusColor = match($o['order_status']) {
                    'delivered' => 'badge-green',
                    'confirmed', 'shipped' => 'badge-gold',
                    'cancelled' => 'badge-red',
                    'disputed' => 'badge-orange',
                    default => 'badge-grey'
                };
            ?>
            <tr>
              <td><strong>#<?= $o['order_id'] ?></strong></td>
              <td><?= htmlspecialchars($o['listing_title']) ?></td>
              <td><?= htmlspecialchars($o['buyer_fname'] . ' ' . $o['buyer_lname']) ?></td>
              <td><?= htmlspecialchars($o['seller_fname'] . ' ' . $o['seller_lname']) ?></td>
              <td style="font-weight:600;">R<?= number_format($o['price_paid'], 2) ?></td>
              <td><span class="badge <?= $o['payment_status'] === 'paid' ? 'badge-green' : 'badge-grey' ?>"><?= ucfirst($o['payment_status']) ?></span></td>
              <td><span class="badge <?= $statusColor ?>"><?= ucfirst($o['order_status']) ?></span></td>
              <td style="font-size:.8rem;"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
              <td>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                  <select name="order_status" class="form-control" style="width:110px;padding:.2rem .5rem;font-size:.8rem;" onchange="this.form.submit()">
                    <option value="placed" <?= $o['order_status'] === 'placed' ? 'selected' : '' ?>>Placed</option>
                    <option value="confirmed" <?= $o['order_status'] === 'confirmed' ? 'selected' : '' ?>>Confirm</option>
                    <option value="shipped" <?= $o['order_status'] === 'shipped' ? 'selected' : '' ?>>Ship</option>
                    <option value="delivered" <?= $o['order_status'] === 'delivered' ? 'selected' : '' ?>>Deliver</option>
                    <option value="cancelled" <?= $o['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancel</option>
                    <option value="disputed" <?= $o['order_status'] === 'disputed' ? 'selected' : '' ?>>Dispute</option>
                  </select>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
</body>
</html>