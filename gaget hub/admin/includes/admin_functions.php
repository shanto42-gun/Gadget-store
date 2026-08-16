<?php
// Admin shared includes helper
define('ADMIN_PATH', __DIR__);

require_once __DIR__ . '/../../includes/functions.php';

function requireAdminPanel() {
    if (!isAdmin()) {
        http_response_code(401);
        echo '<meta http-equiv="refresh" content="0;url=' . SITE_URL . '/admin/login.php">';
        exit;
    }
}

function adminNavLink($href, $icon, $label, $active = false) {
    $cls = $active ? 'active' : '';
    return "<a href='$href' class='tg-admin-nav-link $cls'><i class='$icon'></i>$label</a>";
}

function getAdminStats() {
    global $pdo;
    $stats = [];
    $stats['orders_today'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn();
    $stats['revenue_today'] = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled'")->fetchColumn();
    $stats['revenue_month'] = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW()) AND status!='cancelled'")->fetchColumn();
    $stats['total_orders'] = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['total_products'] = $pdo->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn();
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['low_stock'] = $pdo->query("SELECT COUNT(*) FROM products WHERE stock<=5 AND stock>0")->fetchColumn();
    $stats['out_of_stock'] = $pdo->query("SELECT COUNT(*) FROM products WHERE stock=0")->fetchColumn();
    return $stats;
}
