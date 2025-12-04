<?php
require_once '../config/Database.php';

class AdminController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    
    public function getDashboardStats() {
        
        $stmtUser = $this->conn->query("SELECT COUNT(*) FROM users WHERE role != 'admin'");
        $totalUser = $stmtUser->fetchColumn();

        
        $stmtRev = $this->conn->query("SELECT SUM(total_harga) FROM orders WHERE status = 'completed'");
        $totalRev = $stmtRev->fetchColumn();

        
        $stmtShop = $this->conn->query("SELECT COUNT(*) FROM shops");
        $totalShop = $stmtShop->fetchColumn();

        
        $stmtProd = $this->conn->query("SELECT COUNT(*) FROM products");
        $totalProd = $stmtProd->fetchColumn();

        return [
            'users' => $totalUser,
            'revenue' => $totalRev ?: 0,
            'shops' => $totalShop,
            'products' => $totalProd
        ];
    }

    public function getAllUsers() {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllShops() {
        $sql = "SELECT s.*, u.nama_lengkap as pemilik, 
                (SELECT COUNT(*) FROM products WHERE shop_id = s.id) as jumlah_produk
                FROM shops s 
                JOIN users u ON s.user_id = u.id 
                ORDER BY s.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllProducts() {
        $sql = "SELECT p.*, s.nama_toko, c.nama_kategori 
                FROM products p 
                JOIN shops s ON p.shop_id = s.id 
                JOIN categories c ON p.category_id = c.id
                ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUserBalance($userId, $amount, $type) {
        if ($type == 'add') {
            $query = "UPDATE users SET balance = balance + :amount WHERE id = :id";
        } else {
            $query = "UPDATE users SET balance = :amount WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($query);
        if($stmt->execute([':amount' => $amount, ':id' => $userId])) {
            return json_encode(['status' => 'success', 'message' => 'Saldo berhasil diupdate']);
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal update saldo']);
    }

    public function deleteUser($userId) {
        $this->conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);
        
        if($this->conn->prepare("DELETE FROM users WHERE id = ?")->execute([$userId])) {
            return json_encode(['status' => 'success', 'message' => 'User berhasil dihapus']);
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal hapus user']);
    }

    public function deleteShop($shopId) {
        if($this->conn->prepare("DELETE FROM shops WHERE id = ?")->execute([$shopId])) {
            return json_encode(['status' => 'success', 'message' => 'Toko berhasil dihapus']);
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal hapus toko']);
    }

    public function deleteProduct($prodId) {
        if($this->conn->prepare("DELETE FROM products WHERE id = ?")->execute([$prodId])) {
            return json_encode(['status' => 'success', 'message' => 'Produk berhasil dihapus']);
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal hapus produk']);
    }
}
?>