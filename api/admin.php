<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once '../Controllers/AdminController.php';
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
    exit;
}

try {
    $admin = new AdminController();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($action == 'get_stats') {
            echo json_encode(['status' => 'success', 'data' => $admin->getDashboardStats()]);
        }
        elseif ($action == 'get_recent_orders') {
            echo json_encode(['status' => 'success', 'data' => $admin->getRecentOrders()]);
        }
        elseif ($action == 'get_users') {
            echo json_encode(['status' => 'success', 'data' => $admin->getAllUsers()]);
        }
        elseif ($action == 'get_shops') {
            echo json_encode(['status' => 'success', 'data' => $admin->getAllShops()]);
        }
        elseif ($action == 'get_products') {
            echo json_encode(['status' => 'success', 'data' => $admin->getAllProducts()]);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action == 'update_balance') {
            echo $admin->updateUserBalance($_POST['user_id'], $_POST['amount'], $_POST['type']);
        } 
        elseif ($action == 'delete_user') {
            echo $admin->deleteUser($_POST['user_id']);
        }
        elseif ($action == 'delete_shop') {
            echo $admin->deleteShop($_POST['shop_id']);
        }
        elseif ($action == 'delete_product') {
            echo $admin->deleteProduct($_POST['product_id']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>