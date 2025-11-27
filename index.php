<?php
session_start();
require_once 'config/Database.php';


$is_logged_in = false;
$role = 'guest';
$nama = 'Pengunjung';
$user_id = null;
$has_shop = false;
$shop_data = null;


if (isset($_SESSION['user_id'])) {
    $is_logged_in = true;
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role']; 
    $nama = $_SESSION['nama'];

    
    if ($role == 'member') {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM shops WHERE user_id = :uid LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':uid', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $has_shop = true;
            $shop_data = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}


$database = new Database();
$db = $database->getConnection();

$query_produk = "SELECT p.*, s.nama_toko FROM products p JOIN shops s ON p.shop_id = s.id ORDER BY p.created_at DESC";
$stmt_produk = $db->prepare($query_produk);
$stmt_produk->execute();
$products = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - Belanja Mudah</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-50 font-sans text-slate-700">

    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-2 group">
                <div class="bg-blue-600 text-white p-2 rounded-lg group-hover:bg-blue-700 transition">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <span class="text-xl font-bold text-slate-800 tracking-tight">Marketplace</span>
            </a>

            <div class="hidden md:flex flex-1 mx-10">
                <div class="relative w-full">
                    <input type="text" class="w-full border border-slate-300 rounded-full py-2 px-5 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50" placeholder="Cari barang apa hari ini?">
                    <i class="fas fa-search absolute left-4 top-3 text-slate-400"></i>
                </div>
            </div>

            <div class="flex items-center gap-3">
                
                <?php if ($is_logged_in): ?>
                    <div class="hidden md:block text-right mr-2">
                        <p class="text-sm font-bold text-slate-800 text-ellipsis overflow-hidden w-24 whitespace-nowrap text-right"><?php echo htmlspecialchars($nama); ?></p>
                        <p class="text-xs text-slate-500 capitalize"><?php echo $role; ?></p>
                    </div>
                    
                    <?php if($has_shop): ?>
                        <a href="views/my_shop.php" class="text-slate-600 hover:text-blue-600 p-2" title="Toko Saya"><i class="fas fa-store"></i></a>
                    <?php endif; ?>
                    
                    <a href="#" class="text-slate-600 hover:text-blue-600 p-2 relative">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="absolute top-0 right-0 bg-red-500 text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full">0</span>
                    </a>

                    <a href="logout.php" class="bg-red-50 hover:bg-red-100 text-red-600 p-2 rounded-full transition-all" title="Keluar">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>

                <?php else: ?>
                    <a href="views/login.php" class="text-slate-600 hover:text-blue-600 font-semibold text-sm px-3">Masuk</a>
                    <a href="views/register.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full text-sm transition shadow-md">Daftar</a>
                <?php endif; ?>

            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 py-8 animate-enter">

        <?php if ($role == 'admin'): ?>
            <div class="bg-blue-600 rounded-2xl p-8 text-white shadow-lg mb-8 relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-3xl font-bold mb-2">Dashboard Admin</h2>
                    <p class="text-blue-100">Mode pengelolaan sistem aktif.</p>
                </div>
                <i class="fas fa-user-shield absolute -right-6 -bottom-6 text-9xl text-white opacity-10"></i>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <h3 class="font-bold"><i class="fas fa-tags text-orange-500 mr-2"></i> Kelola Kategori</h3>
                    <button class="text-blue-600 text-sm mt-2 hover:underline">Buka &rarr;</button>
                </div>
            </div>
        <?php endif; ?>


        <?php if (!$has_shop && $role != 'admin'): ?>
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-6 md:p-10 text-white shadow-xl text-center relative overflow-hidden mb-10">
                <div class="relative z-10 max-w-2xl mx-auto">
                    <?php if($role == 'guest'): ?>
                        <h2 class="text-2xl md:text-3xl font-bold mb-2">Selamat Datang di Marketplace!</h2>
                        <p class="text-indigo-100 mb-6">Temukan barang impianmu dengan harga terbaik.</p>
                        <a href="views/register.php" class="bg-white text-indigo-600 font-bold py-2 px-6 rounded-full shadow-lg hover:bg-indigo-50 transition">Gabung Sekarang</a>
                    <?php else: ?>
                        <h2 class="text-2xl md:text-3xl font-bold mb-2">Mau Jualan?</h2>
                        <p class="text-indigo-100 mb-6">Buka tokomu gratis dan mulai hasilkan uang.</p>
                        <a href="views/create_shop.php" class="bg-white text-indigo-600 font-bold py-2 px-6 rounded-full shadow-lg hover:bg-indigo-50 transition">Buka Toko Gratis</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>


        <div class="flex justify-between items-end mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Rekomendasi Produk</h2>
            <a href="#" class="text-blue-600 text-sm font-semibold hover:underline">Lihat Semua</a>
        </div>

        <?php if (count($products) > 0): ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                <?php foreach($products as $prod): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition group">
                        <div class="h-48 bg-slate-200 w-full relative overflow-hidden">
                            <img src="<?php echo $prod['gambar'] ? 'assets/images/'.$prod['gambar'] : 'https://via.placeholder.com/300?text=No+Image'; ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        </div>
                        
                        <div class="p-4">
                            <div class="text-xs text-slate-500 mb-1"><i class="fas fa-store mr-1"></i> <?php echo htmlspecialchars($prod['nama_toko']); ?></div>
                            <h3 class="font-bold text-slate-800 text-lg mb-1 truncate"><?php echo htmlspecialchars($prod['nama_produk']); ?></h3>
                            <p class="text-orange-600 font-bold text-lg">Rp <?php echo number_format($prod['harga'], 0, ',', '.'); ?></p>
                            
                            <div class="mt-4">
                                <button onclick="addToCart(<?php echo $prod['id']; ?>)" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition">
                                    <i class="fas fa-cart-plus mr-1"></i> Beli
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12 bg-white rounded-xl border border-dashed border-slate-300">
                <i class="fas fa-box-open text-4xl text-slate-300 mb-3"></i>
                <p class="text-slate-500">Belum ada produk yang dijual saat ini.</p>
                <?php if($role == 'member' && $has_shop): ?>
                    <p class="text-sm text-blue-500 mt-2">Jadilah yang pertama menjual barang!</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

    <script>
        
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        const role = "<?php echo $role; ?>";

        function addToCart(productId) {
            
            if (!isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Terbatas',
                    text: 'Silakan Login atau Daftar untuk membeli barang!',
                    showCancelButton: true,
                    confirmButtonText: 'Login Sekarang',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#2563EB'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'views/login.php';
                    }
                });
                return; 
            }

            if (role === 'admin') {
                Swal.fire('Info', 'Admin tidak bisa belanja, admin hanya memantau.', 'info');
                return;
            }

            
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Barang (ID: ' + productId + ') masuk ke keranjang! (Simulasi)',
                timer: 1500,
                showConfirmButton: false
            });
        }
    </script>
</body>
</html>