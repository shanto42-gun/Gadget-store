<?php
require_once __DIR__ . '/../includes/functions.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) redirect(SITE_URL . '/pages/products.php');

$product = getProductBySlug($slug);
if (!$product) { http_response_code(404); redirect(SITE_URL . '/pages/products.php'); }

$gallery = json_decode($product['gallery'] ?? '[]', true) ?: [];
$specs   = json_decode($product['specifications'] ?? '{}', true) ?: [];
$related = getRelatedProducts($product['category_id'], $product['id'], 4);
$isWl    = isWishlisted($product['id']);

$disc = $product['discount_price'] ? round(($product['price'] - $product['discount_price']) / $product['price'] * 100) : 0;
$dispPrice = $product['discount_price'] ?: $product['price'];
$img = $product['image'] ? SITE_URL.'/'.$product['image'] : SITE_URL.'/assets/images/no-image.png';

// Reviews
$reviewStmt = $pdo->prepare("SELECT r.*, u.name AS user_name, u.avatar FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 20");
$reviewStmt->execute([$product['id']]);
$reviews = $reviewStmt->fetchAll();

$pageTitle = $product['name'];
include __DIR__ . '/../includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<!-- Banner -->
<section class="tg-page-banner">
  <div class="container-xl">
    <h1 style="font-size:1.3rem"><?php echo sanitize($product['name']); ?></h1>
    <div class="tg-breadcrumb">
      <a href="<?php echo SITE_URL; ?>/">Home</a><span class="sep">/</span>
      <a href="<?php echo SITE_URL; ?>/pages/products.php">Shop</a><span class="sep">/</span>
      <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $product['category_id']; ?>"><?php echo sanitize($product['category_name']); ?></a>
      <span class="sep">/</span><span><?php echo sanitize($product['name']); ?></span>
    </div>
  </div>
</section>

