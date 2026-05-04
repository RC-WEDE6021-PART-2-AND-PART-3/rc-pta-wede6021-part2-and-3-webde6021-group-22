<?php
$pageTitle = 'My Profile';
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';
requireLogin();

$user = getCurrentUser();
$conn = getDBConnection();

$success = '';
$error   = '';

// Fetch full record
$stmt = $conn->prepare("SELECT * FROM tblUser WHERE user_id = ?");
$stmt->bind_param('i', $user['user_id']);
$stmt->execute();
$fullUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fn   = trim($_POST['first_name']    ?? '');
    $ln   = trim($_POST['last_name']     ?? '');
    $ph   = trim($_POST['phone_number']  ?? '');
    $prov = trim($_POST['province']      ?? '');
    $city = trim($_POST['city']          ?? '');
    $shop = trim($_POST['shop_name']     ?? '');
    $desc = trim($_POST['shop_description'] ?? '');
    $hol  = isset($_POST['holiday_mode']) ? 1 : 0;

    if (!$fn || !$ln) {
        $error = 'First and last name are required.';
    } else {
        $upd = $conn->prepare("UPDATE tblUser SET first_name=?,last_name=?,phone_number=?,province=?,city=?,shop_name=?,shop_description=?,holiday_mode=? WHERE user_id=?");
        $upd->bind_param('sssssssii', $fn,$ln,$ph,$prov,$city,$shop,$desc,$hol,$user['user_id']);
        if ($upd->execute()) {
            $success = 'Profile updated successfully.';
            // Update session name
            $_SESSION['first_name'] = $fn;
            $_SESSION['last_name']  = $ln;
            // Refresh
            $stmt2 = $conn->prepare("SELECT * FROM tblUser WHERE user_id=?");
            $stmt2->bind_param('i',$user['user_id']);
            $stmt2->execute();
            $fullUser = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
        } else {
            $error = 'Update failed: ' . $conn->error;
        }
        $upd->close();
    }

    // Change password (optional)
    $newPw = trim($_POST['new_password'] ?? '');
    $curPw = trim($_POST['current_password'] ?? '');
    if ($newPw) {
        if (md5($curPw) !== $fullUser['password_hash']) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPw) < 8) {
            $error = 'New password must be at least 8 characters.';
        } else {
            $hash = md5($newPw);
            $pwUpd = $conn->prepare("UPDATE tblUser SET password_hash=? WHERE user_id=?");
            $pwUpd->bind_param('si',$hash,$user['user_id']);
            $pwUpd->execute();
            $pwUpd->close();
            $success .= ' Password changed.';
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>My Profile — Pastimes</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php require_once 'includes/header.php'; ?>

<div class="container" style="max-width:760px;padding:3rem 1.5rem 5rem;">
  <a href="dashboard.php" style="font-size:.85rem;color:var(--mid-grey);">← Back to Dashboard</a>
  <h1 style="margin-top:.75rem;margin-bottom:.5rem;">My Profile</h1>
  <p style="margin-bottom:2rem;">Update your account details and shop information.</p>

  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <form method="POST">
    <div class="card card-body mb-3">
      <h3 style="margin-bottom:1.25rem;">Personal Information</h3>
      <div class="grid-2" style="gap:1rem;">
        <div class="form-group">
          <label>First Name *</label>
          <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($fullUser['first_name']) ?>">
        </div>
        <div class="form-group">
          <label>Last Name *</label>
          <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($fullUser['last_name']) ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Email (read-only)</label>
        <input type="email" class="form-control" value="<?= htmlspecialchars($fullUser['email']) ?>" readonly style="background:#f5f5f5;cursor:not-allowed;">
      </div>
      <div class="form-group">
        <label>Phone Number</label>
        <input type="tel" name="phone_number" class="form-control" value="<?= htmlspecialchars($fullUser['phone_number'] ?? '') ?>" placeholder="07X XXX XXXX">
      </div>
      <div class="grid-2" style="gap:1rem;">
        <div class="form-group">
          <label>Province</label>
          <select name="province" class="form-control">
            <option value="">— Select Province —</option>
            <?php
            $provinces = ['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape','Limpopo','Mpumalanga','North West','Free State','Northern Cape'];
            foreach ($provinces as $p): ?>
            <option value="<?= $p ?>" <?= $fullUser['province']===$p?'selected':'' ?>><?= $p ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>City</label>
          <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($fullUser['city'] ?? '') ?>" placeholder="Johannesburg">
        </div>
      </div>
    </div>

    <div class="card card-body mb-3">
      <h3 style="margin-bottom:1.25rem;">Shop Information</h3>
      <div class="form-group">
        <label>Shop Name</label>
        <input type="text" name="shop_name" class="form-control" value="<?= htmlspecialchars($fullUser['shop_name'] ?? '') ?>" placeholder="My Vintage Closet">
      </div>
      <div class="form-group">
        <label>Shop Description</label>
        <textarea name="shop_description" class="form-control" rows="3" placeholder="Tell buyers about your shop…"><?= htmlspecialchars($fullUser['shop_description'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;">
          <input type="checkbox" name="holiday_mode" value="1" <?= $fullUser['holiday_mode']?'checked':'' ?> style="accent-color:var(--gold);">
          <span>Holiday Mode — pause my shop (listings stay visible but can't be purchased)</span>
        </label>
      </div>
    </div>

    <div class="card card-body mb-3">
      <h3 style="margin-bottom:1.25rem;">Change Password</h3>
      <p style="margin-bottom:1.25rem;">Leave blank if you don't want to change your password.</p>
      <div class="form-group">
        <label>Current Password</label>
        <input type="password" name="current_password" class="form-control" placeholder="Enter current password">
      </div>
      <div class="form-group">
        <label>New Password</label>
        <input type="password" name="new_password" class="form-control" placeholder="Minimum 8 characters">
      </div>
    </div>

    <div style="display:flex;gap:1rem;justify-content:flex-end;">
      <a href="dashboard.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>
