<?php
// includes/cart.php - Shopping Cart functionality for Pastimes

function getCart(): array {
    return $_SESSION['cart'] ?? [];
}

function addToCart(int $listingId, int $quantity = 1): bool {
    $cart = getCart();
    if (isset($cart[$listingId])) {
        $cart[$listingId] += $quantity;
    } else {
        $cart[$listingId] = $quantity;
    }
    $_SESSION['cart'] = $cart;
    return true;
}

function removeFromCart(int $listingId): bool {
    $cart = getCart();
    if (isset($cart[$listingId])) {
        unset($cart[$listingId]);
        $_SESSION['cart'] = $cart;
        return true;
    }
    return false;
}

function updateCartItem(int $listingId, int $quantity): bool {
    if ($quantity <= 0) {
        return removeFromCart($listingId);
    }
    $cart = getCart();
    $cart[$listingId] = $quantity;
    $_SESSION['cart'] = $cart;
    return true;
}

function clearCart(): void {
    $_SESSION['cart'] = [];
}

function getCartTotal(): float {
    $cart = getCart();
    if (empty($cart)) return 0.00;
    
    require_once 'DBConn.php';
    $conn = getDBConnection();
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare("SELECT listing_id, price FROM tblListing WHERE listing_id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();
    $total = 0;
    while ($row = $res->fetch_assoc()) {
        $total += $row['price'] * $cart[$row['listing_id']];
    }
    $stmt->close();
    $conn->close();
    return $total;
}

function getCartItems(): array {
    $cart = getCart();
    if (empty($cart)) return [];
    
    require_once 'DBConn.php';
    $conn = getDBConnection();
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare("SELECT l.*, u.shop_name, u.first_name, u.last_name 
        FROM tblListing l 
        JOIN tblUser u ON l.seller_id = u.user_id 
        WHERE l.listing_id IN ($placeholders) AND l.listing_status = 'active'");
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();
    $items = [];
    while ($row = $res->fetch_assoc()) {
        $row['quantity'] = $cart[$row['listing_id']];
        $items[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $items;
}

function getCartCount(): int {
    $cart = getCart();
    return array_sum($cart);
}
?>