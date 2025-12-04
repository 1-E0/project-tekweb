<?php
session_start();
require_once '../config/Database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$from = $_GET['from'] ?? 'home';
$back_url = '../index.php';
$back_label = 'Ke Halaman Utama';

if ($from == 'shop') {
    $back_url = 'manage_products.php';
    $back_label = 'Ke Toko Saya';
} elseif ($from == 'browse') {
    $back_url = 'browse.php';
    $back_label = 'Ke Jelajah Produk';
} elseif ($from == 'cart') {
    $back_url = 'cart.php';
    $back_label = 'Ke Keranjang';
}
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
    <style>body { font-family: 'Inter', sans-serif; background-color: #F8F9FA; }</style>
</head>
<body class="text-slate-800">

    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
            <a href="<?php echo $back_url; ?>" class="flex items-center gap-2 group">
                <div class="bg-blue-600 text-white p-2 rounded-lg transition group-hover:bg-blue-700">
                    <i class="fas fa-arrow-left"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs text-slate-500 font-medium">Kembali</span>
                    <span class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition"><?php echo $back_label; ?></span>
                </div>
            </a>
            <div class="font-bold text-lg text-slate-700">Pengaturan Akun</div>
            <div class="w-10"></div> 
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 py-8 max-w-4xl">
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
                        <h2 class="text-3xl font-bold mb-4">Rp <?php 
                            $db = (new Database())->getConnection();
                            $stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
                            $stmt->execute([$user_id]);
                            $saldo = $stmt->fetchColumn();
                            echo number_format($saldo, 0, ',', '.'); 
                        ?></h2>
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

    <script>
    $(document).ready(function() {
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