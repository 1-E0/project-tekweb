<?php
session_start();
require_once '../Controllers/OrderController.php';
require_once '../Controllers/CartController.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$orderObj = new OrderController();

if (isset($_POST['action']) && $_POST['action'] == 'checkout') {
    $userId = $_SESSION['user_id'];
    $cartObj = new CartController();
    $cartItems = $cartObj->getCart($userId);
    
    if (empty($cartItems)) {
        echo json_encode(['status' => 'error', 'message' => 'Keranjang kosong']);
        exit;
    }

    $total = 0;
    foreach($cartItems as $item) {
        $total += $item['harga'] * $item['quantity'];
    }

    echo $orderObj->checkout($userId, $total, $cartItems);
}
elseif (isset($_POST['action']) && $_POST['action'] == 'update_status') {
    echo $orderObj->updateStatus($_POST['order_id'], $_POST['status']);
}
?>