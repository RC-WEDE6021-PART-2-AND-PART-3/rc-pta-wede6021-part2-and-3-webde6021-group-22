<?php
$pageTitle = 'Admin - Manage Listings';
require_once '../includes/session_check.php';
require_once '../includes/DBConn.php';
requireAdmin('../admin/login.php');

$conn = getDBConnection();
$msg = '';
$msgType = 'success';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $listingId = (int)($_POST['listing_id'] ?? 0); 

    // Delete listing
    if ($action === 'delete' && $listingId) {
        $stmt = $conn->prepare("DELETE FROM tblListing WHERE listing_id = ?");
        $stmt->bind_param('i', $listingId);
        if ($stmt->execute()) {
            $msg = "Listing #$listingId deleted successfully.";
            $msgType = 'success';
        } else {
            $msg = 'Error deleting listing.';
            $msgType = 'error';
        }
        $stmt->close();
    }
    
    // Update listing
    elseif ($action === 'update' && $listingId) {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $status = trim($_POST['listing_status'] ?? 'active');
        $isVerified = isset($_POST['is_verified']) ? 1 : 0;
        
        if ($title && $price > 0) {
            $stmt = $conn->prepare("UPDATE tblListing SET 
                title = ?, description = ?, category = ?, brand = ?, 
                price = ?, listing_status = ?, is_verified = ? 
                WHERE listing_id = ?");
            $stmt->bind_param('ssssdsii', $title, $description, $category, $brand, $price, $status, $isVerified, $listingId);
            
            if ($stmt->execute()) {
                $msg = "Listing #$listingId updated successfully.";
                $msgType = 'success';
            } else {
                $msg = 'Error updating listing.';
                $msgType = 'error';
            }
            $stmt->close();
        } else {
            $msg = 'Title and price are required.';
            $msgType = 'error';
        }
    }
    
    // Add new listing
    elseif ($action === 'add') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $sellerId = (int)($_POST['seller_id'] ?? 0);
    $isVerified = isset($_POST['is_verified']) ? 1 : 0;
    $condition = trim($_POST['condition_grade'] ?? 'good');
    $size = trim($_POST['size'] ?? '');
    $colour = trim($_POST['colour'] ?? '');
    
    if ($title && $price > 0 && $sellerId > 0) {
        $stmt = $conn->prepare("INSERT INTO tblListing 
            (seller_id, title, description, category, brand, price, 
             listing_status, is_verified, condition_grade, size, colour) 
            VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?)");
        
        // FIXED: 10 placeholders -> 10 types (i, s, s, s, s, d, i, s, s, s)
        $stmt->bind_param('issssdisss', $sellerId, $title, $description, $category, $brand, $price, $isVerified, $condition, $size, $colour);
        
        if ($stmt->execute()) {
            $msg = "Listing added successfully.";
            $msgType = 'success';
        } else {
            $msg = 'Error adding listing: ' . $conn->error;
            $msgType = 'error';
        }
        $stmt->close();
    } else {
        $msg = 'Title, price, and seller are required.';
        $msgType = 'error';
    }
}
}
// Get filter
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$whereSQL = "WHERE 1=1";
if ($filter === 'active') $whereSQL .= " AND listing_status = 'active'";
if ($filter === 'sold') $whereSQL .= " AND listing_status = 'sold'";
if ($filter === 'draft') $whereSQL .= " AND listing_status = 'draft'";
if ($filter === 'removed') $whereSQL .= " AND listing_status = 'removed'";
if ($filter === 'verified') $whereSQL .= " AND is_verified = 1";
if ($search) {
    $s = $conn->real_escape_string($search);
    $whereSQL .= " AND (title LIKE '%$s%' OR brand LIKE '%$s%' OR category LIKE '%$s%')";
}

