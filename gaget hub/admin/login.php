<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/admin_functions.php';

if (isAdmin()) { redirect(SITE_URL . '/admin/'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $stmt  = $pdo->prepare("SELECT * FROM admins WHERE email=? AND status=1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        redirect(SITE_URL . '/admin/');
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login – TechGadget Store</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body style="padding-bottom:0;background:linear-gradient(135deg,#0d1b2a 0%,#1a3d6e 100%);min-height:100vh;display:flex;align-items:center;justify-content:center">
<div style="width:100%;max-width:420px;padding:20px">
  <div class="tg-auth-card">
    <div class="text-center mb-4">
      <div class="tg-brand-icon mx-auto mb-3" style="width:56px;height:56px;font-size:1.5rem"><i class="fas fa-shield-alt"></i></div>
      <h1 class="tg-auth-title" style="font-size:1.5rem">Admin Panel</h1>
      <p class="tg-auth-sub">TechGadget Store Management</p>
    </div>
    <?php if ($error): ?><div class="alert-msg alert-error"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
      <div class="tg-input-group tg-input-icon">
        <label>Email</label><i class="fas fa-envelope icon"></i>
        <input type="email" name="email" class="tg-input" placeholder="admin@techgadget.com" required autofocus value="<?php echo sanitize($_POST['email']??''); ?>">
      </div>
      <div class="tg-input-group tg-input-icon">
        <label>Password</label><i class="fas fa-lock icon"></i>
        <input type="password" name="password" id="adminPw" class="tg-input" placeholder="Password" required>
        <i class="fas fa-eye icon icon-right toggle-password" data-target="adminPw"></i>
      </div>
      <button type="submit" class="tg-btn tg-btn-primary tg-btn-block tg-btn-lg"><i class="fas fa-sign-in-alt me-2"></i>Login to Admin</button>
      <p class="text-center mt-3 mb-0" style="font-size:.82rem;color:var(--tg-text-muted)"><a href="<?php echo SITE_URL; ?>/" class="text-accent"><i class="fas fa-arrow-left me-1"></i>Back to Store</a></p>
    </form>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.umd.min.js"></script>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body></html>
