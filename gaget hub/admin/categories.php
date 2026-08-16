<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/admin_functions.php';
requireAdminPanel();
$adminTitle = 'Categories';
$msg = ''; $msgType = '';

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = intval($_POST['id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $icon = sanitize($_POST['icon'] ?? 'fas fa-box');
    $desc = sanitize($_POST['description'] ?? '');
    if (!$name) { $msg = 'Name is required.'; $msgType = 'error'; }
    else {
        if ($id) {
            $pdo->prepare("UPDATE categories SET name=?,icon=?,description=? WHERE id=?")->execute([$name,$icon,$desc,$id]);
            $msg = 'Category updated!'; $msgType = 'success';
        } else {
            $slugC = slug($name);
            $pdo->prepare("INSERT INTO categories (name,slug,icon,description) VALUES (?,?,?,?)")->execute([$name,$slugC,$icon,$desc]);
            $msg = 'Category added!'; $msgType = 'success';
        }
    }
}
// Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([intval($_GET['delete'])]);
    header("Location: categories.php"); exit;
}
$editCat = null;
if (isset($_GET['edit'])) { $s = $pdo->prepare("SELECT * FROM categories WHERE id=?"); $s->execute([intval($_GET['edit'])]); $editCat = $s->fetch(); }
$cats = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id=c.id) AS product_count FROM categories c ORDER BY c.name")->fetchAll();

include __DIR__ . '/includes/admin_header.php';
?>
<?php if ($msg): ?><div class="alert-msg alert-<?php echo $msgType; ?> mb-3"><?php echo $msg; ?></div><?php endif; ?>
<div class="row g-4">
  <div class="col-lg-4">
    <div style="background:#fff;border-radius:var(--tg-radius);padding:24px;box-shadow:var(--tg-shadow)">
      <h6 class="fw-700 mb-3"><?php echo $editCat ? 'Edit Category' : 'Add Category'; ?></h6>
      <form method="POST">
        <?php if ($editCat): ?><input type="hidden" name="id" value="<?php echo $editCat['id']; ?>"><?php endif; ?>
        <div class="tg-input-group"><label>Category Name *</label><input name="name" class="tg-input" value="<?php echo sanitize($editCat['name']??''); ?>" placeholder="e.g. Smart Watches" required></div>
        <div class="tg-input-group"><label>Icon Class (Font Awesome)</label><input name="icon" class="tg-input" value="<?php echo sanitize($editCat['icon']??'fas fa-box'); ?>" placeholder="fas fa-watch"></div>
        <div class="tg-input-group"><label>Description</label><textarea name="description" class="tg-input" rows="3"><?php echo sanitize($editCat['description']??''); ?></textarea></div>
        <button type="submit" class="tg-btn tg-btn-primary tg-btn-block"><i class="fas fa-save me-2"></i><?php echo $editCat ? 'Update' : 'Add Category'; ?></button>
        <?php if ($editCat): ?><a href="categories.php" class="tg-btn tg-btn-outline tg-btn-block mt-2">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="tg-admin-table">
      <h6 class="fw-700 mb-3">All Categories (<?php echo count($cats); ?>)</h6>
      <table><thead><tr><th>#</th><th>Icon</th><th>Name</th><th>Products</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($cats as $c): ?>
        <tr>
          <td><?php echo $c['id']; ?></td>
          <td><i class="<?php echo $c['icon']; ?> fa-lg text-accent"></i></td>
          <td class="fw-600"><?php echo sanitize($c['name']); ?></td>
          <td><span class="tg-badge" style="background:var(--tg-bg)"><?php echo $c['product_count']; ?> products</span></td>
          <td><div class="d-flex gap-1">
            <a href="?edit=<?php echo $c['id']; ?>" class="admin-btn-action admin-btn-edit"><i class="fas fa-edit"></i></a>
            <a href="?delete=<?php echo $c['id']; ?>" onclick="return confirm('Delete category?')" class="admin-btn-action admin-btn-delete"><i class="fas fa-trash"></i></a>
          </div></td>
        </tr>
        <?php endforeach; ?>
      </tbody></table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
