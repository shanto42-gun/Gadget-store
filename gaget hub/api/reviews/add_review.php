<?php
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn()) { echo json_encode(['success'=>false,'message'=>'Login required.']); exit; }
    $productId = intval($_POST['product_id'] ?? 0);
    $rating    = intval($_POST['rating'] ?? 0);
    $review    = sanitize($_POST['review'] ?? '');
    if (!$productId || !$rating || !$review || $rating < 1 || $rating > 5) { echo json_encode(['success'=>false,'message'=>'Please fill in rating and review.']); exit; }
    // Check if already reviewed
    $check = $pdo->prepare("SELECT id FROM reviews WHERE product_id=? AND user_id=?");
    $check->execute([$productId, $_SESSION['user_id']]);
    if ($check->fetch()) { echo json_encode(['success'=>false,'message'=>'You already reviewed this product.']); exit; }
    $pdo->prepare("INSERT INTO reviews (product_id,user_id,rating,review) VALUES (?,?,?,?)")->execute([$productId,$_SESSION['user_id'],$rating,$review]);
    // Update product rating
    $avg = $pdo->prepare("SELECT AVG(rating), COUNT(*) FROM reviews WHERE product_id=? AND status='approved'");
    $avg->execute([$productId]); [$avgRating, $count] = $avg->fetch(PDO::FETCH_NUM);
    $pdo->prepare("UPDATE products SET rating=?, review_count=? WHERE id=?")->execute([$avgRating, $count, $productId]);
    echo json_encode(['success'=>true,'message'=>'Review submitted successfully!']);
} else {
    $productId = intval($_GET['product_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON r.user_id=u.id WHERE r.product_id=? AND r.status='approved' ORDER BY r.created_at DESC");
    $stmt->execute([$productId]);
    echo json_encode(['success'=>true,'reviews'=>$stmt->fetchAll()]);
}
