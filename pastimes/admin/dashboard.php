<?php
$pageTitle = 'Admin Dashboard';
require_once '../includes/session_check.php';
require_once '../includes/DBConn.php';
requireAdmin('../admin/login.php');

$admin = getCurrentUser();
$conn  = getDBConnection();
$msg   = '';
$msgType = 'success';

// ── Handle POST actions ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $uid    = (int)($_POST['user_id'] ?? 0);

    if ($action === 'verify' && $uid) {
        $stmt = $conn->prepare("UPDATE tblUser SET account_status='active' WHERE user_id=? AND account_status='pending'");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $msg = $stmt->affected_rows > 0 ? 'User verified and activated successfully.' : 'No change — user may already be active.';
        $stmt->close();

    } elseif ($action === 'suspend' && $uid) {
        $stmt = $conn->prepare("UPDATE tblUser SET account_status='suspended' WHERE user_id=? AND role != 'admin'");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $msg = 'User account suspended.';
        $stmt->close();

    } elseif ($action === 'activate' && $uid) {
        $stmt = $conn->prepare("UPDATE tblUser SET account_status='active' WHERE user_id=?");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $msg = 'User account reactivated.';
        $stmt->close();

    } elseif ($action === 'delete' && $uid) {
        $stmt = $conn->prepare("UPDATE tblUser SET account_status='deleted' WHERE user_id=? AND role != 'admin'");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $msg = 'User account deleted.';
        $stmt->close();

    } elseif ($action === 'add_user') {
        // Admin adds a new customer directly (status = active)
        $fn   = trim($_POST['first_name'] ?? '');
        $ln   = trim($_POST['last_name']  ?? '');
        $em   = trim($_POST['email']      ?? '');
        $ph   = trim($_POST['phone']      ?? '');
        $role = trim($_POST['role']       ?? 'buyer');
        $pw   = trim($_POST['password']   ?? '');

        if ($fn && $ln && $em && $pw) {
            $hash = md5($pw);
            $stmt = $conn->prepare("INSERT INTO tblUser (first_name,last_name,email,phone_number,password_hash,role,account_status) VALUES (?,?,?,?,?,?,'active')");
            $stmt->bind_param('ssssss', $fn, $ln, $em, $ph, $hash, $role);
            if ($stmt->execute()) {
                $newId = $conn->insert_id;
                $w = $conn->prepare("INSERT IGNORE INTO tblWallet (user_id) VALUES (?)");
                $w->bind_param('i', $newId);
                $w->execute();
                $w->close();
                $msg = "User $fn $ln added successfully.";
            } else {
                $msg = 'Error adding user: ' . $conn->error;
                $msgType = 'error';
            }
            $stmt->close();
        } else {
            $msg = 'Please fill in all required fields.';
            $msgType = 'error';
        }

    } elseif ($action === 'update_user') {
        $fn   = trim($_POST['first_name'] ?? '');
        $ln   = trim($_POST['last_name']  ?? '');
        $em   = trim($_POST['email']      ?? '');
        $ph   = trim($_POST['phone']      ?? '');
        $role = trim($_POST['role']       ?? 'buyer');

        $stmt = $conn->prepare("UPDATE tblUser SET first_name=?,last_name=?,email=?,phone_number=?,role=? WHERE user_id=?");
        $stmt->bind_param('sssssi', $fn, $ln, $em, $ph, $role, $uid);
        $stmt->execute();
        $msg = 'User updated successfully.';
        $stmt->close();
    }
}

// ── Fetch stats ───────────────────────────────────────────────────
$totalUsers   = $conn->query("SELECT COUNT(*) c FROM tblUser WHERE account_status != 'deleted'")->fetch_assoc()['c'];
$pendingUsers = $conn->query("SELECT COUNT(*) c FROM tblUser WHERE account_status='pending'")->fetch_assoc()['c'];
$totalListings= $conn->query("SELECT COUNT(*) c FROM tblListing")->fetch_assoc()['c'];
$totalOrders  = $conn->query("SELECT COUNT(*) c FROM tblAorder")->fetch_assoc()['c'];

// ── Fetch all users ───────────────────────────────────────────────
$filter   = $_GET['filter'] ?? 'all';
$search   = trim($_GET['q'] ?? '');
$whereSQL = "WHERE u.account_status != 'deleted'";
if ($filter === 'pending')   $whereSQL .= " AND u.account_status='pending'";
if ($filter === 'active')    $whereSQL .= " AND u.account_status='active'";
if ($filter === 'suspended') $whereSQL .= " AND u.account_status='suspended'";
if ($search) {
    $s = $conn->real_escape_string($search);
    $whereSQL .= " AND (u.first_name LIKE '%$s%' OR u.last_name LIKE '%$s%' OR u.email LIKE '%$s%')";
}

