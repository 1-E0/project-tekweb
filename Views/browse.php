<?php
session_start();
require_once '../config/Database.php';

$is_logged_in = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? 'guest';
$nama = $_SESSION['nama'] ?? 'Pengunjung';
$user_id = $_SESSION['user_id'] ?? null;
$has_shop = false;
$nav_balance = 0;

if ($is_logged_in) {
    $database = new Database();
    $db = $database->getConnection();

    if ($role == 'member') {
        $query = "SELECT id FROM shops WHERE user_id = :uid LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':uid' => $user_id]);
        if ($stmt->rowCount() > 0) $has_shop = true;
    }

    $stmt_bal = $db->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt_bal->execute([$user_id]);
    $nav_balance = $stmt_bal->fetchColumn() ?: 0;
} else {
    $database = new Database();
    $db = $database->getConnection();
}

$category_id = $_GET['category'] ?? null;
$search_query = $_GET['search'] ?? null;
$min_price = $_GET['min'] ?? null;
$max_price = $_GET['max'] ?? null;

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

function renderProductCard($prod) {
    $is_out_of_stock = $prod['stok'] <= 0;
    $imgUrl = $prod['gambar'] ? '../assets/images/'.$prod['gambar'] : 'https://via.placeholder.com/300';
    $price = number_format($prod['harga'], 0, ',', '.');
    
    $linkStart = $is_out_of_stock ? 
        '<div class="bg-white rounded-2xl p-3 border border-slate-100 shadow-sm opacity-60 cursor-not-allowed flex flex-col h-full relative">' : 
        '<a href="detail.php?id='.$prod['id'].'" class="bg-white rounded-2xl p-3 border border-slate-100 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] group cursor-pointer flex flex-col h-full relative overflow-hidden">';
    
    $linkEnd = $is_out_of_stock ? '</div>' : '</a>';
    
    $overlay = $is_out_of_stock ? 
        '<div class="absolute inset-0 bg-black/50 flex items-center justify-center"><span class="text-white font-bold text-sm tracking-widest border-2 border-white px-2 py-1 rounded">HABIS</span></div>' : 
        '<div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/30 to-transparent opacity-0 group-hover:opacity-100 transition duration-500 transform -translate-x-full group-hover:translate-x-full pointer-events-none z-10"></div>';
    
    $imgClass = $is_out_of_stock ? 'grayscale' : 'group-hover:scale-110';
    $titleClass = $is_out_of_stock ? '' : 'group-hover:text-blue-600';
    $btnClass = $is_out_of_stock ? 'bg-slate-100 text-slate-400' : 'bg-slate-50 text-slate-400 group-hover:bg-blue-600 group-hover:text-white';
    $icon = $is_out_of_stock ? 'fa-ban' : 'fa-plus';

    return "
    $linkStart
        $overlay
        <div class='aspect-[4/3] bg-slate-50 rounded-xl relative overflow-hidden mb-3'>
            <img src='$imgUrl' class='w-full h-full object-cover $imgClass transition duration-500'>
            <div class='absolute bottom-2 left-2 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-lg text-[10px] font-bold text-slate-700 flex items-center gap-1 shadow-sm'>
                <i class='fas fa-store text-blue-500'></i> ".htmlspecialchars($prod['nama_toko'])."
            </div>
        </div>
        <div class='flex flex-col flex-grow px-1'>
            <h3 class='font-bold text-slate-800 text-sm line-clamp-2 mb-2 $titleClass transition'>
                ".htmlspecialchars($prod['nama_produk'])."
            </h3>
            <div class='mt-auto'>
                <p class='text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-blue-500 font-extrabold text-lg'>
                    Rp $price
                </p>
                <div class='flex items-center justify-between mt-2 pt-2 border-t border-slate-50'>
                    <div class='flex text-yellow-400 text-[10px] gap-0.5'>
                        <i class='fas fa-star'></i><i class='fas fa-star'></i><i class='fas fa-star'></i><i class='fas fa-star'></i><i class='fas fa-star'></i>
                    </div>
                    <div class='w-8 h-8 rounded-full flex items-center justify-center transition shadow-sm $btnClass'>
                        <i class='fas $icon'></i>
                    </div>
                </div>
            </div>
        </div>
    $linkEnd
    ";
}

