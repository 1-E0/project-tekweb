<?php
session_start();
require_once '../Controllers/CartController.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login']);
    exit;
}

$cartObj = new CartController();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add') {
        echo $cartObj->addToCart($userId, $_POST['product_id']);
    } 
    elseif ($action == 'update_qty') {
        echo $cartObj->updateQty($_POST['cart_id'], $_POST['quantity']);
    }
    elseif ($action == 'delete') {
        echo $cartObj->deleteItem($_POST['cart_id']);
    }
}
?>