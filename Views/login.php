<!DOCTYPE html> 
<html lang="id">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Toko Online</title>
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

    <div class="relative z-10 glass p-8 md:p-12 rounded-3xl shadow-2xl w-full max-w-md animate-enter">
        
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl mx-auto flex items-center justify-center text-white text-2xl shadow-lg mb-4 transform rotate-3 hover:rotate-6 transition">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-800">Selamat Datang</h1>
            <p class="text-slate-500 mt-2">Masuk untuk melanjutkan belanja</p>
        </div>
        
        <form id="loginForm" class="space-y-5">
            <input type="hidden" name="action" value="login">
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="w-full pl-11 pr-4 py-3 input-modern rounded-xl text-sm font-medium" placeholder="Masukkan username" required>
                </div>
            </div>

            <div class="space-y-1">
                 <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Password</label>
                 <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="w-full pl-11 pr-4 py-3 input-modern rounded-xl text-sm font-medium" placeholder="••••••••" required>
                 </div>
            </div>
            
            <button type="submit" id="btnSubmit" class="w-full btn-primary py-3.5 rounded-xl font-bold mt-2">
                <span id="btnText">Masuk Sekarang</span>
                <span id="btnLoading" class="hidden flex items-center justify-center gap-2">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </span>
            </button>
        </form>

        <div class="mt-8 text-center pt-6 border-t border-slate-200">
            <p class="text-sm text-slate-500">
                Belum punya akun? 
                <a href="register.php" class="text-blue-600 font-bold hover:text-blue-800 transition">Daftar disini</a>
            </p>
            <p class="mt-2 text-xs text-slate-400">
                <a href="../index.php" class="hover:text-slate-600 transition">Kembali ke Beranda</a>
            </p>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#loginForm').submit(function(e) {
            e.preventDefault();
            
            let btn = $('#btnSubmit');
            let btnText = $('#btnText');
            let btnLoading = $('#btnLoading');
            
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
                        const Toast = Swal.mixin({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 1000,
                            didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
                        });
                        Toast.fire({ icon: 'success', title: 'Login Berhasil' }).then(() => {
                            if (response.role === 'admin') window.location.href = 'admin_dashboard.php';
                            else window.location.href = '../index.php';
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: response.message, confirmButtonColor: '#2563EB' });
                        btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                        btnText.removeClass('hidden');
                        btnLoading.addClass('hidden');
                    }
                },
                error: function() {
                    Swal.fire({ title: 'Error', text: 'Koneksi terputus', icon: 'error' });
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