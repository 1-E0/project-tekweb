<?php
require_once '../config/Database.php';

class CartController {
    private $conn;
    private $table = "cart";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    
    public function addToCart($userId, $productId) {
        
        $check = "SELECT id, quantity FROM " . $this->table . " WHERE user_id = :uid AND product_id = :pid";
        $stmt = $this->conn->prepare($check);
        $stmt->execute([':uid' => $userId, ':pid' => $productId]);

        if ($stmt->rowCount() > 0) {
           
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $newQty = $row['quantity'] + 1;
            $update = "UPDATE " . $this->table . " SET quantity = :qty WHERE id = :id";
            $stmtUpdate = $this->conn->prepare($update);
            $stmtUpdate->execute([':qty' => $newQty, ':id' => $row['id']]);
        } else {
            
            $insert = "INSERT INTO " . $this->table . " (user_id, product_id, quantity) VALUES (:uid, :pid, 1)";
            $stmtInsert = $this->conn->prepare($insert);
            $stmtInsert->execute([':uid' => $userId, ':pid' => $productId]);
        }
        return json_encode(['status' => 'success', 'message' => 'Produk masuk keranjang']);
    }

    
    public function getCart($userId) {
        $query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.nama_produk, p.harga, p.gambar, s.nama_toko 
                  FROM " . $this->table . " c
                  JOIN products p ON c.product_id = p.id
                  JOIN shops s ON p.shop_id = s.id
                  WHERE c.user_id = :uid ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function updateQty($cartId, $qty) {
        if ($qty < 1) return json_encode(['status' => 'error', 'message' => 'Minimal 1']);
        
        $query = "UPDATE " . $this->table . " SET quantity = :qty WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        if($stmt->execute([':qty' => $qty, ':id' => $cartId])){
            return json_encode(['status' => 'success']);
        }
        return json_encode(['status' => 'error']);
    }

   
    public function deleteItem($cartId) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        if($stmt->execute([':id' => $cartId])){
            return json_encode(['status' => 'success']);
        }
        return json_encode(['status' => 'error']);
    }
}
?>