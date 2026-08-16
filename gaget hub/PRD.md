# 📋 Product Requirements Document (PRD)
## TechGadget Store — E-Commerce Web Application

**Version:** 1.0  
**Date:** March 2026  
**Status:** ✅ Implemented  

---

## 1. Overview

### 1.1 Product Summary
**TechGadget Store** is a full-stack e-commerce web application for buying and selling gadgets online. It targets tech-savvy customers in Bangladesh and provides a seamless shopping experience from product discovery to order delivery, alongside a comprehensive admin management system.

### 1.2 Goals
- Provide customers with an intuitive, fast, and mobile-friendly shopping experience
- Enable admins to manage the entire store — products, orders, users, and settings — from a single dashboard
- Support multiple payment methods common in the Bangladeshi market (COD, bKash, Nagad)
- Maintain a secure, scalable PHP/MySQL codebase

---

## 2. User Personas

### 2.1 Customer (Shopper)
- Age: 18–40, tech enthusiast
- Needs: Browse gadgets, compare products, place orders, track deliveries
- Pain points: Complicated checkout, no order visibility after purchase

### 2.2 Store Admin
- Role: Owner / Manager
- Needs: Manage inventory, process orders, view revenue, run promotions
- Pain points: Manual order tracking, no centralized dashboard

---

## 3. Functional Requirements

### 3.1 Authentication & Accounts

| ID | Requirement | Status |
|----|-------------|--------|
| A1 | User registration with name, email, password | ✅ |
| A2 | User login / logout | ✅ |
| A3 | Forgot password via reset token | ✅ |
| A4 | Admin login (separate from users) | ✅ |
| A5 | Session management with role-based access | ✅ |
| A6 | Profile editing (name, phone, address, avatar) | ✅ |

### 3.2 Product Catalog

| ID | Requirement | Status |
|----|-------------|--------|
| P1 | Product listing with pagination | ✅ |
| P2 | Filter by category, price range, brand, rating | ✅ |
| P3 | Sort by price, rating, newest, popularity | ✅ |
| P4 | Full-text product search | ✅ |
| P5 | Product detail page with image gallery | ✅ |
| P6 | Product specifications (JSON-stored) | ✅ |
| P7 | Featured, Trending, New Arrival, Best Seller tags | ✅ |
| P8 | Stock availability display | ✅ |
| P9 | Related products section | ✅ |

### 3.3 Shopping Cart

| ID | Requirement | Status |
|----|-------------|--------|
| C1 | Add to cart (guest + logged-in users) | ✅ |
| C2 | Session-based cart for guests, merged on login | ✅ |
| C3 | Update quantity / remove items | ✅ |
| C4 | Cart count in navigation | ✅ |
| C5 | Cart subtotal with real-time updates | ✅ |

### 3.4 Checkout & Orders

| ID | Requirement | Status |
|----|-------------|--------|
| O1 | Checkout form (name, phone, address, city) | ✅ |
| O2 | Payment method: COD, bKash, Nagad, Card | ✅ |
| O3 | Coupon / discount code application | ✅ |
| O4 | Shipping cost calculation | ✅ |
| O5 | Order confirmation page with order number | ✅ |
| O6 | Order history for logged-in users | ✅ |
| O7 | Order detail page with item breakdown | ✅ |
| O8 | Order status tracking (pending → delivered) | ✅ |

### 3.5 Wishlist & Reviews

| ID | Requirement | Status |
|----|-------------|--------|
| W1 | Add / remove products from wishlist | ✅ |
| W2 | Wishlist displayed in user dashboard | ✅ |
| R1 | Submit product reviews with rating and title | ✅ |
| R2 | Reviews displayed on product detail page | ✅ |
| R3 | Average rating shown on product cards | ✅ |

### 3.6 Admin Panel

| ID | Requirement | Status |
|----|-------------|--------|
| AD1 | Dashboard with stats: orders today, revenue, users, stock alerts | ✅ |
| AD2 | Product management: add, edit, delete, image upload | ✅ |
| AD3 | Category management: add, edit, delete | ✅ |
| AD4 | Order management: view all orders, update status | ✅ |
| AD5 | User management: view users, block/unblock | ✅ |
| AD6 | Coupon management: create, toggle, delete | ✅ |
| AD7 | Site settings: name, logo, currency, shipping, social links, payment numbers | ✅ |
| AD8 | Low stock and out-of-stock alerts | ✅ |

---

## 4. Non-Functional Requirements

