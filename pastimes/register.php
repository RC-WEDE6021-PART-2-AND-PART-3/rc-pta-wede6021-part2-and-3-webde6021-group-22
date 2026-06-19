<?php
$pageTitle = 'Create Account';
$cssPath   = 'css/style.css';
require_once 'includes/session_check.php';
require_once 'includes/DBConn.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'dashboard.php'));
    exit;
}

$errors   = [];
$success  = '';
$sticky   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sticky = [
        'first_name'   => trim($_POST['first_name']   ?? ''),
        'last_name'    => trim($_POST['last_name']    ?? ''),
        'email'        => trim($_POST['email']        ?? ''),
        'phone_number' => trim($_POST['phone_number'] ?? ''),
        'role'         => trim($_POST['role']         ?? 'buyer'),
        'province'     => trim($_POST['province']     ?? ''),
        'city'         => trim($_POST['city']         ?? ''),
    ];
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validation
    if (empty($sticky['first_name'])) $errors[] = 'First name is required.';
    if (empty($sticky['last_name']))  $errors[] = 'Last name is required.';
    if (empty($sticky['email']) || !filter_var($sticky['email'], FILTER_VALIDATE_EMAIL))
        $errors[] = 'A valid email address is required.';
    if (empty($sticky['phone_number'])) $errors[] = 'Phone number is required.';
    if (strlen($password) < 8)         $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $password2)      $errors[] = 'Passwords do not match.';
    if (!in_array($sticky['role'], ['buyer','seller'])) $errors[] = 'Please choose a valid account type.';

    if (empty($errors)) {
        $conn = getDBConnection();
        // Check email uniqueness
        $stmt = $conn->prepare("SELECT user_id FROM tblUser WHERE email = ?");
        $stmt->bind_param('s', $sticky['email']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = 'An account with that email already exists. <a href="login.php">Sign in instead?</a>';
        } else {
            $hash = md5($password); // MD5 as required by POE spec
            $status = 'pending';    // All new users are pending until admin approves

            $ins = $conn->prepare("INSERT INTO tblUser 
                (first_name, last_name, email, phone_number, password_hash, role,
                 account_status, province, city) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param('sssssssss',
                $sticky['first_name'], $sticky['last_name'], $sticky['email'],
                $sticky['phone_number'], $hash, $sticky['role'], $status,
                $sticky['province'], $sticky['city']
            );

          if ($ins->execute()) {
                $newUserId = $conn->insert_id;
                // Create wallet for new user
                $w = $conn->prepare("INSERT INTO tblWallet (user_id, buyer_balance, seller_balance) VALUES (?,0,0)");
                $w->bind_param('i', $newUserId);
                $w->execute();
                $w->close();

                $success = true;
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
            $ins->close();
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
  <title><?= $pageTitle ?> — Pastimes</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php if ($success): ?>
<!-- ── Success State ─────────────────────────────────────────── -->
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--cream);">
  <div class="card card-body text-center" style="max-width:480px;width:100%;padding:3rem;">
    <div style="font-size:3.5rem;margin-bottom:1.5rem;">🎉</div>
    <h2 style="margin-bottom:1rem;">Welcome to Pastimes!</h2>
    <p style="margin-bottom:.75rem;">Your account has been created and is <strong>pending verification</strong> by our admin team.</p>
    <p style="margin-bottom:2rem;">You'll be able to log in once an administrator has approved your account. This usually happens within 24 hours.</p>
    <a href="login.php" class="btn btn-primary btn-block">Go to Login</a>
    <a href="index.php" class="btn btn-outline btn-block mt-2">Back to Homepage</a>
  </div>
</div>

<?php else: ?>
<!-- ── Registration Form ─────────────────────────────────────── -->
<div class="auth-page">

  <!-- Left Visual -->
  <div class="auth-visual">
    <div class="auth-visual-content">
      <a href="index.php" style="font-family:var(--font-display);font-size:1.8rem;color:var(--gold);display:block;margin-bottom:3rem;">Pastimes</a>
      <h2>Your wardrobe's<br><em>next chapter</em><br>starts here.</h2>
      <p style="margin-top:1.5rem;">Join thousands of South Africans buying and selling pre-owned fashion. Zero listing fees. Real community. Trusted transactions.</p>

      <div style="margin-top:3rem;display:flex;flex-direction:column;gap:1rem;">
        <?php
        $perks = ['✓ Free to join — no listing fees ever','✓ AI-powered listing tool saves you time','✓ Verified luxury items via Pastimes Store','✓ Dual wallet: spend or cash out anytime'];
        foreach ($perks as $p): ?>
        <div style="color:#888;font-size:.9rem;"><?= $p ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Right Form -->
  <div class="auth-form-side">
    <div class="auth-form-wrap">
      <h1>Create Account</h1>
      <p class="subtitle">Already have an account? <a href="login.php">Sign in</a></p>

      <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="register.php" novalidate id="registerForm">

        <div class="grid-2" style="gap:1rem;">
          <div class="form-group">
            <label for="first_name">First Name *</label>
            <input type="text" id="first_name" name="first_name" class="form-control"
              value="<?= htmlspecialchars($sticky['first_name'] ?? '') ?>"
              placeholder="Sipho" required>
          </div>
          <div class="form-group">
            <label for="last_name">Last Name *</label>
            <input type="text" id="last_name" name="last_name" class="form-control"
              value="<?= htmlspecialchars($sticky['last_name'] ?? '') ?>"
              placeholder="Dlamini" required>
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email Address *</label>
          <input type="email" id="email" name="email" class="form-control"
            value="<?= htmlspecialchars($sticky['email'] ?? '') ?>"
            placeholder="you@example.co.za" required>
        </div>

        <div class="form-group">
          <label for="phone_number">Phone Number *</label>
          <input type="tel" id="phone_number" name="phone_number" class="form-control"
            value="<?= htmlspecialchars($sticky['phone_number'] ?? '') ?>"
            placeholder="071 234 5678" required>
        </div>

        <div class="form-group">
          <label for="role">I want to *</label>
          <select id="role" name="role" class="form-control" required>
            <option value="">— Select account type —</option>
            <option value="buyer"  <?= ($sticky['role'] ?? '') === 'buyer'  ? 'selected' : '' ?>>Buy pre-owned fashion</option>
            <option value="seller" <?= ($sticky['role'] ?? '') === 'seller' ? 'selected' : '' ?>>Sell my pre-owned items</option>
          </select>
        </div>

        <div class="grid-2" style="gap:1rem;">
          <div class="form-group">
            <label for="province">Province</label>
            <select id="province" name="province" class="form-control">
              <option value="">— Select Province —</option>
              <?php
              $provinces = ['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape','Limpopo','Mpumalanga','North West','Free State','Northern Cape'];
              foreach ($provinces as $p): ?>
              <option value="<?= $p ?>" <?= ($sticky['province'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" name="city" class="form-control"
              value="<?= htmlspecialchars($sticky['city'] ?? '') ?>"
              placeholder="Johannesburg">
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password *</label>
          <input type="password" id="password" name="password" class="form-control"
            placeholder="At least 8 characters" required minlength="8">
          <div class="form-hint">Minimum 8 characters</div>
        </div>

        <div class="form-group">
          <label for="password2">Confirm Password *</label>
          <input type="password" id="password2" name="password2" class="form-control"
            placeholder="Repeat your password" required>
        </div>

        <div style="background:var(--cream);border:1px solid var(--light-grey);border-radius:var(--radius);padding:1rem;margin-bottom:1.5rem;font-size:.82rem;color:var(--mid-grey);">
          ⏳ <strong>Note:</strong> New accounts require admin verification before you can log in. This usually takes less than 24 hours.
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="font-size:1rem;padding:.9rem;">
          Create My Account
        </button>

        <p style="text-align:center;font-size:.8rem;color:var(--mid-grey);margin-top:1.25rem;">
          By registering you agree to our <a href="#">Terms &amp; Conditions</a> and <a href="#">Privacy Policy</a>.
        </p>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
// HTML5 client-side validation
document.getElementById('registerForm')?.addEventListener('submit', function(e) {
  const fields = ['first_name','last_name','email','phone_number','role','password','password2'];
  let valid = true;
  fields.forEach(id => {
    const el = document.getElementById(id);
    if (el && !el.value.trim()) {
      el.style.borderColor = 'var(--error)';
      valid = false;
    } else if (el) {
      el.style.borderColor = '';
    }
  });
  const pw  = document.getElementById('password').value;
  const pw2 = document.getElementById('password2').value;
  if (pw.length < 8) {
    document.getElementById('password').style.borderColor = 'var(--error)';
    valid = false;
  }
  if (pw !== pw2) {
    document.getElementById('password2').style.borderColor = 'var(--error)';
    valid = false;
  }
  if (!valid) e.preventDefault();
});
</script>
</body>
</html>
