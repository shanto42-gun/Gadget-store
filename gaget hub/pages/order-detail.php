<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$orderId = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) redirect(SITE_URL . '/pages/orders.php');

$items = $pdo->prepare("SELECT oi.*, p.slug FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id=?");
$items->execute([$orderId]); $items = $items->fetchAll();

$statusOrder = ['pending'=>0,'confirmed'=>1,'processing'=>2,'shipped'=>3,'delivered'=>4,'cancelled'=>5];
$curIdx = $statusOrder[$order['status']] ?? 0;
$pageTitle = 'Order ' . $order['order_number'];
include __DIR__ . '/../includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<section class="tg-page-banner">
  <div class="container-xl"><h1>Order <?php echo $order['order_number']; ?></h1>
    <div class="tg-breadcrumb"><a href="<?php echo SITE_URL; ?>/">Home</a><span class="sep">/</span><a href="<?php echo SITE_URL; ?>/pages/orders.php">Orders</a><span class="sep">/</span><span><?php echo $order['order_number']; ?></span></div>
  </div>
</section>
<section class="tg-section-sm">
  <div class="container-xl">
    <div class="row g-4">
      <div class="col-lg-8">
        <!-- Status Timeline -->
        <div style="background:#fff;border-radius:var(--tg-radius);padding:24px;box-shadow:var(--tg-shadow);margin-bottom:20px">
          <h6 class="fw-700 mb-3">Order Status</h6>
          <?php if ($order['status'] !== 'cancelled'): ?>
          <div class="tg-timeline">
            <?php
            $steps = ['pending'=>['fas fa-clock','Pending'],'confirmed'=>['fas fa-check-circle','Confirmed'],'processing'=>['fas fa-cog','Processing'],'shipped'=>['fas fa-truck','Shipped'],'delivered'=>['fas fa-home','Delivered']];
            $stepKeys = array_keys($steps);
            foreach ($stepKeys as $i => $sk):
              $sClass = $curIdx > $statusOrder[$sk] ? 'done' : ($curIdx === $statusOrder[$sk] ? 'active' : '');
              $lineClass = $curIdx > $statusOrder[$sk] ? 'done' : '';
            ?>
            <?php if ($i > 0): ?><div class="tg-timeline-line <?php echo $lineClass; ?>"></div><?php endif; ?>
            <div class="tg-timeline-step <?php echo $sClass; ?>">
              <div class="tg-timeline-icon"><i class="<?php echo $steps[$sk][0]; ?>"></i></div>
              <div class="tg-timeline-label"><?php echo $steps[$sk][1]; ?></div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <div class="alert-msg alert-error"><i class="fas fa-times-circle me-2"></i>This order has been cancelled.</div>
          <?php endif; ?>
        </div>

        <!-- Items -->
        <div style="background:#fff;border-radius:var(--tg-radius);padding:24px;box-shadow:var(--tg-shadow)">
          <h6 class="fw-700 mb-3">Ordered Items</h6>
          <?php foreach ($items as $item):
            $img = $item['product_image'] ? SITE_URL.'/'.$item['product_image'] : SITE_URL.'/assets/images/no-image.png';
          ?>
          <div class="tg-cart-item">
            <img src="<?php echo $img; ?>" class="tg-cart-img">
            <div class="tg-cart-info flex-grow-1">
              <h6><?php echo sanitize($item['product_name']); ?></h6>
              <small>Qty: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['price']); ?></small>
            </div>
            <span class="fw-700 text-accent"><?php echo formatPrice($item['subtotal']); ?></span>
          </div>
          <?php endforeach; ?>
          <div class="tg-summary-row mt-2"><span>Subtotal</span><span><?php echo formatPrice($order['subtotal']); ?></span></div>
          <div class="tg-summary-row"><span>Shipping</span><span><?php echo $order['shipping_cost'] == 0 ? '<span class="text-success">Free</span>' : formatPrice($order['shipping_cost']); ?></span></div>
          <?php if ($order['discount'] > 0): ?>
          <div class="tg-summary-row"><span class="text-success">Discount (<?php echo $order['coupon_code']; ?>)</span><span class="text-success">-<?php echo formatPrice($order['discount']); ?></span></div>
          <?php endif; ?>
          <div class="tg-summary-row total"><span>Total Paid</span><span><?php echo formatPrice($order['total']); ?></span></div>
        </div>
      </div>
      <!-- Order Info -->
      <div class="col-lg-4">
        <div class="tg-order-summary-card mb-3">
          <h6 class="fw-700 mb-3">Order Details</h6>
          <div class="tg-summary-row"><span>Order #</span><span class="fw-700 text-accent"><?php echo $order['order_number']; ?></span></div>
          <div class="tg-summary-row"><span>Date</span><span><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></span></div>
          <div class="tg-summary-row"><span>Payment</span><span class="text-uppercase fw-600"><?php echo $order['payment_method']; ?></span></div>
          <div class="tg-summary-row"><span>Payment Status</span><span class="tg-status-badge badge-<?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span></div>
        </div>
        <div class="tg-order-summary-card mb-3">
          <h6 class="fw-700 mb-3">Shipping To</h6>
          <p class="mb-1 fw-600"><?php echo sanitize($order['name']); ?></p>
          <p class="mb-1 text-muted" style="font-size:.85rem"><i class="fas fa-phone me-1"></i><?php echo sanitize($order['phone']); ?></p>
          <p class="mb-0 text-muted" style="font-size:.85rem"><i class="fas fa-map-marker-alt me-1"></i><?php echo sanitize($order['address']); ?>, <?php echo sanitize($order['city']); ?></p>
          <?php if ($order['notes']): ?><p class="mt-2 text-muted" style="font-size:.82rem"><i class="fas fa-sticky-note me-1"></i><?php echo sanitize($order['notes']); ?></p><?php endif; ?>
        </div>
        <?php if ($order['status'] === 'pending'): ?>
        <button class="tg-btn tg-btn-block" style="background:var(--tg-bg);border:1.5px solid var(--tg-danger);color:var(--tg-danger)" onclick="cancelOrder(<?php echo $order['id']; ?>)"><i class="fas fa-times me-2"></i>Cancel Order</button>
        <?php endif; ?>
        <a href="<?php echo SITE_URL; ?>/pages/orders.php" class="tg-btn tg-btn-outline tg-btn-block mt-2"><i class="fas fa-arrow-left me-2"></i>Back to Orders</a>
      </div>
    </div>
  </div>
</section>
<script>
async function cancelOrder(id) {
  if (!confirm('Cancel this order?')) return;
  const res = await TG.post('<?php echo SITE_URL; ?>/api/orders/cancel_order.php', {order_id: id});
  if (res.success) { TG.toast('Order cancelled.', 'info'); setTimeout(() => location.reload(), 1500); }
  else TG.toast(res.message, 'error');
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
