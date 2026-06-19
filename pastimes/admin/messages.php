<?php
$pageTitle = 'Admin Messages';
require_once '../includes/session_check.php';
require_once '../includes/DBConn.php';
requireAdmin('../admin/login.php');

$conn = getDBConnection();
$admin = getCurrentUser();
$msg = '';
$msgType = 'success';

// Handle reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $replyText = trim($_POST['reply_text'] ?? '');
    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    $listingId = (int)($_POST['listing_id'] ?? 0);
    
    if ($replyText && $receiverId) {
        $stmt = $conn->prepare("INSERT INTO tblMessage (sender_id, receiver_id, listing_id, message_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iiis', $admin['user_id'], $receiverId, $listingId, $replyText);
        $stmt->execute();
        $stmt->close();
        $msg = 'Reply sent successfully.';
        $msgType = 'success';
    }
}

// Fetch all messages with sender name handling
$messages = [];
$res = $conn->query("
    SELECT m.*, 
           CASE 
               WHEN m.sender_id = 0 THEN 'Guest' 
               ELSE u1.first_name 
           END AS sender_fname,
           CASE 
               WHEN m.sender_id = 0 THEN '' 
               ELSE u1.last_name 
           END AS sender_lname,
           CASE 
               WHEN m.sender_id = 0 THEN 'guest' 
               ELSE u1.role 
           END AS sender_role,
           u2.first_name AS receiver_fname, 
           u2.last_name AS receiver_lname,
           u2.role AS receiver_role,
           l.title AS listing_title
    FROM tblMessage m
    LEFT JOIN tblUser u1 ON m.sender_id = u1.user_id
    JOIN tblUser u2 ON m.receiver_id = u2.user_id
    LEFT JOIN tblListing l ON m.listing_id = l.listing_id
    ORDER BY m.sent_at DESC
");
while ($row = $res->fetch_assoc()) $messages[] = $row;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Messages — Pastimes</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="dashboard">
  <aside class="sidebar">
    <div class="sidebar-brand"><a href="../index.php">Pastimes</a></div>
    <nav class="sidebar-nav">
      <div class="sidebar-label">Admin</div>
      <a href="dashboard.php" class="sidebar-link">Dashboard</a>
      <a href="listings.php" class="sidebar-link">👕 Listings</a>
      <a href="seller_requests.php" class="sidebar-link">📋 Seller Requests</a>
      <a href="messages.php" class="sidebar-link active">📬 Messages</a>
      <a href="orders.php" class="sidebar-link">📦 Orders</a>
      <div class="sidebar-label">Site</div>
      <a href="../browse.php" class="sidebar-link">View Site</a>
      <a href="../logout.php" class="sidebar-link">Logout</a>
    </nav>
  </aside>

  <main class="dashboard-content">
    <div class="page-header">
      <h1>Message Center</h1>
      <p>View and reply to messages between users. Messages from "Guest" are from the contact form.</p>
    </div>

    <?php if (isset($msg)): ?>
    <div class="alert alert-<?= $msgType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header"><h3>All Messages (<?= count($messages) ?>)</h3></div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>From</th><th>To</th><th>Message</th><th>Listing</th><th>Date</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($messages)): ?>
            <tr><td colspan="6" style="text-align:center;padding:3rem;color:var(--mid-grey);">No messages found.</td></tr>
            <?php endif; ?>
            <?php foreach ($messages as $m): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($m['sender_fname'] . ' ' . $m['sender_lname']) ?></strong>
                <?php if ($m['sender_role'] !== 'guest'): ?>
                <span class="badge badge-grey"><?= ucfirst($m['sender_role']) ?></span>
                <?php else: ?>
                <span class="badge badge-grey">Guest</span>
                <?php endif; ?>
              </td>
              <td>
                <strong><?= htmlspecialchars($m['receiver_fname'] . ' ' . $m['receiver_lname']) ?></strong>
                <span class="badge badge-grey"><?= ucfirst($m['receiver_role']) ?></span>
              </td>
              <td style="max-width:300px;">
                <?php 
                // If message is from Guest, strip the "From: name (email)" prefix for cleaner display
                $displayMessage = $m['message_text'];
                if ($m['sender_id'] == 0) {
                    // Check if message starts with "From:"
                    if (strpos($displayMessage, 'From:') === 0) {
                        $lines = explode("\n", $displayMessage);
                        // Skip first line (From:), show the rest
                        if (isset($lines[0]) && isset($lines[1]) && strpos($lines[0], 'Subject:') !== false) {
                            // It's a contact form message
                            $displayMessage = '📧 ' . $lines[0] . "\n" . $lines[1] . "\n" . $lines[2];
                        } else {
                            $displayMessage = substr($displayMessage, 0, 100);
                        }
                    }
                }
                echo nl2br(htmlspecialchars(substr($displayMessage, 0, 150)));
                ?>
                <?= strlen($displayMessage) > 150 ? '…' : '' ?>
              </td>
              <td><?= htmlspecialchars($m['listing_title'] ?? '—') ?></td>
              <td style="font-size:.8rem;color:var(--mid-grey);"><?= date('d M Y H:i', strtotime($m['sent_at'])) ?></td>
              <td>
                <button class="btn btn-outline btn-sm" 
                        onclick="openReplyModal(<?= htmlspecialchars(json_encode($m)) ?>)">Reply</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Reply Modal -->
<div class="modal-overlay" id="replyModal">
  <div class="modal">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
      <h3>Reply to Message</h3>
      <button onclick="closeModal('replyModal')" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--mid-grey);">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="receiver_id" id="reply_receiver_id">
      <input type="hidden" name="listing_id" id="reply_listing_id">
      <div style="margin-bottom:1rem;font-size:.9rem;color:var(--mid-grey);">
        Replying to: <strong id="reply_to_name"></strong>
      </div>
      <div class="form-group">
        <label>Your Reply *</label>
        <textarea name="reply_text" id="reply_text" class="form-control" rows="4" required placeholder="Type your reply…"></textarea>
      </div>
      <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1rem;">
        <button type="button" onclick="closeModal('replyModal')" class="btn btn-outline">Cancel</button>
        <button type="submit" name="reply" class="btn btn-primary">Send Reply</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function openReplyModal(msg) {
    document.getElementById('reply_receiver_id').value = msg.sender_id;
    document.getElementById('reply_listing_id').value = msg.listing_id || 0;
    document.getElementById('reply_to_name').textContent = msg.sender_fname + ' ' + msg.sender_lname;
    document.getElementById('reply_text').value = '';
    openModal('replyModal');
}

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});
</script>
</body>
</html>