$users = [];
$res = $conn->query("SELECT u.*, w.buyer_balance, w.seller_balance 
    FROM tblUser u LEFT JOIN tblWallet w ON w.user_id = u.user_id 
    $whereSQL ORDER BY u.created_at DESC");
while ($row = $res->fetch_assoc()) $users[] = $row;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard — Pastimes</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:999; align-items:center; justify-content:center; }
    .modal-overlay.open { display:flex; }
    .modal { background:var(--warm-white); border-radius:var(--radius-lg); padding:2rem; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:var(--shadow-lg); }
    .modal h3 { margin-bottom:1.5rem; }
    .tabs { display:flex; gap:.5rem; margin-bottom:2rem; border-bottom:2px solid var(--light-grey); padding-bottom:0; }
    .tab { padding:.6rem 1.2rem; font-size:.85rem; font-weight:600; color:var(--mid-grey); cursor:pointer; border-bottom:3px solid transparent; margin-bottom:-2px; }
    .tab.active { color:var(--gold-dark); border-bottom-color:var(--gold); }
  </style>
</head>
<body>

<div class="dashboard">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-brand"><a href="../index.php">Pastimes</a></div>
    <nav class="sidebar-nav">
      <div class="sidebar-label">Admin</div>
      <a href="dashboard.php" class="sidebar-link active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>
      <a href="dashboard.php?filter=pending" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Pending Verifications
        <?php if ($pendingUsers > 0): ?>
        <span class="badge badge-orange" style="margin-left:auto;"><?= $pendingUsers ?></span>
        <?php endif; ?>
      </a>
      <a href="dashboard.php?filter=all" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        All Users
      </a>
      <div class="sidebar-label">Site</div>
      <a href="../browse.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        View Site
      </a>
      <a href="../logout.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Logout
      </a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="dashboard-content">
    <div class="alert alert-info" style="margin-bottom:1.5rem;">
      🔐 Administrator <?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?> is logged in
    </div>

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <h1>Admin Dashboard</h1>
        <p>Manage users, verify accounts, and oversee the Pastimes platform.</p>
      </div>
      <button onclick="openModal('addUserModal')" class="btn btn-primary">+ Add User</button>
    </div>

    <!-- Stats -->
    <div class="stat-cards">
      <div class="stat-card"><div class="label">Total Users</div><div class="value"><?= $totalUsers ?></div></div>
      <div class="stat-card"><div class="label">Pending Approval</div><div class="value" style="color:var(--pending);"><?= $pendingUsers ?></div></div>
      <div class="stat-card"><div class="label">Total Listings</div><div class="value"><?= $totalListings ?></div></div>
      <div class="stat-card"><div class="label">Total Orders</div><div class="value"><?= $totalOrders ?></div></div>
    </div>

    <!-- Filter Tabs + Search -->
    <div class="card mb-3">
      <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <div class="tabs" style="border:none;margin:0;padding:0;">
          <?php
          $tabs = ['all'=>'All Users','pending'=>'Pending','active'=>'Active','suspended'=>'Suspended'];
          foreach ($tabs as $k=>$v): ?>
          <a href="dashboard.php?filter=<?= $k ?><?= $search ? '&q='.urlencode($search) : '' ?>"
             class="tab <?= $filter===$k?'active':'' ?>"><?= $v ?>
            <?php if ($k==='pending' && $pendingUsers>0): ?>
            <span class="badge badge-orange" style="margin-left:.3rem;"><?= $pendingUsers ?></span>
            <?php endif; ?>
          </a>
          <?php endforeach; ?>
        </div>
        <form method="GET" style="display:flex;gap:.5rem;">
          <input type="hidden" name="filter" value="<?= $filter ?>">
          <input type="text" name="q" class="form-control" style="width:220px;" placeholder="Search by name or email…" value="<?= htmlspecialchars($search) ?>">
          <button type="submit" class="btn btn-dark btn-sm">Search</button>
          <?php if ($search): ?><a href="dashboard.php?filter=<?= $filter ?>" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
        </form>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th><th>Name</th><th>Email</th><th>Phone</th>
              <th>Role</th><th>Status</th><th>Joined</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($users)): ?>
            <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--mid-grey);">No users found.</td></tr>
            <?php endif; ?>
            <?php foreach ($users as $u): ?>
            <tr>
              <td style="color:var(--mid-grey);">#<?= $u['user_id'] ?></td>
              <td><strong><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></strong></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['phone_number'] ?: '—') ?></td>
              <td><span class="badge badge-gold"><?= ucfirst($u['role']) ?></span></td>
              <td>
                <?php
                $badgeClass = match($u['account_status']) {
                  'active'    => 'badge-green',
                  'pending'   => 'badge-orange',
                  'suspended' => 'badge-red',
                  default     => 'badge-grey'
                };
                ?>
                <span class="badge <?= $badgeClass ?>"><?= ucfirst($u['account_status']) ?></span>
              </td>
              <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
              <td>
                <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                  <!-- Edit -->
                  <button class="btn btn-outline btn-sm"
                    onclick="openEditModal(<?= htmlspecialchars(json_encode($u)) ?>)">Edit</button>

                  <?php if ($u['account_status'] === 'pending'): ?>
                  <!-- Verify (approve) -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action"  value="verify">
                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                    <button type="submit" class="btn btn-success btn-sm"
                      onclick="return confirm('Verify and activate this user?')">✓ Verify</button>
                  </form>

                  <?php elseif ($u['account_status'] === 'active' && $u['role'] !== 'admin'): ?>
                  <!-- Suspend -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action"  value="suspend">
                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" style="border-color:var(--pending);color:var(--pending);"
                      onclick="return confirm('Suspend this user?')">Suspend</button>
                  </form>

                  <?php elseif ($u['account_status'] === 'suspended'): ?>
                  <!-- Reactivate -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action"  value="activate">
                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                    <button type="submit" class="btn btn-success btn-sm"
                      onclick="return confirm('Reactivate this user?')">Reactivate</button>
                  </form>
                  <?php endif; ?>

                  <?php if ($u['role'] !== 'admin'): ?>
                  <!-- Delete -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action"  value="delete">
                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm"
                      onclick="return confirm('Delete user <?= htmlspecialchars(addslashes($u['first_name'])) ?>? This cannot be undone.')">Delete</button>
                  </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div><!-- /card -->
  </main>
