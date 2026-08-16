<?php $adminPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($adminTitle) ? sanitize($adminTitle).' | ' : ''; ?>Admin – TechGadget Store</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
<link href="<?php echo SITE_URL; ?>/admin/assets/admin.css" rel="stylesheet">
</head>
<body style="padding-bottom:0">
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<div class="tg-admin-wrapper">
<!-- ── Sidebar ── -->
<aside class="tg-admin-sidebar">
  <div class="tg-admin-brand">
    <a href="<?php echo SITE_URL; ?>/admin/" class="tg-brand text-decoration-none">
      <div class="tg-brand-icon"><i class="fas fa-microchip"></i></div>
      <div class="tg-brand-text" style="font-size:1rem;color:#fff">Tech<span>Gadget</span><br><small style="font-size:.65rem;opacity:.6;font-weight:400">Admin Panel</small></div>
    </a>
  </div>
  <nav class="tg-admin-nav">
    <div class="tg-admin-nav-section">Main</div>
    <a href="<?php echo SITE_URL; ?>/admin/" class="tg-admin-nav-link <?php echo $adminPage==='index.php'?'active':''; ?>"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
    <div class="tg-admin-nav-section">Catalog</div>
    <a href="<?php echo SITE_URL; ?>/admin/products.php" class="tg-admin-nav-link <?php echo in_array($adminPage,['products.php','add-product.php'])?'active':''; ?>"><i class="fas fa-box"></i>Products</a>
    <a href="<?php echo SITE_URL; ?>/admin/categories.php" class="tg-admin-nav-link <?php echo $adminPage==='categories.php'?'active':''; ?>"><i class="fas fa-th-large"></i>Categories</a>
    <div class="tg-admin-nav-section">Sales</div>
    <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="tg-admin-nav-link <?php echo $adminPage==='orders.php'?'active':''; ?>"><i class="fas fa-shopping-bag"></i>Orders</a>
    <a href="<?php echo SITE_URL; ?>/admin/coupons.php" class="tg-admin-nav-link <?php echo $adminPage==='coupons.php'?'active':''; ?>"><i class="fas fa-ticket-alt"></i>Coupons</a>
    <div class="tg-admin-nav-section">Users</div>
    <a href="<?php echo SITE_URL; ?>/admin/users.php" class="tg-admin-nav-link <?php echo $adminPage==='users.php'?'active':''; ?>"><i class="fas fa-users"></i>Users</a>
    <div class="tg-admin-nav-section">Config</div>
    <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="tg-admin-nav-link <?php echo $adminPage==='settings.php'?'active':''; ?>"><i class="fas fa-cog"></i>Settings</a>
    <a href="<?php echo SITE_URL; ?>/" target="_blank" class="tg-admin-nav-link"><i class="fas fa-external-link-alt"></i>View Store</a>
    <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="tg-admin-nav-link" style="color:rgba(255,80,80,.8)"><i class="fas fa-sign-out-alt"></i>Logout</a>
  </nav>
</aside>
<!-- ── Content ── -->
<div class="tg-admin-content">
  <!-- Topbar -->
  <div class="tg-admin-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="d-lg-none btn btn-sm" style="border:1px solid var(--tg-border);background:#fff"><i class="fas fa-bars"></i></button>
      <h6 class="fw-700 mb-0"><?php echo $adminTitle ?? 'Dashboard'; ?></h6>
    </div>
    <div class="d-flex align-items-center gap-3">
      <?php $admin = currentAdmin(); ?>
      <div class="d-flex align-items-center gap-2">
        <div class="tg-avatar-placeholder" style="width:34px;height:34px;font-size:.85rem"><?php echo strtoupper(substr($admin['name']??'A',0,1)); ?></div>
        <span style="font-size:.85rem;font-weight:600"><?php echo sanitize($admin['name']??'Admin'); ?></span>
      </div>
      <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="tg-btn tg-btn-sm" style="background:var(--tg-bg);color:var(--tg-danger);border:1px solid var(--tg-danger)"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
    </div>
  </div>
<!-- Content starts -->
