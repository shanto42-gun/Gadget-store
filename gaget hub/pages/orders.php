<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$pageTitle = 'My Orders';
$tab = sanitize($_GET['status'] ?? '');
$params = [$_SESSION['user_id']];
$where = 'user_id = ?';
if ($tab) { $where .= ' AND status = ?'; $params[] = $tab; }
$orders = $pdo->prepare("SELECT * FROM orders WHERE $where ORDER BY created_at DESC");
$orders->execute($params); $orders = $orders->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<section class="tg-page-banner">
  <div class="container-xl"><h1>My Orders</h1>
    <div class="tg-breadcrumb"><a href="<?php echo SITE_URL; ?>/">Home</a><span class="sep">/</span><span>Orders</span></div>
  </div>
</section>
<section class="tg-section-sm">
  <div class="container-xl">
    <div class="row g-4">
      <!-- Sidebar -->
      <div class="col-lg-3 d-none d-lg-block">
        <?php $user = currentUser(); ?>
        <div class="tg-sidebar mb-3 text-center p-4">
          <div class="tg-avatar-placeholder mx-auto mb-2" style="width:60px;height:60px;font-size:1.5rem"><?php echo strtoupper(substr($user['name'],0,1)); ?></div>
          <strong><?php echo sanitize($user['name']); ?></strong>
          <small class="text-muted d-block"><?php echo sanitize($user['email']); ?></small>
        </div>
        <div class="tg-sidebar">
          <a href="<?php echo SITE_URL; ?>/pages/dashboard.php" class="tg-sidebar-link"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
          <a href="<?php echo SITE_URL; ?>/pages/orders.php" class="tg-sidebar-link active"><i class="fas fa-box"></i>My Orders</a>
          <a href="<?php echo SITE_URL; ?>/pages/dashboard.php?tab=wishlist" class="tg-sidebar-link"><i class="fas fa-heart"></i>Wishlist</a>
          <a href="<?php echo SITE_URL; ?>/pages/profile.php" class="tg-sidebar-link"><i class="fas fa-user-edit"></i>Edit Profile</a>
          <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="tg-sidebar-link text-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </div>
      </div>
      <!-- Orders -->
      <div class="col-lg-9">
        <div class="tg-admin-table">
          <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <h6 class="fw-700 mb-0">Order History</h6>
            <div class="d-flex gap-2 flex-wrap">
              <?php $statuses = [''=>'All','pending'=>'Pending','confirmed'=>'Confirmed','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled']; ?>
              <?php foreach ($statuses as $sv => $sl): ?>
              <a href="?status=<?php echo $sv; ?>" class="tg-btn tg-btn-sm <?php echo $tab===$sv?'tg-btn-primary':'tg-btn-outline'; ?>"><?php echo $sl; ?></a>
              <?php endforeach; ?>
            </div>
          </div>
          <?php if (empty($orders)): ?>
          <div class="tg-empty-state"><div class="tg-empty-icon"><i class="fas fa-box-open"></i></div><h5>No orders yet</h5><p>You haven't placed any orders.</p><a href="<?php echo SITE_URL; ?>/pages/products.php" class="tg-btn tg-btn-primary">Start Shopping</a></div>
          <?php else: ?>
          <div class="table-responsive">
            <table style="width:100%;border-collapse:collapse;font-size:.88rem">
              <thead><tr><th style="background:var(--tg-bg);padding:10px 14px;font-weight:700;border-bottom:2px solid var(--tg-border)">Order #</th><th style="background:var(--tg-bg);padding:10px 14px;font-weight:700;border-bottom:2px solid var(--tg-border)">Date</th><th style="background:var(--tg-bg);padding:10px 14px;font-weight:700;border-bottom:2px solid var(--tg-border)">Total</th><th style="background:var(--tg-bg);padding:10px 14px;font-weight:700;border-bottom:2px solid var(--tg-border)">Payment</th><th style="background:var(--tg-bg);padding:10px 14px;font-weight:700;border-bottom:2px solid var(--tg-border)">Status</th><th style="background:var(--tg-bg);padding:10px 14px;font-weight:700;border-bottom:2px solid var(--tg-border)">Action</th></tr></thead>
              <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                  <td style="padding:12px 14px;border-bottom:1px solid var(--tg-border)"><a href="<?php echo SITE_URL; ?>/pages/order-detail.php?id=<?php echo $o['id']; ?>" class="fw-600 text-accent"><?php echo $o['order_number']; ?></a></td>
                  <td style="padding:12px 14px;border-bottom:1px solid var(--tg-border)"><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                  <td style="padding:12px 14px;border-bottom:1px solid var(--tg-border)"><strong><?php echo formatPrice($o['total']); ?></strong></td>
                  <td style="padding:12px 14px;border-bottom:1px solid var(--tg-border)"><span class="text-uppercase" style="font-size:.78rem"><?php echo $o['payment_method']; ?></span></td>
                  <td style="padding:12px 14px;border-bottom:1px solid var(--tg-border)"><span class="tg-status-badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                  <td style="padding:12px 14px;border-bottom:1px solid var(--tg-border)"><a href="<?php echo SITE_URL; ?>/pages/order-detail.php?id=<?php echo $o['id']; ?>" class="tg-btn tg-btn-sm tg-btn-outline">View</a></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
