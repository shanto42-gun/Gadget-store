<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/admin_functions.php';
requireAdminPanel();

$editId = intval($_GET['edit'] ?? 0);
$product = null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?"); $stmt->execute([$editId]); $product = $stmt->fetch();
}
$adminTitle = $editId ? 'Edit Product' : 'Add Product';
$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $catId = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $discountPrice = $_POST['discount_price'] !== '' ? floatval($_POST['discount_price']) : null;
    $stock = intval($_POST['stock'] ?? 0);
    $brand = sanitize($_POST['brand'] ?? '');
    $desc  = sanitize($_POST['description'] ?? '');
    $specs = sanitize($_POST['specifications'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $trending = isset($_POST['trending']) ? 1 : 0;
    $newArrival = isset($_POST['new_arrival']) ? 1 : 0;
    $bestSeller = isset($_POST['best_seller']) ? 1 : 0;
    $slugName  = $editId ? ($product['slug'] ?? slug($name)) : slug($name);
    // Ensure unique slug
    $check = $pdo->prepare("SELECT id FROM products WHERE slug=? AND id!=?"); $check->execute([$slugName, $editId]); if ($check->fetch()) $slugName .= '-' . time();
    // Upload image
    $imagePath = $product['image'] ?? '';
    if (!empty($_FILES['image']['tmp_name'])) {
        $up = uploadImage($_FILES['image'], 'products');
        if ($up['success']) $imagePath = $up['path'];
    }
    // Validate specs JSON
    $specsJson = '{}';
    if ($specs) {
        $lines = explode("\n", $specs);
        $specsArr = [];
        foreach ($lines as $line) { $parts = explode(':', $line, 2); if (count($parts)==2) $specsArr[trim($parts[0])] = trim($parts[1]); }
        $specsJson = json_encode($specsArr);
    }

    if (!$name || !$catId || !$price) { $msg = 'Name, category and price are required.'; $msgType = 'error'; }
    else {
        if ($editId) {
            $pdo->prepare("UPDATE products SET name=?,slug=?,category_id=?,price=?,discount_price=?,stock=?,brand=?,description=?,specifications=?,image=?,status=?,featured=?,trending=?,new_arrival=?,best_seller=?,updated_at=NOW() WHERE id=?")
                ->execute([$name,$slugName,$catId,$price,$discountPrice,$stock,$brand,$desc,$specsJson,$imagePath,$status,$featured,$trending,$newArrival,$bestSeller,$editId]);
            $msg = 'Product updated!'; $msgType = 'success';
        } else {
            $pdo->prepare("INSERT INTO products (name,slug,category_id,price,discount_price,stock,brand,description,specifications,image,status,featured,trending,new_arrival,best_seller) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
                ->execute([$name,$slugName,$catId,$price,$discountPrice,$stock,$brand,$desc,$specsJson,$imagePath,$status,$featured,$trending,$newArrival,$bestSeller]);
            $msg = 'Product added!'; $msgType = 'success'; $editId = $pdo->lastInsertId();
        }
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?"); $stmt->execute([$editId]); $product = $stmt->fetch();
    }
}
$cats = getCategories(false);
include __DIR__ . '/includes/admin_header.php';
?>
<a href="products.php" class="tg-btn tg-btn-sm tg-btn-outline mb-3"><i class="fas fa-arrow-left me-2"></i>Back to Products</a>
<?php if ($msg): ?><div class="alert-msg alert-<?php echo $msgType; ?> mb-3"><?php echo $msg; ?></div><?php endif; ?>
<form method="POST" enctype="multipart/form-data" style="background:#fff;border-radius:var(--tg-radius);padding:28px;box-shadow:var(--tg-shadow)">
  <div class="row g-4">
    <div class="col-md-8">
      <div class="tg-input-group"><label>Product Name *</label><input name="name" class="tg-input" value="<?php echo sanitize($product['name']??''); ?>" placeholder="e.g. ProFit Ultra Smart Watch" required></div>
      <div class="tg-input-group"><label>Description</label><textarea name="description" class="tg-input" rows="5" placeholder="Product description..."><?php echo sanitize($product['description']??''); ?></textarea></div>
      <div class="tg-input-group"><label>Specifications (one per line: Key: Value)</label><textarea name="specifications" class="tg-input" rows="6" placeholder="Display: 1.45&quot; AMOLED&#10;Battery: 7 Days&#10;Water: 5ATM"><?php if ($product): $specs = json_decode($product['specifications']??'{}', true); foreach($specs as $k=>$v) echo "$k: $v\n"; endif; ?></textarea></div>
    </div>
    <div class="col-md-4">
      <div class="tg-input-group"><label>Category *</label>
        <select name="category_id" class="tg-input" required>
          <option value="">Select Category</option>
          <?php foreach ($cats as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo ($product['category_id']??0)==$c['id']?'selected':''; ?>><?php echo sanitize($c['name']); ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="row g-2">
        <div class="col-6"><div class="tg-input-group"><label>Price (৳) *</label><input name="price" type="number" step="0.01" class="tg-input" value="<?php echo $product['price']??''; ?>" required></div></div>
        <div class="col-6"><div class="tg-input-group"><label>Discount Price</label><input name="discount_price" type="number" step="0.01" class="tg-input" value="<?php echo $product['discount_price']??''; ?>" placeholder="Optional"></div></div>
        <div class="col-6"><div class="tg-input-group"><label>Stock</label><input name="stock" type="number" class="tg-input" value="<?php echo $product['stock']??0; ?>"></div></div>
        <div class="col-6"><div class="tg-input-group"><label>Brand</label><input name="brand" class="tg-input" value="<?php echo sanitize($product['brand']??''); ?>"></div></div>
      </div>
      <div class="tg-input-group"><label>Status</label>
        <select name="status" class="tg-input">
          <option value="active" <?php echo ($product['status']??'')=='active'?'selected':''; ?>>Active</option>
          <option value="inactive" <?php echo ($product['status']??'')=='inactive'?'selected':''; ?>>Inactive</option>
          <option value="draft" <?php echo ($product['status']??'')=='draft'?'selected':''; ?>>Draft</option>
        </select>
      </div>
      <!-- Product Image -->
      <div class="tg-input-group">
        <label>Product Image</label>
        <?php if ($product && $product['image']): ?><img src="<?php echo SITE_URL.'/'.$product['image']; ?>" style="width:100%;height:160px;object-fit:cover;border-radius:8px;margin-bottom:8px"><?php endif; ?>
        <input type="file" name="image" class="tg-input" accept="image/*">
      </div>
      <!-- Flags -->
      <div class="mb-3">
        <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:8px">Product Flags</label>
        <label class="tg-filter-check"><input type="checkbox" name="featured" <?php echo ($product['featured']??0)?'checked':''; ?>> Featured</label>
        <label class="tg-filter-check"><input type="checkbox" name="trending" <?php echo ($product['trending']??0)?'checked':''; ?>> Trending</label>
        <label class="tg-filter-check"><input type="checkbox" name="new_arrival" <?php echo ($product['new_arrival']??1)?'checked':''; ?>> New Arrival</label>
        <label class="tg-filter-check"><input type="checkbox" name="best_seller" <?php echo ($product['best_seller']??0)?'checked':''; ?>> Best Seller</label>
      </div>
    </div>
  </div>
  <div class="d-flex gap-3 mt-2">
    <button type="submit" class="tg-btn tg-btn-primary tg-btn-lg"><i class="fas fa-save me-2"></i><?php echo $editId ? 'Update Product' : 'Add Product'; ?></button>
    <a href="products.php" class="tg-btn tg-btn-outline tg-btn-lg">Cancel</a>
  </div>
</form>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
