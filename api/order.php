<?php
session_start();
require_once '../Controllers/OrderController.php';
require_once '../Controllers/CartController.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_POST['action'] == 'checkout') {
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

    $orderObj = new OrderController();
    echo $orderObj->checkout($userId, $total, $cartItems);
}
?>