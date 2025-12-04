<?php
require_once '../config/Database.php';

class UserController {
    private $conn;
    private $table = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getUser($id) {
        $query = "SELECT id, username, nama_lengkap, email, role FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $nama, $email, $username) {
        
        $checkQuery = "SELECT id FROM " . $this->table . " WHERE (username = :username OR email = :email) AND id != :id";
        $stmtCheck = $this->conn->prepare($checkQuery);
        $stmtCheck->bindParam(':username', $username);
        $stmtCheck->bindParam(':email', $email);
        $stmtCheck->bindParam(':id', $id);
        $stmtCheck->execute();

        if($stmtCheck->rowCount() > 0){
            return json_encode(['status' => 'error', 'message' => 'Username atau Email sudah digunakan user lain!']);
        }

        $query = "UPDATE " . $this->table . " SET nama_lengkap = :nama, email = :email, username = :username WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':id', $id);

        if($stmt->execute()){
            session_start();
            $_SESSION['nama'] = $nama; 
            return json_encode(['status' => 'success', 'message' => 'Profil berhasil diperbarui!']);
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal update profil.']);
    }

    public function updatePassword($id, $oldPass, $newPass) {
        
        $query = "SELECT password FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!password_verify($oldPass, $user['password'])) {
            return json_encode(['status' => 'error', 'message' => 'Password lama salah!']);
        }

        $newHash = password_hash($newPass, PASSWORD_BCRYPT);
        $updateQuery = "UPDATE " . $this->table . " SET password = :pass WHERE id = :id";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(':pass', $newHash);
        $updateStmt->bindParam(':id', $id);

        if($updateStmt->execute()){
            return json_encode(['status' => 'success', 'message' => 'Password berhasil diubah!']);
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal mengubah password.']);
    }
}
?>