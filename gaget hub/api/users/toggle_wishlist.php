<?php
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['success'=>false,'message'=>'Login required.','redirect'=>SITE_URL.'/pages/login.php']); exit; }

$productId = intval($_POST['product_id'] ?? 0);
if (!$productId) { echo json_encode(['success'=>false,'message'=>'Invalid product.']); exit; }

$check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id=? AND product_id=?");
$check->execute([$_SESSION['user_id'], $productId]);
$existing = $check->fetch();

if ($existing) {
    $pdo->prepare("DELETE FROM wishlist WHERE user_id=? AND product_id=?")->execute([$_SESSION['user_id'], $productId]);
    echo json_encode(['success'=>true,'wishlisted'=>false,'message'=>'Removed from wishlist.']);
} else {
    $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?,?)")->execute([$_SESSION['user_id'], $productId]);
    echo json_encode(['success'=>true,'wishlisted'=>true,'message'=>'Added to wishlist!']);
}
