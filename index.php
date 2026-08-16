<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Home – Best Gadgets Online';
$pageDesc  = 'Shop the latest smartwatches, earbuds, power banks, and accessories at TechGadget Store.';

// Load products for homepage sections
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status='active' AND p.stock>0 AND p.trending=1 LIMIT 8");
$stmt->execute(); $trendingProducts = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status='active' AND p.stock>0 AND p.best_seller=1 LIMIT 8");
$stmt->execute(); $bestSellers = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status='active' AND p.stock>0 AND p.new_arrival=1 ORDER BY p.created_at DESC LIMIT 8");
$stmt->execute(); $newArrivals = $stmt->fetchAll();

$cats = getCategories();
$settings = getSettings();

include __DIR__ . '/includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">

<!-- ═══ HERO CAROUSEL ════════════════════════════════════════════════════ -->
<section class="tg-hero">
  <div id="heroCarousel" class="carousel slide carousel-fade" data-mdb-ride="carousel">
    <div class="carousel-inner">

      <!-- Slide 1 -->
      <?php 
      $heroBgRaw = $settings['site_hero_bg'] ?? '';
      $heroBg = $heroBgRaw ? SITE_URL . '/' . $heroBgRaw . '?v=' . time() : SITE_URL . '/assets/images/hero/hero_banner_new.png'; 
      ?>
      <div class="carousel-item active">
        <div class="tg-hero-slide" style="background:linear-gradient(rgba(245,247,250,0.1), rgba(245,247,250,0.1)), url('<?php echo $heroBg; ?>'); background-size: cover; background-position: center;">
          <div class="container-xl">
            <div class="row align-items-center">
              <div class="col-lg-10 mx-auto tg-hero-content text-center">
                <span class="badge-pill">🔥 Flash Sale – Up to 40% Off</span>
                <h1>Upgrade Your Lifestyle With <span style="color:var(--tg-accent)">Smart Gadgets</span></h1>
                <p>Discover the latest smart watches, chargers, and tech accessories at the best price.</p>
                <div class="tg-hero-btns justify-content-center">
                  <a href="<?php echo SITE_URL; ?>/pages/products.php" class="tg-btn tg-btn-primary tg-btn-lg"><i class="fas fa-shopping-bag"></i> Shop Now</a>
                  <a href="<?php echo SITE_URL; ?>/pages/products.php" class="tg-btn tg-btn-lg" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3)">Explore All</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Slide 2 -->
      <div class="carousel-item">
        <div class="tg-hero-slide" style="background:linear-gradient(rgba(245,247,250,0.1), rgba(245,247,250,0.1)), url('<?php echo $heroBg; ?>'); background-size: cover; background-position: center;">
          <div class="container-xl">
            <div class="row align-items-center">
              <div class="col-lg-7 tg-hero-content">
                <span class="badge-pill">🎧 Best Seller</span>
                <h1>SoundPro X9 TWS<br><span style="color:var(--tg-cyan)">Crystal Audio</span></h1>
                <p>30hr Battery · Active Noise Cancelling · IPX5 Waterproof</p>
                <div class="tg-hero-btns">
                  <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=soundpro-x9-tws" class="tg-btn tg-btn-primary tg-btn-lg"><i class="fas fa-shopping-bag"></i> Buy Now</a>
                  <a href="<?php echo SITE_URL; ?>/pages/products.php?category=2" class="tg-btn tg-btn-lg" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3)">View All Earbuds</a>
                </div>
              </div>
              <div class="col-lg-5 d-none d-lg-block text-center">
                <img src="<?php echo SITE_URL; ?>/assets/images/products/soundpro_x9_earbuds.png?v=1.2" alt="SoundPro X9" style="width: 100%; max-width: 440px; filter: brightness(1.15) contrast(1.1) drop-shadow(0 20px 50px rgba(0,212,255,0.3)); clip-path: inset(5% 15% 15% 15%); mask-image: radial-gradient(ellipse at center, black 50%, transparent 75%); -webkit-mask-image: radial-gradient(ellipse at center, black 50%, transparent 75%); transform: scale(1.2) translateY(-10px);">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Slide 3 -->
      <div class="carousel-item">
        <div class="tg-hero-slide" style="background:linear-gradient(rgba(245,247,250,0.1), rgba(245,247,250,0.1)), url('<?php echo $heroBg; ?>'); background-size: cover; background-position: center;">
          <div class="container-xl">
            <div class="row align-items-center">
              <div class="col-lg-7 tg-hero-content">
                <span class="badge-pill">⚡ Fast Charge</span>
                <h1>PowerMax 20000mAh<br><span style="color:var(--tg-warning)">65W PD Charging</span></h1>
                <p>Charge 3 devices simultaneously · Airline-safe · Ultra-compact</p>
                <div class="tg-hero-btns">
                  <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=powermax-20000mah" class="tg-btn tg-btn-primary tg-btn-lg"><i class="fas fa-shopping-bag"></i> Buy Now</a>
                  <a href="<?php echo SITE_URL; ?>/pages/products.php?category=3" class="tg-btn tg-btn-lg" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3)">See Power Banks</a>
                </div>
              </div>
              <div class="col-lg-5 d-none d-lg-block text-center">
                <img src="<?php echo SITE_URL; ?>/assets/images/products/powermax_20000_powerbank.png?v=1.2" alt="PowerMax 20000" style="width: 100%; max-width: 440px; filter: brightness(1.15) contrast(1.1) drop-shadow(0 20px 50px rgba(255,184,0,0.3)); clip-path: inset(5% 15% 15% 15%); mask-image: radial-gradient(ellipse at center, black 50%, transparent 75%); -webkit-mask-image: radial-gradient(ellipse at center, black 50%, transparent 75%); transform: scale(1.2) translateY(-10px);">
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
    <!-- Controls -->
    <button class="carousel-control-prev" type="button" data-mdb-target="#heroCarousel" data-mdb-slide="prev">
      <span class="tg-carousel-ctrl"><i class="fas fa-chevron-left text-white"></i></span>
    </button>
    <button class="carousel-control-next" type="button" data-mdb-target="#heroCarousel" data-mdb-slide="next">
      <span class="tg-carousel-ctrl"><i class="fas fa-chevron-right text-white"></i></span>
    </button>
    <!-- Indicators -->
    <div class="carousel-indicators" style="bottom:16px">
      <button type="button" data-mdb-target="#heroCarousel" data-mdb-slide-to="0" class="active" style="border-radius:50px;width:30px;height:4px;background:var(--tg-accent)"></button>
      <button type="button" data-mdb-target="#heroCarousel" data-mdb-slide-to="1" style="border-radius:50px;width:12px;height:4px;background:rgba(255,255,255,.5)"></button>
      <button type="button" data-mdb-target="#heroCarousel" data-mdb-slide-to="2" style="border-radius:50px;width:12px;height:4px;background:rgba(255,255,255,.5)"></button>
    </div>
  </div>
