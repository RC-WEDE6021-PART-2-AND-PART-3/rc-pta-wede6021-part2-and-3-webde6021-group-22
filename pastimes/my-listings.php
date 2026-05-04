<?php
$pageTitle = 'My Listings';
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';
requireLogin();

$user = getCurrentUser();
$conn = getDBConnection();

$msg = '';

// Handle delete / toggle status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['listing_id'])) {
    $lid    = (int)$_POST['listing_id'];
    $action = $_POST['action'] ?? '';

    if ($action === 'remove') {
        $stmt = $conn->prepare("UPDATE tblListing SET listing_status='removed' WHERE listing_id=? AND seller_id=?");
        $stmt->bind_param('ii', $lid, $user['user_id']);
        $stmt->execute();
        $msg = 'Listing removed.';
        $stmt->close();
    } elseif ($action === 'activate') {
        $stmt = $conn->prepare("UPDATE tblListing SET listing_status='active' WHERE listing_id=? AND seller_id=?");
        $stmt->bind_param('ii', $lid, $user['user_id']);
        $stmt->execute();
        $msg = 'Listing reactivated.';
        $stmt->close();
    }
}

$filter = $_GET['status'] ?? 'active';
$allowedFilters = ['active','draft','sold','removed'];
if (!in_array($filter, $allowedFilters)) $filter = 'active';

$listings = [];
$stmt = $conn->prepare("SELECT * FROM tblListing WHERE seller_id=? AND listing_status=? ORDER BY created_at DESC");
$stmt->bind_param('is', $user['user_id'], $filter);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $listings[] = $row;
$stmt->close();

// Counts per status
$counts = [];
foreach ($allowedFilters as $s) {
    $r = $conn->query("SELECT COUNT(*) c FROM tblListing WHERE seller_id={$user['user_id']} AND listing_status='$s'");
    $counts[$s] = $r->fetch_assoc()['c'];
}
$conn->close();

require_once 'includes/header.php';
?>

<div class="container" style="padding:3rem 1.5rem 5rem;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
    <div>
      <a href="dashboard.php" style="font-size:.85rem;color:var(--mid-grey);">← Back to Dashboard</a>
      <h1 style="margin-top:.5rem;">My Listings</h1>
    </div>
    <a href="sell.php" class="btn btn-primary">+ New Listing</a>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <!-- Status Tabs -->
  <div style="display:flex;gap:.5rem;border-bottom:2px solid var(--light-grey);margin-bottom:2rem;">
    <?php foreach ($allowedFilters as $s): ?>
    <a href="?status=<?= $s ?>"
       style="padding:.6rem 1.2rem;font-size:.85rem;font-weight:600;border-bottom:3px solid <?= $filter===$s?'var(--gold)':'transparent' ?>;color:<?= $filter===$s?'var(--gold-dark)':'var(--mid-grey)' ?>;margin-bottom:-2px;">
      <?= ucfirst($s) ?> <span style="font-size:.75rem;">(<?= $counts[$s] ?>)</span>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($listings)): ?>
  <div class="card card-body text-center" style="padding:4rem;">
    <div style="font-size:3rem;margin-bottom:1rem;">📦</div>
    <h3>No <?= $filter ?> listings</h3>
    <?php if ($filter === 'active'): ?>
    <p style="margin-bottom:1.5rem;">You haven't listed anything yet. Start selling today!</p>
    <a href="sell.php" class="btn btn-primary">List Your First Item</a>
    <?php else: ?>
    <p>No <?= $filter ?> listings to display.</p>
    <?php endif; ?>
  </div>
  <?php else: ?>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Item</th><th>Category</th><th>Brand</th><th>Size</th>
            <th>Price</th><th>Condition</th><th>Type</th><th>Listed</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($listings as $l): ?>
          <tr>
            <td>
              <div style="font-weight:600;"><?= htmlspecialchars($l['title']) ?></div>
              <?php if ($l['is_verified']): ?><span class="badge badge-gold">✓ Verified</span><?php endif; ?>
            </td>
            <td><?= htmlspecialchars($l['category'] ?: '—') ?></td>
            <td><?= htmlspecialchars($l['brand'] ?: '—') ?></td>
            <td><?= htmlspecialchars($l['size'] ?: '—') ?></td>
            <td style="font-weight:600;">R<?= number_format($l['price'],2) ?></td>
            <td><?= ucfirst(str_replace('_',' ',$l['condition_grade'])) ?></td>
            <td><span class="badge <?= $l['listing_type']==='curated'?'badge-gold':'badge-grey' ?>"><?= ucfirst($l['listing_type']) ?></span></td>
            <td style="font-size:.8rem;color:var(--mid-grey);"><?= date('d M Y', strtotime($l['created_at'])) ?></td>
            <td>
              <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                <?php if ($l['listing_status'] === 'active'): ?>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="listing_id" value="<?= $l['listing_id'] ?>">
                  <input type="hidden" name="action" value="remove">
                  <button type="submit" class="btn btn-danger btn-sm"
                    onclick="return confirm('Remove this listing?')">Remove</button>
                </form>
                <?php elseif ($l['listing_status'] === 'removed'): ?>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="listing_id" value="<?= $l['listing_id'] ?>">
                  <input type="hidden" name="action" value="activate">
                  <button type="submit" class="btn btn-success btn-sm">Relist</button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