// Get listings with seller info
$listings = [];
$res = $conn->query("
    SELECT l.*, u.first_name, u.last_name, u.email 
    FROM tblListing l
    JOIN tblUser u ON l.seller_id = u.user_id
    $whereSQL
    ORDER BY l.created_at DESC
");
while ($row = $res->fetch_assoc()) $listings[] = $row;

// Get sellers for dropdown
$sellers = [];
$res2 = $conn->query("SELECT user_id, first_name, last_name, shop_name FROM tblUser WHERE role IN ('seller', 'admin') AND account_status = 'active'");
while ($row = $res2->fetch_assoc()) $sellers[] = $row;

// Count stats
$totalListings = $conn->query("SELECT COUNT(*) c FROM tblListing")->fetch_assoc()['c'];
$activeListings = $conn->query("SELECT COUNT(*) c FROM tblListing WHERE listing_status = 'active'")->fetch_assoc()['c'];
$verifiedListings = $conn->query("SELECT COUNT(*) c FROM tblListing WHERE is_verified = 1")->fetch_assoc()['c'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin - Manage Listings</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:999; align-items:center; justify-content:center; }
    .modal-overlay.open { display:flex; }
    .modal { background:var(--warm-white); border-radius:var(--radius-lg); padding:2rem; width:100%; max-width:600px; max-height:90vh; overflow-y:auto; box-shadow:var(--shadow-lg); }
    .modal h3 { margin-bottom:1.5rem; }
    .tabs { display:flex; gap:.5rem; margin-bottom:2rem; border-bottom:2px solid var(--light-grey); padding-bottom:0; }
    .tab { padding:.6rem 1.2rem; font-size:.85rem; font-weight:600; color:var(--mid-grey); cursor:pointer; border-bottom:3px solid transparent; margin-bottom:-2px; }
    .tab.active { color:var(--gold-dark); border-bottom-color:var(--gold); }
    .listing-thumb { width:50px; height:50px; object-fit:cover; border-radius:var(--radius); }
  </style>
</head>
<body>

<div class="dashboard">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-brand"><a href="../index.php">Pastimes</a></div>
    <nav class="sidebar-nav">
      <div class="sidebar-label">Admin</div>
      <a href="dashboard.php" class="sidebar-link">Dashboard</a>
      <a href="listings.php" class="sidebar-link active">👕 Listings</a>
      <a href="messages.php" class="sidebar-link">📬 Messages</a>
      <a href="orders.php" class="sidebar-link">📦 Orders</a>
      <div class="sidebar-label">Site</div>
      <a href="../browse.php" class="sidebar-link">View Site</a>
      <a href="../logout.php" class="sidebar-link">Logout</a>
    </nav>
  </aside>

  <main class="dashboard-content">
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <h1>Manage Listings</h1>
        <p>Add, edit, or delete clothing listings.</p>
      </div>
      <button onclick="openModal('addModal')" class="btn btn-primary">+ Add Listing</button>
    </div>

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stat-cards">
      <div class="stat-card"><div class="label">Total Listings</div><div class="value"><?= $totalListings ?></div></div>
      <div class="stat-card"><div class="label">Active Listings</div><div class="value" style="color:var(--success);"><?= $activeListings ?></div></div>
      <div class="stat-card"><div class="label">Verified Items</div><div class="value" style="color:var(--gold);"><?= $verifiedListings ?></div></div>
    </div>

    <!-- Filter Tabs -->
    <div class="card mb-3">
      <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <div class="tabs" style="border:none;margin:0;padding:0;">
          <?php
          $tabs = ['all'=>'All','active'=>'Active','sold'=>'Sold','draft'=>'Draft','removed'=>'Removed','verified'=>'✓ Verified'];
          foreach ($tabs as $k=>$v): ?>
          <a href="listings.php?filter=<?= $k ?><?= $search ? '&q='.urlencode($search) : '' ?>"
             class="tab <?= $filter===$k?'active':'' ?>"><?= $v ?></a>
          <?php endforeach; ?>
        </div>
        <form method="GET" style="display:flex;gap:.5rem;">
          <input type="hidden" name="filter" value="<?= $filter ?>">
          <input type="text" name="q" class="form-control" style="width:200px;" placeholder="Search listings…" value="<?= htmlspecialchars($search) ?>">
          <button type="submit" class="btn btn-dark btn-sm">Search</button>
          <?php if ($search): ?><a href="listings.php?filter=<?= $filter ?>" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
        </form>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Image</th>
              <th>ID</th>
              <th>Title</th>
              <th>Seller</th>
              <th>Brand</th>
              <th>Category</th>
              <th>Price</th>
              <th>Status</th>
              <th>Verified</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($listings)): ?>
            <tr><td colspan="10" style="text-align:center;padding:3rem;color:var(--mid-grey);">No listings found.</td></tr>
            <?php endif; ?>
            <?php foreach ($listings as $l): ?>
            <tr>
              <td>
                <?php if (!empty($l['image_url'])): ?>
                  <img src="../uploads/listings/thumbnails/<?= htmlspecialchars($l['image_url']) ?>" 
                       alt="<?= htmlspecialchars($l['title']) ?>"
                       class="listing-thumb">
                <?php else: ?>
                  <span style="font-size:1.5rem;">👗</span>
                <?php endif; ?>
              </td>
              <td>#<?= $l['listing_id'] ?></td>
              <td><strong><?= htmlspecialchars($l['title']) ?></strong></td>
              <td><?= htmlspecialchars($l['first_name'] . ' ' . $l['last_name']) ?></td>
              <td><?= htmlspecialchars($l['brand'] ?: '—') ?></td>
              <td><?= htmlspecialchars($l['category'] ?: '—') ?></td>
              <td style="font-weight:600;">R<?= number_format($l['price'], 2) ?></td>
              <td>
                <span class="badge <?= $l['listing_status'] === 'active' ? 'badge-green' : ($l['listing_status'] === 'sold' ? 'badge-gold' : 'badge-grey') ?>">
                  <?= ucfirst($l['listing_status']) ?>
                </span>
              </td>
              <td>
                <?php if ($l['is_verified']): ?>
                <span class="badge badge-gold">✓ Verified</span>
                <?php else: ?>
                <span class="badge badge-grey">—</span>
                <?php endif; ?>
              </td>
              <td>
                <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                  <button class="btn btn-outline btn-sm" onclick="openEditModal(<?= htmlspecialchars(json_encode($l)) ?>)">Edit</button>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="listing_id" value="<?= $l['listing_id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm" 
                      onclick="return confirm('Delete listing \'<?= htmlspecialchars(addslashes($l['title'])) ?>\'? This cannot be undone.')">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- ── Add Listing Modal ─────────────────────────────────────────────── -->
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
      <h3>Add New Listing</h3>
      <button onclick="closeModal('addModal')" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--mid-grey);">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-group">
        <label>Title *</label>
        <input type="text" name="title" class="form-control" required placeholder="e.g. Vintage Levi 501 Jeans">
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Describe the item…"></textarea>
      </div>
      <div class="grid-2" style="gap:1rem;">
        <div class="form-group">
          <label>Category</label>
          <select name="category" class="form-control">
            <option value="">Select Category</option>
            <option value="Women">Women</option>
            <option value="Men">Men</option>
            <option value="Shoes">Shoes</option>
            <option value="Bags">Bags</option>
            <option value="Accessories">Accessories</option>
          </select>
        </div>
        <div class="form-group">
          <label>Brand</label>
          <input type="text" name="brand" class="form-control" placeholder="e.g. Nike, Zara">
        </div>
      </div>
      <div class="grid-2" style="gap:1rem;">
        <div class="form-group">
          <label>Price (R) *</label>
          <input type="number" name="price" class="form-control" required step="0.01" placeholder="350.00">
        </div>
        <div class="form-group">
          <label>Condition</label>
          <select name="condition_grade" class="form-control">
            <option value="new">New</option>
            <option value="like_new">Like New</option>
            <option value="good" selected>Good</option>
            <option value="fair">Fair</option>
            <option value="poor">Poor</option>
          </select>
        </div>
      </div>
      <div class="grid-2" style="gap:1rem;">
        <div class="form-group">
          <label>Size</label>
          <input type="text" name="size" class="form-control" placeholder="XS / S / M / L / 32 / 38">
        </div>
        <div class="form-group">
          <label>Colour</label>
          <input type="text" name="colour" class="form-control" placeholder="e.g. Blue, Black">
        </div>
      </div>
      <div class="form-group">
        <label>Seller *</label>
        <select name="seller_id" class="form-control" required>
          <option value="">— Select Seller —</option>
          <?php foreach ($sellers as $s): ?>
          <option value="<?= $s['user_id'] ?>">
            <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name'] . ($s['shop_name'] ? ' (' . $s['shop_name'] . ')' : '')) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
          <input type="checkbox" name="is_verified" value="1" style="accent-color:var(--gold);">
          Pastimes Verified
        </label>
      </div>
      <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1rem;">
        <button type="button" onclick="closeModal('addModal')" class="btn btn-outline">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Listing</button>
      </div>
    </form>
  </div>