| Category | Requirement |
|----------|-------------|
| **Performance** | Pages load under 2 seconds on local server |
| **Responsiveness** | Fully mobile-responsive (320px to 1920px) |
| **Security** | bcrypt password hashing, PDO prepared statements, input sanitization |
| **Scalability** | MySQL with indexed columns for fast queries |
| **Maintainability** | Modular PHP includes (header, footer, functions), RESTful API layer |
| **Compatibility** | Chrome, Firefox, Edge, Safari (modern versions) |

---

## 5. System Architecture

```
┌─────────────────────────────────────────────┐
│               Browser (Client)              │
│  HTML + CSS + Vanilla JS + MDB UI Kit       │
└──────────────────┬──────────────────────────┘
                   │ HTTP
┌──────────────────▼──────────────────────────┐
│           Apache Web Server (XAMPP)         │
│                                             │
│  ┌──────────────┐   ┌────────────────────┐  │
│  │  PHP Pages   │   │    REST API        │  │
│  │  /pages/     │   │    /api/           │  │
│  │  /admin/     │   │  (JSON responses)  │  │
│  └──────┬───────┘   └────────┬───────────┘  │
│         │                   │               │
│  ┌──────▼───────────────────▼───────────┐   │
│  │         includes/config.php          │   │
│  │      PDO MySQL Connection            │   │
│  └──────────────────┬───────────────────┘   │
└─────────────────────┼───────────────────────┘
                      │
┌─────────────────────▼───────────────────────┐
│           MySQL Database                    │
│           techgadget_db                     │
│  12 Tables: users, products, orders...      │
└─────────────────────────────────────────────┘
```

---

## 6. API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/cart/add` | Add item to cart |
| POST | `/api/cart/update` | Update cart quantity |
| POST | `/api/cart/remove` | Remove cart item |
| GET | `/api/products/search` | Search products |
| POST | `/api/reviews/submit` | Submit a review |
| POST | `/api/coupons/apply` | Apply coupon code |
| GET | `/api/orders/status` | Get order status |
| POST | `/api/users/wishlist` | Toggle wishlist item |

---

## 7. Database Schema Summary

```
settings       → Site configuration (1 row)
admins         → Admin accounts
users          → Customer accounts
categories     → Product categories (6 seeded)
products       → Product catalog (8 seeded)
cart           → Cart items (user + session)
coupons        → Discount codes (3 seeded)
orders         → Customer orders
order_items    → Items within each order
reviews        → Product reviews
wishlist       → User wishlist items
notifications  → User notifications
```

---

## 8. Pages Inventory

### Customer Pages (13)
| Page | Route |
|------|-------|
| Homepage | `/index.php` |
| Products | `/pages/products.php` |
| Product Detail | `/pages/product-detail.php?slug=...` |
| Cart | `/pages/cart.php` |
| Checkout | `/pages/checkout.php` |
| Order Success | `/pages/order-success.php` |
| Orders | `/pages/orders.php` |
| Order Detail | `/pages/order-detail.php?id=...` |
| Dashboard | `/pages/dashboard.php` |
| Profile | `/pages/profile.php` |
| Login | `/pages/login.php` |
| Signup | `/pages/signup.php` |
| Forgot Password | `/pages/forgot-password.php` |

### Admin Pages (10)
| Page | Route |
|------|-------|
| Dashboard | `/admin/index.php` |
| Products | `/admin/products.php` |
| Add/Edit Product | `/admin/add-product.php` |
| Categories | `/admin/categories.php` |
| Orders | `/admin/orders.php` |
| Users | `/admin/users.php` |
| Coupons | `/admin/coupons.php` |
| Settings | `/admin/settings.php` |
| Login | `/admin/login.php` |
| Logout | `/admin/logout.php` |

---

## 9. Future Enhancements (v2.0)

- [ ] Email notifications (order confirmation, shipping updates)
- [ ] SMS integration (bKash/Nagad payment verification)
- [ ] Product image multiple upload with gallery
- [ ] Advanced reporting & analytics charts
- [ ] Inventory management with restocking alerts
- [ ] Customer loyalty points system
- [ ] Multi-vendor support
- [ ] PWA (Progressive Web App) support

---

## 10. Acceptance Criteria

- ✅ All 12 database tables created and seeded
- ✅ Customer can register, login, browse, add to cart, and checkout
- ✅ Admin can login, manage products/orders/users/settings
- ✅ Site is responsive on mobile and desktop
- ✅ Passwords are securely hashed
- ✅ All pages are accessible via correct URLs on localhost