if (isset($_GET['ajax'])) {
    if(count($products) > 0) {
        foreach($products as $prod) {
            echo renderProductCard($prod);
        }
    } else {
        echo '
        <div class="col-span-full glass rounded-2xl p-12 text-center border-dashed border-2 border-slate-300">
            <i class="fas fa-search text-4xl text-slate-300 mb-3"></i>
            <h3 class="text-lg font-bold text-slate-700">Tidak ditemukan</h3>
            <p class="text-slate-500 mb-4">Coba kata kunci lain atau kurangi filter.</p>
            <a href="browse.php" class="inline-block px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-bold hover:bg-blue-100 transition">Reset Filter</a>
        </div>';
    }
    exit;
}

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
<body class="text-slate-800 bg-slate-50 flex flex-col min-h-screen">
    <div id="page-transition"></div>

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
                           placeholder="">
                    <i class="fas fa-search absolute left-4 top-3 text-slate-400 group-focus-within:text-blue-500 transition"></i>
                </form>
            </div>

            <div class="flex items-center gap-4 flex-shrink-0">
                <?php if ($is_logged_in): ?>
                    <button onclick="openTopUp()" class="hidden md:flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-full transition font-bold text-xs border border-blue-100 group">
                        <i class="fas fa-wallet text-lg group-hover:scale-110 transition"></i>
                        <span>Rp <?php echo number_format($nav_balance, 0, ',', '.'); ?></span>
                        <div class="w-4 h-4 bg-blue-600 text-white rounded-full flex items-center justify-center text-[10px] ml-1"><i class="fas fa-plus"></i></div>
                    </button>

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
                                <button onclick="openTopUp()" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition md:hidden">
                                    <i class="fas fa-wallet w-5"></i> 
                                    <span class="font-bold text-blue-600">Rp <?php echo number_format($nav_balance, 0, ',', '.'); ?></span>
                                    <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded ml-auto">+ Top Up</span>
                                </button>
                                <?php if($has_shop): ?>
                                    <a href="manage_products.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-store w-5"></i> Toko Saya</a>
                                <?php elseif($role != 'admin'): ?>
                                    <a href="create_shop.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-store w-5"></i> Buka Toko</a>
                                <?php endif; ?>
                                <a href="my_orders.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition"><i class="fas fa-history w-5"></i> Riwayat Belanja</a>
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

    <div class="container mx-auto px-4 sm:px-6 py-8 flex-grow">
        <div class="flex flex-col md:flex-row gap-8">
            
            <div class="w-full md:w-64 flex-shrink-0">
                <div class="glass rounded-2xl p-6 sticky top-24 animate-enter">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-800 text-lg">Filter</h3>
                        <a href="browse.php" class="text-xs text-red-500 font-bold hover:underline filter-link">Reset</a>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="text-sm font-bold text-slate-700 mb-3">Kategori</h4>
                        <div class="space-y-2 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                            <a href="browse.php<?php echo $search_query ? '?search='.$search_query : ''; ?>" 
                               class="filter-link block text-sm <?php echo !$category_id ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600'; ?>">
                               Semua Kategori
                            </a>
                            <?php foreach($categories as $cat): ?>
                                <a href="browse.php?category=<?php echo $cat['id']; ?><?php echo $search_query ? '&search='.$search_query : ''; ?>" 
                                   class="filter-link block text-sm <?php echo $category_id == $cat['id'] ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600'; ?>">
                                    <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <form id="filterForm" action="browse.php" method="GET">
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

                <div id="productGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php if(count($products) > 0): ?>
                        <?php foreach($products as $prod): 
                            echo renderProductCard($prod);
                        endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full glass rounded-2xl p-12 text-center border-dashed border-2 border-slate-300">
                            <i class="fas fa-search text-4xl text-slate-300 mb-3"></i>
                            <h3 class="text-lg font-bold text-slate-700">Tidak ditemukan</h3>
                            <p class="text-slate-500 mb-4">Coba kata kunci lain atau kurangi filter.</p>
                            <a href="browse.php" class="inline-block px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-bold hover:bg-blue-100 transition filter-link">Reset Filter</a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <footer class="bg-white border-t border-slate-200 py-10 mt-12">
        <div class="container mx-auto px-6 text-center">
            <p class="font-bold text-slate-800 text-lg mb-2">MarketPlace</p>
            <p class="text-slate-500 text-sm">&copy; 2025 Daniel & Aldwin.</p>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const transitionEl = document.getElementById('page-transition');
            
            window.addEventListener('pageshow', function(event) {
                if (transitionEl) transitionEl.classList.add('page-loaded');
            });
            
            setTimeout(() => {
                if (transitionEl) transitionEl.classList.add('page-loaded');
            }, 50);

            const links = document.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    const target = this.getAttribute('target');
                    
                    if (this.classList.contains('filter-link')) return;

                    if (!href || href.startsWith('#') || href.startsWith('javascript') || target === '_blank') {
                        return;
                    }
                    
                    const currentUrl = new URL(window.location.href);
                    const targetUrl = new URL(href, window.location.origin);
                    if (currentUrl.pathname === targetUrl.pathname && currentUrl.origin === targetUrl.origin) {
                        return;
                    }

                    e.preventDefault();
                    transitionEl.classList.remove('page-loaded');
                    setTimeout(() => {
                        window.location.href = href;
                    }, 500);
                });
            });
        });

        $(document).ready(function(){
            $('#navProfileTrigger').click(function(e){ e.stopPropagation(); $('#navProfileDropdown').slideToggle(150); $('#navChevron').toggleClass('rotate-180'); });
            $(document).click(function(){ $('#navProfileDropdown').slideUp(150); $('#navChevron').removeClass('rotate-180'); });

            function loadProducts(url) {
                $('#productGrid').animate({ opacity: 0.5 }, 200); 
                
                $.get(url + (url.includes('?') ? '&' : '?') + 'ajax=1', function(data) {
                    $('#productGrid').html(data).animate({ opacity: 1 }, 200);
                }).fail(function() {
                    $('#productGrid').animate({ opacity: 1 }, 200);
                    Swal.fire('Error', 'Gagal memuat produk', 'error');
                });
            }

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                const url = 'browse.php?' + $(this).serialize();
                history.pushState(null, '', url);
                loadProducts(url);
            });

            $(document).on('click', '.filter-link', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                history.pushState(null, '', url);
                
                $('.filter-link').removeClass('text-blue-600 font-bold').addClass('text-slate-600 hover:text-blue-600');
                $(this).removeClass('text-slate-600 hover:text-blue-600').addClass('text-blue-600 font-bold');
                
                loadProducts(url);
            });

            window.addEventListener('popstate', function() {
                loadProducts(window.location.href);
            });
        });

        function openTopUp() {
            Swal.fire({
                title: 'Isi Saldo',
                input: 'number',
                inputLabel: 'Masukkan Nominal (Rp)',
                inputPlaceholder: 'Contoh: 50000',
                showCancelButton: true,
                confirmButtonText: 'Top Up',
                confirmButtonColor: '#2563EB',
                preConfirm: (amount) => {
                    if (!amount) Swal.showValidationMessage('Nominal harus diisi');
                    return amount;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('../api/user.php', { action: 'topup', amount: result.value }, function(res) {
                        if(res.status === 'success') {
                            Swal.fire('Berhasil', 'Saldo bertambah!', 'success').then(() => location.reload());
                        }
                    }, 'json');
                }
            });
        }
    </script>
</body>
</html>