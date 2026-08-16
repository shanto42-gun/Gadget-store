<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'My Cart';
$cartItems = getCartItems();
$subtotal  = cartTotal();
$settings  = getSettings();
$shipping  = (float)$settings['shipping_cost'];
if ($subtotal >= 2000) $shipping = 0;
$total = $subtotal + $shipping;

include __DIR__ . '/../includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<section class="tg-page-banner">
  <div class="container-xl">
    <h1>Shopping Cart</h1>
    <div class="tg-breadcrumb"><a href="<?php echo SITE_URL; ?>/">Home</a><span class="sep">/</span><span>Cart</span></div>
  </div>
</section>

<section class="tg-section-sm">
  <div class="container-xl">
    <?php if (empty($cartItems)): ?>
    <div class="tg-empty-state" style="padding:80px 20px">
      <div class="tg-empty-icon"><i class="fas fa-shopping-cart"></i></div>
      <h5>Your cart is empty</h5>
      <p>Looks like you haven't added anything yet.</p>
      <a href="<?php echo SITE_URL; ?>/pages/products.php" class="tg-btn tg-btn-primary"><i class="fas fa-shopping-bag me-2"></i>Start Shopping</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
      <!-- Cart Items -->
      <div class="col-lg-8">
        <div class="tg-cart-table" id="cartContainer">
          <div style="padding:16px 20px;border-bottom:2px solid var(--tg-border);display:flex;justify-content:space-between;align-items:center">
            <h6 class="fw-700 mb-0"><i class="fas fa-shopping-cart me-2 text-accent"></i>Cart Items (<?php echo count($cartItems); ?>)</h6>
            <button class="tg-btn tg-btn-sm" style="background:var(--tg-bg);color:var(--tg-danger);border:1px solid var(--tg-danger)" onclick="clearCart()"><i class="fas fa-trash me-1"></i>Clear Cart</button>
          </div>
          <?php foreach ($cartItems as $item):
            $price = $item['discount_price'] ?: $item['price'];
            $img   = $item['image'] ? SITE_URL.'/'.$item['image'] : SITE_URL.'/assets/images/no-image.png';
          ?>
          <div class="tg-cart-item" id="cartItem-<?php echo $item['id']; ?>">
            <img src="<?php echo $img; ?>" alt="<?php echo sanitize($item['name']); ?>" class="tg-cart-img">
            <div class="tg-cart-info flex-grow-1">
              <h6><?php echo sanitize($item['name']); ?></h6>
              <small class="text-accent fw-600"><?php echo formatPrice($price); ?></small>
              <?php if ($item['discount_price']): ?><small class="text-muted text-decoration-line-through ms-1"><?php echo formatPrice($item['price']); ?></small><?php endif; ?>
            </div>
            <div class="tg-qty-stepper" data-stock="<?php echo $item['stock']; ?>" data-cart-id="<?php echo $item['id']; ?>">
              <button class="qty-minus">−</button>
              <span class="qty-display"><?php echo $item['quantity']; ?></span>
              <button class="qty-plus">+</button>
              <input type="hidden" class="qty-input" value="<?php echo $item['quantity']; ?>">
            </div>
            <div class="ms-3 text-end">
              <div class="fw-700 text-accent" style="min-width:80px"><?php echo formatPrice($price * $item['quantity']); ?></div>
              <button class="btn btn-sm text-danger mt-1" style="font-size:.78rem;padding:2px 8px" onclick="removeItem(<?php echo $item['id']; ?>)"><i class="fas fa-times me-1"></i>Remove</button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <!-- Continue Shopping -->
        <div class="mt-3">
          <a href="<?php echo SITE_URL; ?>/pages/products.php" class="tg-btn tg-btn-outline tg-btn-sm"><i class="fas fa-arrow-left me-2"></i>Continue Shopping</a>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="col-lg-4">
        <div class="tg-order-summary-card">
          <h6 class="fw-700 mb-3"><i class="fas fa-receipt me-2 text-accent"></i>Order Summary</h6>
          <div class="tg-summary-row"><span>Subtotal</span><span id="summarySubtotal"><?php echo formatPrice($subtotal); ?></span></div>
          <div class="tg-summary-row"><span>Shipping</span><span id="summaryShipping"><?php echo $shipping == 0 ? '<span class="text-success">Free</span>' : formatPrice($shipping); ?></span></div>
          <div class="tg-summary-row" id="discountRow" style="<?php echo 'display:none'; ?>"><span class="text-success">Coupon Discount</span><span class="text-success" id="discountAmount">-৳0</span></div>
          <div class="tg-summary-row total"><span>Total</span><span id="summaryTotal"><?php echo formatPrice($total); ?></span></div>
          <!-- Coupon -->
          <div class="mt-3">
            <div class="d-flex gap-2">
              <input type="text" id="couponInput" class="tg-input" placeholder="Coupon code" style="flex:1">
              <button class="tg-btn tg-btn-dark tg-btn-sm" onclick="applyCoupon()">Apply</button>
            </div>
            <div id="couponMsg" class="mt-1" style="font-size:.78rem"></div>
          </div>
          <a href="<?php echo SITE_URL; ?>/pages/checkout.php" class="tg-btn tg-btn-primary tg-btn-block tg-btn-lg mt-4"><i class="fas fa-lock me-2"></i>Proceed to Checkout</a>
          <!-- Payment icons -->
          <div class="tg-payment-icons mt-3 justify-content-center">
            <span class="tg-payment-icon"><i class="fab fa-cc-visa"></i></span>
            <span class="tg-payment-icon"><i class="fab fa-cc-mastercard"></i></span>
            <span class="tg-payment-icon">bKash</span>
            <span class="tg-payment-icon">Nagad</span>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
