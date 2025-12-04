<?php
require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

$username = 'admin';
$password = 'admin'; 
$nama = 'Administrator';
$role = 'admin';
$balance = 0;


$hashed_password = password_hash($password, PASSWORD_BCRYPT);

try {
    
    $check = $db->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);
    
    if($check->rowCount() > 0) {
        
        $update = $db->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = ?");
        $update->execute([$hashed_password, $username]);
        echo "<h1>Berhasil Update!</h1>";
        echo "Password untuk user <b>$username</b> telah direset menjadi: <b>$password</b>";
    } else {
       
        $insert = $db->prepare("INSERT INTO users (username, password, nama_lengkap, role, balance) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([$username, $hashed_password, $nama, $role, $balance]);
        echo "<h1>Berhasil Dibuat!</h1>";
        echo "User admin telah dibuat.<br>";
        echo "Username: <b>$username</b><br>";
        echo "Password: <b>$password</b>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>