</div>

<!-- ── Add User Modal ─────────────────────────────────────────────── -->
<div class="modal-overlay" id="addUserModal">
  <div class="modal">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
      <h3>Add New User</h3>
      <button onclick="closeModal('addUserModal')" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--mid-grey);">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add_user">
      <div class="grid-2" style="gap:1rem;">
        <div class="form-group"><label>First Name *</label><input type="text" name="first_name" class="form-control" required placeholder="First name"></div>
        <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" class="form-control" required placeholder="Last name"></div>
      </div>
      <div class="form-group"><label>Email *</label><input type="email" name="email" class="form-control" required placeholder="user@example.co.za"></div>
      <div class="form-group"><label>Phone</label><input type="tel" name="phone" class="form-control" placeholder="07X XXX XXXX"></div>
      <div class="form-group">
        <label>Role *</label>
        <select name="role" class="form-control">
          <option value="buyer">Buyer</option>
          <option value="seller">Seller</option>
        </select>
      </div>
      <div class="form-group"><label>Password *</label><input type="password" name="password" class="form-control" required placeholder="Set a password"></div>
      <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1rem;">
        <button type="button" onclick="closeModal('addUserModal')" class="btn btn-outline">Cancel</button>
        <button type="submit" class="btn btn-primary">Add User</button>
      </div>
    </form>
  </div>
</div>

<!-- ── Edit User Modal ────────────────────────────────────────────── -->
<div class="modal-overlay" id="editUserModal">
  <div class="modal">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
      <h3>Edit User</h3>
      <button onclick="closeModal('editUserModal')" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--mid-grey);">×</button>
    </div>
    <form method="POST" id="editForm">
      <input type="hidden" name="action"  value="update_user">
      <input type="hidden" name="user_id" id="edit_user_id">
      <div class="grid-2" style="gap:1rem;">
        <div class="form-group"><label>First Name *</label><input type="text" name="first_name" id="edit_first_name" class="form-control" required></div>
        <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" id="edit_last_name" class="form-control" required></div>
      </div>
      <div class="form-group"><label>Email *</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
      <div class="form-group"><label>Phone</label><input type="tel" name="phone" id="edit_phone" class="form-control"></div>
      <div class="form-group">
        <label>Role</label>
        <select name="role" id="edit_role" class="form-control">
          <option value="buyer">Buyer</option>
          <option value="seller">Seller</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1rem;">
        <button type="button" onclick="closeModal('editUserModal')" class="btn btn-outline">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function openEditModal(user) {
  document.getElementById('edit_user_id').value   = user.user_id;
  document.getElementById('edit_first_name').value= user.first_name;
  document.getElementById('edit_last_name').value = user.last_name;
  document.getElementById('edit_email').value     = user.email;
  document.getElementById('edit_phone').value     = user.phone_number || '';
  document.getElementById('edit_role').value      = user.role;
  openModal('editUserModal');
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});
</script>
</body>
</html>
