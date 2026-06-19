<?php
$pageTitle = 'Message Seller';
$cssPath = 'css/style.css';
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';
requireLogin();

$user = getCurrentUser();
$conn = getDBConnection();

$listingId = (int)($_GET['listing_id'] ?? 0);
$sellerId = (int)($_GET['seller_id'] ?? 0);
$msg = '';
$msgType = '';

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    $listingId = (int)($_POST['listing_id'] ?? 0);
    
    if ($message && $receiverId) {
        $stmt = $conn->prepare("INSERT INTO tblMessage (sender_id, receiver_id, listing_id, message_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iiis', $user['user_id'], $receiverId, $listingId, $message);
        if ($stmt->execute()) {
            $msg = 'Message sent successfully.';
            $msgType = 'success';
        } else {
            $msg = 'Error sending message.';
            $msgType = 'error';
        }
        $stmt->close();
    }
}

// Get seller info
$seller = null;
if ($listingId) {
    $stmt = $conn->prepare("SELECT u.user_id, u.first_name, u.last_name, u.shop_name, l.title 
        FROM tblListing l 
        JOIN tblUser u ON l.seller_id = u.user_id 
        WHERE l.listing_id = ?");
    $stmt->bind_param('i', $listingId);
    $stmt->execute();
    $seller = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} elseif ($sellerId) {
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, shop_name, NULL AS title FROM tblUser WHERE user_id = ?");
    $stmt->bind_param('i', $sellerId);
    $stmt->execute();
    $seller = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get conversation history
$conversation = [];
if ($seller) {
    $stmt = $conn->prepare("
        SELECT m.*, u.first_name, u.last_name 
        FROM tblMessage m 
        JOIN tblUser u ON m.sender_id = u.user_id 
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.sent_at ASC
    ");
    $stmt->bind_param('iiii', $user['user_id'], $seller['user_id'], $seller['user_id'], $user['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $conversation[] = $row;
    $stmt->close();
}

$conn->close();
require_once 'includes/header.php';
?>

<div class="container" style="max-width:760px;padding:3rem 1.5rem 5rem;">
    <a href="browse.php" style="font-size:.85rem;color:var(--mid-grey);">← Back to Shop</a>
    
    <?php if (!$seller): ?>
    <div class="alert alert-error">Seller not found.</div>
    <?php else: ?>
    
    <h1 style="margin-top:.75rem;margin-bottom:.5rem;">Message <?= htmlspecialchars($seller['first_name']) ?></h1>
    <p style="margin-bottom:1.5rem;">
        <?= $seller['shop_name'] ? 'Shop: ' . htmlspecialchars($seller['shop_name']) : '' ?>
        <?= $seller['title'] ? '· Item: ' . htmlspecialchars($seller['title']) : '' ?>
    </p>

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Conversation -->
    <?php if (!empty($conversation)): ?>
    <div class="card card-body mb-3" style="max-height:400px;overflow-y:auto;">
        <?php foreach ($conversation as $m): ?>
        <div style="margin-bottom:1rem;padding:0.75rem;background:<?= $m['sender_id'] == $user['user_id'] ? 'var(--cream)' : 'var(--warm-white)' ?>;border-radius:var(--radius);border:1px solid var(--light-grey);">
            <div style="display:flex;justify-content:space-between;font-size:.8rem;color:var(--mid-grey);margin-bottom:.3rem;">
                <span><strong><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></strong></span>
                <span><?= date('d M Y H:i', strtotime($m['sent_at'])) ?></span>
            </div>
            <div><?= nl2br(htmlspecialchars($m['message_text'])) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info">No previous messages with this seller.</div>
    <?php endif; ?>

    <!-- Send Message -->
    <div class="card card-body">
        <h3 style="margin-bottom:1rem;">Send a Message</h3>
        <form method="POST">
            <input type="hidden" name="receiver_id" value="<?= $seller['user_id'] ?>">
            <input type="hidden" name="listing_id" value="<?= $listingId ?>">
            <div class="form-group">
                <textarea name="message" class="form-control" rows="4" required placeholder="Ask about the item, condition, shipping, or make an offer…"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>