<?php
$pageTitle = 'Shopping Cart';
$cssPath   = 'css/style.css';
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';
require_once 'includes/cart.php';

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $listingId = (int)($_POST['listing_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    if ($listingId) {
        addToCart($listingId, $quantity);
        header('Location: browse.php?added=1');
        exit;
    }
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $listingId = (int)($_POST['listing_id'] ?? 0);
    
    if ($action === 'update' && $listingId) {
        $qty = (int)($_POST['quantity'] ?? 1);
        updateCartItem($listingId, $qty);
    } elseif ($action === 'remove' && $listingId) {
        removeFromCart($listingId);
    } elseif ($action === 'clear') {
        clearCart();
    } elseif ($action === 'checkout') {
        header('Location: checkout.php');
        exit;
    }
}

$cartItems = getCartItems();
$cartTotal = getCartTotal();
$cartCount = getCartCount();

require_once 'includes/header.php';
?>

<div class="container" style="padding:3rem 1.5rem 5rem;max-width:960px;">
    <h1 style="margin-bottom:.5rem;">Shopping Cart</h1>
    <p style="margin-bottom:2rem;"><?= $cartCount ?> item<?= $cartCount !== 1 ? 's' : '' ?> in your cart</p>

    <?php if (empty($cartItems)): ?>
    <div class="card card-body text-center" style="padding:4rem;">
        <div style="font-size:3rem;margin-bottom:1rem;">🛒</div>
        <h3 style="margin-bottom:1rem;">Your cart is empty</h3>
        <p style="margin-bottom:1.5rem;">Browse our latest listings and add items you love.</p>
        <a href="browse.php" class="btn btn-primary">Continue Shopping</a>
    </div>
    <?php else: ?>
    
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:2rem;align-items:start;">
        <!-- Cart Items -->
        <div>
            <?php foreach ($cartItems as $item): ?>
            <div class="card card-body mb-2" style="display:flex;gap:1.5rem;align-items:center;">
                <div style="width:80px;height:80px;background:var(--light-grey);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0;">
                    👗
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700;"><?= htmlspecialchars($item['title']) ?></div>
                    <div style="font-size:.8rem;color:var(--mid-grey);">
                        by <?= htmlspecialchars($item['shop_name'] ?: $item['first_name'] . ' ' . $item['last_name']) ?>
                        <?= $item['brand'] ? '· ' . htmlspecialchars($item['brand']) : '' ?>
                    </div>
                    <div style="font-weight:600;color:var(--gold-dark);">R<?= number_format($item['price'], 2) ?></div>
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="listing_id" value="<?= $item['listing_id'] ?>">
                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" 
                               min="1" max="<?= $item['quantity'] ?>" style="width:60px;padding:.3rem .5rem;border:1px solid var(--light-grey);border-radius:var(--radius);">
                        <button type="submit" class="btn btn-sm btn-outline">Update</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="listing_id" value="<?= $item['listing_id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this item?')">✕</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div style="display:flex;gap:1rem;margin-top:1rem;">
                <form method="POST">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Clear your entire cart?')">Clear Cart</button>
                </form>
                <a href="browse.php" class="btn btn-outline btn-sm">← Continue Shopping</a>
            </div>
        </div>
        
        <!-- Cart Summary -->
        <div class="card card-body" style="position:sticky;top:90px;">
            <h3 style="margin-bottom:1.25rem;">Order Summary</h3>
            <div style="display:flex;flex-direction:column;gap:.75rem;">
                <div style="display:flex;justify-content:space-between;font-size:.9rem;">
                    <span>Subtotal (<?= $cartCount ?> items)</span>
                    <span>R<?= number_format($cartTotal, 2) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.9rem;">
                    <span>Delivery</span>
                    <span>Calculated at checkout</span>
                </div>
                <div style="border-top:1px solid var(--light-grey);padding-top:.75rem;display:flex;justify-content:space-between;font-weight:700;font-size:1.1rem;">
                    <span>Total</span>
                    <span>R<?= number_format($cartTotal, 2) ?></span>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="checkout">
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top:.75rem;">
                        Proceed to Checkout →
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>