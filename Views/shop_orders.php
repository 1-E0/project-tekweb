<?php
session_start();
require_once '../config/Database.php';
require_once '../Controllers/OrderController.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT id, nama_toko FROM shops WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$shop = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$shop) { header("Location: create_shop.php"); exit; }

$orderController = new OrderController();
$orders = $orderController->getShopOrders($shop['id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Masuk - <?php echo htmlspecialchars($shop['nama_toko']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-50 text-slate-800">
    
    <nav class="glass sticky top-0 z-50 transition-all duration-300 border-b border-slate-200/60 bg-white/80 backdrop-blur-md">
        <div class="container mx-auto px-6 h-24 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="manage_products.php" class="group w-12 h-12 bg-white border border-slate-200 rounded-full flex items-center justify-center text-slate-500 hover:text-blue-600 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-100 transition-all duration-300">
                    <i class="fas fa-arrow-left text-lg group-hover:-translate-x-1 transition-transform duration-300"></i>
                </a>
                
                <div class="h-10 w-px bg-slate-200"></div>

                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200 transform rotate-3 hover:rotate-6 transition duration-300">
                        <i class="fas fa-box-open text-xl"></i>
                    </div>
                    <div>
                        <h1 class="font-extrabold text-slate-800 text-xl tracking-tight leading-none">Pesanan Masuk</h1>
                        <p class="text-sm text-slate-500 font-medium mt-1">Toko: <span class="text-blue-600"><?php echo htmlspecialchars($shop['nama_toko']); ?></span></p>
                    </div>
                </div>
            </div>

            <div class="hidden md:flex items-center gap-3">
                <div class="bg-blue-50 text-blue-600 px-4 py-2 rounded-full text-xs font-bold border border-blue-100 flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    Realtime Update
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-10">
        
        <div class="glass rounded-3xl overflow-hidden shadow-xl border border-slate-100">
            <div class="p-6 bg-white/50 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-700 text-lg">Daftar Transaksi</h3>
                <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-xs font-bold"><?php echo count($orders); ?> Pesanan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/80 text-slate-500 text-xs uppercase font-bold tracking-wider">
                        <tr>
                            <th class="p-5 pl-8">Invoice & Tanggal</th>
                            <th class="p-5">Detail Pembeli</th>
                            <th class="p-5">Total Belanja</th>
                            <th class="p-5">Status</th>
                            <th class="p-5 text-right pr-8">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach($orders as $o): ?>
                        <tr class="hover:bg-blue-50/30 transition duration-200">
                            <td class="p-5 pl-8">
                                <div class="font-mono text-sm font-bold text-slate-800 mb-1"><?php echo $o['invoice_number']; ?></div>
                                <div class="text-xs text-slate-400 flex items-center gap-1">
                                    <i class="far fa-clock"></i> <?php echo date('d M Y H:i', strtotime($o['created_at'])); ?>
                                </div>
                            </td>
                            <td class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs border border-slate-200">
                                        <?php echo strtoupper(substr($o['nama_lengkap'], 0, 1)); ?>
                                    </div>
                                    <span class="font-semibold text-slate-700 text-sm"><?php echo htmlspecialchars($o['nama_lengkap']); ?></span>
                                </div>
                            </td>
                            <td class="p-5">
                                <span class="font-extrabold text-blue-600">Rp <?php echo number_format($o['total_harga'], 0, ',', '.'); ?></span>
                            </td>
                            <td class="p-5">
                                <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide border shadow-sm
                                    <?php echo $o['status']=='paid'?'bg-yellow-50 text-yellow-700 border-yellow-100':($o['status']=='shipped'?'bg-blue-50 text-blue-700 border-blue-100':'bg-green-50 text-green-700 border-green-100'); ?>">
                                    <?php 
                                        if($o['status'] == 'paid') echo '<i class="fas fa-money-bill-wave mr-1"></i> Dibayar';
                                        elseif($o['status'] == 'shipped') echo '<i class="fas fa-truck mr-1"></i> Dikirim';
                                        else echo '<i class="fas fa-check mr-1"></i> Selesai';
                                    ?>
                                </span>
                            </td>
                            <td class="p-5 text-right pr-8">
                                <?php if($o['status'] == 'paid'): ?>
                                    <button onclick="updateStatus(<?php echo $o['id']; ?>, 'shipped')" class="group bg-blue-600 hover:bg-blue-700 text-white pl-4 pr-5 py-2.5 rounded-xl text-sm font-bold transition shadow-lg shadow-blue-200 flex items-center gap-2 ml-auto">
                                        <i class="fas fa-shipping-fast group-hover:translate-x-1 transition-transform"></i> 
                                        Kirim Barang
                                    </button>
                                <?php else: ?>
                                    <span class="text-slate-400 text-sm font-medium italic flex items-center justify-end gap-1">
                                        <i class="fas fa-check-circle"></i> Selesai
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($orders)): ?>
                            <tr>
                                <td colspan="5" class="p-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-300">
                                        <i class="fas fa-clipboard-list text-5xl mb-4"></i>
                                        <p class="text-lg font-medium text-slate-500">Belum ada pesanan masuk.</p>
                                        <p class="text-sm">Promosikan tokomu agar lebih banyak pembeli!</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function updateStatus(id, status) {
        Swal.fire({
            title: 'Kirim Pesanan?',
            text: "Pastikan barang sudah siap dikirim ke ekspedisi.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563EB',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Kirim Sekarang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('../api/order.php', { action: 'update_status', order_id: id, status: status }, function(res){
                    if(res.status === 'success') {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Status pesanan telah diperbarui.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    }
    </script>
</body>
</html>