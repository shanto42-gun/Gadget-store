<?php
require_once __DIR__ . '/db.php';

// ─── Auth ────────────────────────────────────────────────────────────────────

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo '<meta http-equiv="refresh" content="0;url=' . SITE_URL . '/pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']) . '">';
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        http_response_code(401);
        echo '<meta http-equiv="refresh" content="0;url=' . SITE_URL . '/admin/login.php">';
        exit;
    }
}

function currentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function currentAdmin() {
    global $pdo;
    if (!isAdmin()) return null;
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

// ─── Redirect & Response ──────────────────────────────────────────────────────

function redirect($url) {
    header("Location: $url");
    exit;
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function success($message, $data = []) {
    jsonResponse(array_merge(['success' => true, 'message' => $message], $data));
}

function error($message, $code = 400) {
    jsonResponse(['success' => false, 'message' => $message], $code);
}

// ─── Sanitization ─────────────────────────────────────────────────────────────

function sanitize($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

function slug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}

// ─── Formatting ─────────────────────────────────────────────────────────────

function formatPrice($amount) {
    return '৳' . number_format($amount, 2);
}

function discountPercent($price, $discountPrice) {
    if ($discountPrice && $price > 0) {
        return round((($price - $discountPrice) / $price) * 100);
    }
    return 0;
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return round($diff/60) . ' min ago';
    if ($diff < 86400) return round($diff/3600) . ' hrs ago';
    if ($diff < 604800) return round($diff/86400) . ' days ago';
    return date('d M Y', $time);
}

function generateOrderNumber() {
    return 'TGS-' . strtoupper(substr(uniqid(), -6)) . '-' . date('y');
}

function starRating($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="fas fa-star text-warning"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
        } else {
            $html .= '<i class="far fa-star text-warning"></i>';
        }
    }
    return $html;
}

// ─── File Upload ─────────────────────────────────────────────────────────────

function uploadImage($file, $folder = 'products') {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($file['type'], $allowed)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, WebP, GIF allowed.'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File too large. Max 5MB.'];
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dir = UPLOAD_PATH . $folder . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        return ['success' => true, 'path' => 'uploads/' . $folder . '/' . $filename];
    }
    return ['success' => false, 'message' => 'Upload failed.'];
}

function productImage($path) {
    if ($path && file_exists(__DIR__ . '/../' . $path)) {
        return SITE_URL . '/' . $path;
    }
    return SITE_URL . '/assets/images/no-image.png';
}

// ─── Site Settings ───────────────────────────────────────────────────────────

function getSettings() {
    global $pdo;
    static $settings = null;
    if ($settings === null) {
        try {
            // Standard single-row format
            $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
            $settings = $stmt->fetch() ?: [];
            
            // If it looks like key-value format (has setting_key), transform it
            if (isset($settings['setting_key'])) {
                $stmt2 = $pdo->query("SELECT setting_key, setting_value FROM settings");
                $rows = $stmt2->fetchAll();
                $settings = [];
                foreach ($rows as $row) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            }
        } catch (Exception $e) {
            $settings = [];
        }
        // Defaults
        $settings += [
            'site_name'     => 'TechGadget Store',
            'site_email'    => 'support@techgadget.com',
            'site_phone'    => '+880 1700-000000',
            'site_address'  => 'Dhaka, Bangladesh',
            'shipping_cost' => 60,
            'bkash_number'  => '01700-000000',
            'nagad_number'  => '01700-000001',
            'currency'      => 'BDT',
            'currency_symbol'=> '৳',
        ];
    }
    return $settings;
}

// ─── Cart ─────────────────────────────────────────────────────────────────────

function getCartCount() {
    global $pdo;
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $sessionId = session_id();
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE session_id = ?");
        $stmt->execute([$sessionId]);
    }
    return (int) $stmt->fetchColumn();
}

function getCartItems() {
    global $pdo;
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.discount_price, p.image, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.discount_price, p.image, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ?");
        $stmt->execute([session_id()]);
    }
    return $stmt->fetchAll();
}

function cartTotal() {
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        $price = $item['discount_price'] ?? $item['price'];
        $total += $price * $item['quantity'];
    }
    return $total;
}

// ─── Wishlist ─────────────────────────────────────────────────────────────────

function isWishlisted($productId) {
    global $pdo;
    if (!isLoggedIn()) return false;
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $productId]);
    return (bool) $stmt->fetchColumn();
}

// ─── Products helpers ─────────────────────────────────────────────────────────

function getCategories($activeOnly = true) {
    global $pdo;
    $sql = "SELECT * FROM categories";
    if ($activeOnly) $sql .= " WHERE status = 1";
    $sql .= " ORDER BY sort_order ASC";
    return $pdo->query($sql)->fetchAll();
}

function getFeaturedProducts($limit = 8) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'active' AND stock > 0 ORDER BY featured DESC, sold_count DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getProductBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ? AND p.status = 'active'");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getRelatedProducts($categoryId, $excludeId, $limit = 4) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND status = 'active' ORDER BY sold_count DESC LIMIT ?");
    $stmt->execute([$categoryId, $excludeId, $limit]);
    return $stmt->fetchAll();
}

// ─── CSRF ─────────────────────────────────────────────────────────────────────

function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
