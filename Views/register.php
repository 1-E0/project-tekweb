<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Toko Online</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center font-sans text-slate-700 py-6">

    <div class="bg-white p-8 md:p-10 rounded-xl shadow-xl w-full max-w-md border border-slate-100 animate-enter">
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Buat Akun</h1>
            
        </div>
        
        <form id="registerForm" class="space-y-4">
            <input type="hidden" name="action" value="register">
            
            <div>
                <label class="block text-sm font-semibold mb-1 text-slate-600">Nama Lengkap</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-id-card"></i></span>
                    <input type="text" name="nama" class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-sm" placeholder="Nama Lengkap" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1 text-slate-600">Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-sm" placeholder="example@email.com" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1 text-slate-600">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-sm" placeholder="username" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1 text-slate-600">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-key"></i></span>
                    <input type="password" name="password" class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-sm" placeholder="Password" required>
                </div>
            </div>

            <button type="submit" id="btnReg" class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg transition-all duration-200 transform active:scale-95 shadow-md">
                <span id="btnRegText">Daftar Sekarang</span>
                <span id="btnRegLoading" class="hidden flex items-center justify-center gap-2">
                    <i class="fas fa-spinner fa-spin"></i> Memproses...
                </span>
            </button>
        </form>
        
        <div class="mt-6 text-center border-t border-slate-100 pt-4">
            <p class="text-sm text-slate-500">
                Sudah punya akun? <a href="login.php" class="text-blue-600 font-semibold hover:text-blue-800 transition">Login</a>
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
            
            // Loading State
            btn.prop('disabled', true).addClass('bg-blue-400 cursor-not-allowed');
            btnText.addClass('hidden');
            btnLoading.removeClass('hidden');

            $.ajax({
                url: '../api/auth.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Akun telah dibuat. Silakan login.',
                            confirmButtonColor: '#2563EB'
                        }).then(() => {
                            window.location.href = 'login.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message,
                            confirmButtonColor: '#DC2626'
                        });
                        btn.prop('disabled', false).removeClass('bg-blue-400 cursor-not-allowed');
                        btnText.removeClass('hidden');
                        btnLoading.addClass('hidden');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal koneksi server', 'error');
                    btn.prop('disabled', false).removeClass('bg-blue-400 cursor-not-allowed');
                    btnText.removeClass('hidden');
                    btnLoading.addClass('hidden');
                }
            });
        });
    });
    </script>
</body>
</html>