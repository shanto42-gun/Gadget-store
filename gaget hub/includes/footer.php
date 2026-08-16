<?php $settings = getSettings(); ?>
</main><!-- /tg-main-content -->

<!-- ═══ FOOTER ═══════════════════════════════════════════════════════════════ -->
<footer class="tg-footer" id="contact">
  <div class="tg-footer-main">
    <div class="container-xl">
      <div class="row g-4">

        <!-- Brand column -->
        <div class="col-lg-4 col-md-6">
          <div class="tg-footer-brand">
            <a href="<?php echo SITE_URL; ?>/" class="tg-brand mb-3 d-inline-flex">
              <span class="tg-brand-icon"><i class="fas fa-microchip"></i></span>
              <span class="tg-brand-text text-white">Tech<span>Gadget</span></span>
            </a>
            <p class="text-white opacity-75 mb-3">Your one-stop shop for the latest gadgets, accessories, and tech products. Quality guaranteed with fast delivery across Bangladesh.</p>

          </div>
        </div>

        <!-- Quick Links -->
        <div class="col-lg-2 col-md-6 col-6">
          <h6 class="tg-footer-heading">Quick Links</h6>
          <ul class="tg-footer-links">
            <li><a href="<?php echo SITE_URL; ?>/">Home</a></li>
            <li><a href="<?php echo SITE_URL; ?>/pages/products.php">Shop</a></li>
            <li><a href="<?php echo SITE_URL; ?>/pages/products.php?filter=trending">Trending</a></li>
            <li><a href="<?php echo SITE_URL; ?>/pages/products.php?filter=best_seller">Best Sellers</a></li>
            <li><a href="<?php echo SITE_URL; ?>/pages/orders.php">Track Order</a></li>
          </ul>
        </div>

        <!-- Categories -->
        <div class="col-lg-2 col-md-6 col-6">
          <h6 class="tg-footer-heading">Categories</h6>
          <ul class="tg-footer-links">
            <?php foreach ($categories as $cat): ?>
            <li><a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Account -->
        <div class="col-lg-2 col-md-6 col-6">
          <h6 class="tg-footer-heading">Account</h6>
          <ul class="tg-footer-links">
            <li><a href="<?php echo SITE_URL; ?>/pages/signup.php">Sign Up</a></li>
            <li><a href="<?php echo SITE_URL; ?>/pages/login.php">Login</a></li>
            <li><a href="<?php echo SITE_URL; ?>/pages/dashboard.php">Dashboard</a></li>
            <li><a href="<?php echo SITE_URL; ?>/pages/orders.php">My Orders</a></li>
            <li><a href="<?php echo SITE_URL; ?>/pages/cart.php">My Cart</a></li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="col-lg-2 col-md-6 col-6">
          <h6 class="tg-footer-heading">Contact</h6>
          <ul class="tg-footer-links">
            <li><i class="fas fa-map-marker-alt me-2 text-warning"></i><?php echo $settings['address']; ?></li>
            <li><i class="fas fa-phone me-2 text-warning"></i><?php echo $settings['phone']; ?></li>
            <li><i class="fas fa-envelope me-2 text-warning"></i><?php echo $settings['email']; ?></li>
          </ul>
          <!-- Payment Icons -->
          <div class="tg-payment-icons mt-3">
            <span class="tg-payment-icon"><i class="fab fa-cc-visa"></i></span>
            <span class="tg-payment-icon"><i class="fab fa-cc-mastercard"></i></span>
            <span class="tg-payment-icon">bKash</span>
            <span class="tg-payment-icon">Nagad</span>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Footer bottom -->
  <div class="tg-footer-bottom">
    <div class="container-xl">
      <div class="row align-items-center">
        <div class="col-md-6">
          <p class="mb-0 text-white opacity-60"><?php echo $settings['footer_text']; ?></p>
        </div>
        <div class="col-md-6 text-md-end">
          <p class="mb-0 text-white opacity-60">Made with <i class="fas fa-heart text-danger"></i> in Bangladesh</p>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.umd.min.js"></script>
<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
