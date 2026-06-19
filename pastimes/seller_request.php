<?php
$pageTitle = 'Become a Seller';
$cssPath = 'css/style.css';
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';
requireLogin();

$user = getCurrentUser();
$conn = getDBConnection();
$msg = '';
$msgType = '';

// Check if user is already a seller
if ($user['role'] === 'seller') {
    header('Location: dashboard.php');
    exit;
}

// Check if user already has a pending request
$stmt = $conn->prepare("SELECT * FROM tblSellerRequest WHERE user_id = ? AND status = 'pending'");
$stmt->bind_param('i', $user['user_id']);
$stmt->execute();
$pending = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check if user was rejected previously
$stmt = $conn->prepare("SELECT * FROM tblSellerRequest WHERE user_id = ? AND status = 'rejected' ORDER BY requested_at DESC LIMIT 1");
$stmt->bind_param('i', $user['user_id']);
$stmt->execute();
$rejected = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$pending) {
    $shopName = trim($_POST['shop_name'] ?? '');
    $shopDesc = trim($_POST['shop_description'] ?? '');
    $businessReg = trim($_POST['business_registration'] ?? '');

    if (empty($shopName)) {
        $msg = 'Shop name is required.';
        $msgType = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO tblSellerRequest (user_id, shop_name, shop_description, business_registration, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param('isss', $user['user_id'], $shopName, $shopDesc, $businessReg);
        if ($stmt->execute()) {
            $msg = 'Your request has been submitted. Admin will review it shortly.';
            $msgType = 'success';
            // Reload to show pending state
            header('Refresh:2; url=seller_request.php');
        } else {
            $msg = 'Error submitting request. Please try again.';
            $msgType = 'error';
        }
        $stmt->close();
    }
}

$conn->close();
require_once 'includes/header.php';
?>

<div class="container" style="max-width:700px;padding:3rem 1.5rem 5rem;">
    <a href="dashboard.php" style="font-size:.85rem;color:var(--mid-grey);">← Back to Dashboard</a>
    <h1 style="margin-top:.75rem;margin-bottom:.5rem;">Become a Seller</h1>
    <p style="margin-bottom:2rem;">Start selling your pre-owned fashion items on Pastimes.</p>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if ($pending): ?>
        <div class="card card-body text-center" style="padding:3rem;">
            <div style="font-size:3rem;margin-bottom:1rem;">⏳</div>
            <h3>Request Pending</h3>
            <p>Your request to become a seller is currently under review by the admin.</p>
            <p style="font-size:.9rem;color:var(--mid-grey);">Submitted: <?= date('d M Y H:i', strtotime($pending['requested_at'])) ?></p>
        </div>
    <?php elseif ($rejected): ?>
        <div class="card card-body" style="padding:2rem;border-left:4px solid var(--error);">
            <div style="display:flex;gap:1rem;align-items:center;">
                <div style="font-size:3rem;">❌</div>
                <div>
                    <h3 style="margin-bottom:.5rem;">Request Rejected</h3>
                    <p>Your previous request was not approved.</p>
                    <?php if ($rejected['admin_notes']): ?>
                        <p style="color:var(--mid-grey);font-style:italic;">Admin note: <?= htmlspecialchars($rejected['admin_notes']) ?></p>
                    <?php endif; ?>
                    <p style="margin-top:1rem;">You can submit a new request with more details.</p>
                </div>
            </div>
        </div>
        <div style="margin-top:1.5rem;">
            <form method="POST">
                <div class="form-group">
                    <label>Shop Name *</label>
                    <input type="text" name="shop_name" class="form-control" required placeholder="My Vintage Closet" value="<?= htmlspecialchars($rejected['shop_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Shop Description</label>
                    <textarea name="shop_description" class="form-control" rows="4" placeholder="Tell us about your shop, what you sell, and your experience."><?= htmlspecialchars($rejected['shop_description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Business Registration (optional)</label>
                    <input type="text" name="business_registration" class="form-control" placeholder="e.g. Registration number or ID" value="<?= htmlspecialchars($rejected['business_registration'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary">Submit New Request</button>
            </form>
        </div>
    <?php else: ?>
        <div class="card card-body">
            <h3 style="margin-bottom:1.25rem;">Seller Application</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Shop Name *</label>
                    <input type="text" name="shop_name" class="form-control" required placeholder="e.g. My Vintage Closet">
                    <div class="form-hint">This name will appear on your listings and shop page.</div>
                </div>
                <div class="form-group">
                    <label>Shop Description</label>
                    <textarea name="shop_description" class="form-control" rows="4" placeholder="Tell us about your shop, what you sell, and your experience."></textarea>
                </div>
                <div class="form-group">
                    <label>Business Registration (optional)</label>
                    <input type="text" name="business_registration" class="form-control" placeholder="e.g. Registration number or ID">
                </div>
                <div style="background:var(--cream);padding:1rem;border-radius:var(--radius);margin-bottom:1.5rem;font-size:.85rem;">
                    <strong>📌 Note:</strong> Admin will review your application and notify you via email. This process typically takes 24-48 hours.
                </div>
                <button type="submit" class="btn btn-primary btn-block">Submit Request</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>