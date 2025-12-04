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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body { font-family: 'Inter', sans-serif; background-color: #ffffff; }</style>
</head>
<body class="text-slate-800">

    <nav class="bg-white sticky top-0 z-50 border-b border-slate-100">
        <div class="container mx-auto px-4 sm:px-6 h-20 flex items-center justify-between gap-4">
            
            <a href="../index.php" class="flex items-center gap-2 flex-shrink-0">
                <div class="bg-blue-600 text-white p-2 rounded-xl shadow-lg shadow-blue-200">
                    <i class="fas fa-shopping-bag text-lg"></i>
                </div>
                <span class="text-xl font-extrabold text-slate-800 tracking-tight hidden md:block">MarketPlace</span>
            </a>

            <div class="flex-1 max-w-2xl mx-4">
                <form action="browse.php" method="GET" class="relative group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>"
                           class="w-full border border-slate-200 bg-slate-50 rounded-full py-3 pl-12 pr-6 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:bg-white transition-all shadow-sm" 
                           placeholder="Cari barang apa hari ini?">
                    <i class="fas fa-search absolute left-4 top-3.5 text-slate-400 group-focus-within:text-blue-500 transition text-lg"></i>
                </form>
            </div>

            <div class="flex items-center gap-4 flex-shrink-0">
                <?php if ($is_logged_in): ?>
                    <a href="#" class="text-slate-500 hover:text-blue-600 p-2 relative transition">
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
                                    <a href="manage_products.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition font-medium"><i class="fas fa-store w-5"></i> Toko Saya</a>
                                <?php elseif($role != 'admin'): ?>
                                    <a href="create_shop.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition font-medium"><i class="fas fa-store w-5"></i> Buka Toko Gratis</a>
                                <?php endif; ?>
                                <a href="settings.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition font-medium"><i class="fas fa-cog w-5"></i> Pengaturan</a>
                                <div class="h-px bg-slate-100 my-1 mx-2"></div>
                                <a href="../logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-xl transition font-medium"><i class="fas fa-sign-out-alt w-5"></i> Keluar</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="text-slate-600 hover:text-blue-600 font-bold text-sm px-4 py-2 transition">Masuk</a>
                    <a href="register.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-full text-sm transition shadow-lg shadow-blue-200">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 py-8">
        <div class="flex flex-col md:flex-row gap-8">
            
            <div class="w-full md:w-64 flex-shrink-0">
                <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-200 p-6 sticky top-24">
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
                        <div class="flex items-center gap-2 mb-3">
                            <div class="relative w-full">
                                <span class="absolute left-2 top-2 text-xs text-slate-400">Rp</span>
                                <input type="number" name="min" placeholder="Min" value="<?php echo $min_price; ?>" class="w-full border rounded-lg pl-6 pr-2 py-1.5 text-sm focus:outline-none focus:border-blue-500 bg-slate-50">
                            </div>
                            <span class="text-slate-400">-</span>
                            <div class="relative w-full">
                                <span class="absolute left-2 top-2 text-xs text-slate-400">Rp</span>
                                <input type="number" name="max" placeholder="Max" value="<?php echo $max_price; ?>" class="w-full border rounded-lg pl-6 pr-2 py-1.5 text-sm focus:outline-none focus:border-blue-500 bg-slate-50">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-slate-800 text-white font-bold py-2.5 rounded-xl text-sm hover:bg-slate-900 transition shadow-md">Terapkan Filter</button>
                    </form>
                </div>
            </div>

            <div class="flex-1">
                
                <div class="mb-6 flex items-center justify-between bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
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
                    
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-500 font-medium">Urutkan:</span>
                        <select class="border border-slate-200 rounded-lg text-sm px-2 py-1.5 focus:outline-none bg-slate-50 cursor-pointer">
                            <option>Paling Sesuai</option>
                            <option>Terbaru</option>
                            <option>Harga Terendah</option>
                            <option>Harga Tertinggi</option>
                        </select>
                    </div>
                </div>

                <?php if(count($products) > 0): ?>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <?php foreach($products as $prod): ?>
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition duration-300 group cursor-pointer flex flex-col h-full" onclick="addToCart(<?php echo $prod['id']; ?>)">
                                
                                <div class="h-48 bg-slate-50 relative overflow-hidden">
                                    <img src="<?php echo $prod['gambar'] ? '../assets/images/'.$prod['gambar'] : 'https://via.placeholder.com/300?text=No+Image'; ?>" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    
                                    <?php if(strtotime($prod['created_at']) > strtotime('-7 days')): ?>
                                    <div class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm px-2 py-0.5 rounded-md text-[9px] font-bold text-blue-600 shadow-sm border border-slate-200">
                                        BARU
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="p-3 flex flex-col flex-grow">
                                    <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-1">
                                        <?php echo htmlspecialchars($prod['nama_toko']); ?>
                                    </div>

                                    <h3 class="font-bold text-slate-800 text-sm line-clamp-2 mb-2 group-hover:text-blue-600 transition leading-snug">
                                        <?php echo htmlspecialchars($prod['nama_produk']); ?>
                                    </h3>
                                    
                                    <div class="mt-auto pt-2 border-t border-slate-50">
                                        <p class="text-slate-900 font-extrabold text-base mb-2">Rp <?php echo number_format($prod['harga'], 0, ',', '.'); ?></p>
                                        <div class="flex items-center justify-between text-[10px] text-slate-500">
                                            <span class="flex items-center gap-1"><i class="fas fa-map-marker-alt"></i> Kota</span>
                                            <span class="flex items-center gap-1"><i class="fas fa-star text-yellow-400"></i> 4.5</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-2xl p-12 text-center border border-dashed border-slate-300">
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
                    if (result.isConfirmed) window.location.href = 'login.php';
                });
                return; 
            }
            if (role === 'admin') {
                Swal.fire('Info', 'Admin tidak bisa belanja.', 'info');
                return;
            }
            Swal.fire({
                icon: 'success', title: 'Berhasil', text: 'Produk masuk keranjang',
                timer: 1500, showConfirmButton: false, position: 'bottom-end', toast: true
            });
        }
    </script>
</body>
</html>