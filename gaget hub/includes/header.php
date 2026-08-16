<?php
require_once __DIR__ . '/functions.php';
$settings = getSettings();
$cartCount = getCartCount();
$user = currentUser();
$categories = getCategories();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' | ' : ''; ?><?php echo $settings['site_name']; ?></title>
<meta name="description" content="<?php echo isset($pageDesc) ? sanitize($pageDesc) : 'Shop the latest gadgets at the best prices on ' . $settings['site_name']; ?>">
<!-- MDB 6 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="<?php echo SITE_URL; ?>/assets/css/style.css?v=1.1" rel="stylesheet">
<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>
<body>

<!-- ═══ TOP HEADER ═══════════════════════════════════════════════════════════ -->
<header class="tg-header">


  <!-- Main Nav -->
  <nav class="navbar navbar-expand-xl tg-navbar">
    <div class="container-xl">
      <!-- Brand -->
      <a class="navbar-brand tg-brand" href="<?php echo SITE_URL; ?>/">
        <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="Logo icon" class="tg-logo">
        <span class="tg-brand-text">Tech<span>Gadget</span></span>
      </a>

      <!-- Mobile: search + cart + hamburger -->
      <div class="d-flex align-items-center gap-2 d-xl-none ms-auto">
        <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn tg-btn-icon-sm">
          <i class="fas fa-search"></i>
        </a>
        <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="btn tg-btn-icon-sm position-relative">
          <i class="fas fa-shopping-cart"></i>
          <span class="tg-cart-badge" id="cartBadge" <?php echo $cartCount == 0 ? 'style="display:none"' : ''; ?>><?php echo $cartCount; ?></span>
        </a>
        <button class="navbar-toggler tg-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#navbarMain">
          <i class="fas fa-bars"></i>
        </button>
      </div>

      <!-- Collapse -->
      <div class="collapse navbar-collapse" id="navbarMain">

        <!-- Search bar (desktop) -->
        <form class="tg-search-form d-none d-xl-flex mx-3 flex-grow-1" action="<?php echo SITE_URL; ?>/pages/products.php" method="GET">
          <div class="tg-search-cat">
            <select name="category" class="form-select tg-select">
              <option value="">All</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <input type="text" name="q" class="form-control tg-search-input" placeholder="Search gadgets, brands, products..." value="<?php echo isset($_GET['q']) ? sanitize($_GET['q']) : ''; ?>">
          <button type="submit" class="btn tg-search-btn"><i class="fas fa-search"></i></button>
        </form>

        <!-- Nav links -->
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link <?php echo $currentPage=='index.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/">Home</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-mdb-toggle="dropdown">Shop</a>
            <ul class="dropdown-menu tg-dropdown">
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/products.php">All Products</a></li>
              <li><hr class="dropdown-divider"></li>
              <?php foreach ($categories as $cat): ?>
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $cat['id']; ?>"><i class="<?php echo $cat['icon']; ?> me-2"></i><?php echo sanitize($cat['name']); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-mdb-toggle="dropdown">Offers</a>
            <ul class="dropdown-menu tg-dropdown">
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/products.php?filter=trending"><i class="fas fa-fire me-2 text-danger"></i>Trending Now</a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/products.php?filter=best_seller"><i class="fas fa-medal me-2 text-warning"></i>Best Sellers</a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/products.php?filter=new_arrival"><i class="fas fa-star me-2 text-info"></i>New Arrivals</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/#contact">Contact</a></li>
        </ul>

        <!-- Right actions -->
        <ul class="navbar-nav ms-auto align-items-center gap-1">
          <!-- Cart -->
          <li class="nav-item">
            <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="btn tg-btn-icon position-relative" title="Cart">
              <i class="fas fa-shopping-cart"></i>
              <span class="tg-cart-badge" id="cartBadgeDesktop" <?php echo $cartCount == 0 ? 'style="display:none"' : ''; ?>><?php echo $cartCount; ?></span>
            </a>
          </li>
          <?php if (isLoggedIn()): ?>
          <!-- Wishlist -->
          <li class="nav-item">
            <a href="<?php echo SITE_URL; ?>/pages/dashboard.php?tab=wishlist" class="btn tg-btn-icon" title="Wishlist">
              <i class="fas fa-heart"></i>
            </a>
          </li>
          <!-- User dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link tg-avatar-trigger dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownUser" role="button" data-mdb-toggle="dropdown" aria-expanded="false">
              <?php if ($user && $user['avatar']): ?>
                <img src="<?php echo SITE_URL . '/' . $user['avatar']; ?>" alt="Avatar" class="tg-avatar-sm">
              <?php else: ?>
                <div class="tg-avatar-placeholder"><?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?></div>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end tg-dropdown" aria-labelledby="navbarDropdownUser">
              <li class="tg-dropdown-user-info">
                <strong><?php echo sanitize($user['name'] ?? ''); ?></strong>
                <small><?php echo sanitize($user['email'] ?? ''); ?></small>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/orders.php"><i class="fas fa-box me-2"></i>My Orders</a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/profile.php"><i class="fas fa-user-edit me-2"></i>Edit Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/pages/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
          </li>
          <?php else: ?>
          <li class="nav-item">
            <a href="<?php echo SITE_URL; ?>/pages/login.php" class="btn tg-btn-outline-sm">Login</a>
          </li>
          <li class="nav-item">
            <a href="<?php echo SITE_URL; ?>/pages/signup.php" class="btn tg-btn-primary-sm">Sign Up</a>
          </li>
          <?php endif; ?>
        </ul>
      </div><!-- /collapse -->
    </div>
  </nav>

</header>

<!-- ═══ MOBILE BOTTOM NAV ════════════════════════════════════════════════════ -->
<nav class="tg-mobile-nav d-xl-none">
  <a href="<?php echo SITE_URL; ?>/" class="tg-mobile-nav-item <?php echo $currentPage=='index.php' ? 'active' : ''; ?>">
    <i class="fas fa-home"></i><span>Home</span>
  </a>
  <a href="<?php echo SITE_URL; ?>/pages/products.php" class="tg-mobile-nav-item <?php echo $currentPage=='products.php' ? 'active' : ''; ?>">
    <i class="fas fa-th-large"></i><span>Shop</span>
  </a>
  <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="tg-mobile-nav-item position-relative <?php echo $currentPage=='cart.php' ? 'active' : ''; ?>">
    <i class="fas fa-shopping-cart"></i>
    <span class="tg-cart-badge" id="cartBadgeMobile" <?php echo $cartCount == 0 ? 'style="display:none"' : ''; ?>><?php echo $cartCount; ?></span>
    <span>Cart</span>
  </a>
  <a href="<?php echo isLoggedIn() ? SITE_URL.'/pages/dashboard.php' : SITE_URL.'/pages/login.php'; ?>" class="tg-mobile-nav-item <?php echo in_array($currentPage, ['dashboard.php','profile.php']) ? 'active' : ''; ?>">
    <i class="fas fa-user"></i><span>Account</span>
  </a>
</nav>

<!-- Toast Container -->
<div id="toastContainer" class="tg-toast-container"></div>

<!-- Page content begins -->
<main class="tg-main-content">
