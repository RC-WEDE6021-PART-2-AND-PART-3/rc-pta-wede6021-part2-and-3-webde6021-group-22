<?php
$pageTitle = 'Admin Login';
require_once '../includes/session_check.php';
require_once '../includes/DBConn.php';

if (isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$error        = '';
$sticky_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $sticky_email = $email;

    if (empty($email) || empty($password)) {
        $error = 'Please enter your admin email and password.';
    } else {
        $conn = getDBConnection();
        $hash = md5($password);

        $stmt = $conn->prepare("SELECT u.user_id, u.first_name, u.last_name, u.email, u.role, 
                                       u.account_status, u.password_hash
                                FROM tblUser u
                                INNER JOIN tblAdmin a ON a.user_id = u.user_id
                                WHERE u.email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = 'No administrator account found with that email address.';
        } else {
            $user = $result->fetch_assoc();
            if ($hash !== $user['password_hash']) {
                $error = 'Incorrect password.';
            } elseif ($user['account_status'] !== 'active') {
                $error = 'This admin account is not active.';
            } else {
                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name']  = $user['last_name'];
                $_SESSION['email']      = $user['email'];
                $_SESSION['role']       = 'admin';
                $_SESSION['status']     = 'active';

                header('Location: dashboard.php');
                exit;
            }
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — Pastimes</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background:var(--dark);min-height:100vh;display:flex;align-items:center;justify-content:center;">

<div class="card" style="max-width:440px;width:100%;margin:2rem auto;">
  <div class="card-header" style="background:var(--charcoal);text-align:center;padding:2rem;">
    <a href="../index.php" style="font-family:var(--font-display);font-size:1.5rem;color:var(--gold);">Pastimes</a>
    <div style="font-size:.75rem;color:#666;margin-top:.5rem;letter-spacing:.1em;text-transform:uppercase;">Administrator Access</div>
  </div>
  <div class="card-body" style="background:var(--warm-white);padding:2rem;">

    <h2 style="margin-bottom:1.5rem;font-size:1.5rem;">Admin Sign In</h2>

    <?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate id="adminLoginForm">
      <div class="form-group">
        <label>Admin Email *</label>
        <input type="email" name="email" class="form-control"
          value="<?= htmlspecialchars($sticky_email) ?>"
          placeholder="admin@pastimes.co.za" required>
      </div>
      <div class="form-group">
        <label>Password *</label>
        <input type="password" name="password" class="form-control"
          placeholder="Admin password" required>
      </div>

      <div style="background:#fef9ec;border:1px solid #f0d060;border-radius:var(--radius);padding:.9rem;font-size:.8rem;color:#856404;margin-bottom:1.25rem;">
        🔐 Demo admin: <strong>admin@pastimes.co.za</strong> / <strong>adminpass</strong>
      </div>

      <button type="submit" class="btn btn-dark btn-block" style="font-size:1rem;padding:.9rem;">
        Sign In as Administrator
      </button>
    </form>

    <div style="text-align:center;margin-top:1.5rem;">
      <a href="../login.php" style="font-size:.85rem;color:var(--mid-grey);">← Back to User Login</a>
    </div>
  </div>
</div>

<script>
document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
  const inputs = this.querySelectorAll('input[required]');
  let ok = true;
  inputs.forEach(i => {
    if (!i.value.trim()) { i.style.borderColor='var(--error)'; ok=false; }
    else i.style.borderColor='';
  });
  if (!ok) e.preventDefault();
});
</script>
</body>
</html>
