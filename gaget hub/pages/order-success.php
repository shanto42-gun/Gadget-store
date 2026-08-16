<?php
require_once __DIR__ . '/../includes/functions.php';
$orderNumber = sanitize($_GET['order'] ?? '');
$order = null;
if ($orderNumber) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]); $order = $stmt->fetch();
}
$pageTitle = 'Order Placed!';
include __DIR__ . '/../includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<section class="tg-section" style="background:linear-gradient(135deg,#f0f4f8,#e2e8f0)">
  <div class="container">
    <div style="max-width:600px;margin:auto;background:#fff;border-radius:var(--tg-radius-lg);padding:50px 40px;box-shadow:var(--tg-shadow-lg);text-align:center">
      <div style="width:80px;height:80px;background:linear-gradient(135deg,var(--tg-success),#00a065);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;margin-bottom:24px;animation:bounceIn .5s ease">
        <i class="fas fa-check"></i>
      </div>
      <h2 class="fw-800 mb-2" style="color:var(--tg-primary)">Order Placed Successfully!</h2>
      <p style="color:var(--tg-text-muted);margin-bottom:28px">
        Thank you for your order. We'll confirm it shortly and keep you updated.
      </p>
      <?php if ($order): ?>
      <div style="background:var(--tg-bg);border-radius:var(--tg-radius);padding:20px;margin-bottom:28px">
        <div class="row g-3 text-start">
          <div class="col-6"><small class="text-muted d-block">Order Number</small><strong class="text-accent"><?php echo $order['order_number']; ?></strong></div>
          <div class="col-6"><small class="text-muted d-block">Payment Method</small><strong><?php echo strtoupper($order['payment_method']); ?></strong></div>
          <div class="col-6"><small class="text-muted d-block">Order Total</small><strong><?php echo formatPrice($order['total']); ?></strong></div>
          <div class="col-6"><small class="text-muted d-block">Status</small><span class="tg-status-badge badge-pending">Pending</span></div>
        </div>
      </div>
      <div class="tg-timeline mb-4">
        <?php
        $steps = [
          ['icon'=>'fas fa-clock','label'=>'Pending','class'=>'active'],
          ['icon'=>'fas fa-check-circle','label'=>'Confirmed','class'=>''],
          ['icon'=>'fas fa-cog','label'=>'Processing','class'=>''],
          ['icon'=>'fas fa-truck','label'=>'Shipped','class'=>''],
          ['icon'=>'fas fa-home','label'=>'Delivered','class'=>''],
        ];
        foreach ($steps as $i => $step):
        ?>
        <?php if ($i > 0): ?><div class="tg-timeline-line"></div><?php endif; ?>
        <div class="tg-timeline-step <?php echo $step['class']; ?>">
          <div class="tg-timeline-icon"><i class="<?php echo $step['icon']; ?>"></i></div>
          <div class="tg-timeline-label"><?php echo $step['label']; ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="<?php echo SITE_URL; ?>/pages/orders.php" class="tg-btn tg-btn-dark"><i class="fas fa-box me-2"></i>Track Orders</a>
        <a href="<?php echo SITE_URL; ?>/" class="tg-btn tg-btn-outline"><i class="fas fa-home me-2"></i>Back to Home</a>
      </div>
    </div>
  </div>
</section>
<style>@keyframes bounceIn{0%{transform:scale(.3);opacity:0}50%{transform:scale(1.05)}70%{transform:scale(.9)}100%{transform:scale(1);opacity:1}}</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
