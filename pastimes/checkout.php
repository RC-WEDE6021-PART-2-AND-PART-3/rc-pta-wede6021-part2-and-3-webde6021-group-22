<?php
$pageTitle = 'Checkout';
$cssPath   = 'css/style.css';
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';
requireLogin();

$listingId = (int)($_GET['listing_id'] ?? 0);
$conn = getDBConnection();
$listing = null;
if ($listingId) {
    $stmt = $conn->prepare("SELECT l.*, u.first_name, u.shop_name FROM tblListing l JOIN tblUser u ON l.seller_id=u.user_id WHERE l.listing_id=? AND l.listing_status='active'");
    $stmt->bind_param('i', $listingId);
    $stmt->execute();
    $listing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
$conn->close();
require_once 'includes/header.php';
?>

<div class="container" style="max-width:860px;padding:3rem 1.5rem 5rem;">
  <a href="browse.php" style="font-size:.85rem;color:var(--mid-grey);">← Continue Shopping</a>
  <h1 style="margin-top:.75rem;margin-bottom:2rem;">Checkout</h1>

  <?php if (!$listing): ?>
  <div class="alert alert-error">Item not found or no longer available. <a href="browse.php">Browse other items</a>.</div>
  <?php else: ?>
  <div class="grid-2" style="gap:2rem;align-items:start;">
    <!-- Order Form -->
    <div>
      <div class="card card-body mb-3">
        <h3 style="margin-bottom:1.25rem;">Delivery Details</h3>
        <div class="form-group"><label>Street Address *</label><input type="text" class="form-control" required placeholder="123 Main Road"></div>
        <div class="form-group"><label>Suburb *</label><input type="text" class="form-control" required placeholder="Yeoville"></div>
        <div class="grid-2" style="gap:1rem;">
          <div class="form-group"><label>City *</label><input type="text" class="form-control" required placeholder="Johannesburg"></div>
          <div class="form-group"><label>Postal Code *</label><input type="text" class="form-control" required placeholder="2198"></div>
        </div>
        <div class="form-group">
          <label>Delivery Method</label>
          <select class="form-control">
            <option>Pargo Pickup Point — R65</option>
            <option>Door-to-Door Courier — R95</option>
            <option>PostNet to PostNet — R75</option>
          </select>
        </div>
      </div>

      <div class="card card-body mb-3">
        <h3 style="margin-bottom:1.25rem;">Payment Method</h3>
        <div style="display:flex;flex-direction:column;gap:.75rem;">
          <?php
          $methods = ['💳 PayFast (Card / EFT)','🏦 Instant EFT','👛 Pastimes Wallet'];
          if ($listing['price'] >= 1000) $methods[] = '📅 PayJustNow — 3 x R'.number_format($listing['price']/3,2).' interest-free';
          foreach ($methods as $m): ?>
          <label style="display:flex;align-items:center;gap:.75rem;cursor:pointer;padding:.75rem;border:1.5px solid var(--light-grey);border-radius:var(--radius);">
            <input type="radio" name="payment" style="accent-color:var(--gold);"> <?= $m ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <button class="btn btn-primary btn-block" style="font-size:1rem;padding:.9rem;"
        onclick="alert('In the full build this would process payment via PayFast API.')">
        Place Order — R<?= number_format($listing['price'] + 65, 2) ?>
      </button>
    </div>

    <!-- Order Summary -->
    <div class="card card-body" style="position:sticky;top:90px;">
      <h3 style="margin-bottom:1.25rem;">Order Summary</h3>
      <div style="display:flex;gap:1rem;margin-bottom:1.5rem;">
        <div style="width:80px;height:80px;background:var(--light-grey);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0;">👗</div>
        <div>
          <div style="font-weight:600;"><?= htmlspecialchars($listing['title']) ?></div>
          <div style="font-size:.8rem;color:var(--mid-grey);">
            <?= htmlspecialchars($listing['shop_name'] ?: $listing['first_name']) ?><br>
            <?= htmlspecialchars($listing['condition_grade'] ? ucfirst(str_replace('_',' ',$listing['condition_grade'])) : '') ?>
            <?= $listing['size'] ? '· Size '.$listing['size'] : '' ?>
          </div>
        </div>
      </div>
      <div style="border-top:1px solid var(--light-grey);padding-top:1rem;display:flex;flex-direction:column;gap:.6rem;">
        <div style="display:flex;justify-content:space-between;font-size:.9rem;"><span>Item price</span><span>R<?= number_format($listing['price'],2) ?></span></div>
        <div style="display:flex;justify-content:space-between;font-size:.9rem;"><span>Delivery</span><span>R65.00</span></div>
        <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.1rem;border-top:1px solid var(--light-grey);padding-top:.75rem;margin-top:.25rem;"><span>Total</span><span>R<?= number_format($listing['price']+65, 2) ?></span></div>
      </div>
      <?php if ($listing['is_verified']): ?>
      <div class="badge badge-gold" style="margin-top:1rem;display:block;text-align:center;">✓ Pastimes Verified Item</div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
