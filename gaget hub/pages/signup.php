<?php
require_once __DIR__ . '/../includes/functions.php';
if (isLoggedIn()) redirect(SITE_URL . '/pages/dashboard.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isApi = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
    if ($isApi) {
        $_POST = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    $name     = sanitize($_POST['name'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $phone    = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    // Let api users skip confirm_password if they just send password
    $confirm  = $_POST['confirm_password'] ?? (isset($_POST['password']) ? $_POST['password'] : '');

    if (!$name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
        if ($isApi) jsonResponse(['success'=>false, 'message'=>$error], 400);
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
        if ($isApi) jsonResponse(['success'=>false, 'message'=>$error], 400);
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
        if ($isApi) jsonResponse(['success'=>false, 'message'=>$error], 400);
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
        if ($isApi) jsonResponse(['success'=>false, 'message'=>$error], 400);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered. <a href="login.php">Login instead?</a>';
            if ($isApi) jsonResponse(['success'=>false, 'message'=>'Email already registered.'], 409);
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?,?,?,?)")
                ->execute([$name, $email, $phone, $hash]);
            $userId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            
            if ($isApi) jsonResponse(['success'=>true, 'message'=>'Account created'], 201);
            
            redirect(SITE_URL . '/pages/dashboard.php?welcome=1');
        }
    }
}

$pageTitle = 'Create Account';
include __DIR__ . '/../includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<section class="tg-auth-section" style="background:linear-gradient(135deg,#f0f4f8 0%,#e2e8f0 100%)">
  <div class="container">
    <div class="tg-auth-card" style="max-width:520px">
      <div class="text-center mb-4">
        <div style="width:56px;height:56px;background:linear-gradient(135deg,var(--tg-accent),#ff3d5a);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;margin-bottom:16px"><i class="fas fa-user-plus"></i></div>
        <h1 class="tg-auth-title">Create Account</h1>
        <p class="tg-auth-sub">Join TechGadget Store and start shopping!</p>
      </div>

      <?php if ($error): ?><div class="alert-msg alert-error"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert-msg alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div><?php endif; ?>

      <form method="POST" id="signupForm">
        <div class="row g-3">
          <div class="col-12">
            <div class="tg-input-group tg-input-icon">
              <label>Full Name *</label>
              <i class="fas fa-user icon"></i>
              <input type="text" name="name" class="tg-input" placeholder="Your full name" value="<?php echo sanitize($_POST['name'] ?? ''); ?>" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="tg-input-group tg-input-icon">
              <label>Email Address *</label>
              <i class="fas fa-envelope icon"></i>
              <input type="email" name="email" class="tg-input" placeholder="you@example.com" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="tg-input-group tg-input-icon">
              <label>Phone Number</label>
              <i class="fas fa-phone icon"></i>
              <input type="tel" name="phone" class="tg-input" placeholder="+880 1700-000000" value="<?php echo sanitize($_POST['phone'] ?? ''); ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="tg-input-group tg-input-icon">
              <label>Password *</label>
              <i class="fas fa-lock icon"></i>
              <input type="password" name="password" id="password" class="tg-input" placeholder="Min. 6 characters" required>
              <i class="fas fa-eye icon icon-right toggle-password" data-target="password"></i>
            </div>
          </div>
          <div class="col-md-6">
            <div class="tg-input-group tg-input-icon">
              <label>Confirm Password *</label>
              <i class="fas fa-lock icon"></i>
              <input type="password" name="confirm_password" id="confirmPassword" class="tg-input" placeholder="Repeat password" required>
              <i class="fas fa-eye icon icon-right toggle-password" data-target="confirmPassword"></i>
            </div>
          </div>
        </div>
        <button type="submit" class="tg-btn tg-btn-primary tg-btn-block tg-btn-lg mt-2">
          <i class="fas fa-user-plus"></i> Create Account
        </button>
        <p class="text-center mt-3 mb-0" style="font-size:.85rem;color:var(--tg-text-muted)">
          Already have an account? <a href="<?php echo SITE_URL; ?>/pages/login.php" class="fw-600">Login</a>
        </p>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
