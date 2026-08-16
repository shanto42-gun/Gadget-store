<?php
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');

$cartId = intval($_POST['cart_id'] ?? 0);
$qty    = max(1, intval($_POST['quantity'] ?? 1));
if (!$cartId) { echo json_encode(['success'=>false,'message'=>'Invalid.']); exit; }

// Get product stock to validate
$stmt = $pdo->prepare("SELECT c.*, p.stock FROM cart c JOIN products p ON c.product_id=p.id WHERE c.id=?");
$stmt->execute([$cartId]);
$item = $stmt->fetch();
if (!$item) { echo json_encode(['success'=>false,'message'=>'Item not found.']); exit; }

$qty = min($qty, $item['stock']);
$pdo->prepare("UPDATE cart SET quantity=?, updated_at=NOW() WHERE id=?")->execute([$qty, $cartId]);

if (isLoggedIn()) {
    $cnt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id=?"); $cnt->execute([$_SESSION['user_id']]);
} else {
    $cnt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE session_id=?"); $cnt->execute([session_id()]);
}
echo json_encode(['success'=>true,'cart_count'=>(int)$cnt->fetchColumn()]);
