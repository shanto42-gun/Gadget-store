<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/admin_functions.php';
requireAdminPanel();
$adminTitle = 'Manage Users';

// Block/Unblock
if (isset($_GET['toggle'])) {
    $uid = intval($_GET['toggle']);
    $cur = $pdo->prepare("SELECT status FROM users WHERE id=?"); $cur->execute([$uid]); $cur = $cur->fetchColumn();
    $new = $cur === 'active' ? 'blocked' : 'active';
    $pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new, $uid]);
    header("Location: users.php"); exit;
}

$users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) AS order_count FROM users u ORDER BY u.created_at DESC")->fetchAll();
include __DIR__ . '/includes/admin_header.php';
?>
<?php if (isset($_GET['view'])):
$vid = intval($_GET['view']);
$vuser = $pdo->prepare("SELECT * FROM users WHERE id=?"); $vuser->execute([$vid]); $vuser = $vuser->fetch();
if (!$vuser) { echo "<div class='alert-msg alert-error'>User not found.</div>"; } else {
$vorders = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC"); $vorders->execute([$vid]); $vorders = $vorders->fetchAll();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
  <h5 class="mb-0 fw-700">User Profile</h5>
  <a href="users.php" class="tg-btn tg-btn-outline tg-btn-sm"><i class="fas fa-arrow-left me-2"></i>Back to Users</a>
</div>
<div class="row g-4">
  <div class="col-md-4">
    <div class="tg-admin-table text-center">
      <div class="tg-avatar-placeholder mx-auto mb-3" style="width:80px;height:80px;font-size:2.5rem"><?php echo strtoupper(substr($vuser['name'],0,1)); ?></div>
      <h6 class="fw-700 text-dark mb-1"><?php echo sanitize($vuser['name']); ?></h6>
      <p class="text-muted mb-3" style="font-size:.85rem"><?php echo sanitize($vuser['email']); ?></p>
      <div class="d-flex justify-content-center gap-2 mb-3">
        <span class="tg-status-badge badge-<?php echo $vuser['status']=='active'?'delivered':'cancelled'; ?>"><?php echo ucfirst($vuser['status']); ?></span>
      </div>
      <hr>
      <div class="text-start" style="font-size:.85rem">
        <div class="mb-2"><strong class="text-dark">Phone:</strong> <?php echo sanitize($vuser['phone']?:'N/A'); ?></div>
        <div class="mb-2"><strong class="text-dark">City:</strong> <?php echo sanitize($vuser['city']?:'N/A'); ?></div>
        <div class="mb-2"><strong class="text-dark">Address:</strong> <?php echo sanitize($vuser['address']?:'N/A'); ?></div>
        <div class="mb-2"><strong class="text-dark">Joined:</strong> <?php echo date('d M Y', strtotime($vuser['created_at'])); ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="tg-admin-table">
      <h6 class="fw-700 mb-3">Order History (<?php echo count($vorders); ?>)</h6>
      <?php if(empty($vorders)): ?>
      <p class="text-muted" style="font-size:.85rem">No orders found for this user.</p>
      <?php else: ?>
      <div class="table-responsive"><table>
        <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($vorders as $o): ?>
          <tr>
            <td class="fw-600"><?php echo $o['order_number']; ?></td>
            <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
            <td class="fw-700"><?php echo formatPrice($o['total']); ?></td>
            <td><span class="tg-status-badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
            <td><a href="orders.php?view=<?php echo $o['id']; ?>" class="admin-btn-action admin-btn-edit"><i class="fas fa-eye"></i>View</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php } else: ?>
<div class="tg-admin-table">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-700 mb-0">All Users (<?php echo count($users); ?>)</h6>
  </div>
  <div class="table-responsive"><table>
    <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?php echo $u['id']; ?></td>
        <td><div class="d-flex align-items-center gap-2"><div class="tg-avatar-placeholder" style="width:30px;height:30px;font-size:.75rem"><?php echo strtoupper(substr($u['name'],0,1)); ?></div><a href="users.php?view=<?php echo $u['id']; ?>" class="fw-600 text-dark" style="font-size:.85rem"><?php echo sanitize($u['name']); ?></a></div></td>
        <td style="font-size:.82rem"><?php echo sanitize($u['email']); ?></td>
        <td style="font-size:.82rem"><?php echo sanitize($u['phone']); ?></td>
        <td><span class="fw-700"><?php echo $u['order_count']; ?></span></td>
        <td><span class="tg-status-badge badge-<?php echo $u['status']=='active'?'delivered':'cancelled'; ?>"><?php echo ucfirst($u['status']); ?></span></td>
        <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
        <td><a href="users.php?toggle=<?php echo $u['id']; ?>" class="admin-btn-action <?php echo $u['status']=='active'?'admin-btn-delete':'admin-btn-edit'; ?>" onclick="return confirm('Toggle user status?')"><i class="fas fa-<?php echo $u['status']=='active'?'ban':'check'; ?>"></i><?php echo $u['status']=='active'?'Block':'Unblock'; ?></a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<?php endif; ?>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
