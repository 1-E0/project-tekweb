<!DOCTYPE html> 
<html lang="id">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Toko Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-50 h-screen flex items-center justify-center font-sans text-slate-700">

    <div class="bg-white p-8 md:p-10 rounded-xl shadow-xl w-full max-w-sm border border-slate-100 animate-enter">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">LOGIN</h1>
        </div>
        
        <form id="loginForm" class="space-y-5">
            <input type="hidden" name="action" value="login">
            
            <div>
                <label class="block text-sm font-semibold mb-2 text-slate-600">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" name="username" class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" placeholder="username" required>
                </div>
            </div>

            <div>
                <div class="flex justify-between mb-2">
                    <label class="block text-sm font-semibold text-slate-600">Password</label>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" placeholder="••••••••" required>
                </div>
            </div>
            
            <button type="submit" id="btnSubmit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg transition-all duration-200 transform active:scale-95 shadow-md hover:shadow-lg">
                <span id="btnText">Masuk</span>
                <span id="btnLoading" class="hidden flex items-center justify-center gap-2">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </span>
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-sm text-slate-500">
                Belum punya akun? 
                <a href="register.php" class="text-blue-600 font-semibold hover:text-blue-800 transition">Register</a>
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
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1000,
                            timerProgressBar: false,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });

                        Toast.fire({ icon: 'success', title: 'Login Berhasil' }).then(() => {
                            if (response.role === 'admin') {
                                window.location.href = 'admin_dashboard.php';
                            } else {
                                window.location.href = '../index.php';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message,
                            confirmButtonColor: '#2563EB',
                        });
                        
                        btn.prop('disabled', false).removeClass('bg-blue-400 cursor-not-allowed');
                        btnText.removeClass('hidden');
                        btnLoading.addClass('hidden');
                    }
                },
                error: function() {
                    Swal.fire({ title: 'Error', text: 'Koneksi terputus', icon: 'error' });
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