<?php
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');

// Clear all
if (!empty($_POST['clear'])) {
    if (isLoggedIn()) $pdo->prepare("DELETE FROM cart WHERE user_id=?")->execute([$_SESSION['user_id']]);
    else $pdo->prepare("DELETE FROM cart WHERE session_id=?")->execute([session_id()]);
    echo json_encode(['success'=>true,'cart_count'=>0]); exit;
}

$cartId = intval($_POST['cart_id'] ?? 0);
if (!$cartId) { echo json_encode(['success'=>false,'message'=>'Invalid item.']); exit; }

// Verify ownership
if (isLoggedIn()) {
    $pdo->prepare("DELETE FROM cart WHERE id=? AND user_id=?")->execute([$cartId, $_SESSION['user_id']]);
    $cnt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id=?"); $cnt->execute([$_SESSION['user_id']]);
} else {
    $pdo->prepare("DELETE FROM cart WHERE id=? AND session_id=?")->execute([$cartId, session_id()]);
    $cnt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE session_id=?"); $cnt->execute([session_id()]);
}

echo json_encode(['success'=>true,'cart_count'=>(int)$cnt->fetchColumn()]);
