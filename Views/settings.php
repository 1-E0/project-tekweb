<?php
session_start();
require_once '../config/Database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>body { font-family: 'Inter', sans-serif; background-color: #F8F9FA; }</style>
</head>
<body class="text-slate-800 bg-slate-50">
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

    <div class="container mx-auto px-4 sm:px-6 py-8 max-w-4xl">
        <div class="mb-6 flex justify-between items-center">
            <button onclick="history.back()" class="group inline-flex items-center gap-2 text-slate-500 hover:text-blue-600 transition-colors duration-200 font-medium text-sm">
                <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm group-hover:border-blue-200 group-hover:bg-blue-50 transition-all">
                    <i class="fas fa-arrow-left text-xs"></i>
                </div>
                Kembali
            </button>
            <h1 class="text-xl font-bold text-slate-800">Pengaturan Akun</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="md:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 text-center">
                    <div class="w-24 h-24 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-3xl font-bold mx-auto mb-4 border-4 border-white shadow-md">
                        <?php echo strtoupper(substr($nama, 0, 1)); ?>
                    </div>
                    <h2 class="font-bold text-lg text-slate-800"><?php echo htmlspecialchars($nama); ?></h2>
                    <p class="text-sm text-slate-500 capitalize mb-4"><?php echo $role; ?></p>
                    <div class="h-px bg-slate-100 w-full mb-4"></div>
                    <p class="text-xs text-slate-400">Bergabung sejak 2025</p>
                </div>
            </div>

            <div class="md:col-span-2 space-y-6">
                
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-blue-100 text-sm font-medium mb-1">Saldo Saya</p>
                        <h2 class="text-3xl font-bold mb-4">Rp <?php echo number_format($nav_balance, 0, ',', '.'); ?></h2>
                        <button onclick="openTopUp()" class="bg-white text-blue-700 px-4 py-2 rounded-lg font-bold text-sm hover:bg-blue-50 transition shadow-sm">
                            <i class="fas fa-plus-circle mr-1"></i> Isi Saldo
                        </button>
                    </div>
                    <i class="fas fa-wallet absolute -bottom-4 -right-4 text-9xl text-white opacity-10"></i>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-bold text-lg text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-user-edit text-blue-600"></i> Edit Profil
                    </h3>
                    <form id="formProfile" class="space-y-4">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap</label>
                                <input type="text" name="nama" id="inputNama" class="w-full border p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50 focus:bg-white transition" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Username</label>
                                <input type="text" name="username" id="inputUsername" class="w-full border p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50 focus:bg-white transition" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                            <input type="email" name="email" id="inputEmail" class="w-full border p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50 focus:bg-white transition" required>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg text-sm transition shadow-md">Simpan Profil</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-bold text-lg text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-lock text-orange-600"></i> Ganti Password
                    </h3>
                    <form id="formPassword" class="space-y-4">
                        <input type="hidden" name="action" value="change_password">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Password Lama</label>
                            <input type="password" name="old_password" class="w-full border p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 outline-none bg-slate-50 focus:bg-white transition" required>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Password Baru</label>
                                <input type="password" name="new_password" id="newPass" class="w-full border p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 outline-none bg-slate-50 focus:bg-white transition" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" id="confPass" class="w-full border p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 outline-none bg-slate-50 focus:bg-white transition" required>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-6 rounded-lg text-sm transition shadow-md">Ganti Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-white border-t border-slate-200 py-10 mt-12">
        <div class="container mx-auto px-6 text-center">
            <p class="font-bold text-slate-800 text-lg mb-2">MarketPlace</p>
            <p class="text-slate-500 text-sm">&copy; 2025 Daniel & Aldwin.</p>
        </div>
    </footer>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const transitionEl = document.getElementById('page-transition');
        window.addEventListener('pageshow', function(event) {
            if (transitionEl) transitionEl.classList.add('page-loaded');
        });
        setTimeout(() => {
            if (transitionEl) transitionEl.classList.add('page-loaded');
        }, 100);
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

    $(document).ready(function() {
        $('#navProfileTrigger').click(function(e){ e.stopPropagation(); $('#navProfileDropdown').slideToggle(150); $('#navChevron').toggleClass('rotate-180'); });
        $(document).click(function(){ $('#navProfileDropdown').slideUp(150); $('#navChevron').removeClass('rotate-180'); });

        loadUserData();

        function loadUserData() {
            $.post('../api/user.php', { action: 'get_user' }, function(response) {
                if(response.status === 'success') {
                    let u = response.data;
                    $('#inputNama').val(u.nama_lengkap);
                    $('#inputUsername').val(u.username);
                    $('#inputEmail').val(u.email);
                }
            }, 'json');
        }

        $('#formProfile').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '../api/user.php', type: 'POST', data: $(this).serialize(), dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 1500, showConfirmButton: false }).then(() => location.reload());
                    } else { Swal.fire('Gagal', response.message, 'error'); }
                }
            });
        });

        $('#formPassword').submit(function(e) {
            e.preventDefault();
            let newP = $('#newPass').val();
            let confP = $('#confPass').val();

            if(newP !== confP) {
                Swal.fire('Error', 'Konfirmasi password tidak cocok!', 'error');
                return;
            }

            $.ajax({
                url: '../api/user.php', type: 'POST', data: $(this).serialize(), dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 1500, showConfirmButton: false }).then(() => { $('#formPassword')[0].reset(); });
                    } else { Swal.fire('Gagal', response.message, 'error'); }
                }
            });
        });
    });

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