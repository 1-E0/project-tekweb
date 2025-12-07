<?php
session_start();
require_once '../config/Database.php';

$is_logged_in = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? 'guest';
$nama = $_SESSION['nama'] ?? 'Pengunjung';
$has_shop = false;

if ($is_logged_in && $role == 'member') {
    $database = new Database();
    $db = $database->getConnection();
    $query = "SELECT id FROM shops WHERE user_id = :uid LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':uid' => $_SESSION['user_id']]);
    if ($stmt->rowCount() > 0) $has_shop = true;
}

$category_id = $_GET['category'] ?? null;
$search_query = $_GET['search'] ?? null;
$min_price = $_GET['min'] ?? null;
$max_price = $_GET['max'] ?? null;

$database = new Database();
$db = $database->getConnection();

$sql = "SELECT p.*, s.nama_toko, c.nama_kategori 
        FROM products p 
        JOIN shops s ON p.shop_id = s.id 
        JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

$params = [];

if ($category_id) {
    $sql .= " AND p.category_id = :cat_id";
    $params[':cat_id'] = $category_id;
}
if ($search_query) {
    $sql .= " AND p.nama_produk LIKE :search";
    $params[':search'] = "%$search_query%";
}
if ($min_price) {
    $sql .= " AND p.harga >= :min";
    $params[':min'] = $min_price;
}
if ($max_price) {
    $sql .= " AND p.harga <= :max";
    $params[':max'] = $max_price;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt_cat = $db->prepare("SELECT * FROM categories ORDER BY nama_kategori ASC");
$stmt_cat->execute();
$categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jelajah Produk</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="text-slate-800 bg-slate-50">

    <nav class="glass sticky top-0 z-50 transition-all duration-300">
        <div class="container mx-auto px-4 sm:px-6 h-20 flex items-center justify-between gap-4">
            <a href="../index.php" class="flex items-center gap-2 flex-shrink-0 group">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-600 text-white p-2.5 rounded-xl shadow-lg shadow-blue-200 group-hover:scale-105 transition duration-300">
                    <i class="fas fa-shopping-bag text-lg"></i>
                </div>
                <span class="text-xl font-extrabold text-slate-800 tracking-tight hidden md:block">MarketPlace</span>
            </a>

            <div class="flex-1 max-w-2xl mx-4">
                <form action="browse.php" method="GET" class="relative group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>"
                           class="w-full input-modern rounded-full py-2.5 pl-12 pr-6 text-sm" 
                           placeholder="Cari barang apa hari ini?">
                    <i class="fas fa-search absolute left-4 top-3 text-slate-400 group-focus-within:text-blue-500 transition"></i>
                </form>
            </div>

            <div class="flex items-center gap-4 flex-shrink-0">
                <?php if ($is_logged_in): ?>
                    <a href="cart.php" class="text-slate-500 hover:text-blue-600 p-2 relative transition">
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
                                    <p class="text-xs text-slate-500 capitalize"><?php echo ($role === 'admin') ? 'Admin' : 'User'; ?></p>
                                </div>
                            </div>
                            <div class="p-2 space-y-1">
                                <?php if($has_shop): ?>
                                    <a href="manage_products.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-store w-5"></i> Toko Saya</a>
                                <?php elseif($role != 'admin'): ?>
                                    <a href="create_shop.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-store w-5"></i> Buka Toko</a>
                                <?php endif; ?>
                                <a href="settings.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-cog w-5"></i> Pengaturan</a>
                                <div class="h-px bg-slate-100 my-1 mx-2"></div>
                                <a href="../logout.php" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-xl transition"><i class="fas fa-sign-out-alt w-5"></i> Keluar</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="text-slate-600 hover:text-blue-600 font-bold text-sm px-4 py-2 transition">Masuk</a>
                    <a href="register.php" class="btn-primary px-6 py-2.5 rounded-full text-sm font-bold shadow-lg">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 py-8">
        <div class="flex flex-col md:flex-row gap-8">
            
            <div class="w-full md:w-64 flex-shrink-0">
                <div class="glass rounded-2xl p-6 sticky top-24 animate-enter">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-800 text-lg">Filter</h3>
                        <a href="browse.php" class="text-xs text-red-500 font-bold hover:underline">Reset</a>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="text-sm font-bold text-slate-700 mb-3">Kategori</h4>
                        <div class="space-y-2 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                            <a href="browse.php<?php echo $search_query ? '?search='.$search_query : ''; ?>" 
                               class="block text-sm <?php echo !$category_id ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600'; ?>">
                               Semua Kategori
                            </a>
                            <?php foreach($categories as $cat): ?>
                                <a href="browse.php?category=<?php echo $cat['id']; ?><?php echo $search_query ? '&search='.$search_query : ''; ?>" 
                                   class="block text-sm <?php echo $category_id == $cat['id'] ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600'; ?>">
                                    <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <form action="browse.php" method="GET">
                        <?php if($category_id): ?><input type="hidden" name="category" value="<?php echo $category_id; ?>"><?php endif; ?>
                        <?php if($search_query): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>"><?php endif; ?>
                        
                        <h4 class="text-sm font-bold text-slate-700 mb-3">Harga</h4>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="relative w-full">
                                <span class="absolute left-2 top-2 text-xs text-slate-400">Rp</span>
                                <input type="number" name="min" placeholder="Min" value="<?php echo $min_price; ?>" class="w-full border rounded-lg pl-6 pr-2 py-1.5 text-xs focus:outline-none focus:border-blue-500 input-modern">
                            </div>
                            <span class="text-slate-400">-</span>
                            <div class="relative w-full">
                                <span class="absolute left-2 top-2 text-xs text-slate-400">Rp</span>
                                <input type="number" name="max" placeholder="Max" value="<?php echo $max_price; ?>" class="w-full border rounded-lg pl-6 pr-2 py-1.5 text-xs focus:outline-none focus:border-blue-500 input-modern">
                            </div>
                        </div>
                        <button type="submit" class="w-full btn-primary py-2.5 rounded-xl text-sm font-bold shadow-md">Terapkan</button>
                    </form>
                </div>
            </div>

            <div class="flex-1 animate-enter" style="animation-delay: 0.2s">
                
                <div class="mb-6 flex items-center justify-between glass p-4 rounded-xl">
                    <div>
                        <h1 class="text-lg font-bold text-slate-800">
                            <?php 
                                if($search_query) echo 'Hasil pencarian: "' . htmlspecialchars($search_query) . '"';
                                elseif($category_id) {
                                    $catName = "Kategori";
                                    foreach($categories as $c) { if($c['id'] == $category_id) $catName = $c['nama_kategori']; }
                                    echo 'Kategori: ' . htmlspecialchars($catName);
                                }
                                else echo 'Semua Produk';
                            ?>
                        </h1>
                        <span class="text-xs text-slate-500">Menampilkan <?php echo count($products); ?> barang</span>
                    </div>
                </div>

                <?php if(count($products) > 0): ?>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <?php foreach($products as $prod): 
                            $is_out_of_stock = $prod['stok'] <= 0;
                        ?>
                            <?php if($is_out_of_stock): ?>
                            <div class="bg-white rounded-2xl p-3 border border-slate-100 shadow-sm opacity-60 cursor-not-allowed flex flex-col h-full relative">
                            <?php else: ?>
                            <a href="detail.php?id=<?php echo $prod['id']; ?>" class="bg-white rounded-2xl p-3 border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer flex flex-col h-full relative">
                            <?php endif; ?>

                                <div class="aspect-[4/3] bg-slate-50 rounded-xl relative overflow-hidden mb-3">
                                    <img src="<?php echo $prod['gambar'] ? '../assets/images/'.$prod['gambar'] : 'https://via.placeholder.com/300'; ?>" 
                                         class="w-full h-full object-cover <?php echo $is_out_of_stock ? 'grayscale' : 'group-hover:scale-110'; ?> transition duration-500">
                                    
                                    <?php if($is_out_of_stock): ?>
                                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                            <span class="text-white font-bold text-sm tracking-widest border-2 border-white px-2 py-1 rounded">HABIS</span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="absolute bottom-2 left-2 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-lg text-[10px] font-bold text-slate-700 flex items-center gap-1 shadow-sm">
                                        <i class="fas fa-store text-blue-500"></i> <?php echo htmlspecialchars($prod['nama_toko']); ?>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col flex-grow px-1">
                                    <h3 class="font-bold text-slate-800 text-sm line-clamp-2 mb-2 <?php echo !$is_out_of_stock ? 'group-hover:text-blue-600' : ''; ?> transition">
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
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center transition shadow-sm <?php echo $is_out_of_stock ? 'bg-slate-100 text-slate-400' : 'bg-slate-50 text-slate-400 group-hover:bg-blue-600 group-hover:text-white'; ?>">
                                                <i class="fas <?php echo $is_out_of_stock ? 'fa-ban' : 'fa-plus'; ?>"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                            <?php if($is_out_of_stock): ?>
                            </div>
                            <?php else: ?>
                            </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="glass rounded-2xl p-12 text-center border-dashed border-2 border-slate-300">
                        <i class="fas fa-search text-4xl text-slate-300 mb-3"></i>
                        <h3 class="text-lg font-bold text-slate-700">Tidak ditemukan</h3>
                        <p class="text-slate-500 mb-4">Coba kata kunci lain atau kurangi filter.</p>
                        <a href="browse.php" class="inline-block px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-bold hover:bg-blue-100 transition">Reset Filter</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script>
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        const role = "<?php echo $role; ?>";

        $(document).ready(function(){
            $('#navProfileTrigger').click(function(e){ e.stopPropagation(); $('#navProfileDropdown').slideToggle(150); $('#navChevron').toggleClass('rotate-180'); });
            $(document).click(function(){ $('#navProfileDropdown').slideUp(150); $('#navChevron').removeClass('rotate-180'); });
        });
    </script>
</body>
</html>