<?php
require_once __DIR__ . '/../includes/functions.php';
if (isLoggedIn()) redirect(SITE_URL . '/pages/dashboard.php');

$msg = ''; $msgType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $stmt  = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user  = $stmt->fetch();
    if ($user) {
        $token  = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $pdo->prepare("UPDATE users SET reset_token=?, reset_token_expiry=? WHERE id=?")->execute([$token, $expiry, $user['id']]);
        // In a real app send email; for now show reset link
        $resetLink = SITE_URL . '/pages/reset-password.php?token=' . $token;
        $msg = "Password reset link generated! (In production this would be emailed.) <a href='$resetLink'>Click here to reset</a>";
        $msgType = 'success';
    } else {
        $msg = 'If that email exists, a reset link has been sent.'; $msgType = 'success';
    }
}
$pageTitle = 'Forgot Password';
include __DIR__ . '/../includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<section class="tg-auth-section" style="background:linear-gradient(135deg,#f0f4f8 0%,#e2e8f0 100%)">
  <div class="container">
    <div class="tg-auth-card">
      <div class="text-center mb-4">
        <div style="width:56px;height:56px;background:linear-gradient(135deg,var(--tg-accent),#ff3d5a);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;margin-bottom:16px"><i class="fas fa-key"></i></div>
        <h1 class="tg-auth-title">Forgot Password</h1>
        <p class="tg-auth-sub">Enter your email and we'll send a reset link</p>
      </div>
      <?php if ($msg): ?><div class="alert-msg alert-<?php echo $msgType; ?>"><?php echo $msg; ?></div><?php endif; ?>
      <form method="POST">
        <div class="tg-input-group tg-input-icon">
          <label>Email Address</label>
          <i class="fas fa-envelope icon"></i>
          <input type="email" name="email" class="tg-input" placeholder="you@example.com" required>
        </div>
        <button type="submit" class="tg-btn tg-btn-primary tg-btn-block tg-btn-lg"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
        <p class="text-center mt-3 mb-0" style="font-size:.85rem"><a href="<?php echo SITE_URL; ?>/pages/login.php" class="text-accent"><i class="fas fa-arrow-left me-1"></i>Back to Login</a></p>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
