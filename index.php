<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: views/login.php");
    exit;
}


echo "<h1>LOGIN BERHASIL!</h1>";
echo "<p>Halo, " . $_SESSION['nama'] . " (" . $_SESSION['role'] . ")</p>";
echo "<a href='logout.php'>Logout</a>";
?>