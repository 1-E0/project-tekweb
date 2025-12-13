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

    public function getPublicProduct($id) {
        $query = "SELECT p.*, s.nama_toko, s.alamat_toko, c.nama_kategori,
                  (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.product_id = p.id) as terjual,
                  (SELECT COALESCE(AVG(rating), 0) FROM product_reviews WHERE product_id = p.id) as rating_produk,
                  (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.id) as jumlah_review
                  FROM " . $this->table . " p 
                  JOIN shops s ON p.shop_id = s.id 
                  JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProductsByShop($shopId) {
        $query = "SELECT p.*, c.nama_kategori,
                  (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.product_id = p.id) as terjual
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
        $query = "SELECT SUM(oi.subtotal) as total_revenue, SUM(oi.quantity) as total_sold
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.id
                  JOIN orders o ON oi.order_id = o.id
                  WHERE p.shop_id = :shop_id AND o.status IN ('paid', 'shipped', 'completed')"; 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':shop_id', $shopId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $queryRating = "SELECT AVG(r.rating) as avg_rating FROM product_reviews r JOIN products p ON r.product_id = p.id WHERE p.shop_id = :shop_id";
        $stmtRating = $this->conn->prepare($queryRating);
        $stmtRating->bindParam(':shop_id', $shopId);
        $stmtRating->execute();
        $ratingResult = $stmtRating->fetch(PDO::FETCH_ASSOC);
        
        return ['revenue' => $result['total_revenue'] ?? 0, 'sold' => $result['total_sold'] ?? 0, 'rating' => $ratingResult['avg_rating'] ?? 0];
    }

    public function addProduct($shopId, $nama, $kategori, $harga, $stok, $deskripsi, $file) {
        if ($stok < 0) return json_encode(['status' => 'error', 'message' => 'Stok tidak boleh kurang dari 0!']);
        if ($harga < 0) return json_encode(['status' => 'error', 'message' => 'Harga tidak boleh kurang dari 0!']);

        $targetDir = "../assets/images/"; 
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file["tmp_name"]);
        finfo_close($finfo);

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if(in_array($mimeType, $allowedMimeTypes)){
            $fileName = time() . '_' . basename($file["name"]);
            $targetFilePath = $targetDir . $fileName;

            if(move_uploaded_file($file["tmp_name"], $targetFilePath)){
                $query = "INSERT INTO " . $this->table . " (shop_id, category_id, nama_produk, deskripsi, harga, stok, gambar) VALUES (:shop_id, :cat_id, :nama, :desc, :harga, :stok, :img)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':shop_id', $shopId);
                $stmt->bindParam(':cat_id', $kategori);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':desc', $deskripsi);
                $stmt->bindParam(':harga', $harga);
                $stmt->bindParam(':stok', $stok);
                $stmt->bindParam(':img', $fileName);

                if($stmt->execute()) return json_encode(['status' => 'success', 'message' => 'Produk berhasil ditambahkan!']);
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Format file harus JPG, PNG, atau GIF.']);
    }

    public function updateProduct($id, $shopId, $nama, $kategori, $harga, $stok, $deskripsi, $file) {
        if ($stok < 0) return json_encode(['status' => 'error', 'message' => 'Stok tidak boleh kurang dari 0!']);
        if ($harga < 0) return json_encode(['status' => 'error', 'message' => 'Harga tidak boleh kurang dari 0!']);

        $imageQueryPart = "";
        $fileName = "";
        
        if (!empty($file['name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file["tmp_name"]);
            finfo_close($finfo);

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

            if(in_array($mimeType, $allowedMimeTypes)){
                $targetDir = "../assets/images/";
                $fileName = time() . '_' . basename($file["name"]);
                $targetFilePath = $targetDir . $fileName;

                if(move_uploaded_file($file["tmp_name"], $targetFilePath)){
                    $imageQueryPart = ", gambar = :img";
                } else {
                    return json_encode(['status' => 'error', 'message' => 'Gagal upload gambar baru.']);
                }
            } else {
                return json_encode(['status' => 'error', 'message' => 'Format file tidak didukung.']);
            }
        }

        $query = "UPDATE " . $this->table . " SET category_id = :cat_id, nama_produk = :nama, deskripsi = :desc, harga = :harga, stok = :stok" . $imageQueryPart . " WHERE id = :id AND shop_id = :shop_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':shop_id', $shopId);
        $stmt->bindParam(':cat_id', $kategori);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':desc', $deskripsi);
        $stmt->bindParam(':harga', $harga);
        $stmt->bindParam(':stok', $stok);
        if (!empty($imageQueryPart)) $stmt->bindParam(':img', $fileName);

        if($stmt->execute()) return json_encode(['status' => 'success', 'message' => 'Produk berhasil diperbarui!']);
        return json_encode(['status' => 'error', 'message' => 'Gagal update database.']);
    }
    
    public function deleteProduct($id, $shopId) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id AND shop_id = :shop_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':shop_id', $shopId);

        if($stmt->execute()) return json_encode(['status' => 'success', 'message' => 'Produk berhasil dihapus.']);
        return json_encode(['status' => 'error', 'message' => 'Gagal menghapus produk.']);
    }
}
?>