</section>

<!-- ═══ TRUST BAR ═════════════════════════════════════════════════════════ -->
<section style="background:#fff;border-bottom:1px solid var(--tg-border);padding:20px 0">
  <div class="container-xl">
    <div class="row g-3 text-center">
      <div class="col-6 col-md-3">
        <div class="d-flex align-items-center justify-content-center gap-3">
          <i class="fas fa-shipping-fast fa-2x text-accent"></i>
          <div class="text-start"><strong style="font-size:.88rem;display:block">Free Delivery</strong><small class="text-muted">On orders over ৳2000</small></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="d-flex align-items-center justify-content-center gap-3">
          <i class="fas fa-shield-alt fa-2x text-accent"></i>
          <div class="text-start"><strong style="font-size:.88rem;display:block">Genuine Products</strong><small class="text-muted">100% authentic guarantee</small></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="d-flex align-items-center justify-content-center gap-3">
          <i class="fas fa-undo fa-2x text-accent"></i>
          <div class="text-start"><strong style="font-size:.88rem;display:block">7-Day Returns</strong><small class="text-muted">Hassle-free returns</small></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="d-flex align-items-center justify-content-center gap-3">
          <i class="fas fa-headset fa-2x text-accent"></i>
          <div class="text-start"><strong style="font-size:.88rem;display:block">24/7 Support</strong><small class="text-muted">We're always here</small></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ CATEGORIES ════════════════════════════════════════════════════════ -->