<section class="tg-section-sm">
  <div class="container-xl">
    <div class="row g-4">
      <!-- Image Gallery -->
      <div class="col-lg-5">
        <div class="tg-product-gallery shadow-tg p-3">
          <img src="<?php echo $img; ?>" alt="<?php echo sanitize($product['name']); ?>" class="tg-product-main-img rounded-tg" id="mainImg">
          <?php if (!empty($gallery)): ?>
          <div class="tg-product-thumbnails mt-2">
            <img src="<?php echo $img; ?>" class="tg-thumb active" onclick="changeImg(this, '<?php echo $img; ?>')">
            <?php foreach ($gallery as $gImg): ?>
            <?php $gUrl = SITE_URL . '/' . $gImg; ?>
            <img src="<?php echo $gUrl; ?>" class="tg-thumb" onclick="changeImg(this, '<?php echo $gUrl; ?>')">
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Product Info -->
      <div class="col-lg-7">
        <div style="background:#fff;border-radius:var(--tg-radius);padding:28px;box-shadow:var(--tg-shadow)">
          <!-- Brand & badges -->
          <div class="d-flex align-items-center gap-2 mb-2">
            <?php if ($product['brand']): ?><span class="tg-badge" style="background:var(--tg-bg);color:var(--tg-text-muted);font-size:.72rem"><?php echo sanitize($product['brand']); ?></span><?php endif; ?>
            <?php if ($disc): ?><span class="tg-badge tg-badge-discount">-<?php echo $disc; ?>% OFF</span><?php endif; ?>
            <?php if ($product['trending']): ?><span class="tg-badge tg-badge-trending">🔥 Trending</span><?php endif; ?>
          </div>
          <h1 style="font-size:1.5rem;font-weight:800;line-height:1.3;margin-bottom:12px"><?php echo sanitize($product['name']); ?></h1>
          <!-- Rating -->
          <div class="d-flex align-items-center gap-3 mb-16" style="margin-bottom:16px">
            <div class="d-flex gap-1"><?php echo starRating($product['rating']); ?></div>
            <span style="color:var(--tg-text-muted);font-size:.85rem"><?php echo $product['rating']; ?>/5 (<?php echo $product['review_count']; ?> reviews)</span>
            <span style="color:var(--tg-text-muted);font-size:.85rem">·</span>
            <span style="font-size:.85rem;color:var(--tg-success)"><i class="fas fa-check-circle me-1"></i><?php echo number_format($product['sold_count']); ?> sold</span>
          </div>
          <!-- Price -->
          <div class="tg-product-price mb-3">
            <span class="tg-orig-price"><?php echo formatPrice($dispPrice); ?></span>
            <?php if ($product['discount_price']): ?><span class="tg-price-original" style="font-size:1.1rem"><?php echo formatPrice($product['price']); ?></span><span class="tg-badge tg-badge-discount">Save <?php echo formatPrice($product['price'] - $product['discount_price']); ?></span><?php endif; ?>
          </div>
          <!-- Stock -->
          <div class="mb-3">
            <?php if ($product['stock'] > 10): ?>
            <span class="tg-badge" style="background:#d4edda;color:#155724"><i class="fas fa-check-circle me-1"></i>In Stock (<?php echo $product['stock']; ?> units)</span>
            <?php elseif ($product['stock'] > 0): ?>
            <span class="tg-badge" style="background:#fff3cd;color:#856404"><i class="fas fa-exclamation-circle me-1"></i>Only <?php echo $product['stock']; ?> left!</span>
            <?php else: ?>
            <span class="tg-badge tg-badge-discount"><i class="fas fa-times-circle me-1"></i>Out of Stock</span>
            <?php endif; ?>
          </div>
          <hr>
          <!-- Quantity + Cart -->
          <div class="d-flex align-items-center gap-3 flex-wrap mb-3">
            <div class="tg-qty-stepper" data-stock="<?php echo $product['stock']; ?>">
              <button class="qty-minus">−</button>
              <span class="qty-display">1</span>
              <button class="qty-plus">+</button>
              <input type="hidden" class="qty-input" value="1">
            </div>
            <button class="tg-btn tg-btn-dark flex-grow-1 add-cart-btn" data-id="<?php echo $product['id']; ?>" id="addToCartBtn" <?php echo $product['stock']<=0?'disabled':''; ?>>
              <i class="fas fa-cart-plus"></i><?php echo $product['stock']<=0?'Out of Stock':'Add to Cart'; ?>
            </button>
            <button class="tg-btn tg-btn-primary flex-grow-1" id="buyNowBtn" <?php echo $product['stock']<=0?'disabled':''; ?>>
              <i class="fas fa-bolt"></i> Buy Now
            </button>
          </div>
          <!-- Wishlist / Share -->
          <div class="d-flex gap-2">
            <button class="tg-btn tg-btn-outline tg-btn-sm wishlist-btn <?php echo $isWl?'wishlisted':''; ?>" data-id="<?php echo $product['id']; ?>" id="wishlistBtn">
              <i class="<?php echo $isWl?'fas':'far'; ?> fa-heart"></i><?php echo $isWl?'Wishlisted':'Add to Wishlist'; ?>
            </button>
            <button class="tg-btn tg-btn-sm" style="background:var(--tg-bg);border:1.5px solid var(--tg-border)" onclick="navigator.share ? navigator.share({title:'<?php echo addslashes($product['name']); ?>',url:window.location.href}) : navigator.clipboard.writeText(window.location.href).then(()=>TG.toast('Link copied!','info'))">
              <i class="fas fa-share-alt"></i> Share
            </button>
          </div>
          <hr>
          <!-- Trust points -->
          <div class="row g-2 mt-1">
            <div class="col-6"><small class="d-flex align-items-center gap-2"><i class="fas fa-shipping-fast text-accent"></i>Free delivery on ৳2000+</small></div>
            <div class="col-6"><small class="d-flex align-items-center gap-2"><i class="fas fa-undo text-accent"></i>7-day easy returns</small></div>
            <div class="col-6"><small class="d-flex align-items-center gap-2"><i class="fas fa-shield-alt text-accent"></i>Genuine product guarantee</small></div>
            <div class="col-6"><small class="d-flex align-items-center gap-2"><i class="fas fa-headset text-accent"></i>24/7 customer support</small></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs: Description / Specs / Reviews -->
    <div class="mt-4">
      <ul class="nav nav-tabs tg-detail-tabs" id="productTabs">
        <li class="nav-item"><button class="nav-link active" data-mdb-toggle="tab" data-mdb-target="#tabDesc">Description</button></li>
        <li class="nav-item"><button class="nav-link" data-mdb-toggle="tab" data-mdb-target="#tabSpec">Specifications</button></li>
        <li class="nav-item"><button class="nav-link" data-mdb-toggle="tab" data-mdb-target="#tabReviews">Reviews (<?php echo count($reviews); ?>)</button></li>
      </ul>
      <div class="tab-content" style="background:#fff;border-radius:0 var(--tg-radius) var(--tg-radius) var(--tg-radius);padding:28px;box-shadow:var(--tg-shadow)">
        <!-- Description -->
        <div class="tab-pane fade show active" id="tabDesc">
          <p><?php echo nl2br(sanitize($product['description'])); ?></p>
        </div>
        <!-- Specifications -->
        <div class="tab-pane fade" id="tabSpec">
          <?php if ($specs): ?>
          <table class="table" style="font-size:.9rem">
            <?php foreach ($specs as $key => $val): ?>
            <tr><td class="fw-600" style="width:200px;color:var(--tg-text-muted)"><?php echo sanitize($key); ?></td><td><?php echo sanitize($val); ?></td></tr>
            <?php endforeach; ?>
          </table>
          <?php else: ?><p class="text-muted">No specifications available.</p><?php endif; ?>
        </div>
        <!-- Reviews -->
        <div class="tab-pane fade" id="tabReviews">
          <?php if (isLoggedIn()): ?>
          <div class="mb-4 p-3" style="background:var(--tg-bg);border-radius:var(--tg-radius-sm)">
            <h6 class="fw-700 mb-3">Write a Review</h6>
            <form id="reviewForm">
              <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
              <div class="d-flex gap-2 mb-3">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                <label class="rating-star" style="cursor:pointer;font-size:1.5rem;color:var(--tg-border)">
                  <input type="radio" name="rating" value="<?php echo $i; ?>" style="display:none"> ★
                </label>
                <?php endfor; ?>
              </div>
              <textarea name="review" class="tg-input mb-2" rows="3" placeholder="Share your experience..." required></textarea>
              <button type="submit" class="tg-btn tg-btn-primary tg-btn-sm"><i class="fas fa-paper-plane"></i> Submit Review</button>
            </form>
          </div>
          <?php else: ?><p><a href="<?php echo SITE_URL; ?>/pages/login.php" class="text-accent fw-600">Login</a> to write a review.</p><?php endif; ?>

          <?php if (empty($reviews)): ?>
          <div class="tg-empty-state" style="padding:40px"><div class="tg-empty-icon"><i class="fas fa-star"></i></div><h5>No reviews yet</h5><p>Be the first to review this product.</p></div>
          <?php else: ?>
          <div class="row g-3 mt-2">
            <?php foreach ($reviews as $rev): ?>
            <div class="col-12">
              <div class="p-3" style="border:1px solid var(--tg-border);border-radius:var(--tg-radius-sm)">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div class="tg-reviewer-avatar" style="width:34px;height:34px;font-size:.85rem"><?php echo strtoupper(substr($rev['user_name'],0,1)); ?></div>
                  <div><strong style="font-size:.88rem"><?php echo sanitize($rev['user_name']); ?></strong><br><span style="font-size:.7rem;color:var(--tg-text-muted)"><?php echo timeAgo($rev['created_at']); ?></span></div>
                  <div class="ms-auto"><?php echo starRating($rev['rating']); ?></div>
                </div>
                <p style="font-size:.88rem;margin:0"><?php echo sanitize($rev['review']); ?></p>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
    <div class="mt-5">
      <h3 class="fw-800 mb-4">Related Products</h3>
      <div class="tg-product-grid grid-4">
        <?php foreach ($related as $p):
          $rImg = $p['image'] ? SITE_URL.'/'.$p['image'] : SITE_URL.'/assets/images/no-image.png';
          $rDisc = $p['discount_price'] ? round(($p['price']-$p['discount_price'])/$p['price']*100) : 0;
          $rDispP = $p['discount_price'] ?: $p['price'];
        ?>
        <div class="tg-product-card">
          <div class="tg-product-img-wrap">
            <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $p['slug']; ?>">
              <img src="<?php echo $rImg; ?>" alt="<?php echo sanitize($p['name']); ?>" loading="lazy">
            </a>
            <?php if ($rDisc): ?><div class="tg-product-badges"><span class="tg-badge tg-badge-discount">-<?php echo $rDisc; ?>%</span></div><?php endif; ?>
          </div>
          <div class="tg-product-body">
            <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $p['slug']; ?>" class="tg-product-name"><?php echo sanitize($p['name']); ?></a>
            <div class="tg-product-price"><span class="tg-price-current"><?php echo formatPrice($rDispP); ?></span><?php if ($p['discount_price']): ?><span class="tg-price-original"><?php echo formatPrice($p['price']); ?></span><?php endif; ?></div>
            <button class="tg-add-cart-btn add-cart-btn" data-id="<?php echo $p['id']; ?>"><i class="fas fa-cart-plus"></i> Add to Cart</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
