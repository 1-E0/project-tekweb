<?php
session_start();
require_once '../config/Database.php';
require_once '../Controllers/ProductController.php';
require_once '../Controllers/ReviewController.php';

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'];
$productObj = new ProductController();
$product = $productObj->getPublicProduct($id);

if (!$product) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location='../index.php';</script>";
    exit;
}


$reviewObj = new ReviewController();
$reviews = $reviewObj->getReviews($id);


$is_logged_in = isset($_SESSION['user_id']);
$can_review = false;
if($is_logged_in){
    $can_review = $reviewObj->canReview($_SESSION['user_id'], $id);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['nama_produk']); ?> - Detail</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .star-rating i { cursor: pointer; transition: color 0.2s; }
        .star-rating i.active { color: #FBBF24; }
        .star-rating i:hover { color: #FBBF24; }
    </style>
</head>
<body class="bg-white text-slate-800">

    <nav class="bg-white sticky top-0 z-50 border-b border-slate-100 shadow-sm">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between">
            <a href="javascript:history.back()" class="flex items-center gap-2 text-slate-600 hover:text-blue-600 transition">
                <i class="fas fa-arrow-left"></i> <span class="font-bold">Kembali</span>
            </a>
            <div class="font-bold text-lg hidden md:block">Detail Produk</div>
            
            <a href="cart.php" class="relative p-2 text-slate-600 hover:text-blue-600">
                <i class="fas fa-shopping-cart text-xl"></i>
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 mb-12">
            
            <div class="space-y-4">
                <div class="bg-slate-50 rounded-2xl overflow-hidden border border-slate-100 aspect-square flex items-center justify-center relative group">
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
                <div class="text-sm text-blue-600 font-bold uppercase tracking-wide mb-2">
                    <?php echo htmlspecialchars($product['nama_kategori']); ?>
                </div>

                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-4 leading-tight">
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
                    <span class="text-3xl md:text-4xl font-extrabold text-slate-900">
                        Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?>
                    </span>
                </div>

                <div class="mb-8">
                    <h3 class="font-bold text-slate-800 mb-2">Deskripsi Produk</h3>
                    <p class="text-slate-600 leading-relaxed text-sm md:text-base">
                        <?php echo nl2br(htmlspecialchars($product['deskripsi'] ?: 'Tidak ada deskripsi.')); ?>
                    </p>
                </div>

                <div class="bg-slate-50 rounded-xl p-4 flex items-center gap-4 border border-slate-100 mb-8">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-xl">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-800"><?php echo htmlspecialchars($product['nama_toko']); ?></div>
                        <div class="text-xs text-slate-500"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($product['alamat_toko']); ?></div>
                    </div>
                </div>

                <div class="mt-auto bg-white border-t border-slate-100 pt-6 md:border-0 md:pt-0 pb-4 md:pb-0 sticky bottom-0 md:static z-40">
                    <div class="flex gap-4">
                        <div class="flex items-center border border-slate-300 rounded-xl w-32 bg-white">
                            <button onclick="changeQty(-1)" class="w-10 h-12 flex items-center justify-center text-slate-500 hover:text-blue-600 font-bold text-lg">-</button>
                            <input type="number" id="qtyInput" value="1" min="1" max="<?php echo $product['stok']; ?>" class="w-full text-center font-bold text-slate-800 outline-none h-12" readonly>
                            <button onclick="changeQty(1)" class="w-10 h-12 flex items-center justify-center text-slate-500 hover:text-blue-600 font-bold text-lg">+</button>
                        </div>

                        <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                                class="flex-1 bg-slate-900 hover:bg-black text-white font-bold rounded-xl py-3 px-6 shadow-lg transform active:scale-95 transition flex items-center justify-center gap-2">
                            <i class="fas fa-shopping-bag"></i>
                            + Keranjang
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 pt-10">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">Ulasan Pembeli (<?php echo count($reviews); ?>)</h2>

            <?php if($can_review): ?>
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 mb-8">
                <h3 class="font-bold text-blue-800 mb-4">Tulis Ulasan Anda</h3>
                <form id="formReview">
                    <input type="hidden" name="action" value="add_review">
                    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-600 mb-1">Rating</label>
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
                        <label class="block text-sm font-semibold text-slate-600 mb-1">Ulasan</label>
                        <textarea name="review" class="w-full border border-blue-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none" rows="3" placeholder="Bagaimana kualitas produk ini?" required></textarea>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg text-sm hover:bg-blue-700 transition">Kirim Ulasan</button>
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
                                    <?php for($i=$r['rating']; $i<5; $i++) echo '<i class="far fa-star text-slate-300"></i>'; ?>
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-slate-400"><?php echo date('d M Y', strtotime($r['created_at'])); ?></span>
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed pl-14">
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

    <script>
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        const maxStock = <?php echo $product['stok']; ?>;

        $(document).ready(function(){
            
            $('.star-rating i').click(function(){
                let val = $(this).data('val');
                $('#ratingInput').val(val);
                $('.star-rating i').removeClass('active text-yellow-400').addClass('text-slate-300');
                $('.star-rating i').each(function(index){
                    if(index < val) {
                        $(this).addClass('active text-yellow-400').removeClass('text-slate-300');
                    }
                });
            });

            // Submit Review
            $('#formReview').submit(function(e){
                e.preventDefault();
                if(!$('#ratingInput').val()){
                    Swal.fire('Error', 'Silakan pilih bintang rating!', 'warning');
                    return;
                }

                $.ajax({
                    url: '../api/review.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(res){
                        if(res.status === 'success'){
                            Swal.fire({ icon: 'success', title: 'Terima Kasih', text: res.message, showConfirmButton: false, timer: 1500 }).then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    }
                });
            });
        });

        function changeQty(amount) {
            let input = $('#qtyInput');
            let currentVal = parseInt(input.val());
            let newVal = currentVal + amount;
            if (newVal >= 1 && newVal <= maxStock) input.val(newVal);
        }

        function addToCart(productId) {
            if (!isLoggedIn) {
                Swal.fire({
                    icon: 'warning', title: 'Login Dulu', text: 'Silakan login untuk belanja',
                    showCancelButton: true, confirmButtonText: 'Login', cancelButtonText: 'Nanti', confirmButtonColor: '#2563EB'
                }).then((res) => {
                    if (res.isConfirmed) window.location.href = 'login.php';
                });
                return;
            }

            let qty = $('#qtyInput').val();
            let btn = $('button i.fa-shopping-bag').parent();
            let originalText = btn.html();

            btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

            $.ajax({
                url: '../api/cart.php',
                type: 'POST',
                data: { action: 'add', product_id: productId, quantity: qty },
                dataType: 'json',
                success: function(response) {
                    btn.html(originalText).prop('disabled', false);
                    if(response.status === 'success') {
                        const Toast = Swal.mixin({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 2000,
                            didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
                        });
                        Toast.fire({ icon: 'success', title: 'Berhasil masuk keranjang!' });
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                },
                error: function() {
                    btn.html(originalText).prop('disabled', false);
                    Swal.fire('Error', 'Gagal menghubungi server', 'error');
                }
            });
        }
    </script>
</body>
</html>