<section class="tg-section-sm" style="background:var(--tg-bg)">
  <div class="container-xl">
    <div class="tg-section-title">
      <div class="tg-badge-line"></div>
      <h2>Shop by Category</h2>
      <p>Browse our curated collection of top-rated gadget categories</p>
    </div>
    <div class="row g-3">
      <?php
      $catCounts = [];
      foreach ($cats as $cat) {
        $s = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id=? AND status='active'");
        $s->execute([$cat['id']]); $catCounts[$cat['id']] = $s->fetchColumn();
      }
      foreach ($cats as $cat):
      ?>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $cat['id']; ?>" class="tg-cat-card">
          <div class="tg-cat-icon"><i class="<?php echo $cat['icon']; ?>"></i></div>
          <h6><?php echo sanitize($cat['name']); ?></h6>
          <small><?php echo $catCounts[$cat['id']]; ?> products</small>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ TRENDING / BEST / NEW TABS ═══════════════════════════════════════ -->
<section class="tg-section" style="background:#fff">
  <div class="container-xl">
    <div class="tg-section-title">
      <div class="tg-badge-line"></div>
      <h2>Our Top Products</h2>
      <p>Handpicked gadgets loved by thousands of customers</p>
    </div>
    <div class="tg-tab-selector">
      <button class="tg-tab active" data-tab="trending">🔥 Trending</button>
      <button class="tg-tab" data-tab="best_seller">🏆 Best Sellers</button>
      <button class="tg-tab" data-tab="new_arrival">✨ New Arrivals</button>
    </div>

    <?php
    $productSets = ['trending' => $trendingProducts, 'best_seller' => $bestSellers, 'new_arrival' => $newArrivals];
    foreach ($productSets as $tabKey => $products):
    ?>
    <div class="products-tab-pane" id="tab-<?php echo $tabKey; ?>" <?php echo $tabKey !== 'trending' ? 'style="display:none"' : ''; ?>>
      <div class="swiper tg-product-slider">
        <div class="swiper-wrapper">
          <?php foreach ($products as $p):
            $discP = $p['discount_price'];
            $dispP = $discP ?: $p['price'];
            $disc  = $discP ? round(($p['price']-$discP)/$p['price']*100) : 0;
            $img   = $p['image'] ? SITE_URL.'/'.$p['image'] : SITE_URL.'/assets/images/no-image.png';
          ?>
          <div class="swiper-slide">
            <div class="tg-product-card h-100">
              <div class="tg-product-img-wrap">
                <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $p['slug']; ?>">
                  <img src="<?php echo $img; ?>" alt="<?php echo sanitize($p['name']); ?>" loading="lazy">
                </a>
                <div class="tg-product-badges">
                  <?php if ($disc): ?><span class="tg-badge tg-badge-discount">-<?php echo $disc; ?>%</span><?php endif; ?>
                  <?php if ($p['new_arrival']): ?><span class="tg-badge tg-badge-new">New</span><?php endif; ?>
                  <?php if ($p['trending']): ?><span class="tg-badge tg-badge-trending">Hot</span><?php endif; ?>
                </div>
                <div class="tg-product-actions">
                  <button class="tg-action-btn wishlist-btn" data-id="<?php echo $p['id']; ?>" title="Wishlist"><i class="far fa-heart"></i></button>
                  <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $p['slug']; ?>" class="tg-action-btn"><i class="fas fa-eye"></i></a>
                </div>
              </div>
              <div class="tg-product-body">
                <div class="tg-product-brand"><?php echo sanitize($p['brand']); ?></div>
                <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $p['slug']; ?>" class="tg-product-name"><?php echo sanitize($p['name']); ?></a>
                <div class="tg-product-rating">
                  <span class="stars"><?php echo starRating($p['rating']); ?></span>
                  <small>(<?php echo $p['review_count']; ?>)</small>
                </div>
                <div class="tg-product-price">
                  <span class="tg-price-current"><?php echo formatPrice($dispP); ?></span>
                  <?php if ($discP): ?><span class="tg-price-original"><?php echo formatPrice($p['price']); ?></span><?php endif; ?>
                </div>
                <button class="tg-add-cart-btn add-cart-btn" data-id="<?php echo $p['id']; ?>" <?php echo $p['stock']<=0 ? 'disabled' : ''; ?>>
                  <i class="fas fa-cart-plus"></i><?php echo $p['stock']<=0 ? 'Out of Stock' : 'Add to Cart'; ?>
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <!-- Add Navigation -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <!-- Add Pagination -->
        <div class="swiper-pagination"></div>
      </div>
      <div class="text-center mt-4">
        <a href="<?php echo SITE_URL; ?>/pages/products.php?filter=<?php echo $tabKey; ?>" class="tg-btn tg-btn-outline"><i class="fas fa-th-large me-2"></i>View All <?php echo ucwords(str_replace('_',' ',$tabKey)); ?></a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ═══ OFFER BANNERS ═════════════════════════════════════════════════════ -->
