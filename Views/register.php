<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Toko Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-50 h-screen w-full flex items-center justify-center relative overflow-hidden">

    <div class="absolute top-0 left-0 w-full h-full">
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-blue-200 rounded-full blur-[100px] opacity-40"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] bg-indigo-200 rounded-full blur-[100px] opacity-40"></div>
    </div>

    <div class="relative z-10 glass p-8 md:p-10 rounded-3xl shadow-2xl w-full max-w-md animate-enter">
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-slate-800">Buat Akun Baru</h1>
            <p class="text-slate-500 text-sm mt-1">Lengkapi data untuk mulai berbelanja</p>
        </div>
        
        <form id="registerForm" class="space-y-4">
            <input type="hidden" name="action" value="register">
            
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Nama Lengkap</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-id-card"></i></span>
                    <input type="text" name="nama" class="w-full pl-11 pr-4 py-2.5 input-modern rounded-xl text-sm" placeholder="Nama Lengkap" required>
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="w-full pl-11 pr-4 py-2.5 input-modern rounded-xl text-sm" placeholder="email@contoh.com" required>
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="w-full pl-11 pr-4 py-2.5 input-modern rounded-xl text-sm" placeholder="username" required>
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-key"></i></span>
                    <input type="password" name="password" class="w-full pl-11 pr-4 py-2.5 input-modern rounded-xl text-sm" placeholder="Password" required>
                </div>
            </div>

            <button type="submit" id="btnReg" class="w-full btn-primary py-3 rounded-xl font-bold mt-2">
                <span id="btnRegText">Daftar Sekarang</span>
                <span id="btnRegLoading" class="hidden flex items-center justify-center gap-2">
                    <i class="fas fa-spinner fa-spin"></i> Memproses...
                </span>
            </button>
        </form>
        
        <div class="mt-6 text-center pt-4 border-t border-slate-200">
            <p class="text-sm text-slate-500">
                Sudah punya akun? <a href="login.php" class="text-blue-600 font-bold hover:text-blue-800 transition">Login</a>
            </p>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#registerForm').submit(function(e) {
            e.preventDefault();
            let btn = $('#btnReg');
            let btnText = $('#btnRegText');
            let btnLoading = $('#btnRegLoading');
            
            btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
            btnText.addClass('hidden');
            btnLoading.removeClass('hidden');

            $.ajax({
                url: '../api/auth.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Akun telah dibuat. Silakan login.', confirmButtonColor: '#2563EB' })
                        .then(() => { window.location.href = 'login.php'; });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: response.message, confirmButtonColor: '#DC2626' });
                        btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                        btnText.removeClass('hidden');
                        btnLoading.addClass('hidden');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal koneksi server', 'error');
                    btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    btnText.removeClass('hidden');
                    btnLoading.addClass('hidden');
                }
            });
        });
    });
    </script>
</body>
</html>