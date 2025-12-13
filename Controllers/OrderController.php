<?php
require_once '../config/Database.php';

class OrderController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function checkout($userId, $total, $items) {
        try {
            $this->conn->beginTransaction();

            $stmtUser = $this->conn->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
            $stmtUser->execute([$userId]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user['balance'] < $total) {
                $this->conn->rollBack();
                return json_encode(['status' => 'error', 'message' => 'Saldo tidak mencukupi!']);
            }

            foreach ($items as $item) {
                $stmtCheck = $this->conn->prepare("SELECT stok FROM products WHERE id = ? FOR UPDATE");
                $stmtCheck->execute([$item['product_id']]);
                $currentStock = $stmtCheck->fetchColumn();

                if ($currentStock < $item['quantity']) {
                    $this->conn->rollBack();
                    return json_encode(['status' => 'error', 'message' => 'Stok produk ' . $item['nama_produk'] . ' habis!']);
                }
            }

            $newBalance = $user['balance'] - $total;
            $stmtUpdate = $this->conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmtUpdate->execute([$newBalance, $userId]);

            $invoice = 'INV-' . time() . '-' . $userId;
            $stmtOrder = $this->conn->prepare("INSERT INTO orders (user_id, invoice_number, total_harga, status, metode_pembayaran) VALUES (?, ?, ?, 'paid', 'saldo')");
            $stmtOrder->execute([$userId, $invoice, $total]);
            $orderId = $this->conn->lastInsertId();

            $stmtItem = $this->conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmtStock = $this->conn->prepare("UPDATE products SET stok = stok - ?, terjual = terjual + ? WHERE id = ?");

            foreach ($items as $item) {
                $subtotal = $item['harga'] * $item['quantity'];
                $stmtItem->execute([$orderId, $item['product_id'], $item['quantity'], $item['harga'], $subtotal]);
                $stmtStock->execute([$item['quantity'], $item['quantity'], $item['product_id']]);
            }

            $stmtCart = $this->conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmtCart->execute([$userId]);

            $this->conn->commit();
            return json_encode(['status' => 'success', 'message' => 'Pembayaran Berhasil!']);

        } catch (Exception $e) {
            $this->conn->rollBack();
            return json_encode(['status' => 'error', 'message' => 'Transaksi gagal: ' . $e->getMessage()]);
        }
    }

    public function updateStatus($orderId, $status) {
        $query = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $orderId);
        
        if($stmt->execute()) {
            return json_encode(['status' => 'success', 'message' => 'Status pesanan diperbarui']);
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal update status']);
    }

    public function getShopOrders($shopId) {
        $query = "SELECT DISTINCT o.id, o.invoice_number, o.status, o.created_at, o.total_harga, u.nama_lengkap 
                  FROM orders o
                  JOIN order_items oi ON o.id = oi.order_id
                  JOIN products p ON oi.product_id = p.id
                  JOIN users u ON o.user_id = u.id
                  WHERE p.shop_id = :shop_id
                  ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':shop_id', $shopId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>