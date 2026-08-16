<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$pageTitle = 'Checkout';
$cartItems = getCartItems();
if (empty($cartItems)) redirect(SITE_URL . '/pages/cart.php');
$settings  = getSettings();
$user      = currentUser();
$subtotal  = cartTotal();
$shipping  = (float)$settings['shipping_cost'];
if ($subtotal >= 2000) $shipping = 0;
$total = $subtotal + $shipping;
include __DIR__ . '/../includes/header.php';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<section class="tg-page-banner">
  <div class="container-xl">
    <h1>Checkout</h1>
    <div class="tg-breadcrumb"><a href="<?php echo SITE_URL; ?>/">Home</a><span class="sep">/</span><a href="<?php echo SITE_URL; ?>/pages/cart.php">Cart</a><span class="sep">/</span><span>Checkout</span></div>
  </div>
</section>
<!-- Checkout steps -->
<div style="background:#fff;border-bottom:1px solid var(--tg-border);padding:12px 0">
  <div class="container-xl">
    <div class="d-flex align-items-center gap-3" style="font-size:.82rem;font-weight:600">
      <span class="text-muted"><i class="fas fa-shopping-cart me-1"></i>Cart</span>
      <i class="fas fa-chevron-right text-muted" style="font-size:.65rem"></i>
      <span class="text-accent"><i class="fas fa-truck me-1"></i>Checkout</span>
      <i class="fas fa-chevron-right text-muted" style="font-size:.65rem"></i>
      <span class="text-muted"><i class="fas fa-check-circle me-1"></i>Confirmation</span>
    </div>
  </div>
