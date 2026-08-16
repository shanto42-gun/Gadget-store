<?php
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');

$category  = intval($_GET['category'] ?? 0);
$filter    = sanitize($_GET['filter'] ?? '');
$search    = sanitize($_GET['q'] ?? '');
$sort      = sanitize($_GET['sort'] ?? 'popular');
$limit     = min(50, intval($_GET['limit'] ?? 8));
$offset    = intval($_GET['offset'] ?? 0);

$where = ["p.status = 'active'", "p.stock > 0"];
$params = [];
if ($category) { $where[] = "p.category_id = ?"; $params[] = $category; }
if ($filter === 'trending')    { $where[] = "p.trending = 1"; }
if ($filter === 'best_seller') { $where[] = "p.best_seller = 1"; }
if ($filter === 'new_arrival') { $where[] = "p.new_arrival = 1"; }
if ($search) { $where[] = "(p.name LIKE ? OR p.brand LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

$orderBy = match($sort) {
    'price_asc'  => 'COALESCE(p.discount_price,p.price) ASC',
    'price_desc' => 'COALESCE(p.discount_price,p.price) DESC',
    'newest'     => 'p.created_at DESC',
    'rating'     => 'p.rating DESC',
    default      => 'p.sold_count DESC, p.rating DESC',
};

$whereStr = implode(' AND ', $where);
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE $whereStr ORDER BY $orderBy LIMIT $limit OFFSET $offset");
$stmt->execute($params);

echo json_encode(['success'=>true,'products'=>$stmt->fetchAll()]);
