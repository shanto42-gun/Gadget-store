<?php
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');

$code     = sanitize($_POST['code'] ?? '');
$subtotal = floatval($_POST['subtotal'] ?? 0);

if (!$code) { echo json_encode(['success'=>false,'message'=>'Please enter a coupon code.']); exit; }

$stmt = $pdo->prepare("SELECT * FROM coupons WHERE code=? AND status=1 AND (expiry_date IS NULL OR expiry_date>=CURDATE()) AND (usage_limit IS NULL OR used_count<usage_limit)");
$stmt->execute([$code]);
$coupon = $stmt->fetch();

if (!$coupon) { echo json_encode(['success'=>false,'message'=>'Invalid or expired coupon code.']); exit; }
if ($subtotal < $coupon['min_order']) { echo json_encode(['success'=>false,'message'=>'Minimum order of '.formatPrice($coupon['min_order']).' required for this coupon.']); exit; }

$discount = 0;
if ($coupon['type'] === 'percent') {
    $discount = $subtotal * $coupon['value'] / 100;
    if ($coupon['max_discount']) $discount = min($discount, $coupon['max_discount']);
} else {
    $discount = $coupon['value'];
}
$discount = min($discount, $subtotal);

echo json_encode(['success'=>true,'discount'=>round($discount,2),'message'=>"Coupon applied! You save ".formatPrice($discount)]);
