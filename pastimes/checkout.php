<?php
$pageTitle = 'Checkout';
$cssPath   = 'css/style.css';
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';
require_once 'includes/cart.php';
requireLogin();

$cartItems = getCartItems();
$cartTotal = getCartTotal();

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$conn = getDBConnection();
$user = getCurrentUser();
$orderPlaced = false;

// Process order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $address = trim($_POST['address'] ?? '');
    $suburb = trim($_POST['suburb'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal = trim($_POST['postal'] ?? '');
    $delivery = $_POST['delivery'] ?? 'Pargo Pickup Point';
    
    $deliveryFee = match($delivery) {
        'Door-to-Door Courier' => 95.00,
        'PostNet to PostNet' => 75.00,
        default => 65.00
    };
    
    $fullAddress = "$address, $suburb, $city, $postal";
    
    // Create order for each item
    foreach ($cartItems as $item) {
        $stmt = $conn->prepare("INSERT INTO tblAorder 
            (buyer_id, seller_id, listing_id, price_paid, delivery_method, delivery_fee, delivery_address, payment_status, order_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'paid', 'placed')");
        $stmt->bind_param('iiiddss', $user['user_id'], $item['seller_id'], $item['listing_id'], 
                         $item['price'], $delivery, $deliveryFee, $fullAddress);
        $stmt->execute();
        $stmt->close();
    }
    
    clearCart();
    $orderPlaced = true;
}

$conn->close();
require_once 'includes/header.php';
?>

<div class="container" style="max-width:860px;padding:3rem 1.5rem 5rem;">
    <a href="browse.php" style="font-size:.85rem;color:var(--mid-grey);">← Continue Shopping</a>
    <h1 style="margin-top:.75rem;margin-bottom:2rem;">Checkout</h1>

    <?php if ($orderPlaced): ?>
    <div style="text-align:center;padding:3rem;">
        <div style="font-size:4rem;margin-bottom:1rem;">✅</div>
        <h2>Order Placed!</h2>
        <p>Your order has been placed successfully. You'll receive a confirmation email shortly.</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-top:1.5rem;">
            <a href="browse.php" class="btn btn-primary">Continue Shopping</a>
            <a href="orders.php" class="btn btn-outline">View My Orders</a>
        </div>
    </div>
    <?php elseif (!empty($cartItems)): ?>
    <div class="grid-2" style="gap:2rem;align-items:start;">
        <!-- Order Form -->
        <div>
            <div class="card card-body mb-3">
                <h3 style="margin-bottom:1.25rem;">Delivery Details</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Street Address *</label>
                        <input type="text" name="address" class="form-control" required placeholder="123 Main Road">
                    </div>
                    <div class="form-group">
                        <label>Suburb *</label>
                        <input type="text" name="suburb" class="form-control" required placeholder="Yeoville">
                    </div>
                    <div class="grid-2" style="gap:1rem;">
                        <div class="form-group">
                            <label>City *</label>
                            <input type="text" name="city" class="form-control" required placeholder="Johannesburg">
                        </div>
                        <div class="form-group">
                            <label>Postal Code *</label>
                            <input type="text" name="postal" class="form-control" required placeholder="2198">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Delivery Method</label>
                        <select name="delivery" class="form-control">
                            <option>Pargo Pickup Point — R65</option>
                            <option>Door-to-Door Courier — R95</option>
                            <option>PostNet to PostNet — R75</option>
                        </select>
                    </div>
                    <button type="submit" name="place_order" class="btn btn-primary btn-block" style="font-size:1rem;padding:.9rem;margin-top:1rem;">
                        Place Order — R<?= number_format($cartTotal + 65, 2) ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="card card-body" style="position:sticky;top:90px;">
            <h3 style="margin-bottom:1.25rem;">Order Summary</h3>
            <?php foreach ($cartItems as $item): ?>
            <div style="display:flex;gap:1rem;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid var(--light-grey);">
                <div style="width:60px;height:60px;background:var(--light-grey);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">👗</div>
                <div style="flex:1;">
                    <div style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars($item['title']) ?></div>
                    <div style="font-size:.8rem;color:var(--mid-grey);"><?= htmlspecialchars($item['shop_name'] ?: $item['first_name']) ?></div>
                    <div style="font-weight:600;">R<?= number_format($item['price'],2) ?> × <?= $item['quantity'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <div style="border-top:1px solid var(--light-grey);padding-top:1rem;display:flex;flex-direction:column;gap:.6rem;">
                <div style="display:flex;justify-content:space-between;font-size:.9rem;">
                    <span>Subtotal (<?= count($cartItems) ?> items)</span>
                    <span>R<?= number_format($cartTotal,2) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.9rem;">
                    <span>Delivery</span>
                    <span>R65.00</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.1rem;border-top:1px solid var(--light-grey);padding-top:.75rem;margin-top:.25rem;">
                    <span>Total</span>
                    <span>R<?= number_format($cartTotal + 65, 2) ?></span>
                </div>
            </div>
            <?php if (count(array_filter($cartItems, function($i) { return $i['is_verified'] ?? 0; })) > 0): ?>
            <div class="badge badge-gold" style="margin-top:1rem;display:block;text-align:center;">✓ Some items are Pastimes Verified</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>