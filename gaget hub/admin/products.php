<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/admin_functions.php';
requireAdminPanel();
$adminTitle = 'Manage Products';

$msg = '';
// Delete product
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
    $msg = 'Product deleted.';
}

$search = sanitize($_GET['q'] ?? '');
$catFilter = intval($_GET['cat'] ?? 0);
$params = []; $where = ['1'];
if ($search) { $where[] = "(p.name LIKE ? OR p.brand LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catFilter) { $where[] = "p.category_id=?"; $params[] = $catFilter; }

$products = $pdo->prepare("SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE ".implode(' AND ',$where)." ORDER BY p.created_at DESC LIMIT 100");
$products->execute($params); $products = $products->fetchAll();
$cats = getCategories(false);

include __DIR__ . '/includes/admin_header.php';
?>
<?php if ($msg): ?><div class="alert-msg alert-success mb-3"><i class="fas fa-check-circle me-2"></i><?php echo $msg; ?></div><?php endif; ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <form class="d-flex gap-2 flex-wrap" method="GET">
    <input name="q" class="tg-input" style="width:240px;padding:8px 14px" placeholder="Search products..." value="<?php echo $search; ?>">
    <select name="cat" class="tg-input" style="width:180px;padding:8px 14px">
      <option value="">All Categories</option>
      <?php foreach ($cats as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo $catFilter==$c['id']?'selected':''; ?>><?php echo sanitize($c['name']); ?></option><?php endforeach; ?>
    </select>
    <button type="submit" class="tg-btn tg-btn-dark tg-btn-sm"><i class="fas fa-search"></i></button>
  </form>
  <a href="<?php echo SITE_URL; ?>/admin/add-product.php" class="tg-btn tg-btn-primary"><i class="fas fa-plus me-2"></i>Add Product</a>
</div>
<div class="tg-admin-table">
  <div class="table-responsive"><table>
    <thead><tr><th>Image</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Sold</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($products as $p):
        $img = $p['image'] ? SITE_URL.'/'.$p['image'] : SITE_URL.'/assets/images/no-image.png';
        $dispP = $p['discount_price'] ?: $p['price'];
      ?>
      <tr>
        <td><img src="<?php echo $img; ?>" class="img-thumbnail-admin"></td>
        <td>
          <div class="fw-600" style="font-size:.85rem"><?php echo sanitize($p['name']); ?></div>
          <small class="text-muted"><?php echo sanitize($p['brand']); ?></small>
        </td>
        <td><span class="tg-badge" style="background:var(--tg-bg);color:var(--tg-text-muted)"><?php echo sanitize($p['cat_name']); ?></span></td>
        <td>
          <div class="fw-700 text-accent" style="font-size:.85rem"><?php echo formatPrice($dispP); ?></div>
          <?php if ($p['discount_price']): ?><small class="text-muted text-decoration-line-through"><?php echo formatPrice($p['price']); ?></small><?php endif; ?>
        </td>
        <td>
          <?php if ($p['stock'] == 0): ?><span class="tg-badge tg-badge-discount">Out of Stock</span>
          <?php elseif ($p['stock'] <= 5): ?><span class="tg-badge" style="background:#fff3cd;color:#856404"><?php echo $p['stock']; ?> left</span>
          <?php else: ?><span class="fw-600"><?php echo $p['stock']; ?></span><?php endif; ?>
        </td>
        <td><span class="tg-status-badge <?php echo $p['status']==='active'?'badge-delivered':'badge-cancelled'; ?>"><?php echo ucfirst($p['status']); ?></span></td>
        <td><?php echo $p['sold_count']; ?></td>
        <td>
          <div class="d-flex gap-1">
            <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $p['slug']; ?>" target="_blank" class="admin-btn-action admin-btn-view"><i class="fas fa-eye"></i></a>
            <a href="add-product.php?edit=<?php echo $p['id']; ?>" class="admin-btn-action admin-btn-edit"><i class="fas fa-edit"></i></a>
            <a href="?delete=<?php echo $p['id']; ?>" class="admin-btn-action admin-btn-delete" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
