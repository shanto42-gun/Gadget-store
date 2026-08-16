<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$user = currentUser();
$pageTitle = 'Edit Profile';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $addr  = sanitize($_POST['address'] ?? '');
    $city  = sanitize($_POST['city'] ?? '');
    $newPw = $_POST['new_password'] ?? '';
    $confPw= $_POST['confirm_password'] ?? '';

    if (!$name) { $error = 'Name is required.'; }
    elseif ($newPw && strlen($newPw) < 6) { $error = 'Password must be at least 6 characters.'; }
    elseif ($newPw && $newPw !== $confPw) { $error = 'Passwords do not match.'; }
    else {
        $avatarPath = $user['avatar'];
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $up = uploadImage($_FILES['avatar'], 'avatars');
            if ($up['success']) $avatarPath = $up['path'];
        }
        if ($newPw) {
            $hash = password_hash($newPw, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET name=?,phone=?,address=?,city=?,avatar=?,password=?,updated_at=NOW() WHERE id=?")->execute([$name,$phone,$addr,$city,$avatarPath,$hash,$_SESSION['user_id']]);
        } else {
            $pdo->prepare("UPDATE users SET name=?,phone=?,address=?,city=?,avatar=?,updated_at=NOW() WHERE id=?")->execute([$name,$phone,$addr,$city,$avatarPath,$_SESSION['user_id']]);
        }
        $_SESSION['user_name'] = $name;
        $success = 'Profile updated successfully!';
        $user = currentUser();
    }
}
include __DIR__ . '/../includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<section class="tg-page-banner"><div class="container-xl"><h1>Edit Profile</h1><div class="tg-breadcrumb"><a href="<?php echo SITE_URL; ?>/">Home</a><span class="sep">/</span><a href="<?php echo SITE_URL; ?>/pages/dashboard.php">Dashboard</a><span class="sep">/</span><span>Profile</span></div></div></section>
<section class="tg-section-sm">
  <div class="container-xl">
    <div class="row g-4 justify-content-center">
      <div class="col-lg-7">
        <?php if ($success): ?><div class="alert-msg alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert-msg alert-error"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div><?php endif; ?>
        <form method="POST" enctype="multipart/form-data" style="background:#fff;border-radius:var(--tg-radius);padding:32px;box-shadow:var(--tg-shadow)">
          <!-- Avatar -->
          <div class="text-center mb-4">
            <div class="position-relative d-inline-block">
              <?php if ($user['avatar']): ?>
              <img src="<?php echo SITE_URL.'/'.$user['avatar']; ?>" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--tg-accent)">
              <?php else: ?>
              <div class="tg-avatar-placeholder mx-auto" style="width:90px;height:90px;font-size:2rem"><?php echo strtoupper(substr($user['name'],0,1)); ?></div>
              <?php endif; ?>
              <label for="avatarInput" style="position:absolute;bottom:0;right:0;width:28px;height:28px;background:var(--tg-accent);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;font-size:.7rem"><i class="fas fa-camera"></i></label>
              <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display:none">
            </div>
            <div style="font-size:.78rem;color:var(--tg-text-muted);margin-top:8px">Click camera to change avatar</div>
          </div>
          <div class="row g-3">
            <div class="col-md-6"><div class="tg-input-group"><label>Full Name *</label><input name="name" class="tg-input" value="<?php echo sanitize($user['name']); ?>" required></div></div>
            <div class="col-md-6"><div class="tg-input-group"><label>Email (read-only)</label><input type="email" class="tg-input" value="<?php echo sanitize($user['email']); ?>" disabled style="background:#f0f4f8;color:var(--tg-text-muted)"></div></div>
            <div class="col-md-6"><div class="tg-input-group"><label>Phone</label><input name="phone" class="tg-input" value="<?php echo sanitize($user['phone']); ?>"></div></div>
            <div class="col-md-6"><div class="tg-input-group"><label>City</label><input name="city" class="tg-input" value="<?php echo sanitize($user['city']); ?>"></div></div>
            <div class="col-12"><div class="tg-input-group"><label>Address</label><input name="address" class="tg-input" value="<?php echo sanitize($user['address']); ?>"></div></div>
            <div class="col-12"><hr><h6 class="fw-700 mb-3">Change Password (optional)</h6></div>
            <div class="col-md-6"><div class="tg-input-group tg-input-icon"><label>New Password</label><i class="fas fa-lock icon"></i><input type="password" name="new_password" id="newPw" class="tg-input" placeholder="Leave blank to keep current"><i class="fas fa-eye icon icon-right toggle-password" data-target="newPw"></i></div></div>
            <div class="col-md-6"><div class="tg-input-group tg-input-icon"><label>Confirm Password</label><i class="fas fa-lock icon"></i><input type="password" name="confirm_password" id="confPw" class="tg-input" placeholder="Repeat new password"><i class="fas fa-eye icon icon-right toggle-password" data-target="confPw"></i></div></div>
          </div>
          <div class="d-flex gap-3 mt-4">
            <button type="submit" class="tg-btn tg-btn-primary tg-btn-lg flex-grow-1"><i class="fas fa-save me-2"></i>Save Changes</button>
            <a href="<?php echo SITE_URL; ?>/pages/dashboard.php" class="tg-btn tg-btn-outline tg-btn-lg">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
