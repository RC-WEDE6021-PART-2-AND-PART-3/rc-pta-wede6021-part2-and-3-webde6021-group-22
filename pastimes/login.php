<?php
$pageTitle = 'Sign In';
$cssPath   = 'css/style.css';
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'dashboard.php'));
    exit;
}

$error      = '';
$loginBanner = '';
$sticky_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $sticky_email = $email;

    // HTML5 validation fallback
    if (empty($email) || empty($password)) {
        $error = 'Please enter your email address and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $conn = getDBConnection();
        $hash = md5($password);

        $stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, role, account_status, password_hash 
                                FROM tblUser WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = 'No account found with that email address. <a href="register.php">Register here</a>.';
        } else {
            $user = $result->fetch_assoc();

            // Compare submitted hash to stored hash
            if ($hash !== $user['password_hash']) {
                $error = 'Incorrect password. Please try again.';
                // Sticky form: keep email, clear password — handled by $sticky_email above
            } elseif ($user['account_status'] === 'pending') {
                $error = 'Your account is <strong>pending admin verification</strong>. You will be able to log in once an administrator has approved your account.';
            } elseif ($user['account_status'] === 'suspended') {
                $error = 'Your account has been suspended. Please <a href="contact.php">contact support</a> for assistance.';
            } elseif ($user['account_status'] === 'deleted') {
                $error = 'This account no longer exists.';
            } else {
                // Success — set session
                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name']  = $user['last_name'];
                $_SESSION['email']      = $user['email'];
                $_SESSION['role']       = $user['role'];
                $_SESSION['status']     = $user['account_status'];

                $loginBanner = "User {$user['first_name']} {$user['last_name']} is logged in";

                $redirect = ($user['role'] === 'admin') ? 'admin/dashboard.php' : 'dashboard.php';
                header("Location: $redirect");
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — Pastimes</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-page">

  <!-- Left Visual -->
  <div class="auth-visual">
    <div class="auth-visual-content">
      <a href="index.php" style="font-family:var(--font-display);font-size:1.8rem;color:var(--gold);display:block;margin-bottom:3rem;">Pastimes</a>
      <h2>Welcome<br>back to your<br><em>fashion story.</em></h2>
      <p style="margin-top:1.5rem;">Sign in to browse your wishlist, manage your listings, track orders, and access your wallet.</p>

      <div style="margin-top:3rem;padding:1.5rem;background:rgba(200,169,110,.08);border:1px solid rgba(200,169,110,.2);border-radius:var(--radius-lg);">
        <div style="font-size:.75rem;color:var(--gold);font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:1rem;">Demo Credentials</div>
        <div style="font-size:.82rem;color:#888;line-height:1.9;">
          <strong style="color:#aaa;">Buyer:</strong> j.doe@abc.co.za / password123<br>
          <strong style="color:#aaa;">Seller:</strong> s.nkosi@gmail.com / seller456<br>
          <strong style="color:#aaa;">Admin:</strong> admin@pastimes.co.za / adminpass
        </div>
      </div>
    </div>
  </div>

  <!-- Right Form -->
  <div class="auth-form-side">
    <div class="auth-form-wrap">
      <h1>Sign In</h1>
      <p class="subtitle">Don't have an account? <a href="register.php">Register free</a></p>

      <?php if (!empty($loginBanner)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($loginBanner) ?></div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
      <!-- Sticky form: error shown, fields pre-filled so user doesn't retype everything -->
      <div class="alert alert-error"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" novalidate id="loginForm">

        <div class="form-group">
          <label for="email">Email Address *</label>
          <!-- Sticky: repopulate email on error -->
          <input type="email" id="email" name="email" class="form-control"
            value="<?= htmlspecialchars($sticky_email) ?>"
            placeholder="you@example.co.za"
            required
            <?= !empty($error) ? 'autofocus' : '' ?>>
        </div>

        <div class="form-group">
          <label for="password">Password *</label>
          <!-- Password intentionally cleared on sticky form redisplay -->
          <input type="password" id="password" name="password" class="form-control"
            placeholder="Your password"
            required>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
          <label style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;cursor:pointer;">
            <input type="checkbox" name="remember" style="accent-color:var(--gold);"> Remember me
          </label>
          <a href="#" style="font-size:.85rem;color:var(--gold-dark);">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="font-size:1rem;padding:.9rem;">
          Sign In
        </button>

        <div class="auth-divider">or</div>

        <a href="admin/login.php" class="btn btn-dark btn-block" style="justify-content:center;">
          🔐 Admin Login
        </a>

        <p style="text-align:center;font-size:.82rem;color:var(--mid-grey);margin-top:1.5rem;">
          New to Pastimes? <a href="register.php">Create a free account</a>
        </p>
      </form>
    </div>
  </div>
</div>

<script>
// HTML5 client-side validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
  let valid = true;
  const email = document.getElementById('email');
  const pass  = document.getElementById('password');

  if (!email.value.trim()) {
    email.style.borderColor = 'var(--error)';
    valid = false;
  } else { email.style.borderColor = ''; }

  if (!pass.value.trim()) {
    pass.style.borderColor = 'var(--error)';
    valid = false;
  } else { pass.style.borderColor = ''; }

  if (!valid) {
    e.preventDefault();
    const banner = document.createElement('div');
    banner.className = 'alert alert-error';
    banner.textContent = 'Please fill in all required fields.';
    const form = document.getElementById('loginForm');
    const existing = document.querySelector('.alert');
    if (!existing) form.prepend(banner);
  }
});
</script>
</body>
</html>
