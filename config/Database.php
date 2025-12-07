<?php
class Database {
    private $host = "localhost";
    private $db_name = "db_toko_online";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            
            die(json_encode(['status' => 'error', 'message' => 'Database Error: ' . $exception->getMessage()]));
        }
        return $this->conn;
    }
}
?>