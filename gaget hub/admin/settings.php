<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/admin_functions.php';
requireAdminPanel();
$adminTitle = 'Site Settings';
$msg = ''; $msgType = '';

$settings = getSettings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = sanitize($_POST['site_name'] ?? 'TechGadget Store');
    $site_email = sanitize($_POST['site_email'] ?? '');
    $site_phone = sanitize($_POST['site_phone'] ?? '');
    $site_address = sanitize($_POST['site_address'] ?? '');
    $shipping_cost = (float)($_POST['shipping_cost'] ?? 60);
    $bkash_number = sanitize($_POST['bkash_number'] ?? '');
    $nagad_number = sanitize($_POST['nagad_number'] ?? '');
    
    // Prepare data for update
    $updateData = [
        'site_name' => $site_name,
        'site_email' => $site_email,
        'site_phone' => $site_phone,
        'site_address' => $site_address,
        'shipping_cost' => $shipping_cost,
        'bkash_number' => $bkash_number,
        'nagad_number' => $nagad_number
    ];

    // Handle Hero Background Upload
    if (isset($_FILES['site_hero_bg']) && $_FILES['site_hero_bg']['error'] === UPLOAD_ERR_OK) {
        $res = uploadImage($_FILES['site_hero_bg'], 'hero');
        if ($res['success']) {
            $updateData['site_hero_bg'] = $res['path'];
        } else {
            $msg = $res['message']; $msgType = 'danger';
        }
    }

    if ($msgType !== 'danger') {
        $setClause = [];
        $params = [];
        foreach ($updateData as $key => $val) {
            $setClause[] = "`$key` = ?";
            $params[] = $val;
        }
        $params[] = 1; // ID = 1
        
        $sql = "UPDATE settings SET " . implode(', ', $setClause) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $settings = getSettings();
        $msg = 'Settings saved!'; $msgType = 'success';
    }
}
include __DIR__ . '/includes/admin_header.php';
?>
<?php if ($msg): ?><div class="alert-msg alert-<?php echo $msgType; ?> mb-3"><?php echo $msg; ?></div><?php endif; ?>
<form method="POST" enctype="multipart/form-data" style="background:#fff;border-radius:var(--tg-radius);padding:28px;box-shadow:var(--tg-shadow)">
  <div class="row g-3">
    <div class="col-12"><h6 class="fw-700 mb-3"><i class="fas fa-store me-2 text-accent"></i>General Settings</h6></div>
    <div class="col-md-6"><div class="tg-input-group"><label>Site Name</label><input name="site_name" class="tg-input" value="<?php echo sanitize($settings['site_name']??'TechGadget Store'); ?>"></div></div>
    <div class="col-md-6"><div class="tg-input-group"><label>Support Email</label><input name="site_email" type="email" class="tg-input" value="<?php echo sanitize($settings['site_email']??''); ?>"></div></div>
    <div class="col-md-6"><div class="tg-input-group"><label>Support Phone</label><input name="site_phone" class="tg-input" value="<?php echo sanitize($settings['site_phone']??''); ?>"></div></div>
    <div class="col-md-6"><div class="tg-input-group"><label>Shipping Cost (৳)</label><input name="shipping_cost" type="number" step="0.01" class="tg-input" value="<?php echo $settings['shipping_cost']??60; ?>"><small class="text-muted">Set 0 to disable. Free above ৳2000.</small></div></div>
    <div class="col-12"><div class="tg-input-group"><label>Business Address</label><input name="site_address" class="tg-input" value="<?php echo sanitize($settings['site_address']??''); ?>"></div></div>
    
    <div class="col-12"><hr><h6 class="fw-700 mb-3"><i class="fas fa-image me-2 text-accent"></i>Homepage Appearance</h6></div>
    <div class="col-md-12">
      <div class="tg-input-group">
        <label>Hero Background Image</label>
        <div class="d-flex align-items-center gap-3">
          <?php 
          $heroBg = $settings['site_hero_bg'] ?? '';
          if ($heroBg): ?>
            <img src="<?php echo SITE_URL . '/' . $heroBg; ?>" alt="Hero BG" style="height: 60px; width: 100px; object-fit: cover; border-radius: 4px; border: 1px solid var(--tg-border)">
          <?php endif; ?>
          <input type="file" name="site_hero_bg" class="tg-input" accept="image/*">
        </div>
        <small class="text-muted">Recommended size: 1920x800px. Standard banner: `hero_banner_new.png`</small>
      </div>
    </div>

    <div class="col-12"><hr><h6 class="fw-700 mb-3"><i class="fas fa-mobile-alt me-2 text-accent"></i>Payment Settings</h6></div>
    <div class="col-md-6"><div class="tg-input-group"><label>bKash Number</label><input name="bkash_number" class="tg-input" value="<?php echo sanitize($settings['bkash_number']??''); ?>" placeholder="01700-000000"></div></div>
    <div class="col-md-6"><div class="tg-input-group"><label>Nagad Number</label><input name="nagad_number" class="tg-input" value="<?php echo sanitize($settings['nagad_number']??''); ?>" placeholder="01700-000001"></div></div>
  </div>
  <button type="submit" class="tg-btn tg-btn-primary tg-btn-lg mt-3"><i class="fas fa-save me-2"></i>Save Settings</button>
</form>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
