<?php
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['success'=>false,'message'=>'Login required.']); exit; }

$orderId = intval($_POST['order_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=? AND status='pending'");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) { echo json_encode(['success'=>false,'message'=>'Order not found or cannot be cancelled.']); exit; }

$pdo->prepare("UPDATE orders SET status='cancelled' WHERE id=?")->execute([$orderId]);
// Restore stock
$items = $pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
$items->execute([$orderId]); $items = $items->fetchAll();
foreach ($items as $item) {
    $pdo->prepare("UPDATE products SET stock=stock+?, sold_count=GREATEST(0,sold_count-?) WHERE id=?")->execute([$item['quantity'],$item['quantity'],$item['product_id']]);
}
echo json_encode(['success'=>true,'message'=>'Order cancelled successfully.']);
