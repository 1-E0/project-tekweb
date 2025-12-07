<?php
session_start();
require_once '../Controllers/CartController.php';
header('Content-Type: application/json');

error_reporting(0); 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login']);
    exit;
}

try {
    $cartObj = new CartController();
    $userId = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action == 'add') {
          
            $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            $productId = $_POST['product_id'];
            
         
            echo $cartObj->addToCart($userId, $productId, $qty);
        } 
        elseif ($action == 'update_qty') {
            echo $cartObj->updateQty($_POST['cart_id'], $_POST['quantity']);
        }
        elseif ($action == 'delete') {
            echo $cartObj->deleteItem($_POST['cart_id']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>