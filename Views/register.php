<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Toko Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold text-center mb-2 text-green-600">Buat Akun Baru</h2>
        <p class="text-center text-gray-500 text-sm mb-6">Mulai jualan dan belanja sekarang</p>
        
        <form id="registerForm">
            <input type="hidden" name="action" value="register">
            
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-1">Nama Lengkap</label>
                <input type="text" name="nama" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-green-300" required>
            </div>
            
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-1">Email</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-green-300" required>
            </div>

            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-1">Username</label>
                <input type="text" name="username" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-green-300" required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-1">Password</label>
                <input type="password" name="password" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-green-300" required>
            </div>

            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-200">
                Daftar Sekarang
            </button>
        </form>
        
        <p class="text-center text-sm mt-4">
            Sudah punya akun? <a href="login.php" class="text-blue-500 hover:underline">Login disini</a>
        </p>
    </div>

    <script>
    $(document).ready(function() {
        $('#registerForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '../api/auth.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Berhasil!', response.message, 'success').then(() => {
                            window.location.href = 'login.php';
                        });
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                }
            });
        });
    });
    </script>
</body>
</html>