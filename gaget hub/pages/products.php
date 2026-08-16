<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Shop Gadgets';

// Filters
$categoryId = intval($_GET['category'] ?? 0);
$filter     = sanitize($_GET['filter'] ?? '');
$search     = sanitize($_GET['q'] ?? '');
$sort       = sanitize($_GET['sort'] ?? 'popular');
$minPrice   = intval($_GET['min_price'] ?? 0);
$maxPrice   = intval($_GET['max_price'] ?? 10000);
$page       = max(1, intval($_GET['page'] ?? 1));
$perPage    = 12;
$offset     = ($page - 1) * $perPage;

// Build query
$where = ["p.status = 'active'"];
$params = [];
if ($categoryId) { $where[] = "p.category_id = ?"; $params[] = $categoryId; }
if ($filter === 'trending')   { $where[] = "p.trending = 1"; }
if ($filter === 'best_seller'){ $where[] = "p.best_seller = 1"; }
if ($filter === 'new_arrival'){ $where[] = "p.new_arrival = 1"; }
if ($search) { $where[] = "(p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($minPrice > 0) { $where[] = "COALESCE(p.discount_price, p.price) >= ?"; $params[] = $minPrice; }
if ($maxPrice < 10000) { $where[] = "COALESCE(p.discount_price, p.price) <= ?"; $params[] = $maxPrice; }

$orderBy = match($sort) {
    'price_asc'  => 'COALESCE(p.discount_price, p.price) ASC',
    'price_desc' => 'COALESCE(p.discount_price, p.price) DESC',
    'newest'     => 'p.created_at DESC',
    'rating'     => 'p.rating DESC',
    default      => 'p.sold_count DESC, p.rating DESC',
};

$whereStr = implode(' AND ', $where);
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE $whereStr");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereStr ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

$cats = getCategories();
$catName = '';
if ($categoryId) {
    foreach ($cats as $c) { if ($c['id'] == $categoryId) { $catName = $c['name']; break; } }
}

include __DIR__ . '/../includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<!-- Page Banner -->
<section class="tg-page-banner">
  <div class="container-xl">
    <h1><?php echo $search ? "Search: \"$search\"" : ($catName ?: ucwords(str_replace('_',' ',$filter)) ?: 'All Gadgets'); ?></h1>
    <div class="tg-breadcrumb"><a href="<?php echo SITE_URL; ?>/">Home</a><span class="sep">/</span><span>Shop</span><?php if ($catName): ?><span class="sep">/</span><span><?php echo $catName; ?></span><?php endif; ?></div>
  </div>
</section>

<section class="tg-section-sm">
  <div class="container-xl">
    <div class="row g-4">
      <!-- ── Sidebar ── -->
      <div class="col-lg-3 d-none d-lg-block">
        <!-- Categories -->
        <div class="tg-filter-card">
          <h6 class="tg-filter-title"><i class="fas fa-th-large me-2 text-accent"></i>Categories</h6>
          <a href="<?php echo SITE_URL; ?>/pages/products.php" class="tg-filter-check text-decoration-none <?php echo !$categoryId?'fw-600 text-accent':''; ?>">
            <i class="fas fa-globe me-2"></i>All Categories
          </a>
          <?php foreach ($cats as $cat): ?>
          <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $cat['id']; ?>" class="tg-filter-check text-decoration-none <?php echo $categoryId==$cat['id']?'fw-600 text-accent':''; ?>">
            <i class="<?php echo $cat['icon']; ?> me-2"></i><?php echo sanitize($cat['name']); ?>
          </a>
          <?php endforeach; ?>
        </div>

        <!-- Price Range -->
        <form id="filterForm" action="" method="GET">
          <?php if ($categoryId): ?><input type="hidden" name="category" value="<?php echo $categoryId; ?>"><?php endif; ?>
          <?php if ($filter): ?><input type="hidden" name="filter" value="<?php echo $filter; ?>"><?php endif; ?>
          <?php if ($search): ?><input type="hidden" name="q" value="<?php echo $search; ?>"><?php endif; ?>
          <input type="hidden" name="sort" value="<?php echo $sort; ?>">
          <div class="tg-filter-card">
            <h6 class="tg-filter-title"><i class="fas fa-tag me-2 text-accent"></i>Price Range</h6>
            <div class="d-flex gap-2 mb-2">
              <input type="number" name="min_price" class="tg-input" placeholder="Min" value="<?php echo $minPrice ?: ''; ?>" style="padding:8px 10px;font-size:.85rem">
              <input type="number" name="max_price" class="tg-input" placeholder="Max" value="<?php echo $maxPrice < 10000 ? $maxPrice : ''; ?>" style="padding:8px 10px;font-size:.85rem">
            </div>
            <button type="submit" class="tg-btn tg-btn-primary tg-btn-block tg-btn-sm">Apply Filter</button>
          </div>
          <!-- Quick Filters -->
          <div class="tg-filter-card">
            <h6 class="tg-filter-title"><i class="fas fa-fire me-2 text-accent"></i>Quick Filters</h6>
            <?php $filters = ['trending'=>'🔥 Trending','best_seller'=>'🏆 Best Sellers','new_arrival'=>'✨ New Arrivals']; ?>
            <?php foreach ($filters as $fk => $fl): ?>
            <a href="<?php echo SITE_URL; ?>/pages/products.php?filter=<?php echo $fk; ?>" class="tg-filter-check text-decoration-none <?php echo $filter===$fk?'fw-600 text-accent':''; ?>"><?php echo $fl; ?></a>
            <?php endforeach; ?>
          </div>
        </form>
      </div>

      <!-- ── Products ── -->
      <div class="col-lg-9">
        <!-- Sort Bar -->
        <div class="tg-sort-bar">
          <span style="font-size:.88rem;color:var(--tg-text-muted)"><strong style="color:var(--tg-text)"><?php echo $total; ?></strong> products found</span>
          <div class="d-flex align-items-center gap-2">
            <span style="font-size:.85rem;white-space:nowrap">Sort by:</span>
            <select class="tg-input" style="width:auto;padding:6px 12px;font-size:.85rem" onchange="sortProducts(this.value)">
              <option value="popular" <?php echo $sort=='popular'?'selected':''; ?>>Most Popular</option>
              <option value="newest" <?php echo $sort=='newest'?'selected':''; ?>>Newest</option>
              <option value="price_asc" <?php echo $sort=='price_asc'?'selected':''; ?>>Price: Low to High</option>
              <option value="price_desc" <?php echo $sort=='price_desc'?'selected':''; ?>>Price: High to Low</option>
              <option value="rating" <?php echo $sort=='rating'?'selected':''; ?>>Best Rating</option>
            </select>
          </div>
        </div>

        <!-- Product Grid -->
        <?php if (empty($products)): ?>
        <div class="tg-empty-state">
          <div class="tg-empty-icon"><i class="fas fa-search"></i></div>
          <h5>No products found</h5>
          <p>Try adjusting your search or filters.</p>
          <a href="<?php echo SITE_URL; ?>/pages/products.php" class="tg-btn tg-btn-primary">Clear Filters</a>
        </div>
        <?php else: ?>
        <div class="tg-product-grid grid-4" id="productGrid">
          <?php foreach ($products as $p):
            $discP = $p['discount_price'];
            $dispP = $discP ?: $p['price'];
            $disc  = $discP ? round(($p['price']-$discP)/$p['price']*100) : 0;
            $img   = $p['image'] ? SITE_URL.'/'.$p['image'] : SITE_URL.'/assets/images/no-image.png';
          ?>
          <div class="tg-product-card">
            <div class="tg-product-img-wrap">
              <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $p['slug']; ?>">
                <img src="<?php echo $img; ?>" alt="<?php echo sanitize($p['name']); ?>" loading="lazy">
              </a>
              <div class="tg-product-badges">
                <?php if ($disc): ?><span class="tg-badge tg-badge-discount">-<?php echo $disc; ?>%</span><?php endif; ?>
                <?php if ($p['new_arrival']): ?><span class="tg-badge tg-badge-new">New</span><?php endif; ?>
              </div>
              <div class="tg-product-actions">
                <button class="tg-action-btn wishlist-btn" data-id="<?php echo $p['id']; ?>"><i class="far fa-heart"></i></button>
                <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $p['slug']; ?>" class="tg-action-btn"><i class="fas fa-eye"></i></a>
              </div>
            </div>
            <div class="tg-product-body">
              <div class="tg-product-brand"><?php echo sanitize($p['brand']); ?></div>
              <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $p['slug']; ?>" class="tg-product-name"><?php echo sanitize($p['name']); ?></a>
              <div class="tg-product-rating"><span class="stars"><?php echo starRating($p['rating']); ?></span><small>(<?php echo $p['review_count']; ?>)</small></div>
              <div class="tg-product-price">
                <span class="tg-price-current"><?php echo formatPrice($dispP); ?></span>
                <?php if ($discP): ?><span class="tg-price-original"><?php echo formatPrice($p['price']); ?></span><?php endif; ?>
              </div>
              <button class="tg-add-cart-btn add-cart-btn" data-id="<?php echo $p['id']; ?>" <?php echo $p['stock']<=0?'disabled':''; ?>>
                <i class="fas fa-cart-plus"></i><?php echo $p['stock']<=0?'Out of Stock':'Add to Cart'; ?>
              </button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="d-flex justify-content-center mt-4">
          <ul class="pagination" style="gap:6px">
            <?php if ($page > 1): ?>
            <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$page-1])); ?>" class="tg-btn tg-btn-outline tg-btn-sm"><i class="fas fa-chevron-left"></i></a></li>
            <?php endif; ?>
            <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
            <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$i])); ?>" class="tg-btn tg-btn-sm <?php echo $i==$page?'tg-btn-primary':'tg-btn-outline'; ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$page+1])); ?>" class="tg-btn tg-btn-outline tg-btn-sm"><i class="fas fa-chevron-right"></i></a></li>
            <?php endif; ?>
          </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<script>
function sortProducts(val) {
  const u = new URL(window.location);
  u.searchParams.set('sort', val);
  u.searchParams.delete('page');
  window.location = u;
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