<section class="tg-section-sm" style="background:var(--tg-bg)">
  <div class="container-xl">
    <div class="row g-4">
      <div class="col-md-6">
        <div class="tg-offer-banner tg-offer-banner-1">
          <div>
            <div class="offer-badge">40%</div>
            <h3>Smart Watches<br>Flash Sale</h3>
            <p>48 hours only — don't miss it!</p>
            <a href="<?php echo SITE_URL; ?>/pages/products.php?category=1" class="tg-btn tg-btn-primary mt-2">Shop Watches</a>
          </div>
          <div class="ms-auto d-none d-md-block" style="font-size:6rem;opacity:.3">⌚</div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="tg-offer-banner tg-offer-banner-2">
          <div>
            <div class="offer-badge">30%</div>
            <h3>Audio Upgrade<br>Week</h3>
            <p>Earbuds & headphones on sale now</p>
            <a href="<?php echo SITE_URL; ?>/pages/products.php?category=2" class="tg-btn tg-btn-primary mt-2">Shop Audio</a>
          </div>
          <div class="ms-auto d-none d-md-block" style="font-size:6rem;opacity:.3">🎧</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ CUSTOMER REVIEWS ══════════════════════════════════════════════════ -->
<section class="tg-section" style="background:#fff">
  <div class="container-xl">
    <div class="tg-section-title">
      <div class="tg-badge-line"></div>
      <h2>What Our Customers Say</h2>
      <p>Real reviews from verified buyers across Bangladesh</p>
    </div>
    <div class="row g-4">
      <?php
      $reviews = [
        ['name'=>'Arif Hossain','loc'=>'Dhaka','rating'=>5,'text'=>'Absolutely love the SoundPro X9 earbuds! ANC is incredible, and the battery lasts all day. Best purchase I\'ve made this year!','init'=>'A'],
        ['name'=>'Nusrat Jahan','loc'=>'Chittagong','rating'=>5,'text'=>'The ProFit smartwatch exceeded all my expectations. Tracks my sleep, shows notifications, and the display is stunning. Worth every taka!','init'=>'N'],
        ['name'=>'Md. Rakib','loc'=>'Sylhet','rating'=>4,'text'=>'PowerMax 20000mAh is a game-changer. Charged my laptop and phone simultaneously on a 6-hour bus ride. Delivery was fast too!','init'=>'R'],
        ['name'=>'Sumaiya Akter','loc'=>'Rajshahi','rating'=>5,'text'=>'Great prices, genuine products, and super fast delivery. TechGadget is now my go-to store for all electronics. Highly recommend!','init'=>'S'],
      ];
      foreach ($reviews as $r):
      ?>
      <div class="col-md-6 col-lg-3">
        <div class="tg-review-card">
          <div class="mb-2"><?php echo starRating($r['rating']); ?></div>
          <p class="tg-review-text">"<?php echo $r['text']; ?>"</p>
          <div class="tg-reviewer">
            <div class="tg-reviewer-avatar"><?php echo $r['init']; ?></div>
            <div>
              <strong><?php echo $r['name']; ?></strong>
              <small><i class="fas fa-map-marker-alt me-1"></i><?php echo $r['loc']; ?></small>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ NEWSLETTER ════════════════════════════════════════════════════════ -->
<section class="tg-newsletter">
  <div class="container-xl text-center">
    <i class="fas fa-envelope-open-text fa-3x text-accent mb-3"></i>
    <h2 class="text-white fw-800">Stay in the Loop</h2>
    <p class="text-white opacity-75">Get exclusive deals, new arrivals, and tech tips directly in your inbox.</p>
    <form id="newsletterForm" class="tg-newsletter-form mx-auto">
      <input type="email" placeholder="Enter your email address..." required>
      <button type="submit">Subscribe <i class="fas fa-paper-plane ms-1"></i></button>
    </form>
    <small class="text-white opacity-50 mt-3 d-block">We respect your privacy. Unsubscribe at any time.</small>
  </div>
</section>

<script>
// Tab switching
document.querySelectorAll('.tg-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.tg-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    document.querySelectorAll('.products-tab-pane').forEach(p => p.style.display = 'none');
    const target = document.getElementById('tab-' + tab.dataset.tab);
    if (target) target.style.display = 'block';
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