</div>

<!-- ── Edit Listing Modal ────────────────────────────────────────────── -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
      <h3>Edit Listing</h3>
      <button onclick="closeModal('editModal')" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--mid-grey);">×</button>
    </div>
    <form method="POST" id="editForm">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="listing_id" id="edit_listing_id">
      <div class="form-group">
        <label>Title *</label>
        <input type="text" name="title" id="edit_title" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
      </div>
      <div class="grid-2" style="gap:1rem;">
        <div class="form-group">
          <label>Category</label>
          <select name="category" id="edit_category" class="form-control">
            <option value="">Select Category</option>
            <option value="Women">Women</option>
            <option value="Men">Men</option>
            <option value="Shoes">Shoes</option>
            <option value="Bags">Bags</option>
            <option value="Accessories">Accessories</option>
          </select>
        </div>
        <div class="form-group">
          <label>Brand</label>
          <input type="text" name="brand" id="edit_brand" class="form-control">
        </div>
      </div>
      <div class="grid-2" style="gap:1rem;">
        <div class="form-group">
          <label>Price (R) *</label>
          <input type="number" name="price" id="edit_price" class="form-control" required step="0.01">
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="listing_status" id="edit_status" class="form-control">
            <option value="active">Active</option>
            <option value="sold">Sold</option>
            <option value="draft">Draft</option>
            <option value="removed">Removed</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
          <input type="checkbox" name="is_verified" id="edit_verified" value="1" style="accent-color:var(--gold);">
          Pastimes Verified
        </label>
      </div>
      <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1rem;">
        <button type="button" onclick="closeModal('editModal')" class="btn btn-outline">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function openEditModal(listing) {
    document.getElementById('edit_listing_id').value = listing.listing_id;
    document.getElementById('edit_title').value = listing.title;
    document.getElementById('edit_description').value = listing.description || '';
    document.getElementById('edit_category').value = listing.category || '';
    document.getElementById('edit_brand').value = listing.brand || '';
    document.getElementById('edit_price').value = listing.price;
    document.getElementById('edit_status').value = listing.listing_status;
    document.getElementById('edit_verified').checked = listing.is_verified == 1;
    openModal('editModal');
}

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});
</script>
</body>
</html>