<img width="1914" height="634" alt="Screenshot 2026-08-17 073107" src="https://github.com/user-attachments/assets/1275ae0c-93ab-496f-865a-959374951a34" />

# 🛒 TechGadget Store

A fully functional, responsive **e-commerce web application** for gadgets — built with PHP, MySQL, and vanilla CSS/JS. Features include product browsing, cart, checkout, order tracking, user dashboard, wishlist, reviews, and a full-featured admin panel.

---

## 🚀 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8+ |
| Database | MySQL (via PDO) |
| Frontend | HTML5, CSS3, Vanilla JS |
| UI Library | MDB UI Kit 6, Font Awesome 6 |
| Fonts | Google Fonts — Inter |
| Server | Apache (XAMPP) |

---

## 📁 Project Structure

```
gaget hub/
├── index.php                  # Homepage (hero, featured, trending products)
├── pages/                     # Customer-facing pages
│   ├── login.php
│   ├── signup.php
│   ├── logout.php
│   ├── forgot-password.php
│   ├── products.php           # Product listing with filters
│   ├── product-detail.php     # Single product with reviews
│   ├── cart.php
│   ├── checkout.php
│   ├── order-success.php
│   ├── orders.php
│   ├── order-detail.php
│   ├── dashboard.php          # User dashboard + wishlist
│   └── profile.php
├── admin/                     # Admin panel (protected)
│   ├── index.php              # Dashboard with stats
│   ├── login.php
│   ├── logout.php
│   ├── products.php
│   ├── add-product.php        # Add / edit product
│   ├── categories.php
│   ├── orders.php
│   ├── users.php
│   ├── coupons.php
│   ├── settings.php
│   └── includes/
│       ├── admin_functions.php
│       ├── admin_header.php
│       └── admin_footer.php
├── api/                       # JSON REST API endpoints
│   ├── cart/
│   ├── orders/
│   ├── products/
│   ├── reviews/
│   ├── users/
│   └── coupons/
├── includes/                  # Shared PHP includes
│   ├── config.php             # DB connection & constants
│   ├── functions.php          # Helper functions
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/style.css
│   └── js/main.js
├── uploads/                   # Product images
└── database/
    └── schema.sql             # Full DB schema + seed data
```

---

## ⚙️ Installation & Setup

### Prerequisites
- XAMPP (Apache + MySQL) installed and running

### Steps

1. **Clone / copy** the project folder into:
   ```
   C:\xampp\htdocs\gaget hub\
   ```

2. **Start XAMPP** — make sure Apache and MySQL are running.

3. **Import the database:**
   ```powershell
   # Run in PowerShell
   Get-Content "C:\xampp\htdocs\gaget hub\database\schema.sql" | C:\xampp\mysql\bin\mysql.exe -u root
   ```
   Or import via **phpMyAdmin** → Import → select `database/schema.sql`

4. **Open the site:**
   - Store: [http://localhost/gaget%20hub/](http://localhost/gaget%20hub/)
   - Admin: [http://localhost/gaget%20hub/admin/](http://localhost/gaget%20hub/admin/)

---

## 🔑 Default Credentials

### Admin Login
| Field | Value |
|-------|-------|
| URL | `http://localhost/gaget hub/admin/login.php` |
| Email | `admin@techgadget.com` |
| Password | `admin123` |

### Customer
Register a new account at `/pages/signup.php`

---

## 🗃️ Database Tables

| Table | Purpose |
|-------|---------|
| `settings` | Site-wide configuration |
| `admins` | Admin accounts |
| `users` | Customer accounts |
| `categories` | Product categories |
| `products` | Product catalog |
| `cart` | Shopping cart (session + user) |
| `coupons` | Discount coupons |
| `orders` | Customer orders |
| `order_items` | Line items per order |
| `reviews` | Product reviews & ratings |
| `wishlist` | User wishlists |
| `notifications` | User notifications |

---

## ✨ Features

### Customer Side
- 🏠 Homepage with hero section, featured & trending products
- 🔍 Product listing with search, category filter, sort & price range
- 📦 Product detail with image gallery, specs, and reviews
- 🛒 Cart (guest + logged-in, session-based merge)
- 💳 Checkout with COD / bKash / Nagad / Card payment
- 📬 Order tracking & order history
- ❤️ Wishlist management
- ⭐ Product reviews & ratings
- 👤 User dashboard & profile editor
- 🔐 Auth: Login, Signup, Forgot Password

### Admin Panel
- 📊 Dashboard with real-time stats (orders, revenue, users, stock)
- 📦 Product management (add/edit/delete, image upload)
- 🗂️ Category management
- 🛍️ Order management (status updates)
- 👥 User management (block/unblock)
- 🏷️ Coupon management
- ⚙️ Site settings (logo, currency, social links, payment info)

---

## 🔒 Security
- Passwords hashed with `password_hash()` (bcrypt)
- PDO prepared statements (SQL injection protection)
- Input sanitization on all user inputs
- Session-based authentication with role separation (admin vs user)

---

## 📄 License
This project is for educational and personal use.
