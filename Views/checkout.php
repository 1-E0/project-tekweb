<?php
session_start();
require_once '../config/Database.php';
require_once '../Controllers/CartController.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$cartObj = new CartController();
$items = $cartObj->getCart($user_id);

if(empty($items)) { header("Location: cart.php"); exit; }

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$balance = $stmt->fetchColumn();

$total = 0;
foreach($items as $i) $total += $i['harga'] * $i['quantity'];
$grand_total = $total;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800">
    <div class="container mx-auto px-4 py-10 max-w-3xl">
        <h1 class="text-2xl font-bold mb-6">Konfirmasi Pembayaran</h1>
        
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
            <h3 class="font-bold mb-4 border-b pb-2">Rincian Pesanan</h3>
            <?php foreach($items as $item): ?>
            <div class="flex justify-between py-2 text-sm">
                <span><?php echo $item['quantity']; ?>x <?php echo $item['nama_produk']; ?></span>
                <span class="font-medium">Rp <?php echo number_format($item['harga'] * $item['quantity']); ?></span>
            </div>
            <?php endforeach; ?>
            
            <div class="flex justify-between py-4 text-lg font-bold border-t border-slate-200 mt-2">
                <span>Total Bayar</span>
                <span class="text-blue-600">Rp <?php echo number_format($grand_total); ?></span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
            <h3 class="font-bold mb-4">Metode Pembayaran</h3>
            <div class="flex items-center justify-between p-4 border rounded-lg <?php echo $balance >= $grand_total ? 'border-blue-500 bg-blue-50' : 'border-red-300 bg-red-50'; ?>">
                <div class="flex items-center gap-3">
                    <i class="fas fa-wallet text-2xl text-slate-600"></i>
                    <div>
                        <p class="font-bold text-sm">Saldo Akun</p>
                        <p class="text-xs text-slate-500">Sisa: Rp <?php echo number_format($balance); ?></p>
                    </div>
                </div>
                <?php if($balance < $grand_total): ?>
                    <span class="text-xs font-bold text-red-600">Saldo Kurang</span>
                <?php else: ?>
                    <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex gap-4">
            <a href="cart.php" class="w-1/3 py-3 rounded-xl border border-slate-300 text-center font-bold text-slate-600 hover:bg-slate-50">Batal</a>
            <button onclick="processPay()" class="w-2/3 py-3 rounded-xl font-bold text-white shadow-lg transition <?php echo $balance >= $grand_total ? 'bg-blue-600 hover:bg-blue-700' : 'bg-slate-400 cursor-not-allowed'; ?>" <?php echo $balance < $grand_total ? 'disabled' : ''; ?>>
                Bayar Sekarang
            </button>
        </div>
    </div>

    <script>
    function processPay() {
        Swal.fire({
            title: 'Konfirmasi Bayar',
            text: 'Saldo akan terpotong Rp <?php echo number_format($grand_total); ?>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Bayar',
            confirmButtonColor: '#2563EB'
        }).then((res) => {
            if (res.isConfirmed) {
                $.post('../api/order.php', { action: 'checkout' }, function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Sukses!', 'Pembayaran berhasil.', 'success').then(() => {
                            window.location.href = '../index.php';
                        });
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                }, 'json');
            }
        });
    }
    </script>
</body>
</html>