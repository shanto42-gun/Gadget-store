/* TechGadget Store – main.js */
'use strict';

const TG = {
  siteUrl: document.querySelector('meta[name="site-url"]')?.content || '',

  /* ── Toast ─────────────────────────────────────────────────── */
  toast(message, type = 'success', duration = 3500) {
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle', warning: 'fa-exclamation-triangle' };
    const c = document.getElementById('toastContainer');
    if (!c) return;
    const t = document.createElement('div');
    t.className = `tg-toast ${type}`;
    t.innerHTML = `<i class="fas ${icons[type] || icons.info} icon"></i><span>${message}</span>`;
    c.appendChild(t);
    setTimeout(() => { t.classList.add('hiding'); setTimeout(() => t.remove(), 350); }, duration);
  },

  /* ── AJAX POST ─────────────────────────────────────────────── */
  async post(url, data) {
    const fd = data instanceof FormData ? data : (() => { const f = new FormData(); Object.entries(data).forEach(([k, v]) => f.append(k, v)); return f; })();
    const r = await fetch(url, { method: 'POST', body: fd });
    return r.json();
  },

  /* ── Cart ─────────────────────────────────────────────────── */
  updateCartBadge(count) {
    document.querySelectorAll('[id^="cartBadge"]').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? 'flex' : 'none';
    });
  },

  async addToCart(productId, qty = 1, btn = null) {
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...'; }
    try {
      const res = await this.post(`${this.siteUrl}/api/cart/add_to_cart.php`, { product_id: productId, quantity: qty });
      if (res.success) {
        this.toast('Added to cart!', 'success');
        this.updateCartBadge(res.cart_count);
        if (btn) { btn.innerHTML = '<i class="fas fa-check"></i> Added!'; btn.style.background = 'var(--tg-success)'; }
        setTimeout(() => { if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart'; btn.style.background = ''; }}, 2000);
      } else {
        this.toast(res.message || 'Failed to add.', 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart'; }
      }
    } catch (e) {
      this.toast('Network error.', 'error');
      if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart'; }
    }
  },

  async toggleWishlist(productId, btn) {
    try {
      const res = await this.post(`${this.siteUrl}/api/users/toggle_wishlist.php`, { product_id: productId });
      if (res.success) {
        this.toast(res.message, 'success');
        btn.classList.toggle('wishlisted', res.wishlisted);
        btn.querySelector('i').classList.toggle('fas', res.wishlisted);
        btn.querySelector('i').classList.toggle('far', !res.wishlisted);
      } else {
        if (res.redirect) window.location.href = res.redirect;
        else this.toast(res.message, 'error');
      }
    } catch(e) { this.toast('Network error.', 'error'); }
  },

  /* ── Product Grid Loader ──────────────────────────────────── */
  async loadProducts(container, params = {}) {
    if (!container) return;
    container.innerHTML = this.skeletonCards(4);
    const qs = new URLSearchParams(params).toString();
    try {
      const res = await fetch(`${this.siteUrl}/api/products/get_products.php?${qs}`);
      const data = await res.json();
      if (!data.products || data.products.length === 0) {
        container.innerHTML = `<div class="col-12"><div class="tg-empty-state"><div class="tg-empty-icon"><i class="fas fa-box-open"></i></div><h5>No products found</h5><p>Try adjusting your filters.</p></div></div>`;
        return;
      }
      container.innerHTML = data.products.map(p => this.productCardHtml(p)).join('');
      this.bindProductCardEvents(container);
    } catch(e) { container.innerHTML = '<div class="col-12 text-center text-danger">Failed to load products.</div>'; }
  },

  productCardHtml(p) {
    const price = p.discount_price ? `<span class="tg-price-current">৳${parseFloat(p.discount_price).toLocaleString()}</span><span class="tg-price-original">৳${parseFloat(p.price).toLocaleString()}</span>` : `<span class="tg-price-current">৳${parseFloat(p.price).toLocaleString()}</span>`;
    const disc = p.discount_price ? `<span class="tg-badge tg-badge-discount">-${Math.round((p.price-p.discount_price)/p.price*100)}%</span>` : '';
    const newBadge = p.new_arrival == 1 ? '<span class="tg-badge tg-badge-new">New</span>' : '';
    const trending = p.trending == 1 ? '<span class="tg-badge tg-badge-trending">🔥 Hot</span>' : '';
    const stars = this.starsHtml(p.rating);
    const img = p.image ? `${this.siteUrl}/${p.image}` : `${this.siteUrl}/assets/images/no-image.png`;
    const outOfStock = parseInt(p.stock) === 0;
    return `<div class="tg-product-card">
      <div class="tg-product-img-wrap">
        <a href="${this.siteUrl}/pages/product-detail.php?slug=${p.slug}"><img src="${img}" alt="${p.name}" loading="lazy"></a>
        <div class="tg-product-badges">${disc}${newBadge}${trending}</div>
        <div class="tg-product-actions">
          <button class="tg-action-btn wishlist-btn" data-id="${p.id}" title="Wishlist"><i class="far fa-heart"></i></button>
          <a href="${this.siteUrl}/pages/product-detail.php?slug=${p.slug}" class="tg-action-btn" title="Quick view"><i class="fas fa-eye"></i></a>
        </div>
      </div>
      <div class="tg-product-body">
        <div class="tg-product-brand">${p.brand || p.category_name || ''}</div>
        <a href="${this.siteUrl}/pages/product-detail.php?slug=${p.slug}" class="tg-product-name">${p.name}</a>
        <div class="tg-product-rating"><span class="stars">${stars}</span><small>(${p.review_count})</small></div>
        <div class="tg-product-price">${price}</div>
        <button class="tg-add-cart-btn add-cart-btn" data-id="${p.id}" ${outOfStock ? 'disabled' : ''}><i class="fas fa-cart-plus"></i>${outOfStock ? 'Out of Stock' : 'Add to Cart'}</button>
      </div>
    </div>`;
  },

  starsHtml(rating) {
    let h = '';
    for (let i = 1; i <= 5; i++) {
      if (i <= rating) h += '<i class="fas fa-star text-warning"></i>';
      else if (i - 0.5 <= rating) h += '<i class="fas fa-star-half-alt text-warning"></i>';
      else h += '<i class="far fa-star text-warning"></i>';
    }
    return h;
  },

  bindProductCardEvents(container) {
    container.querySelectorAll('.add-cart-btn').forEach(btn => {
      btn.addEventListener('click', () => this.addToCart(btn.dataset.id, 1, btn));
    });
    container.querySelectorAll('.wishlist-btn').forEach(btn => {
      btn.addEventListener('click', () => this.toggleWishlist(btn.dataset.id, btn));
    });
  },

  skeletonCards(n) {
    return Array(n).fill(0).map(() => `<div class="tg-product-card" style="border:none;">
      <div class="tg-skeleton" style="aspect-ratio:1;width:100%;"></div>
      <div style="padding:14px;"><div class="tg-skeleton mb-2" style="height:12px;width:60%;"></div><div class="tg-skeleton mb-3" style="height:16px;"></div><div class="tg-skeleton" style="height:36px;"></div></div>
    </div>`).join('');
  },

  /* ── Newsletter ──────────────────────────────────────────── */
  async subscribeNewsletter(email) {
    this.toast('Thank you for subscribing! 🎉', 'success');
  },

  /* ── Coupon ─────────────────────────────────────────────── */
  async applyCoupon(code, subtotal) {
    const res = await this.post(`${this.siteUrl}/api/coupons/apply.php`, { code, subtotal });
    return res;
  },

  /* ── Quantity Stepper ───────────────────────────────────── */
  initQtySteppers() {
    document.querySelectorAll('.tg-qty-stepper').forEach(stepper => {
      const minus = stepper.querySelector('.qty-minus');
      const plus = stepper.querySelector('.qty-plus');
      const display = stepper.querySelector('.qty-display');
      const input = stepper.querySelector('.qty-input');
      if (!minus || !plus || !display) return;
      let qty = parseInt(display.textContent) || 1;
      minus.addEventListener('click', () => {
        qty = Math.max(1, qty - 1);
        display.textContent = qty;
        if (input) input.value = qty;
        stepper.dispatchEvent(new CustomEvent('change', { detail: { qty } }));
      });
      plus.addEventListener('click', () => {
        const maxStock = parseInt(stepper.dataset.stock) || 99;
        qty = Math.min(maxStock, qty + 1);
        display.textContent = qty;
        if (input) input.value = qty;
        stepper.dispatchEvent(new CustomEvent('change', { detail: { qty } }));
      });
    });
  },

  /* ── Init ───────────────────────────────────────────────── */
  init() {
    // Get site URL from meta tag if available
    const metaUrl = document.querySelector('meta[name="site-url"]');
    if (metaUrl) this.siteUrl = metaUrl.content;

    this.initQtySteppers();

    // Global add-to-cart buttons
    document.querySelectorAll('.add-cart-btn').forEach(btn => {
      btn.addEventListener('click', () => this.addToCart(btn.dataset.id, 1, btn));
    });

    // Global wishlist buttons
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
      btn.addEventListener('click', () => this.toggleWishlist(btn.dataset.id, btn));
    });

    // Newsletter form
    const nlForm = document.getElementById('newsletterForm');
    if (nlForm) {
      nlForm.addEventListener('submit', e => {
        e.preventDefault();
        const email = nlForm.querySelector('input[type=email]').value;
        if (email) this.subscribeNewsletter(email);
      });
    }

    // Password toggle
    document.querySelectorAll('.toggle-password').forEach(btn => {
      btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.classList.toggle('fa-eye');
        btn.classList.toggle('fa-eye-slash');
      });
    });
    // Generic dropdown fallback
    document.querySelectorAll('.nav-item.dropdown > .dropdown-toggle, .nav-item.dropdown > [data-mdb-toggle="dropdown"]').forEach(dropdown => {
      dropdown.addEventListener('click', function(e) {
        e.preventDefault();
        const menu = this.nextElementSibling;
        const isExpanded = menu && menu.classList.contains('show');
        
        // Close all others
        document.querySelectorAll('.tg-dropdown.show').forEach(m => {
          if (m !== menu) {
            m.classList.remove('show');
            if (m.previousElementSibling) m.previousElementSibling.setAttribute('aria-expanded', 'false');
          }
        });

        // Toggle current
        if (menu && menu.classList.contains('dropdown-menu')) {
          menu.classList.toggle('show');
          this.setAttribute('aria-expanded', menu.classList.contains('show') ? 'true' : 'false');
        }
      });
    });

    document.addEventListener('click', function(e) {
      if (!e.target.closest('.nav-item.dropdown')) {
        document.querySelectorAll('.tg-dropdown.show').forEach(m => {
          m.classList.remove('show');
          if (m.previousElementSibling) m.previousElementSibling.setAttribute('aria-expanded', 'false');
        });
      }
    });

    // Product Slider Initialization
    const swiper = new Swiper('.tg-product-slider', {
      slidesPerView: 1,
      spaceBetween: 10,
      loop: true,
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      breakpoints: {
        640: {
          slidesPerView: 2,
          spaceBetween: 20,
        },
        768: {
          slidesPerView: 3,
          spaceBetween: 25,
        },
        1024: {
          slidesPerView: 4,
          spaceBetween: 30,
        },
      },
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      speed: 800,
      effect: 'slide',
    });
  }
};

document.addEventListener('DOMContentLoaded', () => TG.init());
