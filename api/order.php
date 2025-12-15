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
    $selectedIds = $_POST['selected_items'] ?? [];

    $cartObj = new CartController();
    $allCartItems = $cartObj->getCart($userId);
    
    $checkoutItems = array_filter($allCartItems, function($item) use ($selectedIds) {
        return in_array($item['cart_id'], $selectedIds);
    });

    if (empty($checkoutItems)) {
        echo json_encode(['status' => 'error', 'message' => 'Tidak ada barang yang dipilih/keranjang kosong']);
        exit;
    }

    $total = 0;
    foreach($checkoutItems as $item) {
        $total += $item['harga'] * $item['quantity'];
    }

    echo $orderObj->checkout($userId, $total, $checkoutItems);
}
elseif (isset($_POST['action']) && $_POST['action'] == 'update_status') {
    echo $orderObj->updateStatus($_POST['order_id'], $_POST['status']);
}
?>