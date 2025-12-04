<?php
session_start();
include_once '../Controllers/ProductController.php';
header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/Database.php';
$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT id FROM shops WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$shop = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$shop) {
    echo json_encode(['status' => 'error', 'message' => 'Anda belum memiliki toko!']);
    exit;
}

$shopId = $shop['id'];
$productObj = new ProductController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add') {
        echo $productObj->addProduct(
            $shopId,
            $_POST['nama_produk'],
            $_POST['category_id'],
            $_POST['harga'],
            $_POST['stok'],
            $_POST['deskripsi'],
            $_FILES['gambar']
        );
    } 
    elseif ($action == 'update') {
        
        echo $productObj->updateProduct(
            $_POST['product_id'],
            $shopId,
            $_POST['nama_produk'],
            $_POST['category_id'],
            $_POST['harga'],
            $_POST['stok'],
            $_POST['deskripsi'],
            $_FILES['gambar'] ?? null 
        );
    }
    elseif ($action == 'delete') {
        echo $productObj->deleteProduct($_POST['product_id'], $shopId);
    }
    elseif ($action == 'get_detail') {
        
        $data = $productObj->getProductById($_POST['product_id'], $shopId);
        if($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan']);
        }
    }
}
?>