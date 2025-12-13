<?php
include_once '../config/Database.php';

class AuthController {
    private $conn;
    private $table = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register($nama, $username, $email, $password) {
        $checkQuery = "SELECT id FROM " . $this->table . " WHERE username = :username OR email = :email";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if($stmt->rowCount() > 0){
            return json_encode(['status' => 'error', 'message' => 'Username atau Email sudah terdaftar!']);
        }

        $query = "INSERT INTO " . $this->table . " (nama_lengkap, username, email, password, role) VALUES (:nama, :username, :email, :password, 'member')";
        $stmt = $this->conn->prepare($query);
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);

        if($stmt->execute()){
            return json_encode(['status' => 'success', 'message' => 'Registrasi berhasil! Silakan Login.']);
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal mendaftar.']);
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password, nama_lengkap, role FROM " . $this->table . " WHERE username = :username LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['nama'] = $row['nama_lengkap'];
                $_SESSION['role'] = $row['role']; 
                
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                
                return json_encode([
                    'status' => 'success', 
                    'role' => $row['role'], 
                    'message' => 'Login Berhasil!'
                ]);
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Username atau Password salah!']);
    }
}
?>