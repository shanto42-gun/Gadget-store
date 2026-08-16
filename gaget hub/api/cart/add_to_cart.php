<?php
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');

$productId = intval($_POST['product_id'] ?? 0);
$qty       = max(1, intval($_POST['quantity'] ?? 1));

if (!$productId) { echo json_encode(['success'=>false,'message'=>'Invalid product.']); exit; }

// Check product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=? AND status='active' AND stock>0");
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) { echo json_encode(['success'=>false,'message'=>'Product not available.']); exit; }

if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $check = $pdo->prepare("SELECT * FROM cart WHERE user_id=? AND product_id=?");
    $check->execute([$userId, $productId]);
    $existing = $check->fetch();
    if ($existing) {
        $newQty = min($product['stock'], $existing['quantity'] + $qty);
        $pdo->prepare("UPDATE cart SET quantity=?, updated_at=NOW() WHERE id=?")->execute([$newQty, $existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?)")->execute([$userId, $productId, $qty]);
    }
    $countStmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id=?");
    $countStmt->execute([$userId]);
} else {
    $sid = session_id();
    $check = $pdo->prepare("SELECT * FROM cart WHERE session_id=? AND product_id=?");
    $check->execute([$sid, $productId]);
    $existing = $check->fetch();
    if ($existing) {
        $newQty = min($product['stock'], $existing['quantity'] + $qty);
        $pdo->prepare("UPDATE cart SET quantity=?, updated_at=NOW() WHERE id=?")->execute([$newQty, $existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?,?,?)")->execute([$sid, $productId, $qty]);
    }
    $countStmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE session_id=?");
    $countStmt->execute([$sid]);
}

$cartCount = (int)$countStmt->fetchColumn();
echo json_encode(['success'=>true,'message'=>'Added to cart!','cart_count'=>$cartCount]);
