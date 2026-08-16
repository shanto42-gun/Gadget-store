<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/admin_functions.php';
requireAdminPanel();
$adminTitle = 'Manage Orders';

$statusFilter = sanitize($_GET['status'] ?? '');
$view = intval($_GET['view'] ?? 0);

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id'] ?? 0);
    $newStatus = sanitize($_POST['status'] ?? '');
    $allowed = ['pending','confirmed','processing','shipped','delivered','cancelled'];
    if ($orderId && in_array($newStatus, $allowed)) {
        $pdo->prepare("UPDATE orders SET status=?, updated_at=NOW() WHERE id=?")->execute([$newStatus, $orderId]);
        TG_REDIRECT: header("Location: orders.php?view=$orderId"); exit;
    }
}

$where = '1'; $params = [];
if ($statusFilter) { $where = 'o.status=?'; $params[] = $statusFilter; }

$orders = $pdo->prepare("SELECT o.*, u.name AS uname FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE $where ORDER BY o.created_at DESC LIMIT 200");
$orders->execute($params); $orders = $orders->fetchAll();

// View single order
$viewOrder = null; $viewItems = [];
if ($view) {
    $vs = $pdo->prepare("SELECT * FROM orders WHERE id=?"); $vs->execute([$view]); $viewOrder = $vs->fetch();
    $vi = $pdo->prepare("SELECT * FROM order_items WHERE order_id=?"); $vi->execute([$view]); $viewItems = $vi->fetchAll();
}

include __DIR__ . '/includes/admin_header.php';
?>
<?php if ($viewOrder): ?>
<!-- Single Order View -->
<div class="d-flex align-items-center gap-3 mb-3">
  <a href="orders.php" class="tg-btn tg-btn-sm tg-btn-outline"><i class="fas fa-arrow-left me-2"></i>Back</a>
  <h6 class="fw-700 mb-0">Order <?php echo $viewOrder['order_number']; ?></h6>
  <span class="tg-status-badge badge-<?php echo $viewOrder['status']; ?> ms-2"><?php echo ucfirst($viewOrder['status']); ?></span>
</div>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="tg-admin-table mb-3">
      <h6 class="fw-700 mb-3">Order Items</h6>
      <table><thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead><tbody>
        <?php foreach ($viewItems as $item): ?>
        <tr><td><?php echo sanitize($item['product_name']); ?></td><td><?php echo formatPrice($item['price']); ?></td><td><?php echo $item['quantity']; ?></td><td class="fw-700"><?php echo formatPrice($item['subtotal']); ?></td></tr>
        <?php endforeach; ?>
      </tbody></table>
      <div class="tg-summary-row mt-3"><span>Subtotal</span><span><?php echo formatPrice($viewOrder['subtotal']); ?></span></div>
      <div class="tg-summary-row"><span>Shipping</span><span><?php echo $viewOrder['shipping_cost']==0?'Free':formatPrice($viewOrder['shipping_cost']); ?></span></div>
      <?php if ($viewOrder['discount']>0): ?><div class="tg-summary-row"><span class="text-success">Discount</span><span class="text-success">-<?php echo formatPrice($viewOrder['discount']); ?></span></div><?php endif; ?>
      <div class="tg-summary-row total"><span>Total</span><span><?php echo formatPrice($viewOrder['total']); ?></span></div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="tg-order-summary-card mb-3">
      <h6 class="fw-700 mb-3">Customer Info</h6>
      <p class="mb-1 fw-600"><?php echo sanitize($viewOrder['name']); ?></p>
      <p class="mb-1 text-muted" style="font-size:.85rem"><?php echo sanitize($viewOrder['phone']); ?></p>
      <p class="mb-1 text-muted" style="font-size:.85rem"><?php echo sanitize($viewOrder['email']); ?></p>
      <p class="mb-0 text-muted" style="font-size:.85rem"><?php echo sanitize($viewOrder['address']); ?>, <?php echo sanitize($viewOrder['city']); ?></p>
    </div>
    <div class="tg-order-summary-card">
      <h6 class="fw-700 mb-3">Update Status</h6>
      <form method="POST">
        <input type="hidden" name="update_status" value="1">
        <input type="hidden" name="order_id" value="<?php echo $viewOrder['id']; ?>">
        <select name="status" class="tg-input mb-2">
          <?php foreach (['pending','confirmed','processing','shipped','delivered','cancelled'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php echo $viewOrder['status']===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="tg-btn tg-btn-primary tg-btn-block"><i class="fas fa-check me-2"></i>Update Status</button>
      </form>
    </div>
  </div>
</div>
<?php else: ?>
<!-- Orders List -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div class="d-flex gap-2 flex-wrap">
    <?php foreach ([''=>'All','pending'=>'Pending','confirmed'=>'Confirmed','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled'] as $sv=>$sl): ?>
    <a href="?status=<?php echo $sv; ?>" class="tg-btn tg-btn-sm <?php echo $statusFilter===$sv?'tg-btn-primary':'tg-btn-outline'; ?>"><?php echo $sl; ?></a>
    <?php endforeach; ?>
  </div>
  <span style="font-size:.85rem;color:var(--tg-text-muted)"><?php echo count($orders); ?> orders</span>
</div>
<div class="tg-admin-table">
  <div class="table-responsive"><table>
    <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
      <tr>
        <td><a href="?view=<?php echo $o['id']; ?>" class="fw-600 text-accent"><?php echo $o['order_number']; ?></a></td>
        <td><div style="font-size:.85rem;font-weight:600"><?php echo sanitize($o['uname']?:$o['name']); ?></div><small class="text-muted"><?php echo sanitize($o['phone']); ?></small></td>
        <td class="fw-700"><?php echo formatPrice($o['total']); ?></td>
        <td><span class="text-uppercase" style="font-size:.75rem;font-weight:600"><?php echo $o['payment_method']; ?></span></td>
        <td><span class="tg-status-badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
        <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
        <td><a href="?view=<?php echo $o['id']; ?>" class="admin-btn-action admin-btn-view"><i class="fas fa-eye"></i> View</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<?php endif; ?>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
