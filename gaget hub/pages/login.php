<?php
require_once __DIR__ . '/../includes/functions.php';
if (isLoggedIn()) redirect(SITE_URL . '/pages/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isApi = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
    if ($isApi) {
        $_POST = json_decode(file_get_contents('php://input'), true) ?? [];
    }
    
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
        if ($isApi) jsonResponse(['success'=>false, 'message'=>$error], 400);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'blocked') {
                $error = 'Your account has been suspended. Contact support.';
                if ($isApi) jsonResponse(['success'=>false, 'message'=>$error], 403);
            } else {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                // Merge guest cart
                $sid = session_id();
                $pdo->prepare("UPDATE cart SET user_id=?, session_id=NULL WHERE session_id=?")->execute([$user['id'], $sid]);
                
                if ($isApi) jsonResponse(['success'=>true, 'message'=>'Logged in'], 200);
                
                $redirect = $_GET['redirect'] ?? (SITE_URL . '/pages/dashboard.php');
                redirect($redirect);
            }
        } else {
            $error = 'Invalid email or password.';
            http_response_code(401);
            if ($isApi) jsonResponse(['success'=>false, 'message'=>$error], 401);
        }
    }
}

$pageTitle = 'Login';
include __DIR__ . '/../includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<section class="tg-auth-section" style="background:linear-gradient(135deg,#f0f4f8 0%,#e2e8f0 100%)">
  <div class="container">
    <div class="tg-auth-card">
      <div class="text-center mb-4">
        <div style="width:56px;height:56px;background:linear-gradient(135deg,var(--tg-accent),#ff3d5a);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;margin-bottom:16px"><i class="fas fa-sign-in-alt"></i></div>
        <h1 class="tg-auth-title">Welcome Back!</h1>
        <p class="tg-auth-sub">Login to your TechGadget account</p>
      </div>

      <?php if ($error): ?><div class="alert-msg alert-error"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div><?php endif; ?>
      <?php if (isset($_GET['registered'])): ?><div class="alert-msg alert-success"><i class="fas fa-check-circle me-2"></i>Account created! Please login.</div><?php endif; ?>

      <form method="POST">
        <div class="tg-input-group tg-input-icon">
          <label>Email Address</label>
          <i class="fas fa-envelope icon"></i>
          <input type="email" name="email" class="tg-input" placeholder="you@example.com" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" required autofocus>
        </div>
        <div class="tg-input-group tg-input-icon">
          <label>Password</label>
          <i class="fas fa-lock icon"></i>
          <input type="password" name="password" id="loginPassword" class="tg-input" placeholder="Your password" required>
          <i class="fas fa-eye icon icon-right toggle-password" data-target="loginPassword"></i>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-20" style="margin-bottom:20px">
          <label class="tg-filter-check">
            <input type="checkbox" name="remember"> Remember me
          </label>
          <a href="<?php echo SITE_URL; ?>/pages/forgot-password.php" class="text-accent" style="font-size:.85rem">Forgot password?</a>
        </div>
        <button type="submit" class="tg-btn tg-btn-primary tg-btn-block tg-btn-lg">
          <i class="fas fa-sign-in-alt"></i> Login
        </button>
        <p class="text-center mt-3 mb-0" style="font-size:.85rem;color:var(--tg-text-muted)">
          Don't have an account? <a href="<?php echo SITE_URL; ?>/pages/signup.php" class="fw-600">Sign Up</a>
        </p>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
