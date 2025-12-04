<?php //aldwin
session_start();
require_once '../Controllers/ShopController.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$shopObj = new ShopController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'create_shop') {
        echo $shopObj->createShop(
            $_SESSION['user_id'],
            $_POST['nama_toko'],
            $_POST['deskripsi_toko'],
            $_POST['alamat_toko']
        );
    }
    elseif ($action == 'update_shop') {
        echo $shopObj->updateShop(
            $_SESSION['user_id'],
            $_POST['nama_toko'],
            $_POST['deskripsi_toko'],
            $_POST['alamat_toko']
        );
    }
    elseif ($action == 'get_shop') {
        $data = $shopObj->getShop($_SESSION['user_id']);
        if ($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Toko tidak ditemukan']);
        }
    }
}
?>