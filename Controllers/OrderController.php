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
                return json_encode(['status' => 'error', 'message' => 'Saldo tidak mencukupi! Silakan Top Up.']);
            }

            
            $newBalance = $user['balance'] - $total;
            $stmtUpdate = $this->conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmtUpdate->execute([$newBalance, $userId]);

            
            $invoice = 'INV-' . time() . '-' . $userId;
            $stmtOrder = $this->conn->prepare("INSERT INTO orders (user_id, invoice_number, total_harga, status, metode_pembayaran) VALUES (?, ?, ?, 'completed', 'saldo')");
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
            return json_encode(['status' => 'success', 'message' => 'Pembayaran Berhasil!', 'invoice' => $invoice]);

        } catch (Exception $e) {
            $this->conn->rollBack();
            return json_encode(['status' => 'error', 'message' => 'Gagal memproses transaksi: ' . $e->getMessage()]);
        }
    }
}
?>