function changeImg(el, src) {
  document.getElementById('mainImg').src = src;
  document.querySelectorAll('.tg-thumb').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
}
// Rating stars interactive
document.querySelectorAll('.rating-star').forEach((star, idx, arr) => {
  star.addEventListener('mouseover', () => arr.forEach((s, i) => { s.style.color = i >= idx ? 'var(--tg-warning)' : 'var(--tg-border)'; }));
  star.addEventListener('click', () => star.querySelector('input').checked = true);
  document.querySelector('.rating-star').closest('div')?.addEventListener('mouseleave', () => {
    const checked = document.querySelector('input[name=rating]:checked');
    if (checked) { const v = parseInt(checked.value); arr.forEach((s,i,a)=>{ s.style.color = (a.length-1-i) < v ? 'var(--tg-warning)':'var(--tg-border)'; }); }
    else arr.forEach(s=>s.style.color='var(--tg-border)');
  });
});
// Buy Now
document.getElementById('buyNowBtn')?.addEventListener('click', async () => {
  const btn = document.getElementById('buyNowBtn');
  const qty = parseInt(document.querySelector('.qty-input').value) || 1;
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
  await TG.addToCart(<?php echo $product['id']; ?>, qty, document.getElementById('addToCartBtn'));
  window.location = '<?php echo SITE_URL; ?>/pages/cart.php';
});
// Add to cart with qty
document.getElementById('addToCartBtn')?.addEventListener('click', function() {
  const qty = parseInt(document.querySelector('.qty-input').value) || 1;
  TG.addToCart(<?php echo $product['id']; ?>, qty, this);
});
// Review form
document.getElementById('reviewForm')?.addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  if (!fd.get('rating')) { TG.toast('Please select a rating.','warning'); return; }
  const res = await TG.post('<?php echo SITE_URL; ?>/api/reviews/add_review.php', fd);
  if (res.success) { TG.toast('Review submitted!','success'); e.target.reset(); setTimeout(()=>location.reload(),1500); }
  else TG.toast(res.message||'Failed.','error');
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
