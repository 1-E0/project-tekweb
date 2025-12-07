<?php
session_start();
require_once 'config/Database.php';

// Logika PHP tetap sama
$is_logged_in = false;
$role = 'guest';
$nama = 'Pengunjung';
$user_id = null;
$has_shop = false;

if (isset($_SESSION['user_id'])) {
    $is_logged_in = true;
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role']; 
    $nama = $_SESSION['nama'];

    if ($role == 'member') {
        $database = new Database();
        $db = $database->getConnection();
        $query = "SELECT id FROM shops WHERE user_id = :uid LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':uid', $user_id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) $has_shop = true;
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
    if(strpos($name, 'pakaian') !== false) return 'fa-tshirt';
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="text-slate-800">

    <nav class="glass sticky top-0 z-50 transition-all duration-300">
        <div class="container mx-auto px-4 sm:px-6 h-20 flex items-center justify-between gap-4">
            <a href="index.php" class="flex items-center gap-2 flex-shrink-0 group">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-600 text-white p-2.5 rounded-xl shadow-lg shadow-blue-200 group-hover:scale-105 transition duration-300">
                    <i class="fas fa-shopping-bag text-lg"></i>
                </div>
                <span class="text-xl font-extrabold text-slate-800 tracking-tight hidden md:block">MarketPlace</span>
            </a>

            <div class="flex-1 max-w-2xl mx-4">
                <form action="views/browse.php" method="GET" class="relative group">
                    <input type="text" name="search"
                           class="w-full input-modern rounded-full py-2.5 pl-12 pr-6 text-sm" 
                           placeholder="Cari produk impianmu...">
                    <i class="fas fa-search absolute left-4 top-3 text-slate-400 group-focus-within:text-blue-500 transition"></i>
                </form>
            </div>

            <div class="flex items-center gap-4 flex-shrink-0">
                <?php if ($is_logged_in): ?>
                    <a href="views/cart.php" class="text-slate-500 hover:text-blue-600 p-2 relative transition">
                        <i class="fas fa-shopping-cart text-xl"></i>
                    </a>
                    <div class="relative">
                        <button id="navProfileTrigger" class="flex items-center gap-2 hover:bg-white/50 p-1 pr-3 rounded-full transition border border-transparent hover:border-slate-200">
                            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border-2 border-white shadow-sm">
                                <?php echo strtoupper(substr($nama, 0, 1)); ?>
                            </div>
                            <span class="text-sm font-semibold text-slate-700 hidden md:block max-w-[100px] truncate"><?php echo htmlspecialchars($nama); ?></span>
                            <i class="fas fa-chevron-down text-xs text-slate-400 ml-1 transition" id="navChevron"></i>
                        </button>
                        <div id="navProfileDropdown" class="hidden absolute right-0 mt-3 w-64 bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50 animate-enter">
                            <div class="p-5 border-b border-slate-100 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg"><i class="fas fa-user"></i></div>
                                <div>
                                    <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($nama); ?></p>
                                    <p class="text-xs text-slate-500 capitalize"><?php echo $role; ?></p>
                                </div>
                            </div>
                            <div class="p-2 space-y-1">
                                <?php if($has_shop): ?>
                                    <a href="views/manage_products.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-store w-5"></i> Toko Saya</a>
                                <?php elseif($role != 'admin'): ?>
                                    <a href="views/create_shop.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-store w-5"></i> Buka Toko</a>
                                <?php endif; ?>
                                <a href="views/settings.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-cog w-5"></i> Pengaturan</a>
                                <div class="h-px bg-slate-100 my-1 mx-2"></div>
                                <a href="logout.php" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-xl transition"><i class="fas fa-sign-out-alt w-5"></i> Keluar</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="views/login.php" class="text-slate-600 hover:text-blue-600 font-bold text-sm px-4 py-2 transition">Masuk</a>
                    <a href="views/register.php" class="btn-primary px-6 py-2.5 rounded-full text-sm font-bold shadow-lg">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="relative pt-16 pb-12 px-4 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-blue-200/30 rounded-full blur-3xl -z-10 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-indigo-200/30 rounded-full blur-3xl -z-10"></div>

        <div class="text-center max-w-4xl mx-auto relative z-10">
            <div class="inline-flex items-center gap-2 py-2 px-4 rounded-full bg-white/60 border border-white shadow-sm mb-6 animate-enter backdrop-blur-sm">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                </span>
                <span class="text-xs font-bold text-slate-600 tracking-wide uppercase">Marketplace #1 Indonesia</span>
            </div>
            
            <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 tracking-tight mb-6 leading-tight animate-enter" style="animation-delay: 0.1s">
                Belanja <span class="text-gradient">Lebih Cerdas,</span> <br>
                Hidup Lebih Mudah.
            </h1>
            
            <p class="text-slate-500 text-lg max-w-2xl mx-auto leading-relaxed animate-enter mb-8" style="animation-delay: 0.2s">
                Temukan ribuan barang unik dari penjual terpercaya. Mulai dari gadget hingga fashion, semua ada di sini.
            </p>

            <div class="animate-enter" style="animation-delay: 0.3s">
                <a href="#produk-terbaru" class="btn-primary px-8 py-3 rounded-full font-bold text-lg inline-flex items-center gap-2">
                    Mulai Belanja <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 pb-20 -mt-6 relative z-10">

        <div class="glass rounded-3xl p-8 mb-12 animate-enter" style="animation-delay: 0.4s">
            <div class="flex justify-between items-end mb-6">
                <h2 class="text-2xl font-bold text-slate-900">Kategori Populer</h2>
                <a href="views/browse.php" class="text-blue-600 text-sm font-bold hover:underline">Lihat Semua</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                <?php foreach($categories as $cat): ?>
                    <a href="views/browse.php?category=<?php echo $cat['id']; ?>" class="group flex flex-col items-center justify-center gap-3 p-4 rounded-2xl border border-transparent hover:bg-blue-50 hover:border-blue-100 transition duration-300">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-xl group-hover:bg-blue-600 group-hover:text-white transition duration-300 shadow-sm">
                            <i class="fas <?php echo getCategoryIcon($cat['nama_kategori']); ?>"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-600 group-hover:text-blue-700 text-center line-clamp-1">
                            <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="produk-terbaru" class="animate-enter" style="animation-delay: 0.5s">
            <h2 class="text-2xl font-bold text-slate-900 mb-2">Rekomendasi Terbaru</h2>
            <p class="text-slate-500 mb-6">Barang pilihan yang baru saja diupload.</p>

            <?php if (count($products) > 0): ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach($products as $prod): ?>
                        <a href="views/detail.php?id=<?php echo $prod['id']; ?>" class="bg-white rounded-2xl p-3 border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer flex flex-col h-full relative">
                            <div class="aspect-[4/3] bg-slate-50 rounded-xl relative overflow-hidden mb-3">
                                <img src="<?php echo $prod['gambar'] ? 'assets/images/'.$prod['gambar'] : 'https://via.placeholder.com/300'; ?>" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                
                                <div class="absolute bottom-2 left-2 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-lg text-[10px] font-bold text-slate-700 flex items-center gap-1 shadow-sm">
                                    <i class="fas fa-store text-blue-500"></i> <?php echo htmlspecialchars($prod['nama_toko']); ?>
                                </div>
                            </div>
                            
                            <div class="flex flex-col flex-grow px-1">
                                <h3 class="font-bold text-slate-800 text-sm line-clamp-2 mb-2 group-hover:text-blue-600 transition">
                                    <?php echo htmlspecialchars($prod['nama_produk']); ?>
                                </h3>
                                <div class="mt-auto">
                                    <p class="text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-blue-500 font-extrabold text-lg">
                                        Rp <?php echo number_format($prod['harga'], 0, ',', '.'); ?>
                                    </p>
                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-50">
                                        <div class="flex text-yellow-400 text-[10px] gap-0.5">
                                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                        </div>
                                        <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition shadow-sm">
                                            <i class="fas fa-plus"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-slate-300">
                    <h3 class="text-lg font-bold text-slate-700">Belum ada produk</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-white border-t border-slate-200 py-10 mt-12">
        <div class="container mx-auto px-6 text-center">
            <p class="font-bold text-slate-800 text-lg mb-2">MarketPlace</p>
            <p class="text-slate-500 text-sm">&copy; 2025 Daniel & Aldwin. All rights reserved.</p>
        </div>
    </footer>

    <script>
        $(document).ready(function(){
            $('#navProfileTrigger').click(function(e){ e.stopPropagation(); $('#navProfileDropdown').slideToggle(150); $('#navChevron').toggleClass('rotate-180'); });
            $(document).click(function(){ $('#navProfileDropdown').slideUp(150); $('#navChevron').removeClass('rotate-180'); });
        });
    </script>
</body>
</html>