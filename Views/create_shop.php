<?php //aldwin
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buka Toko Gratis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center font-sans text-slate-700 py-10">

    <div class="bg-white p-8 md:p-10 rounded-xl shadow-xl w-full max-w-lg border border-slate-100 animate-enter relative">
        
        <div class="absolute top-0 right-0 p-4 opacity-10 text-blue-600">
            <i class="fas fa-store text-9xl"></i>
        </div>

        <div class="mb-6 relative z-10">
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Mulai Bisnismu!</h1>
            <p class="text-slate-500">Isi data di bawah ini untuk membuka toko.</p>
        </div>
        
        <form id="createShopForm" class="space-y-4 relative z-10">
            <input type="hidden" name="action" value="create_shop">
            
            <div>
                <label class="block text-sm font-semibold mb-1 text-slate-600">Nama Toko</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-tag"></i></span>
                    <input type="text" name="nama_toko" class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm"  required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1 text-slate-600">Alamat Toko</label>
                <div class="relative">
                    <span class="absolute top-3 left-0 flex items-start pl-3 text-slate-400"><i class="fas fa-map-marker-alt"></i></span>
                    <textarea name="alamat_toko" class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm" rows="2" required></textarea>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1 text-slate-600">Deskripsi </label>
                <textarea name="deskripsi_toko" class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm" rows="3" ></textarea>
            </div>

            <button type="submit" id="btnSave" class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition-all shadow-md flex justify-center items-center gap-2">
                <i class="fas fa-paper-plane"></i> Buka Toko Sekarang
            </button>
            
            <div class="text-center mt-4">
                <a href="../index.php" class="text-sm text-slate-500 hover:text-blue-600">Batal, kembali ke halaman utama</a>
            </div>
        </form>
    </div>

    <script>
    $(document).ready(function() {
        $('#createShopForm').submit(function(e) {
            e.preventDefault();
            
            let btn = $('#btnSave');
            btn.prop('disabled', true).text('Memproses...');

            $.ajax({
                url: '../api/shop.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Selamat!',
                            text: 'Tokomu berhasil dibuat.',
                            confirmButtonColor: '#2563EB'
                        }).then(() => {
                            
                            window.location.href = 'manage_products.php';
                        });
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Buka Toko Sekarang');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Buka Toko Sekarang');
                }
            });
        });
    });
    </script>
</body>
</html>