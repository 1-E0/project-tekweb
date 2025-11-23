<?php
include_once '../controllers/AuthController.php';
header('Content-Type: application/json');

$auth = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'register') {
        echo $auth->register($_POST['nama'], $_POST['username'], $_POST['email'], $_POST['password']);
    } 
    elseif ($action == 'login') {
        echo $auth->login($_POST['username'], $_POST['password']);
    } 
    else {
        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
    }
}
?>