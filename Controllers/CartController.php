<?php
require_once '../config/Database.php';

class CartController {
    private $conn;
    private $table = "cart";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

   
    public function addToCart($userId, $productId, $qty = 1) {
        
        $checkOwner = "SELECT s.user_id FROM products p 
                       JOIN shops s ON p.shop_id = s.id 
                       WHERE p.id = :pid LIMIT 1";
        $stmtOwner = $this->conn->prepare($checkOwner);
        $stmtOwner->execute([':pid' => $productId]);
        $ownerId = $stmtOwner->fetchColumn();

        if ($ownerId == $userId) {
            return json_encode(['status' => 'error', 'message' => 'Anda tidak dapat membeli barang dagangan sendiri!']);
        }

        $stockQuery = "SELECT stok FROM products WHERE id = :pid";
        $stockStmt = $this->conn->prepare($stockQuery);
        $stockStmt->execute([':pid' => $productId]);
        $productStock = $stockStmt->fetchColumn();

        if ($productStock === false) {
             return json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan.']);
        }
        if ($productStock <= 0) {
             return json_encode(['status' => 'error', 'message' => 'Stok produk habis.']);
        }

        $check = "SELECT id, quantity FROM " . $this->table . " WHERE user_id = :uid AND product_id = :pid";
        $stmt = $this->conn->prepare($check);
        $stmt->execute([':uid' => $userId, ':pid' => $productId]);

        if ($stmt->rowCount() > 0) {
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $currentQty = $row['quantity'];
            $newQty = $currentQty + $qty;

           
            if ($newQty > $productStock) {
                return json_encode(['status' => 'error', 'message' => 'Stok tidak mencukupi. Sisa stok: ' . $productStock]);
            }

            $update = "UPDATE " . $this->table . " SET quantity = :qty WHERE id = :id";
            $stmtUpdate = $this->conn->prepare($update);
            if($stmtUpdate->execute([':qty' => $newQty, ':id' => $row['id']])) {
                return json_encode(['status' => 'success', 'message' => 'Jumlah produk diperbarui']);
            }
        } else {
            
            if ($qty > $productStock) {
                return json_encode(['status' => 'error', 'message' => 'Stok tidak mencukupi. Sisa stok: ' . $productStock]);
            }

            $insert = "INSERT INTO " . $this->table . " (user_id, product_id, quantity) VALUES (:uid, :pid, :qty)";
            $stmtInsert = $this->conn->prepare($insert);
            if($stmtInsert->execute([':uid' => $userId, ':pid' => $productId, ':qty' => $qty])) {
                return json_encode(['status' => 'success', 'message' => 'Produk masuk keranjang']);
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data']);
    }

    public function getCart($userId) {
    
        $query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.nama_produk, p.harga, p.gambar, p.stok, s.nama_toko 
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
        
       
        $qProd = "SELECT product_id FROM " . $this->table . " WHERE id = :id";
        $stmtProd = $this->conn->prepare($qProd);
        $stmtProd->execute([':id' => $cartId]);
        $prodId = $stmtProd->fetchColumn();

        if($prodId) {
             $stockQuery = "SELECT stok FROM products WHERE id = :pid";
             $stockStmt = $this->conn->prepare($stockQuery);
             $stockStmt->execute([':pid' => $prodId]);
             $stok = $stockStmt->fetchColumn();

             if ($qty > $stok) {
                 return json_encode(['status' => 'error', 'message' => 'Stok tidak mencukupi (Max: '.$stok.')']);
             }
        }

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