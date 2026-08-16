<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/admin_functions.php';
requireAdminPanel();

$adminTitle = 'Dashboard';
$stats = getAdminStats();

// Chart data: orders per day (last 14 days)
$chartData = $pdo->query("SELECT DATE(created_at) AS day, COUNT(*) AS cnt, COALESCE(SUM(total),0) AS rev FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND status != 'cancelled' GROUP BY DATE(created_at) ORDER BY day ASC")->fetchAll();
$chartLabels = []; $chartOrders = []; $chartRevenue = [];
foreach ($chartData as $r) { $chartLabels[] = date('d M', strtotime($r['day'])); $chartOrders[] = $r['cnt']; $chartRevenue[] = $r['rev']; }

// Recent orders
$recentOrders = $pdo->query("SELECT o.*,u.name AS uname FROM orders o LEFT JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 8")->fetchAll();
// Top products
$topProducts = $pdo->query("SELECT * FROM products ORDER BY sold_count DESC LIMIT 5")->fetchAll();

include __DIR__ . '/includes/admin_header.php';
$adminExtraScripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
?>

<div class="row g-4 mb-4">
  <div class="col-md-3 col-6"><div class="tg-admin-stat tg-admin-stat-1"><p>Today's Orders</p><h3><?php echo $stats['orders_today']; ?></h3><small class="stat-trend up"><i class="fas fa-arrow-up me-1"></i>Today</small></div></div>
  <div class="col-md-3 col-6"><div class="tg-admin-stat tg-admin-stat-2"><p>Today's Revenue</p><h3><?php echo formatPrice($stats['revenue_today']); ?></h3><small class="stat-trend up"><i class="fas fa-arrow-up me-1"></i>Today</small></div></div>
  <div class="col-md-3 col-6"><div class="tg-admin-stat tg-admin-stat-3"><p>Total Products</p><h3><?php echo $stats['total_products']; ?></h3><?php if ($stats['low_stock']): ?><small class="stat-trend down"><?php echo $stats['low_stock']; ?> low stock</small><?php endif; ?></div></div>
  <div class="col-md-3 col-6"><div class="tg-admin-stat tg-admin-stat-4"><p>Total Users</p><h3><?php echo $stats['total_users']; ?></h3><small class="stat-trend up">Registered</small></div></div>
</div>

<div class="row g-4 mb-4">
  <!-- Sales Chart -->
  <div class="col-lg-8">
    <div class="admin-chart-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-700 mb-0">Sales – Last 14 Days</h6>
        <span class="tg-badge" style="background:rgba(255,107,53,.1);color:var(--tg-accent)">Revenue: <?php echo formatPrice($stats['revenue_month']); ?> this month</span>
      </div>
      <canvas id="salesChart" height="100"></canvas>
    </div>
  </div>
  <!-- Quick Stats -->
  <div class="col-lg-4">
    <div class="admin-chart-card h-100">
      <h6 class="fw-700 mb-3">Order Status Breakdown</h6>
      <canvas id="statusChart" height="200"></canvas>
      <?php
      $statusCounts = $pdo->query("SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status")->fetchAll();
      $scLabels = array_column($statusCounts,'status');
      $scData   = array_column($statusCounts,'cnt');
      ?>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Recent Orders -->
  <div class="col-lg-7">
    <div class="tg-admin-table">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-700 mb-0">Recent Orders</h6>
        <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="tg-btn tg-btn-sm tg-btn-outline">View All</a>
      </div>
      <div class="table-responsive"><table>
        <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($recentOrders as $o): ?>
          <tr>
            <td><a href="<?php echo SITE_URL; ?>/admin/orders.php?view=<?php echo $o['id']; ?>" class="fw-600 text-accent"><?php echo $o['order_number']; ?></a></td>
            <td><?php echo sanitize($o['uname'] ?: $o['name']); ?></td>
            <td class="fw-700"><?php echo formatPrice($o['total']); ?></td>
            <td><span class="tg-status-badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
            <td><?php echo date('d M', strtotime($o['created_at'])); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
  </div>
  <!-- Top Products -->
  <div class="col-lg-5">
    <div class="tg-admin-table">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-700 mb-0">Top Products</h6>
        <a href="<?php echo SITE_URL; ?>/admin/products.php" class="tg-btn tg-btn-sm tg-btn-outline">Manage</a>
      </div>
      <?php foreach ($topProducts as $p):
        $img = $p['image'] ? SITE_URL.'/'.$p['image'] : SITE_URL.'/assets/images/no-image.png';
      ?>
      <div class="d-flex align-items-center gap-3 py-2 border-bottom">
        <img src="<?php echo $img; ?>" class="img-thumbnail-admin">
        <div class="flex-grow-1">
          <div style="font-size:.82rem;font-weight:600"><?php echo sanitize($p['name']); ?></div>
          <small class="text-muted"><?php echo $p['sold_count']; ?> sold · Stock: <?php echo $p['stock']; ?></small>
        </div>
        <span class="fw-700 text-accent" style="font-size:.85rem"><?php echo formatPrice($p['discount_price']??$p['price']); ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
const chartLabels = <?php echo json_encode($chartLabels); ?>;
const chartOrders = <?php echo json_encode($chartOrders); ?>;
const chartRevenue = <?php echo json_encode($chartRevenue); ?>;
const scLabels = <?php echo json_encode($scLabels); ?>;
const scData = <?php echo json_encode($scData); ?>;

document.addEventListener('DOMContentLoaded', () => {
  new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
      labels: chartLabels,
      datasets: [
        { label: 'Revenue (৳)', data: chartRevenue, borderColor: '#ff6b35', backgroundColor: 'rgba(255,107,53,.1)', tension: 0.4, fill: true, yAxisID: 'y1' },
        { label: 'Orders', data: chartOrders, borderColor: '#00d4ff', backgroundColor: 'rgba(0,212,255,.08)', tension: 0.4, fill: false, yAxisID: 'y' }
      ]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true, position: 'left', ticks: { precision: 0 } }, y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } } } }
  });
  new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
      labels: scLabels.map(l => l.charAt(0).toUpperCase()+l.slice(1)),
      datasets: [{ data: scData, backgroundColor: ['#ffb800','#00c576','#8b5cf6','#00d4ff','#0d1b2a','#ff3d5a'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
  });
});
</script>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
