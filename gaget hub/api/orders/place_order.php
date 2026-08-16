<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

require_once __DIR__ . '/../../includes/functions.php';

// Prepare a log function
function checkout_log($msg) {
    file_put_contents(__DIR__ . '/checkout_debug.log', date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
}

header('Content-Type: application/json');

checkout_log("Checkout attempt started. Method: " . $_SERVER['REQUEST_METHOD']);

if (!isLoggedIn()) {
    checkout_log("Error: Not logged in.");
    ob_end_clean();
    echo json_encode(['success'=>false,'message'=>'Please login to checkout.']);
    exit;
}

$name    = sanitize($_POST['name'] ?? '');
$phone   = sanitize($_POST['phone'] ?? '');
$email   = sanitize($_POST['email'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$city    = sanitize($_POST['city'] ?? '');
$notes   = sanitize($_POST['notes'] ?? '');
$method  = sanitize($_POST['payment_method'] ?? 'cod');
$payRef  = sanitize($_POST['payment_ref'] ?? '');
$couponCode = sanitize($_POST['coupon_code'] ?? '');

checkout_log("Data received: " . print_r($_POST, true));

if (!$name || !$phone || !$address || !$city) {
    checkout_log("Error: Missing required fields.");
    ob_end_clean();
    echo json_encode(['success'=>false,'message'=>'Please fill in all required shipping fields.']);
    exit;
}

$cartItems = getCartItems();
if (empty($cartItems)) {
    checkout_log("Error: Cart is empty.");
    ob_end_clean();
    echo json_encode(['success'=>false,'message'=>'Cart is empty.']);
    exit;
}

checkout_log("Cart items count: " . count($cartItems));

$settings = getSettings();
$subtotal = cartTotal();
$shipping = (float)($settings['shipping_cost'] ?? 60);
if ($subtotal >= 2000) $shipping = 0;
$discount = 0;

// Apply coupon
if ($couponCode) {
    $cpn = $pdo->prepare("SELECT * FROM coupons WHERE code=? AND status=1 AND (expiry_date IS NULL OR expiry_date>=CURDATE()) AND (usage_limit IS NULL OR used_count<usage_limit) AND min_order<=?");
    $cpn->execute([$couponCode, $subtotal]);
    $coupon = $cpn->fetch();
    if ($coupon) {
        if ($coupon['type'] === 'percent') {
            $discount = $subtotal * $coupon['value'] / 100;
            if ($coupon['max_discount']) $discount = min($discount, $coupon['max_discount']);
        } else {
            $discount = $coupon['value'];
        }
        $discount = min($discount, $subtotal);
        checkout_log("Coupon applied: Discount=$discount");
    } else {
        checkout_log("Coupon invalid or conditions not met.");
    }
}

$total = $subtotal + $shipping - $discount;
$orderNumber = generateOrderNumber();

try {
    $pdo->beginTransaction();
    checkout_log("Transaction started. Order Number: $orderNumber");

    $stmt = $pdo->prepare("INSERT INTO orders (order_number,user_id,name,phone,email,address,city,notes,payment_method,payment_ref,subtotal,shipping_cost,discount,coupon_code,total) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$orderNumber,$_SESSION['user_id'],$name,$phone,$email,$address,$city,$notes,$method,$payRef,$subtotal,$shipping,$discount,$couponCode,$total]);
    $orderId = $pdo->lastInsertId();
    checkout_log("Order record created. ID: $orderId");

    foreach ($cartItems as $item) {
        $price = $item['discount_price'] ?: $item['price'];
        $sub   = $price * $item['quantity'];
        $pdo->prepare("INSERT INTO order_items (order_id,product_id,product_name,product_image,price,quantity,subtotal) VALUES (?,?,?,?,?,?,?)")
            ->execute([$orderId,$item['product_id'],$item['name'],$item['image'],$price,$item['quantity'],$sub]);
        // Update stock + sold count
        $pdo->prepare("UPDATE products SET stock=stock-?, sold_count=sold_count+? WHERE id=?")->execute([$item['quantity'],$item['quantity'],$item['product_id']]);
    }
    checkout_log("Order items and stock updated.");

    // Clear cart
    $pdo->prepare("DELETE FROM cart WHERE user_id=?")->execute([$_SESSION['user_id']]);
    checkout_log("Cart cleared.");

    // Update coupon usage
    if ($couponCode && !empty($coupon)) {
        $pdo->prepare("UPDATE coupons SET used_count=used_count+1 WHERE id=?")->execute([$coupon['id']]);
        checkout_log("Coupon usage updated.");
    }

    $pdo->commit();
    checkout_log("Transaction committed successfully.");
    
    ob_end_clean();
    http_response_code(201);
    echo json_encode(['success'=>true,'order_number'=>$orderNumber,'message'=>'Order placed successfully!']);
} catch (Exception $e) {
    $pdo->rollBack();
    checkout_log("Exception: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success'=>false,'message'=>'Failed to place order: ' . $e->getMessage()]);
}

