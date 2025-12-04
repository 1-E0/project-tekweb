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
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
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
                    <input type="text" class="w-full border border-slate-300 rounded-full py-2 px-5 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition" placeholder="Cari barang apa hari ini?">
                    <i class="fas fa-search absolute left-4 top-3 text-slate-400"></i>
                </div>
            </div>

            <div class="flex items-center gap-4">
                
                <?php if ($is_logged_in): ?>
                    
                    <a href="#" class="text-slate-500 hover:text-blue-600 p-2 relative transition group">
                        <i class="fas fa-shopping-cart text-lg group-hover:scale-110 transition"></i>
                        <span class="absolute top-0 right-0 bg-red-500 text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full">0</span>
                    </a>

                    <?php if($has_shop): ?>
                        <a href="views/manage_products.php" class="text-slate-500 hover:text-blue-600 p-2 transition" title="Toko Saya">
                            <i class="fas fa-store text-lg"></i>
                        </a>
                    <?php endif; ?>

                    <div class="h-6 w-px bg-slate-200 hidden md:block"></div>

                    <div class="relative">
                        <button id="navProfileTrigger" class="flex items-center gap-2 hover:bg-slate-50 p-1 pr-2 rounded-full transition group cursor-pointer border border-transparent hover:border-slate-200">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-200">
                                <?php 
                                    $parts = explode(" ", $nama);
                                    echo strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                                ?>
                            </div>
                            <span class="text-sm font-semibold text-slate-700 hidden md:block max-w-[100px] truncate"><?php echo htmlspecialchars($nama); ?></span>
                            <i class="fas fa-chevron-down text-xs text-slate-400 ml-1 transition-transform duration-200" id="navChevron"></i>
                        </button>

                        <div id="navProfileDropdown" class="hidden absolute right-0 mt-3 w-64 bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden z-50 origin-top-right">
                            <div class="p-4 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg shadow-sm">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($nama); ?></p>
                                    <p class="text-xs text-slate-500 capitalize"><?php echo ($role === 'admin') ? 'Admin' : 'User'; ?></p>
                                </div>
                            </div>

                            <div class="p-2 space-y-1">
                                <?php if(!$has_shop && $role != 'admin'): ?>
                                    <a href="views/create_shop.php" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition group">
                                        <div class="w-6 text-center"><i class="fas fa-store text-slate-400 group-hover:text-blue-500"></i></div>
                                        Buka Toko Gratis
                                    </a>
                                <?php endif; ?>
                                
                                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 rounded-lg transition group">
                                    <div class="w-6 text-center"><i class="fas fa-cog text-slate-400 group-hover:text-blue-500"></i></div>
                                    Pengaturan
                                </a>
                                
                                <div class="h-px bg-slate-100 my-1 mx-2"></div>
                                
                                <a href="logout.php" class="flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition group">
                                    <div class="w-6 text-center"><i class="fas fa-sign-out-alt text-red-400 group-hover:text-red-500"></i></div>
                                    Keluar
                                </a>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <a href="views/login.php" class="text-slate-600 hover:text-blue-600 font-semibold text-sm px-3 transition">Masuk</a>
                    <a href="views/register.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-full text-sm transition shadow-md shadow-blue-200">Daftar</a>
                <?php endif; ?>

            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 py-8 animate-enter min-h-screen">

        <?php if ($role == 'admin'): ?>
            <div class="bg-blue-600 rounded-2xl p-8 text-white shadow-lg mb-8 relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-3xl font-bold mb-2">Dashboard Admin</h2>
                    <p class="text-blue-100">Mode pengelolaan sistem aktif.</p>
                </div>
                <i class="fas fa-user-shield absolute -right-6 -bottom-6 text-9xl text-white opacity-10"></i>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 hover:shadow-md transition cursor-pointer">
                    <h3 class="font-bold text-slate-700"><i class="fas fa-tags text-orange-500 mr-2"></i> Kelola Kategori</h3>
                    <button class="text-blue-600 text-sm mt-2 hover:underline">Buka &rarr;</button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$has_shop && $role != 'admin'): ?>
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 md:p-10 text-white shadow-xl text-center relative overflow-hidden mb-10">
                <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                    <i class="fas fa-shopping-bag absolute top-[-20px] left-[-20px] text-9xl"></i>
                    <i class="fas fa-gift absolute bottom-[-20px] right-[-20px] text-9xl"></i>
                </div>

                <div class="relative z-10 max-w-2xl mx-auto">
                    <?php if($role == 'guest'): ?>
                        <h2 class="text-2xl md:text-3xl font-bold mb-3">Selamat Datang di Marketplace!</h2>
                        <p class="text-indigo-100 mb-6 text-lg">Temukan barang impianmu dengan harga terbaik dan aman.</p>
                        <a href="views/register.php" class="bg-white text-indigo-600 font-bold py-3 px-8 rounded-full shadow-lg hover:bg-indigo-50 transition transform hover:-translate-y-1">Gabung Sekarang</a>
                    <?php else: ?>
                        <h2 class="text-2xl md:text-3xl font-bold mb-3">Mau Jualan?</h2>
                        <p class="text-indigo-100 mb-6 text-lg">Buka tokomu gratis dan mulai hasilkan uang dari rumah.</p>
                        <a href="views/create_shop.php" class="bg-white text-indigo-600 font-bold py-3 px-8 rounded-full shadow-lg hover:bg-indigo-50 transition transform hover:-translate-y-1">Buka Toko Gratis</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex justify-between items-end mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Rekomendasi Produk</h2>
                <p class="text-slate-500 text-sm">Pilihan terbaik untukmu hari ini</p>
            </div>
            <a href="#" class="text-blue-600 text-sm font-semibold hover:underline">Lihat Semua</a>
        </div>

        <?php if (count($products) > 0): ?>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6">
                <?php foreach($products as $prod): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition duration-300 group flex flex-col h-full">
                        <div class="h-48 bg-slate-200 w-full relative overflow-hidden">
                            <img src="<?php echo $prod['gambar'] ? 'assets/images/'.$prod['gambar'] : 'https://via.placeholder.com/300?text=No+Image'; ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        </div>
                        
                        <div class="p-4 flex flex-col flex-grow">
                            <div class="text-xs text-slate-500 mb-1 flex items-center gap-1">
                                <i class="fas fa-store text-slate-400"></i> <?php echo htmlspecialchars($prod['nama_toko']); ?>
                            </div>
                            <h3 class="font-bold text-slate-800 text-base mb-1 truncate" title="<?php echo htmlspecialchars($prod['nama_produk']); ?>">
                                <?php echo htmlspecialchars($prod['nama_produk']); ?>
                            </h3>
                            <p class="text-orange-600 font-bold text-lg mb-4">Rp <?php echo number_format($prod['harga'], 0, ',', '.'); ?></p>
                            
                            <div class="mt-auto">
                                <button onclick="addToCart(<?php echo $prod['id']; ?>)" class="w-full bg-white border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-bold py-2 px-4 rounded-lg text-sm transition flex items-center justify-center gap-2">
                                    <i class="fas fa-cart-plus"></i> Beli
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16 bg-white rounded-xl border border-dashed border-slate-300">
                <div class="bg-slate-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-box-open text-4xl text-slate-300"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700">Belum ada produk</h3>
                <p class="text-slate-500">Jadilah yang pertama menjual barang disini!</p>
                <?php if($role == 'member' && $has_shop): ?>
                    <a href="views/manage_products.php" class="text-blue-600 font-semibold mt-2 inline-block hover:underline">Tambah Produk Sekarang</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

    <script>
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        const role = "<?php echo $role; ?>";

        $(document).ready(function(){
            $('#navProfileTrigger').click(function(e){
                e.stopPropagation(); 
                $('#navProfileDropdown').slideToggle(200); 
                $('#navChevron').toggleClass('rotate-180');
            });

            $(document).click(function(){
                $('#navProfileDropdown').slideUp(200);
                $('#navChevron').removeClass('rotate-180');
            });

            $('#navProfileDropdown').click(function(e){
                e.stopPropagation();
            });
        });

        function addToCart(productId) {
            if (!isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Terbatas',
                    text: 'Silakan Login atau Daftar untuk berbelanja!',
                    showCancelButton: true,
                    confirmButtonText: 'Login Sekarang',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#2563EB',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'views/login.php';
                    }
                });
                return; 
            }

            if (role === 'admin') {
                Swal.fire('Info', 'Admin tidak bisa belanja, hanya bisa memantau.', 'info');
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Produk berhasil ditambahkan ke keranjang!',
                timer: 1500,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
            
            let currentCount = parseInt($('.fa-shopping-cart').next('span').text());
            $('.fa-shopping-cart').next('span').text(currentCount + 1);
        }
    </script>
</body>
</html>