<?php
session_start();
require_once '../config/Database.php';
require_once '../Controllers/ProductController.php';
require_once '../Controllers/ReviewController.php';

if (!isset($_GET['id'])) { header("Location: ../index.php"); exit; }

$id = $_GET['id'];
$productObj = new ProductController();
$product = $productObj->getPublicProduct($id);

if (!$product) { echo "<script>alert('Produk tidak ditemukan!'); window.location='../index.php';</script>"; exit; }

$reviewObj = new ReviewController();
$reviews = $reviewObj->getReviews($id);

$is_logged_in = isset($_SESSION['user_id']);
$can_review = false;
$user_id = $_SESSION['user_id'] ?? null;
$nama = $_SESSION['nama'] ?? 'Pengunjung';
$role = $_SESSION['role'] ?? 'guest';
$has_shop = false;
$nav_balance = 0;

if ($is_logged_in) {
    $database = new Database();
    $db = $database->getConnection();

    if($role == 'member') {
        $stmt = $db->prepare("SELECT id FROM shops WHERE user_id = ? LIMIT 1");
        $stmt->execute([$user_id]);
        if($stmt->rowCount() > 0) $has_shop = true;
    }
    $can_review = $reviewObj->canReview($user_id, $id);

    $stmt_bal = $db->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt_bal->execute([$user_id]);
    $nav_balance = $stmt_bal->fetchColumn() ?: 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['nama_produk']); ?> - Detail</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style> .star-rating i { cursor: pointer; transition: color 0.2s; } .star-rating i.active { color: #FBBF24; } </style>
</head>
<body class="bg-slate-50 text-slate-800">
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
                    <input type="text" name="search" class="w-full input-modern rounded-full py-2.5 pl-12 pr-6 text-sm" placeholder="Cari produk impianmu...">
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

                    <a href="cart.php" class="relative p-2 text-slate-500 hover:text-blue-600 transition">
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
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <div class="mb-6">
            <button onclick="history.back()" class="group inline-flex items-center gap-2 text-slate-500 hover:text-blue-600 transition-colors duration-200 font-medium text-sm">
                <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm group-hover:border-blue-200 group-hover:bg-blue-50 transition-all">
                    <i class="fas fa-arrow-left text-xs"></i>
                </div>
                Kembali
            </button>
        </div>

        <div class="glass rounded-3xl p-6 md:p-10 mb-8 animate-enter">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl overflow-hidden border border-slate-100 aspect-square flex items-center justify-center relative group shadow-sm">
                        <?php if($product['gambar']): ?>
                            <img src="../assets/images/<?php echo $product['gambar']; ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                        <?php else: ?>
                            <div class="text-slate-300 flex flex-col items-center">
                                <i class="fas fa-image text-6xl mb-2"></i>
                                <span>Tidak ada gambar</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-col h-full">
                    <div class="inline-block px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wide mb-3 w-fit">
                        <?php echo htmlspecialchars($product['nama_kategori']); ?>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-4 leading-tight">
                        <?php echo htmlspecialchars($product['nama_produk']); ?>
                    </h1>

                    <div class="flex items-center gap-4 text-sm text-slate-500 mb-6 border-b border-slate-100 pb-6">
                        <div class="flex items-center gap-1 text-yellow-400">
                            <i class="fas fa-star"></i>
                            <span class="text-slate-700 font-bold ml-1"><?php echo number_format($product['rating_produk'], 1); ?></span>
                            <span class="text-slate-400 font-normal ml-1">(<?php echo $product['jumlah_review']; ?> Ulasan)</span>
                        </div>
                        <div class="w-px h-4 bg-slate-300"></div>
                        <div>Terjual <span class="font-bold text-slate-700"><?php echo $product['terjual']; ?></span></div>
                        <div class="w-px h-4 bg-slate-300"></div>
                        <div>Stok <span class="font-bold text-slate-700"><?php echo $product['stok']; ?></span></div>
                    </div>

                    <div class="mb-8">
                        <span class="text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
                            Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?>
                        </span>
                    </div>

                    <div class="bg-white/50 rounded-xl p-4 border border-slate-100 mb-8">
                        <h3 class="font-bold text-slate-800 mb-2 text-sm uppercase tracking-wide">Deskripsi</h3>
                        <p class="text-slate-600 leading-relaxed text-sm md:text-base">
                            <?php echo nl2br(htmlspecialchars($product['deskripsi'] ?: 'Tidak ada deskripsi.')); ?>
                        </p>
                    </div>

                    <div class="bg-white rounded-xl p-4 flex items-center gap-4 border border-slate-100 mb-8 shadow-sm">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-xl">
                            <i class="fas fa-store"></i>
                        </div>
                        <div>
                            <div class="font-bold text-slate-800"><?php echo htmlspecialchars($product['nama_toko']); ?></div>
                            <div class="text-xs text-slate-500"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($product['alamat_toko']); ?></div>
                        </div>
                    </div>

                    <div class="mt-auto pt-6 border-t border-slate-100">
                        <div class="flex gap-4">
                            <div class="flex items-center border border-slate-200 rounded-xl bg-white shadow-sm">
                                <button onclick="changeQty(-1)" class="w-12 h-12 flex items-center justify-center text-slate-500 hover:text-blue-600 font-bold text-lg hover:bg-slate-50 rounded-l-xl transition">-</button>
                                <input type="number" id="qtyInput" value="1" min="1" max="<?php echo $product['stok']; ?>" class="w-12 text-center font-bold text-slate-800 outline-none h-12 bg-transparent" readonly>
                                <button onclick="changeQty(1)" class="w-12 h-12 flex items-center justify-center text-slate-500 hover:text-blue-600 font-bold text-lg hover:bg-slate-50 rounded-r-xl transition">+</button>
                            </div>

                            <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                                    class="flex-1 btn-primary text-white font-bold rounded-xl py-3 px-6 shadow-lg transform active:scale-95 transition flex items-center justify-center gap-2 text-lg">
                                <i class="fas fa-shopping-bag"></i>
                                + Keranjang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass rounded-3xl p-8 animate-enter" style="animation-delay: 0.2s">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">Ulasan Pembeli (<?php echo count($reviews); ?>)</h2>

            <?php if($can_review): ?>
            <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-6 mb-8">
                <h3 class="font-bold text-blue-800 mb-4">Tulis Ulasan Anda</h3>
                <form id="formReview">
                    <input type="hidden" name="action" value="add_review">
                    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Rating</label>
                        <div class="star-rating flex text-2xl text-slate-300 gap-1">
                            <i class="fas fa-star" data-val="1"></i>
                            <i class="fas fa-star" data-val="2"></i>
                            <i class="fas fa-star" data-val="3"></i>
                            <i class="fas fa-star" data-val="4"></i>
                            <i class="fas fa-star" data-val="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" required>
                    </div>
                    
                    <div class="mb-4">
                         <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Ulasan</label>
                        <textarea name="review" class="w-full input-modern p-3 rounded-xl text-sm" rows="3" placeholder="Bagaimana kualitas produk ini?" required></textarea>
                    </div>

                    <button type="submit" class="btn-primary py-2.5 px-6 rounded-xl text-sm font-bold shadow-md">Kirim Ulasan</button>
                </form>
            </div>
            <?php endif; ?>

            <div class="space-y-6">
                <?php foreach($reviews as $r): ?>
                <div class="border-b border-slate-100 pb-6 last:border-0">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold">
                                <?php echo strtoupper(substr($r['nama_lengkap'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($r['nama_lengkap']); ?></p>
                                <div class="flex text-xs text-yellow-400">
                                    <?php for($i=0; $i<$r['rating']; $i++) echo '<i class="fas fa-star"></i>'; ?>
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-slate-400"><?php echo date('d M Y', strtotime($r['created_at'])); ?></span>
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed pl-14 bg-slate-50 p-3 rounded-xl rounded-tl-none ml-10 inline-block">
                        <?php echo htmlspecialchars($r['review']); ?>
                    </p>
                </div>
                <?php endforeach; ?>

                <?php if(empty($reviews)): ?>
                <div class="text-center py-10 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    <p class="text-slate-500">Belum ada ulasan untuk produk ini.</p>
                </div>
                <?php endif; ?>
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
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        const maxStock = <?php echo $product['stok']; ?>;
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
            $('.star-rating i').click(function(){
                let val = $(this).data('val');
                $('#ratingInput').val(val);
                $('.star-rating i').removeClass('active text-yellow-400').addClass('text-slate-300');
                $('.star-rating i').each(function(index){ if(index < val) $(this).addClass('active text-yellow-400').removeClass('text-slate-300'); });
            });
            $('#formReview').submit(function(e){
                e.preventDefault();
                if(!$('#ratingInput').val()){ Swal.fire('Error', 'Silakan pilih bintang rating!', 'warning'); return; }
                $.ajax({
                    url: '../api/review.php', type: 'POST', data: $(this).serialize(), dataType: 'json',
                    success: function(res){
                        if(res.status === 'success'){ Swal.fire({ icon: 'success', title: 'Terima Kasih', text: res.message, showConfirmButton: false, timer: 1500 }).then(() => location.reload()); } else { Swal.fire('Gagal', res.message, 'error'); }
                    }
                });
            });
            $('#navProfileTrigger').click(function(e){ e.stopPropagation(); $('#navProfileDropdown').slideToggle(150); $('#navChevron').toggleClass('rotate-180'); });
            $(document).click(function(){ $('#navProfileDropdown').slideUp(150); $('#navChevron').removeClass('rotate-180'); });
        });
        function changeQty(amount) {
            let input = $('#qtyInput'); let currentVal = parseInt(input.val()); let newVal = currentVal + amount;
            if (newVal >= 1 && newVal <= maxStock) input.val(newVal);
        }
        function addToCart(productId) {
            if (!isLoggedIn) {
                Swal.fire({ icon: 'warning', title: 'Login Dulu', text: 'Silakan login untuk belanja', showCancelButton: true, confirmButtonText: 'Login', cancelButtonText: 'Nanti', confirmButtonColor: '#2563EB' }).then((res) => { if (res.isConfirmed) window.location.href = 'login.php'; }); return;
            }
            let qty = $('#qtyInput').val(); let btn = $('button i.fa-shopping-bag').parent(); let originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);
            $.ajax({
                url: '../api/cart.php', type: 'POST', data: { action: 'add', product_id: productId, quantity: qty }, dataType: 'json',
                success: function(response) {
                    btn.html(originalText).prop('disabled', false);
                    if(response.status === 'success') {
                        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); } });
                        Toast.fire({ icon: 'success', title: 'Berhasil masuk keranjang!' });
                    } else { Swal.fire('Gagal', response.message, 'error'); }
                },
                error: function() { btn.html(originalText).prop('disabled', false); Swal.fire('Error', 'Gagal menghubungi server', 'error'); }
            });
        }

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