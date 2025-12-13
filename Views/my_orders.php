<?php
session_start();
require_once '../config/Database.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$has_shop = false;
$nav_balance = 0;

$database = new Database();
$db = $database->getConnection();

if ($role == 'member') {
    $stmt = $db->prepare("SELECT id FROM shops WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    if ($stmt->rowCount() > 0) $has_shop = true;
}

$stmt_bal = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmt_bal->execute([$user_id]);
$nav_balance = $stmt_bal->fetchColumn() ?: 0;

$stmt_orders = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt_orders->execute([$user_id]);
$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-50 text-slate-800">
    <div id="page-transition"></div>

    <nav class="glass sticky top-0 z-50 transition-all duration-300">
        <div class="container mx-auto px-4 sm:px-6 h-20 flex items-center justify-between gap-4">
            <a href="../index.php" class="flex items-center gap-2 flex-shrink-0 group">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-600 text-white p-2.5 rounded-xl shadow-lg shadow-blue-200 group-hover:scale-105 transition duration-300">
                    <i class="fas fa-shopping-bag text-lg"></i>
                </div>
                <span class="text-xl font-extrabold text-slate-800 tracking-tight hidden md:block">MarketPlace</span>
            </a>

            <div class="flex-1 max-w-2xl mx-4">
                <form action="browse.php" method="GET" class="relative group">
                    <input type="text" name="search" class="w-full input-modern rounded-full py-2.5 pl-12 pr-6 text-sm" placeholder="Cari barang apa hari ini?">
                    <i class="fas fa-search absolute left-4 top-3 text-slate-400 group-focus-within:text-blue-500 transition"></i>
                </form>
            </div>

            <div class="flex items-center gap-4 flex-shrink-0">
                <button onclick="openTopUp()" class="hidden md:flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-full transition font-bold text-xs border border-blue-100 group">
                    <i class="fas fa-wallet text-lg group-hover:scale-110 transition"></i>
                    <span>Rp <?php echo number_format($nav_balance, 0, ',', '.'); ?></span>
                    <div class="w-4 h-4 bg-blue-600 text-white rounded-full flex items-center justify-center text-[10px] ml-1"><i class="fas fa-plus"></i></div>
                </button>

                <a href="cart.php" class="text-slate-500 hover:text-blue-600 p-2 relative transition">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </a>

                <div class="relative">
                    <button id="navProfileTrigger" class="flex items-center gap-2 hover:bg-white/50 p-1 pr-3 rounded-full transition border border-transparent hover:border-slate-200">
                        <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border-2 border-white shadow-sm">
                            <?php echo strtoupper(substr($nama, 0, 1)); ?>
                        </div>
                        <span class="text-sm font-semibold text-slate-700 hidden md:block max-w-[100px] truncate"><?php echo htmlspecialchars($nama); ?></span>
                        <i class="fas fa-chevron-down text-xs text-slate-400 ml-1 transition" id="navChevron"></i>
                    </button>
                    <div id="navProfileDropdown" class="hidden absolute right-0 mt-3 w-64 bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50 animate-enter">
                        <div class="p-5 border-b border-slate-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg"><i class="fas fa-user"></i></div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($nama); ?></p>
                                <p class="text-xs text-slate-500 capitalize"><?php echo $role; ?></p>
                            </div>
                        </div>
                        <div class="p-2 space-y-1">
                            <button onclick="openTopUp()" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition md:hidden">
                                <i class="fas fa-wallet w-5"></i> 
                                <span class="font-bold text-blue-600">Rp <?php echo number_format($nav_balance, 0, ',', '.'); ?></span>
                                <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded ml-auto">+ Top Up</span>
                            </button>
                            <?php if($has_shop): ?>
                                <a href="manage_products.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-store w-5"></i> Toko Saya</a>
                            <?php elseif($role != 'admin'): ?>
                                <a href="create_shop.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-store w-5"></i> Buka Toko</a>
                            <?php endif; ?>
                            <a href="my_orders.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-history w-5"></i> Riwayat Belanja</a>
                            <a href="settings.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-cog w-5"></i> Pengaturan</a>
                            <div class="h-px bg-slate-100 my-1 mx-2"></div>
                            <a href="../logout.php" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-xl transition"><i class="fas fa-sign-out-alt w-5"></i> Keluar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-10 max-w-4xl">
        <div class="mb-6">
            <button onclick="history.back()" class="group inline-flex items-center gap-2 text-slate-500 hover:text-blue-600 transition-colors duration-200 font-medium text-sm">
                <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm group-hover:border-blue-200 group-hover:bg-blue-50 transition-all">
                    <i class="fas fa-arrow-left text-xs"></i>
                </div>
                Kembali
            </button>
        </div>
        <h1 class="text-2xl font-bold mb-6 text-slate-800">Riwayat Belanja</h1>
        
        <div class="space-y-4">
            <?php foreach($orders as $o): ?>
            <div class="glass p-6 rounded-xl bg-white border border-slate-100 animate-enter">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="font-bold text-lg text-slate-800">Order #<?php echo $o['invoice_number']; ?></div>
                        <div class="text-sm text-slate-500"><?php echo date('d M Y H:i', strtotime($o['created_at'])); ?></div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide shadow-sm
                        <?php echo $o['status']=='completed'?'bg-green-100 text-green-700 border border-green-200':($o['status']=='shipped'?'bg-blue-100 text-blue-700 border border-blue-200':'bg-yellow-100 text-yellow-700 border border-yellow-200'); ?>">
                        <?php echo $o['status']; ?>
                    </span>
                </div>
                
                <div class="flex justify-between items-center pt-4 border-t border-slate-50">
                    <div class="font-bold text-blue-600 text-lg">Rp <?php echo number_format($o['total_harga']); ?></div>
                    
                    <?php if($o['status'] == 'shipped'): ?>
                        <button onclick="confirmOrder(<?php echo $o['id']; ?>)" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg text-sm font-bold transition shadow-lg shadow-green-200 transform hover:-translate-y-0.5">
                            <i class="fas fa-check-circle mr-1"></i> Pesanan Diterima
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($orders)): ?>
                <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-slate-300">
                    <i class="fas fa-shopping-bag text-4xl text-slate-300 mb-4"></i>
                    <p class="text-slate-500 font-medium">Belum ada riwayat pesanan.</p>
                    <a href="browse.php" class="text-blue-600 font-bold hover:underline text-sm mt-2 inline-block">Mulai Belanja</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const transitionEl = document.getElementById('page-transition');
        window.addEventListener('pageshow', function(event) {
            if (transitionEl) transitionEl.classList.add('page-loaded');
        });
        setTimeout(() => {
            if (transitionEl) transitionEl.classList.add('page-loaded');
        }, 50);
        const links = document.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                const target = this.getAttribute('target');
                if (!href || href.startsWith('#') || href.startsWith('javascript') || target === '_blank') {
                    return;
                }
                const currentUrl = new URL(window.location.href);
                const targetUrl = new URL(href, window.location.origin);
                if (currentUrl.pathname === targetUrl.pathname && currentUrl.origin === targetUrl.origin) {
                    return;
                }
                e.preventDefault();
                transitionEl.classList.remove('page-loaded');
                setTimeout(() => {
                    window.location.href = href;
                }, 500);
            });
        });
    });

    $(document).ready(function(){
        $('#navProfileTrigger').click(function(e){ e.stopPropagation(); $('#navProfileDropdown').slideToggle(150); $('#navChevron').toggleClass('rotate-180'); });
        $(document).click(function(){ $('#navProfileDropdown').slideUp(150); $('#navChevron').removeClass('rotate-180'); });
    });

    function confirmOrder(id) {
        Swal.fire({
            title: 'Pesanan Diterima?',
            text: "Konfirmasi barang sudah sampai dengan aman.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Diterima',
            confirmButtonColor: '#10B981'
        }).then((res) => {
            if(res.isConfirmed) {
                $.post('../api/order.php', { action: 'update_status', order_id: id, status: 'completed' }, function(data){
                    if(data.status === 'success') {
                         Swal.fire('Sukses', 'Status pesanan diperbarui!', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', 'Gagal update status', 'error');
                    }
                }, 'json');
            }
        });
    }

    function openTopUp() {
        Swal.fire({
            title: 'Isi Saldo',
            input: 'number',
            inputLabel: 'Masukkan Nominal (Rp)',
            inputPlaceholder: 'Contoh: 50000',
            showCancelButton: true,
            confirmButtonText: 'Top Up',
            confirmButtonColor: '#2563EB',
            preConfirm: (amount) => {
                if (!amount) Swal.showValidationMessage('Nominal harus diisi');
                return amount;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('../api/user.php', { action: 'topup', amount: result.value }, function(res) {
                    if(res.status === 'success') {
                        Swal.fire('Berhasil', 'Saldo bertambah!', 'success').then(() => location.reload());
                    }
                }, 'json');
            }
        });
    }
    </script>
</body>
</html>