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


$query_produk = "SELECT p.*, s.nama_toko FROM products p JOIN shops s ON p.shop_id = s.id ORDER BY p.created_at DESC LIMIT 12";
$stmt_produk = $db->prepare($query_produk);
$stmt_produk->execute();
$products = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);

$query_kategori = "SELECT * FROM categories ORDER BY nama_kategori ASC LIMIT 8";
$stmt_kategori = $db->prepare($query_kategori);
$stmt_kategori->execute();
$categories = $stmt_kategori->fetchAll(PDO::FETCH_ASSOC);


function getCategoryIcon($name) {
    $name = strtolower($name);
    if(strpos($name, 'elektronik') !== false) return 'fa-mobile-alt';
    if(strpos($name, 'pakaian') !== false || strpos($name, 'fashion') !== false) return 'fa-tshirt';
    if(strpos($name, 'hobi') !== false) return 'fa-gamepad';
    if(strpos($name, 'makanan') !== false) return 'fa-utensils';
    if(strpos($name, 'otomotif') !== false) return 'fa-car';
    if(strpos($name, 'rumah') !== false) return 'fa-couch';
    if(strpos($name, 'kesehatan') !== false) return 'fa-heartbeat';
    if(strpos($name, 'komputer') !== false) return 'fa-laptop';
    return 'fa-box'; 
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - Belanja Mudah</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; }
        
    
        .hero-text-highlight {
            background: linear-gradient(120deg, #dbeafe 0%, #dbeafe 100%);
            background-repeat: no-repeat;
            background-size: 100% 30%;
            background-position: 0 85%;
        }
        
       
        .bg-hero-gradient {
            background: linear-gradient(180deg, #f0f9ff 0%, #ffffff 100%);
        }
    </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white sticky top-0 z-50 border-b border-slate-100">
        <div class="container mx-auto px-4 sm:px-6 h-20 flex items-center justify-between gap-4">
            
            <a href="index.php" class="flex items-center gap-2 flex-shrink-0">
                <div class="bg-blue-600 text-white p-2 rounded-xl shadow-lg shadow-blue-200">
                    <i class="fas fa-shopping-bag text-lg"></i>
                </div>
                <span class="text-xl font-extrabold text-slate-800 tracking-tight hidden md:block">MarketPlace</span>
            </a>

            <div class="flex-1 max-w-2xl mx-4">
                <form action="views/browse.php" method="GET" class="relative group">
                    <input type="text" name="search"
                           class="w-full border border-slate-200 bg-slate-50 rounded-full py-3 pl-12 pr-6 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:bg-white transition-all shadow-sm" 
                           placeholder="Cari barang apa hari ini?">
                    <i class="fas fa-search absolute left-4 top-3.5 text-slate-400 group-focus-within:text-blue-500 transition text-lg"></i>
                </form>
            </div>

            <div class="flex items-center gap-4 flex-shrink-0">
                <?php if ($is_logged_in): ?>
                    <a href="#" class="text-slate-500 hover:text-blue-600 p-2 relative transition">
                        <a href="views/cart.php" class="text-slate-500 hover:text-blue-600 p-2 relative transition">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white">0</span>
                    </a>
                    <div class="h-8 w-px bg-slate-200 hidden md:block"></div>
                    <div class="relative">
                        <button id="navProfileTrigger" class="flex items-center gap-2 hover:bg-slate-50 p-1 pr-3 rounded-full transition border border-transparent hover:border-slate-200">
                            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border-2 border-white shadow-sm">
                                <?php echo strtoupper(substr($nama, 0, 1)); ?>
                            </div>
                            <span class="text-sm font-semibold text-slate-700 hidden md:block max-w-[100px] truncate"><?php echo htmlspecialchars($nama); ?></span>
                            <i class="fas fa-chevron-down text-xs text-slate-400 ml-1 transition-transform duration-200" id="navChevron"></i>
                        </button>
                        <div id="navProfileDropdown" class="hidden absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50 origin-top-right ring-1 ring-black/5">
                            <div class="p-5 bg-slate-50 border-b border-slate-100 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg shadow-md"><i class="fas fa-user"></i></div>
                                <div>
                                    <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($nama); ?></p>
                                    <p class="text-xs text-slate-500 capitalize"><?php echo ($role === 'admin') ? 'Admin' : 'User'; ?></p>
                                </div>
                            </div>
                            <div class="p-2 space-y-1">
                                <?php if($has_shop): ?>
                                    <a href="views/manage_products.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition font-medium"><i class="fas fa-store w-5"></i> Toko Saya</a>
                                <?php elseif($role != 'admin'): ?>
                                    <a href="views/create_shop.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition font-medium"><i class="fas fa-store w-5"></i> Buka Toko Gratis</a>
                                <?php endif; ?>
                                <a href="views/settings.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition font-medium"><i class="fas fa-cog w-5"></i> Pengaturan</a>
                                <div class="h-px bg-slate-100 my-1 mx-2"></div>
                                <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-xl transition font-medium"><i class="fas fa-sign-out-alt w-5"></i> Keluar</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="views/login.php" class="text-slate-600 hover:text-blue-600 font-bold text-sm px-4 py-2 transition">Masuk</a>
                    <a href="views/register.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-full text-sm transition shadow-lg shadow-blue-200">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="bg-hero-gradient pt-16 pb-12 px-4">
        <div class="text-center max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 py-1.5 px-4 rounded-full bg-white text-blue-600 text-[11px] font-bold uppercase tracking-widest mb-6 border border-blue-100 shadow-sm animate-enter">
                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span> Marketplace #1 Indonesia
            </div>
            
            <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 tracking-tight mb-6 leading-tight animate-enter" style="animation-delay: 0.1s">
                Jual Beli <span class="text-blue-600 hero-text-highlight px-2">Apa Saja</span> <br class="hidden md:block">
                Lebih Mudah & Aman.
            </h1>
            
            <p class="text-slate-500 text-lg max-w-2xl mx-auto leading-relaxed animate-enter" style="animation-delay: 0.2s">
                Temukan ribuan barang unik dari penjual terpercaya atau mulai bisnis onlinemu sendiri dalam hitungan menit.
            </p>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 pb-20 -mt-6 relative z-10">

        <div class="bg-white border border-slate-200 rounded-[2.5rem] p-8 md:p-10 mb-12 shadow-sm animate-enter" style="animation-delay: 0.3s">
            
            <div class="flex justify-between items-end mb-8 px-2">
                <h2 class="text-2xl font-bold text-slate-900">Kategori Populer</h2>
                <a href="views/browse.php" class="text-blue-600 text-sm font-bold hover:underline">Lihat Semua</a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 md:gap-6">
                <?php foreach($categories as $cat): ?>
                    <a href="views/browse.php?category=<?php echo $cat['id']; ?>" class="group flex flex-col items-center justify-center gap-4 p-4 rounded-2xl border border-slate-100 hover:border-blue-400 hover:shadow-lg hover:-translate-y-1 transition duration-300 cursor-pointer bg-white h-36">
                        <div class="w-12 h-12 rounded-full bg-slate-50 text-slate-500 flex items-center justify-center text-xl group-hover:bg-blue-50 group-hover:text-blue-600 transition duration-300">
                            <i class="fas <?php echo getCategoryIcon($cat['nama_kategori']); ?>"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-600 group-hover:text-blue-600 text-center line-clamp-2 transition">
                            <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="px-2 animate-enter" style="animation-delay: 0.4s">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Rekomendasi Terbaru</h2>
                    <p class="text-slate-500 mt-1 text-sm">Barang pilihan yang baru saja diupload.</p>
                </div>
            </div>

            <?php if (count($products) > 0): ?>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    <?php foreach($products as $prod): ?>
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition duration-300 group cursor-pointer flex flex-col h-full" onclick="addToCart(<?php echo $prod['id']; ?>)">
                            <div class="h-56 bg-slate-50 relative overflow-hidden">
                                <img src="<?php echo $prod['gambar'] ? 'assets/images/'.$prod['gambar'] : 'https://via.placeholder.com/300?text=No+Image'; ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                
                                <?php if(strtotime($prod['created_at']) > strtotime('-7 days')): ?>
                                <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-md text-[10px] font-bold text-blue-600 shadow-sm border border-slate-200">
                                    BARU
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-4 flex flex-col flex-grow">
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1.5">
                                    <?php echo htmlspecialchars($prod['nama_toko']); ?>
                                </div>
                                <h3 class="font-bold text-slate-800 text-sm line-clamp-2 mb-3 group-hover:text-blue-600 transition h-10 leading-snug">
                                    <?php echo htmlspecialchars($prod['nama_produk']); ?>
                                </h3>
                                <div class="flex items-center justify-between mt-auto pt-3 border-t border-slate-50">
                                    <p class="text-slate-900 font-extrabold text-lg">Rp <?php echo number_format($prod['harga'], 0, ',', '.'); ?></p>
                                    <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition">
                                        <i class="fas fa-plus text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-slate-300">
                    <i class="fas fa-search text-4xl text-slate-300 mb-3"></i>
                    <h3 class="text-lg font-bold text-slate-700">Belum ada produk</h3>
                    <p class="text-slate-500">Jadilah yang pertama menjual barang disini!</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <footer class="bg-white border-t border-slate-100 py-10 mt-12">
        <div class="container mx-auto px-6 text-center">
            <p class="font-bold text-slate-800 text-lg mb-2">Toko Online</p>
            <p class="text-slate-500 text-sm">&copy; C14240126 Daniel, C14240132 Aldwin .</p>
        </div>
    </footer>

    <script>
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        const role = "<?php echo $role; ?>";

        $(document).ready(function(){
            
            $('#navProfileTrigger').click(function(e){
                e.stopPropagation(); 
                $('#navProfileDropdown').slideToggle(150); 
                $('#navChevron').toggleClass('rotate-180');
            });
            $(document).click(function(){
                $('#navProfileDropdown').slideUp(150);
                $('#navChevron').removeClass('rotate-180');
            });
            $('#navProfileDropdown').click(function(e){
                e.stopPropagation();
            });
        });

        function addToCart(productId) {
            if (!isLoggedIn) {
                Swal.fire({
                    icon: 'warning', title: 'Login Dulu', text: 'Silakan login untuk mulai belanja',
                    showCancelButton: true, confirmButtonText: 'Login', cancelButtonText: 'Batal', confirmButtonColor: '#2563EB'
                }).then((result) => {
                    if (result.isConfirmed) window.location.href = 'views/login.php';
                });
                return; 
            }
            if (role === 'admin') {
                Swal.fire('Info', 'Admin tidak bisa belanja.', 'info');
                return;
            }
            
    $.ajax({
        url: 'api/cart.php',
        type: 'POST',
        data: { action: 'add', product_id: productId },
        dataType: 'json',
        success: function(response) {
            if(response.status === 'success') {
                Swal.fire({
                    icon: 'success', title: 'Berhasil', text: 'Produk masuk keranjang',
                    timer: 1000, showConfirmButton: false, position: 'bottom-end', toast: true
                });
                
            } else {
                Swal.fire('Gagal', 'Terjadi kesalahan', 'error');
            }
        }
    });
}
    </script>
</body>
</html>