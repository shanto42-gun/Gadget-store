<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/admin_functions.php';
requireAdminPanel();
$adminTitle = 'Coupons';
$msg = ''; $msgType = '';

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = intval($_POST['id'] ?? 0);
    $code     = strtoupper(sanitize($_POST['code'] ?? ''));
    $type     = sanitize($_POST['type'] ?? 'percent');
    $value    = floatval($_POST['value'] ?? 0);
    $maxDisc  = $_POST['max_discount'] !== '' ? floatval($_POST['max_discount']) : null;
    $minOrder = floatval($_POST['min_order'] ?? 0);
    $limit    = $_POST['usage_limit'] !== '' ? intval($_POST['usage_limit']) : null;
    $expiry   = $_POST['expiry_date'] ?: null;
    $status   = intval($_POST['status'] ?? 1);

    if (!$code || !$value) { $msg = 'Code and value required.'; $msgType = 'error'; }
    else {
        if ($id) {
            $pdo->prepare("UPDATE coupons SET code=?,type=?,value=?,max_discount=?,min_order=?,usage_limit=?,expiry_date=?,status=? WHERE id=?")->execute([$code,$type,$value,$maxDisc,$minOrder,$limit,$expiry,$status,$id]);
            $msg = 'Coupon updated!'; $msgType = 'success';
        } else {
            $pdo->prepare("INSERT INTO coupons (code,type,value,max_discount,min_order,usage_limit,expiry_date,status) VALUES (?,?,?,?,?,?,?,?)")->execute([$code,$type,$value,$maxDisc,$minOrder,$limit,$expiry,$status]);
            $msg = 'Coupon created!'; $msgType = 'success';
        }
    }
}
// Delete/toggle
if (isset($_GET['delete'])) { $pdo->prepare("DELETE FROM coupons WHERE id=?")->execute([intval($_GET['delete'])]); header("Location: coupons.php"); exit; }
if (isset($_GET['toggle'])) { $pdo->prepare("UPDATE coupons SET status=1-status WHERE id=?")->execute([intval($_GET['toggle'])]); header("Location: coupons.php"); exit; }

$editCpn = null;
if (isset($_GET['edit'])) { $s = $pdo->prepare("SELECT * FROM coupons WHERE id=?"); $s->execute([intval($_GET['edit'])]); $editCpn = $s->fetch(); }
$coupons = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();
include __DIR__ . '/includes/admin_header.php';
?>
<?php if ($msg): ?><div class="alert-msg alert-<?php echo $msgType; ?> mb-3"><?php echo $msg; ?></div><?php endif; ?>
<div class="row g-4">
  <div class="col-lg-4">
    <div style="background:#fff;border-radius:var(--tg-radius);padding:24px;box-shadow:var(--tg-shadow)">
      <h6 class="fw-700 mb-3"><?php echo $editCpn ? 'Edit Coupon' : 'Create Coupon'; ?></h6>
      <form method="POST">
        <?php if ($editCpn): ?><input type="hidden" name="id" value="<?php echo $editCpn['id']; ?>"><?php endif; ?>
        <div class="tg-input-group"><label>Coupon Code *</label><input name="code" class="tg-input" value="<?php echo sanitize($editCpn['code']??''); ?>" placeholder="SAVE20" required></div>
        <div class="row g-2">
          <div class="col-6"><div class="tg-input-group"><label>Type</label><select name="type" class="tg-input"><option value="percent" <?php echo ($editCpn['type']??'')==='percent'?'selected':''; ?>>Percent %</option><option value="fixed" <?php echo ($editCpn['type']??'')==='fixed'?'selected':''; ?>>Fixed ৳</option></select></div></div>
          <div class="col-6"><div class="tg-input-group"><label>Value *</label><input name="value" type="number" step="0.01" class="tg-input" value="<?php echo $editCpn['value']??''; ?>" required></div></div>
          <div class="col-6"><div class="tg-input-group"><label>Max Discount</label><input name="max_discount" type="number" step="0.01" class="tg-input" value="<?php echo $editCpn['max_discount']??''; ?>" placeholder="Optional"></div></div>
          <div class="col-6"><div class="tg-input-group"><label>Min Order</label><input name="min_order" type="number" step="0.01" class="tg-input" value="<?php echo $editCpn['min_order']??0; ?>"></div></div>
          <div class="col-6"><div class="tg-input-group"><label>Usage Limit</label><input name="usage_limit" type="number" class="tg-input" value="<?php echo $editCpn['usage_limit']??''; ?>" placeholder="Unlimited"></div></div>
          <div class="col-6"><div class="tg-input-group"><label>Status</label><select name="status" class="tg-input"><option value="1" <?php echo ($editCpn['status']??1)==1?'selected':''; ?>>Active</option><option value="0" <?php echo ($editCpn['status']??1)==0?'selected':''; ?>>Inactive</option></select></div></div>
        </div>
        <div class="tg-input-group"><label>Expiry Date</label><input name="expiry_date" type="date" class="tg-input" value="<?php echo $editCpn['expiry_date']??''; ?>"></div>
        <button type="submit" class="tg-btn tg-btn-primary tg-btn-block"><i class="fas fa-save me-2"></i><?php echo $editCpn ? 'Update' : 'Create Coupon'; ?></button>
        <?php if ($editCpn): ?><a href="coupons.php" class="tg-btn tg-btn-outline tg-btn-block mt-2">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="tg-admin-table">
      <h6 class="fw-700 mb-3">All Coupons</h6>
      <table><thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Used</th><th>Expiry</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($coupons as $cpn): ?>
        <tr>
          <td class="fw-700 text-accent"><?php echo $cpn['code']; ?></td>
          <td><?php echo ucfirst($cpn['type']); ?></td>
          <td><?php echo $cpn['type']==='percent' ? $cpn['value'].'%' : formatPrice($cpn['value']); ?></td>
          <td><?php echo formatPrice($cpn['min_order']); ?></td>
          <td><?php echo $cpn['used_count']; ?>/<?php echo $cpn['usage_limit'] ?: '∞'; ?></td>
          <td><?php echo $cpn['expiry_date'] ? date('d M Y', strtotime($cpn['expiry_date'])) : '—'; ?></td>
          <td><span class="tg-status-badge <?php echo $cpn['status']?'badge-delivered':'badge-cancelled'; ?>"><?php echo $cpn['status']?'Active':'Inactive'; ?></span></td>
          <td><div class="d-flex gap-1">
            <a href="?edit=<?php echo $cpn['id']; ?>" class="admin-btn-action admin-btn-edit"><i class="fas fa-edit"></i></a>
            <a href="?toggle=<?php echo $cpn['id']; ?>" class="admin-btn-action <?php echo $cpn['status']?'admin-btn-delete':'admin-btn-edit'; ?>"><i class="fas fa-<?php echo $cpn['status']?'ban':'check'; ?>"></i></a>
            <a href="?delete=<?php echo $cpn['id']; ?>" onclick="return confirm('Delete?')" class="admin-btn-action admin-btn-delete"><i class="fas fa-trash"></i></a>
          </div></td>
        </tr>
        <?php endforeach; ?>
      </tbody></table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
