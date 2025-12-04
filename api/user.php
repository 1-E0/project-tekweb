<?php
session_start();
require_once '../Controllers/UserController.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userObj = new UserController();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'get_user') {
        $data = $userObj->getUser($userId);
        if ($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
        }
    } 
    elseif ($action == 'update_profile') {
        echo $userObj->updateProfile(
            $userId,
            $_POST['nama'],
            $_POST['email'],
            $_POST['username']
        );
    } 
    elseif ($action == 'change_password') {
        echo $userObj->updatePassword(
            $userId,
            $_POST['old_password'],
            $_POST['new_password']
        );
    }
    elseif ($action == 'topup') {
        echo $userObj->topUp($userId, $_POST['amount']);
    }
}
?>