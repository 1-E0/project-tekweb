<?php
require_once '../config/Database.php';

class ReviewController {
    private $conn;
    private $table = "product_reviews";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getReviews($productId) {
        $query = "SELECT r.*, u.nama_lengkap, u.username 
                  FROM " . $this->table . " r
                  JOIN users u ON r.user_id = u.id
                  WHERE r.product_id = :pid
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pid', $productId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function canReview($userId, $productId) {
        
        $checkReview = "SELECT id FROM " . $this->table . " WHERE user_id = :uid AND product_id = :pid";
        $stmtR = $this->conn->prepare($checkReview);
        $stmtR->execute([':uid' => $userId, ':pid' => $productId]);
        if($stmtR->rowCount() > 0) return false; 

        
        $checkOrder = "SELECT o.id 
                       FROM orders o
                       JOIN order_items oi ON o.id = oi.order_id
                       WHERE o.user_id = :uid 
                       AND oi.product_id = :pid 
                       AND o.status = 'completed'";
        $stmtO = $this->conn->prepare($checkOrder);
        $stmtO->execute([':uid' => $userId, ':pid' => $productId]);
        
        return $stmtO->rowCount() > 0;
    }

    public function addReview($userId, $productId, $rating, $review) {
        if(!$this->canReview($userId, $productId)){
            return json_encode(['status' => 'error', 'message' => 'Anda belum membeli produk ini (status completed) atau sudah memberikan ulasan.']);
        }

        $query = "INSERT INTO " . $this->table . " (user_id, product_id, rating, review) VALUES (:uid, :pid, :rating, :review)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':pid', $productId);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':review', $review);

        if($stmt->execute()){
            return json_encode(['status' => 'success', 'message' => 'Ulasan berhasil dikirim!']);
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal mengirim ulasan.']);
    }
}
?>