</div>
<section class="tg-section-sm">
  <div class="container-xl">
    <div class="row g-4">
      <!-- Shipping Form -->
      <div class="col-lg-7">
        <form id="checkoutForm">
        <div style="background:#fff;border-radius:var(--tg-radius);padding:28px;box-shadow:var(--tg-shadow)">
          <h5 class="fw-700 mb-4"><i class="fas fa-map-marker-alt me-2 text-accent"></i>Shipping Information</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="tg-input-group">
                <label>Full Name *</label>
                <input name="name" class="tg-input" value="<?php echo sanitize($user['name']); ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="tg-input-group">
                <label>Phone Number *</label>
                <input name="phone" class="tg-input" placeholder="+880 1700-000000" value="<?php echo sanitize($user['phone']); ?>" required>
              </div>
            </div>
            <div class="col-12">
              <div class="tg-input-group">
                <label>Email Address</label>
                <input name="email" type="email" class="tg-input" value="<?php echo sanitize($user['email']); ?>">
              </div>
            </div>
            <div class="col-md-8">
              <div class="tg-input-group">
                <label>Full Address *</label>
                <input name="address" class="tg-input" placeholder="House, Road, Area…" value="<?php echo sanitize($user['address'] ?? ''); ?>" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="tg-input-group">
                <label>City *</label>
                <input name="city" class="tg-input" placeholder="Dhaka" value="<?php echo sanitize($user['city'] ?? ''); ?>" required>
              </div>
            </div>
            <div class="col-12">
              <div class="tg-input-group">
                <label>Order Notes (optional)</label>
                <textarea name="notes" class="tg-input" rows="2" placeholder="Any special instructions…"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Method -->
        <div style="background:#fff;border-radius:var(--tg-radius);padding:28px;box-shadow:var(--tg-shadow);margin-top:20px">
          <h5 class="fw-700 mb-4"><i class="fas fa-credit-card me-2 text-accent"></i>Payment Method</h5>
          <div class="tg-payment-method">
            <div class="tg-payment-option">
              <input type="radio" name="payment_method" id="pmCod" value="cod" checked>
              <label class="tg-payment-label" for="pmCod"><i class="fas fa-money-bill-wave" style="color:#00c576"></i>Cash on Delivery</label>
            </div>
            <div class="tg-payment-option">
              <input type="radio" name="payment_method" id="pmBkash" value="bkash">
              <label class="tg-payment-label" for="pmBkash"><span style="font-size:1.1rem;font-weight:800;color:#e2136e">bKash</span>Mobile Banking</label>
            </div>
            <div class="tg-payment-option">
              <input type="radio" name="payment_method" id="pmNagad" value="nagad">
              <label class="tg-payment-label" for="pmNagad"><span style="font-size:1.1rem;font-weight:800;color:#f05a28">Nagad</span>Mobile Banking</label>
            </div>
            <div class="tg-payment-option">
              <input type="radio" name="payment_method" id="pmCard" value="card">
              <label class="tg-payment-label" for="pmCard"><i class="fas fa-credit-card" style="color:var(--tg-primary)"></i>Debit/Credit Card</label>
            </div>
          </div>
          <!-- bKash instruction -->
          <div id="bkashInfo" style="display:none;background:#fdf0f7;border:1px solid #e2136e;border-radius:8px;padding:14px;margin-top:16px;font-size:.85rem">
            <strong>bKash Payment:</strong> Send money to <strong><?php echo $settings['bkash_number'] ?: '01700-000000'; ?></strong> and enter the transaction ID below.
            <input type="text" name="payment_ref" class="tg-input mt-2" placeholder="bKash Transaction ID">
          </div>
          <div id="nagadInfo" style="display:none;background:#fff5f0;border:1px solid #f05a28;border-radius:8px;padding:14px;margin-top:16px;font-size:.85rem">
            <strong>Nagad Payment:</strong> Send money to <strong><?php echo $settings['nagad_number'] ?: '01700-000001'; ?></strong> and enter the transaction ID below.
            <input type="text" name="payment_ref" class="tg-input mt-2" placeholder="Nagad Transaction ID">
          </div>
        </div>

        <button type="submit" class="tg-btn tg-btn-primary tg-btn-block tg-btn-lg mt-4" id="placeOrderBtn">
          <i class="fas fa-lock me-2"></i>Place Order – <?php echo formatPrice($total); ?>
        </button>
        </form>
      </div>

      <!-- Order Summary -->
      <div class="col-lg-5">
        <div class="tg-order-summary-card">
          <h6 class="fw-700 mb-3"><i class="fas fa-receipt me-2 text-accent"></i>Your Order (<?php echo count($cartItems); ?> items)</h6>
          <?php foreach ($cartItems as $item):
            $p = $item['discount_price'] ?: $item['price'];
            $img = $item['image'] ? SITE_URL.'/'.$item['image'] : SITE_URL.'/assets/images/no-image.png';
          ?>
          <div class="d-flex align-items-center gap-3 py-2 border-bottom">
            <img src="<?php echo $img; ?>" style="width:52px;height:52px;border-radius:8px;object-fit:cover;flex-shrink:0">
            <div class="flex-grow-1">
              <div style="font-size:.82rem;font-weight:600"><?php echo sanitize($item['name']); ?></div>
              <div style="font-size:.75rem;color:var(--tg-text-muted)">Qty: <?php echo $item['quantity']; ?></div>
            </div>
            <span style="font-size:.88rem;font-weight:700;color:var(--tg-accent)"><?php echo formatPrice($p * $item['quantity']); ?></span>
          </div>
          <?php endforeach; ?>
          <div class="mt-3">
            <div class="tg-summary-row"><span>Subtotal</span><span><?php echo formatPrice($subtotal); ?></span></div>
            <div class="tg-summary-row"><span>Shipping</span><span><?php echo $shipping == 0 ? '<span class="text-success fw-600">Free</span>' : formatPrice($shipping); ?></span></div>
            <div class="tg-summary-row total"><span>Total</span><span><?php echo formatPrice($total); ?></span></div>
          </div>
          <!-- Coupon input -->
          <div class="mt-3">
            <div class="d-flex gap-2">
              <input type="text" id="couponCode" class="tg-input" placeholder="Coupon code" style="flex:1">
              <button class="tg-btn tg-btn-dark tg-btn-sm" onclick="applyCouponChk()">Apply</button>
            </div>
            <div id="couponMsgChk" style="font-size:.78rem;margin-top:6px"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
const siteUrl = '<?php echo SITE_URL; ?>';
// Payment method toggle
document.querySelectorAll('input[name=payment_method]').forEach(r => {
  r.addEventListener('change', () => {
    document.getElementById('bkashInfo').style.display = r.value === 'bkash' ? 'block' : 'none';
    document.getElementById('nagadInfo').style.display = r.value === 'nagad' ? 'block' : 'none';
  });
});

// Coupon on checkout page
async function applyCouponChk() {
  const code = document.getElementById('couponCode').value.trim();
  const subtotal = <?php echo $subtotal; ?>;
  const res = await TG.post(`${siteUrl}/api/coupons/apply.php`, {code, subtotal});
  const msg = document.getElementById('couponMsgChk');
  if (res.success) { msg.innerHTML = `<span class='text-success'><i class='fas fa-check-circle me-1'></i>${res.message}</span>`; }
  else { msg.innerHTML = `<span class='text-danger'>${res.message}</span>`; }
}

// Place order
document.getElementById('checkoutForm').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('placeOrderBtn');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Placing Order…';
  const fd = new FormData(e.target);
  const couponCode = document.getElementById('couponCode').value.trim();
  if (couponCode) fd.append('coupon_code', couponCode);
  const res = await TG.post(`${siteUrl}/api/orders/place_order.php`, fd);
  if (res.success) {
    window.location = `${siteUrl}/pages/order-success.php?order=${res.order_number}`;
  } else {
    TG.toast(res.message || 'Failed to place order.', 'error');
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-lock me-2"></i>Place Order';
  }
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
