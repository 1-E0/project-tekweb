<?php
session_start();
require_once '../Controllers/ReviewController.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$reviewObj = new ReviewController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add_review') {
        echo $reviewObj->addReview(
            $_SESSION['user_id'],
            $_POST['product_id'],
            $_POST['rating'],
            $_POST['review']
        );
    }
}
?>