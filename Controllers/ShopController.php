<?php //aldwin
require_once '../config/Database.php';

class ShopController {
    private $conn;
    private $table = "shops";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function hasShop($userId) {
        $query = "SELECT id FROM " . $this->table . " WHERE user_id = :uid LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getShop($userId) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :uid LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createShop($userId, $nama_toko, $deskripsi, $alamat) {
        if ($this->hasShop($userId)) {
            return json_encode(['status' => 'error', 'message' => 'Anda sudah memiliki toko!']);
        }

        $query = "INSERT INTO " . $this->table . " (user_id, nama_toko, deskripsi_toko, alamat_toko) VALUES (:uid, :nama, :desc, :alamat)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':nama', $nama_toko);
        $stmt->bindParam(':desc', $deskripsi);
        $stmt->bindParam(':alamat', $alamat);

        if($stmt->execute()){
            return json_encode(['status' => 'success', 'message' => 'Toko berhasil dibuat!']);
        }

        return json_encode(['status' => 'error', 'message' => 'Gagal membuat toko.']);
    }

    public function updateShop($userId, $nama_toko, $deskripsi, $alamat) {
        $query = "UPDATE " . $this->table . " SET nama_toko = :nama, deskripsi_toko = :desc, alamat_toko = :alamat WHERE user_id = :uid";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':nama', $nama_toko);
        $stmt->bindParam(':desc', $deskripsi);
        $stmt->bindParam(':alamat', $alamat);

        if($stmt->execute()){
            return json_encode(['status' => 'success', 'message' => 'Informasi toko berhasil diperbarui!']);
        }

        return json_encode(['status' => 'error', 'message' => 'Gagal memperbarui toko.']);
    }
}
?>