const siteUrl = '<?php echo SITE_URL; ?>';
let discount = 0;

// Qty stepper → update cart
document.querySelectorAll('.tg-qty-stepper').forEach(stepper => {
  const minus = stepper.querySelector('.qty-minus');
  const plus  = stepper.querySelector('.qty-plus');
  const disp  = stepper.querySelector('.qty-display');
  const cartId = stepper.dataset.cartId;
  let qty = parseInt(disp.textContent);
  if (!cartId) return;
  minus.addEventListener('click', () => { if (qty > 1) { qty--; disp.textContent = qty; updateCart(cartId, qty); } });
  plus.addEventListener('click', () => { const max = parseInt(stepper.dataset.stock)||99; if (qty < max) { qty++; disp.textContent = qty; updateCart(cartId, qty); } });
});

async function updateCart(cartId, qty) {
  const res = await TG.post(`${siteUrl}/api/cart/update_cart.php`, {cart_id: cartId, quantity: qty});
  if (res.success) { TG.updateCartBadge(res.cart_count); refreshSummary(); }
}

async function removeItem(cartId) {
  const res = await TG.post(`${siteUrl}/api/cart/remove_cart.php`, {cart_id: cartId});
  if (res.success) { document.getElementById(`cartItem-${cartId}`)?.remove(); TG.updateCartBadge(res.cart_count); refreshSummary(); TG.toast('Item removed','info'); if (res.cart_count === 0) location.reload(); }
}

async function clearCart() {
  if (!confirm('Clear all items from cart?')) return;
  const res = await TG.post(`${siteUrl}/api/cart/remove_cart.php`, {clear: 1});
  if (res.success) { TG.updateCartBadge(0); location.reload(); }
}

async function applyCoupon() {
  const code = document.getElementById('couponInput').value.trim();
  if (!code) return;
  const subtotal = parseFloat('<?php echo $subtotal; ?>');
  const res = await TG.post(`${siteUrl}/api/coupons/apply.php`, {code, subtotal});
  const msg = document.getElementById('couponMsg');
  if (res.success) {
    discount = res.discount;
    msg.innerHTML = `<span class='text-success'><i class='fas fa-check-circle me-1'></i>${res.message}</span>`;
    document.getElementById('discountRow').style.display = 'flex';
    document.getElementById('discountAmount').textContent = `-৳${discount.toFixed(2)}`;
    refreshSummary();
  } else {
    msg.innerHTML = `<span class='text-danger'><i class='fas fa-times-circle me-1'></i>${res.message}</span>`;
  }
}

function refreshSummary() {
  // Simple client-side refresh — a full reload would be more accurate
  location.reload();
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
