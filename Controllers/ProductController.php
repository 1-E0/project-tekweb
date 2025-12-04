<?php
require_once '../config/Database.php';

class ProductController {
    private $conn;
    private $table = "products";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getProductById($id, $shopId) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id AND shop_id = :shop_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':shop_id', $shopId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
    public function getProductsByShop($shopId) {
       
        $query = "SELECT p.*, c.nama_kategori,
                  (SELECT COALESCE(SUM(oi.quantity), 0) 
                   FROM order_items oi 
                   WHERE oi.product_id = p.id) as terjual
                  FROM " . $this->table . " p 
                  JOIN categories c ON p.category_id = c.id 
                  WHERE p.shop_id = :shop_id 
                  ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':shop_id', $shopId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function getShopStats($shopId) {
        $query = "SELECT 
                    SUM(oi.subtotal) as total_revenue,
                    SUM(oi.quantity) as total_sold
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.id
                  JOIN orders o ON oi.order_id = o.id
                  WHERE p.shop_id = :shop_id 
                  AND o.status IN ('paid', 'shipped', 'completed')"; 
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':shop_id', $shopId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'revenue' => $result['total_revenue'] ?? 0,
            'sold' => $result['total_sold'] ?? 0
        ];
    }

    public function addProduct($shopId, $nama, $kategori, $harga, $stok, $deskripsi, $file) {
        $targetDir = "../assets/images/"; 
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($file["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg','png','jpeg','gif'); 
        
        if(in_array(strtolower($fileType), $allowTypes)){
            if(move_uploaded_file($file["tmp_name"], $targetFilePath)){
                $query = "INSERT INTO " . $this->table . " 
                          (shop_id, category_id, nama_produk, deskripsi, harga, stok, gambar) 
                          VALUES (:shop_id, :cat_id, :nama, :desc, :harga, :stok, :img)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':shop_id', $shopId);
                $stmt->bindParam(':cat_id', $kategori);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':desc', $deskripsi);
                $stmt->bindParam(':harga', $harga);
                $stmt->bindParam(':stok', $stok);
                $stmt->bindParam(':img', $fileName);

                if($stmt->execute()){
                    return json_encode(['status' => 'success', 'message' => 'Produk berhasil ditambahkan!']);
                }
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal upload gambar atau simpan data.']);
    }

    public function updateProduct($id, $shopId, $nama, $kategori, $harga, $stok, $deskripsi, $file) {
        $imageQueryPart = "";
        $fileName = "";
        
        if (!empty($file['name'])) {
            $targetDir = "../assets/images/";
            $fileName = time() . '_' . basename($file["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            $allowTypes = array('jpg','png','jpeg','gif');

            if(in_array(strtolower($fileType), $allowTypes)){
                if(move_uploaded_file($file["tmp_name"], $targetFilePath)){
                    $imageQueryPart = ", gambar = :img";
                } else {
                    return json_encode(['status' => 'error', 'message' => 'Gagal upload gambar baru.']);
                }
            } else {
                return json_encode(['status' => 'error', 'message' => 'Format file tidak didukung.']);
            }
        }

        $query = "UPDATE " . $this->table . " 
                  SET category_id = :cat_id, 
                      nama_produk = :nama, 
                      deskripsi = :desc, 
                      harga = :harga, 
                      stok = :stok" . $imageQueryPart . " 
                  WHERE id = :id AND shop_id = :shop_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':shop_id', $shopId);
        $stmt->bindParam(':cat_id', $kategori);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':desc', $deskripsi);
        $stmt->bindParam(':harga', $harga);
        $stmt->bindParam(':stok', $stok);

        if (!empty($imageQueryPart)) {
            $stmt->bindParam(':img', $fileName);
        }

        if($stmt->execute()){
            return json_encode(['status' => 'success', 'message' => 'Produk berhasil diperbarui!']);
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal update database.']);
    }
    
    public function deleteProduct($id, $shopId) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id AND shop_id = :shop_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':shop_id', $shopId);

        if($stmt->execute()){
            return json_encode(['status' => 'success', 'message' => 'Produk berhasil dihapus.']);
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal menghapus produk.']);
    }
}
?>