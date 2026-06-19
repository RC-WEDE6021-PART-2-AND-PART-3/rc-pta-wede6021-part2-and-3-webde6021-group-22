<?php
$pageTitle = 'Admin - Seller Requests';
require_once '../includes/session_check.php';
require_once '../includes/DBConn.php';
requireAdmin('../admin/login.php');

$conn = getDBConnection();
$admin = getCurrentUser();
$msg = '';
$msgType = 'success';

// Handle action (approve/reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $requestId = (int)$_POST['request_id'];
    $action = $_POST['action'] ?? '';
    $adminNotes = trim($_POST['admin_notes'] ?? '');

    if ($action === 'approve') {
        // Get user_id from request
        $stmt = $conn->prepare("SELECT user_id FROM tblSellerRequest WHERE request_id = ?");
        $stmt->bind_param('i', $requestId);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        $stmt->close();

        if ($request) {
            $userId = $request['user_id'];
            // Update user role to seller
            $stmt = $conn->prepare("UPDATE tblUser SET role = 'seller' WHERE user_id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->close();

            // Update request status
            $stmt = $conn->prepare("UPDATE tblSellerRequest SET status = 'approved', reviewed_at = NOW(), admin_notes = ? WHERE request_id = ?");
            $stmt->bind_param('si', $adminNotes, $requestId);
            $stmt->execute();
            $stmt->close();

            $msg = 'Seller request approved. User is now a seller.';
            $msgType = 'success';
        } else {
            $msg = 'Request not found.';
            $msgType = 'error';
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE tblSellerRequest SET status = 'rejected', reviewed_at = NOW(), admin_notes = ? WHERE request_id = ?");
        $stmt->bind_param('si', $adminNotes, $requestId);
        $stmt->execute();
        $stmt->close();
        $msg = 'Request rejected.';
        $msgType = 'success';
    }
}

// Fetch all requests
$requests = [];
$res = $conn->query("
    SELECT r.*, u.first_name, u.last_name, u.email, u.role
    FROM tblSellerRequest r
    JOIN tblUser u ON r.user_id = u.user_id
    ORDER BY CASE WHEN r.status = 'pending' THEN 0 ELSE 1 END, r.requested_at DESC
");
while ($row = $res->fetch_assoc()) $requests[] = $row;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin - Seller Requests</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .status-badge { padding: .2rem .6rem; border-radius: 50px; font-size: .75rem; font-weight: 600; }
        .status-pending { background: #fef9ec; color: #e67e22; }
        .status-approved { background: #edfaf3; color: #27ae60; }
        .status-rejected { background: #fdf0ef; color: #c0392b; }
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:999; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal { background:var(--warm-white); border-radius:var(--radius-lg); padding:2rem; width:100%; max-width:500px; max-height:90vh; overflow-y:auto; box-shadow:var(--shadow-lg); }
    </style>
</head>
<body>

<div class="dashboard">
    <aside class="sidebar">
        <div class="sidebar-brand"><a href="../index.php">Pastimes</a></div>
        <nav class="sidebar-nav">
            <div class="sidebar-label">Admin</div>
            <a href="dashboard.php" class="sidebar-link">Dashboard</a>
            <a href="listings.php" class="sidebar-link">👕 Listings</a>
            <a href="seller_requests.php" class="sidebar-link active">📋 Seller Requests</a>
            <a href="messages.php" class="sidebar-link">📬 Messages</a>
            <a href="orders.php" class="sidebar-link">📦 Orders</a>
            <div class="sidebar-label">Site</div>
            <a href="../browse.php" class="sidebar-link">View Site</a>
            <a href="../logout.php" class="sidebar-link">Logout</a>
        </nav>
    </aside>

    <main class="dashboard-content">
        <div class="page-header">
            <h1>Seller Requests</h1>
            <p>Review and approve or reject seller applications.</p>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msgType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h3>All Requests</h3></div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th><th>Applicant</th><th>Email</th><th>Shop Name</th>
                            <th>Description</th><th>Status</th><th>Submitted</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--mid-grey);">No requests found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($requests as $r): ?>
                        <tr>
                            <td>#<?= $r['request_id'] ?></td>
                            <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                            <td><?= htmlspecialchars($r['email']) ?></td>
                            <td><strong><?= htmlspecialchars($r['shop_name'] ?: '—') ?></strong></td>
                            <td style="max-width:150px;font-size:.85rem;"><?= htmlspecialchars(substr($r['shop_description'] ?? '', 0, 60)) ?><?= strlen($r['shop_description'] ?? '') > 60 ? '…' : '' ?></td>
                            <td>
                                <span class="status-badge status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
                            </td>
                            <td style="font-size:.8rem;"><?= date('d M Y', strtotime($r['requested_at'])) ?></td>
                            <td>
                                <?php if ($r['status'] === 'pending'): ?>
                                <div style="display:flex;gap:.4rem;">
                                    <button class="btn btn-success btn-sm" onclick="openActionModal(<?= $r['request_id'] ?>, 'approve', '<?= htmlspecialchars($r['shop_name']) ?>')">Approve</button>
                                    <button class="btn btn-danger btn-sm" onclick="openActionModal(<?= $r['request_id'] ?>, 'reject', '<?= htmlspecialchars($r['shop_name']) ?>')">Reject</button>
                                </div>
                                <?php else: ?>
                                <span style="color:var(--mid-grey);font-size:.8rem;">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Action Modal -->
<div class="modal-overlay" id="actionModal">
    <div class="modal">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
            <h3 id="modalTitle">Confirm Action</h3>
            <button onclick="closeModal('actionModal')" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--mid-grey);">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="request_id" id="action_request_id">
            <input type="hidden" name="action" id="action_action">
            <p id="modalMessage">Are you sure you want to perform this action?</p>
            <div class="form-group" style="margin-top:1rem;">
                <label>Admin Notes (optional)</label>
                <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add any notes about this decision..."></textarea>
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" onclick="closeModal('actionModal')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function openActionModal(requestId, action, shopName) {
    document.getElementById('action_request_id').value = requestId;
    document.getElementById('action_action').value = action;
    const title = action === 'approve' ? 'Approve Seller Request' : 'Reject Seller Request';
    const msg = action === 'approve' 
        ? `Approve <strong>${shopName}</strong> as a seller? This will upgrade the user to seller role.`
        : `Reject <strong>${shopName}</strong>'s seller request? The user will be notified.`;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').innerHTML = msg;
    document.getElementById('modalSubmitBtn').textContent = action === 'approve' ? 'Approve' : 'Reject';
    document.getElementById('modalSubmitBtn').className = action === 'approve' ? 'btn btn-success' : 'btn btn-danger';
    openModal('actionModal');
}

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});
</script>
</body>
</html>