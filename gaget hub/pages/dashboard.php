<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$pageTitle = 'My Dashboard';
$user = currentUser();
$tab = sanitize($_GET['tab'] ?? 'overview');

$orderCount = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id=?"); $orderCount->execute([$_SESSION['user_id']]); $orderCount = $orderCount->fetchColumn();
$wishCount  = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id=?"); $wishCount->execute([$_SESSION['user_id']]); $wishCount = $wishCount->fetchColumn();
$reviewCount= $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id=?"); $reviewCount->execute([$_SESSION['user_id']]); $reviewCount = $reviewCount->fetchColumn();

$recentOrders = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$recentOrders->execute([$_SESSION['user_id']]); $recentOrders = $recentOrders->fetchAll();

// Wishlist
if ($tab === 'wishlist') {
  $wishItems = $pdo->prepare("SELECT p.*, w.id AS wid FROM wishlist w JOIN products p ON w.product_id=p.id WHERE w.user_id=? ORDER BY w.created_at DESC");
  $wishItems->execute([$_SESSION['user_id']]); $wishItems = $wishItems->fetchAll();
}

include __DIR__ . '/../includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<?php if (isset($_GET['welcome'])): ?><script>document.addEventListener('DOMContentLoaded',()=>setTimeout(()=>TG.toast('Welcome to TechGadget! 🎉','success'),500));</script><?php endif; ?>
<section class="tg-page-banner">
  <div class="container-xl"><h1>My Dashboard</h1>
    <div class="tg-breadcrumb"><a href="<?php echo SITE_URL; ?>/">Home</a><span class="sep">/</span><span>Dashboard</span></div>
  </div>
</section>
<section class="tg-section-sm">
  <div class="container-xl">
    <div class="row g-4">
      <!-- Sidebar -->
      <div class="col-lg-3">
        <div class="tg-sidebar mb-3 text-center p-4">
          <div class="tg-avatar-placeholder mx-auto mb-2" style="width:70px;height:70px;font-size:1.8rem"><?php echo strtoupper(substr($user['name'],0,1)); ?></div>
          <strong class="d-block"><?php echo sanitize($user['name']); ?></strong>
          <small class="text-muted"><?php echo sanitize($user['email']); ?></small>
        </div>
        <div class="tg-sidebar">
          <a href="?tab=overview" class="tg-sidebar-link <?php echo $tab==='overview'?'active':''; ?>"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
          <a href="<?php echo SITE_URL; ?>/pages/orders.php" class="tg-sidebar-link"><i class="fas fa-box"></i>My Orders</a>
          <a href="?tab=wishlist" class="tg-sidebar-link <?php echo $tab==='wishlist'?'active':''; ?>"><i class="fas fa-heart"></i>Wishlist</a>
          <a href="<?php echo SITE_URL; ?>/pages/profile.php" class="tg-sidebar-link"><i class="fas fa-user-edit"></i>Edit Profile</a>
          <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="tg-sidebar-link text-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </div>
      </div>
      <!-- Content -->
      <div class="col-lg-9">
        <?php if ($tab === 'overview'): ?>
        <!-- Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-4"><div class="tg-dash-stat tg-dash-stat-1"><div class="tg-dash-icon tg-dash-icon-1"><i class="fas fa-box"></i></div><div><h3><?php echo $orderCount; ?></h3><p>Total Orders</p></div></div></div>
          <div class="col-md-4"><div class="tg-dash-stat tg-dash-stat-3"><div class="tg-dash-icon tg-dash-icon-3"><i class="fas fa-heart"></i></div><div><h3><?php echo $wishCount; ?></h3><p>Wishlist Items</p></div></div></div>
          <div class="col-md-4"><div class="tg-dash-stat tg-dash-stat-4"><div class="tg-dash-icon tg-dash-icon-4"><i class="fas fa-star"></i></div><div><h3><?php echo $reviewCount; ?></h3><p>Reviews Written</p></div></div></div>
        </div>
        <!-- Recent Orders -->
        <div class="tg-admin-table">
          <h6 class="fw-700 mb-3">Recent Orders</h6>
          <?php if (empty($recentOrders)): ?>
          <div class="tg-empty-state" style="padding:30px"><div class="tg-empty-icon"><i class="fas fa-box-open"></i></div><h5>No orders yet</h5><a href="<?php echo SITE_URL; ?>/pages/products.php" class="tg-btn tg-btn-primary">Shop Now</a></div>
          <?php else: ?>
          <div class="table-responsive"><table class="table mb-0" style="font-size:.85rem">
            <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($recentOrders as $o): ?>
              <tr>
                <td class="fw-600 text-accent"><?php echo $o['order_number']; ?></td>
                <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                <td class="fw-700"><?php echo formatPrice($o['total']); ?></td>
                <td><span class="tg-status-badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                <td><a href="<?php echo SITE_URL; ?>/pages/order-detail.php?id=<?php echo $o['id']; ?>" class="tg-btn tg-btn-sm tg-btn-outline">View</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table></div>
          <?php endif; ?>
        </div>

        <?php elseif ($tab === 'wishlist'): ?>
        <h6 class="fw-700 mb-3">My Wishlist (<?php echo count($wishItems ?? []); ?>)</h6>
        <?php if (empty($wishItems)): ?>
        <div class="tg-empty-state"><div class="tg-empty-icon"><i class="fas fa-heart"></i></div><h5>Your wishlist is empty</h5><a href="<?php echo SITE_URL; ?>/pages/products.php" class="tg-btn tg-btn-primary">Discover Products</a></div>
        <?php else: ?>
        <div class="tg-product-grid grid-4">
          <?php foreach ($wishItems as $p):
            $img = $p['image'] ? SITE_URL.'/'.$p['image'] : SITE_URL.'/assets/images/no-image.png';
            $dispP = $p['discount_price'] ?: $p['price'];
          ?>
          <div class="tg-product-card">
            <div class="tg-product-img-wrap"><a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $p['slug']; ?>"><img src="<?php echo $img; ?>" alt="<?php echo sanitize($p['name']); ?>"></a></div>
            <div class="tg-product-body">
              <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $p['slug']; ?>" class="tg-product-name"><?php echo sanitize($p['name']); ?></a>
              <div class="tg-product-price"><span class="tg-price-current"><?php echo formatPrice($dispP); ?></span></div>
              <button class="tg-add-cart-btn add-cart-btn" data-id="<?php echo $p['id']; ?>"><i class="fas fa-cart-plus"></i> Add to Cart</button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
