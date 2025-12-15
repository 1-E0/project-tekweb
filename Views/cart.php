<?php
session_start();
require_once '../config/Database.php';
require_once '../Controllers/CartController.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$has_shop = false;
$nav_balance = 0;

$database = new Database();
$db = $database->getConnection();

if ($role == 'member') {
    $stmt = $db->prepare("SELECT id FROM shops WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    if ($stmt->rowCount() > 0) $has_shop = true;
}

$stmt_bal = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmt_bal->execute([$user_id]);
$nav_balance = $stmt_bal->fetchColumn() ?: 0;

$cartController = new CartController();
$cartItems = $cartController->getCart($user_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="text-slate-800 bg-slate-50">
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
                    <input type="text" name="search" class="w-full input-modern rounded-full py-2.5 pl-12 pr-6 text-sm" placeholder="Cari barang apa hari ini?">
                    <i class="fas fa-search absolute left-4 top-3 text-slate-400 group-focus-within:text-blue-500 transition"></i>
                </form>
            </div>

            <div class="flex items-center gap-4 flex-shrink-0">
                <button onclick="openTopUp()" class="hidden md:flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-full transition font-bold text-xs border border-blue-100 group">
                    <i class="fas fa-wallet text-lg group-hover:scale-110 transition"></i>
                    <span>Rp <?php echo number_format($nav_balance, 0, ',', '.'); ?></span>
                    <div class="w-4 h-4 bg-blue-600 text-white rounded-full flex items-center justify-center text-[10px] ml-1"><i class="fas fa-plus"></i></div>
                </button>

                <a href="#" class="text-blue-600 p-2 relative transition">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <?php if(count($cartItems) > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white"><?php echo count($cartItems); ?></span>
                    <?php endif; ?>
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
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 py-10 min-h-screen">
        <div class="mb-6">
            <button onclick="history.back()" class="group inline-flex items-center gap-2 text-slate-500 hover:text-blue-600 transition-colors duration-200 font-medium text-sm">
                <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm group-hover:border-blue-200 group-hover:bg-blue-50 transition-all">
                    <i class="fas fa-arrow-left text-xs"></i>
                </div>
                Kembali
            </button>
        </div>

        <h1 class="text-2xl font-bold text-slate-900 mb-8 animate-enter">Keranjang Belanja</h1>

        <?php if(count($cartItems) > 0): ?>
        <form action="checkout.php" method="POST" id="checkoutForm">
            <div class="flex flex-col lg:flex-row gap-8">
                <div class="flex-1 space-y-4 animate-enter" style="animation-delay: 0.1s">
                    <div class="glass rounded-2xl p-4 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="checkAll" checked class="w-5 h-5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer">
                            <label for="checkAll" class="text-sm font-semibold text-slate-700 cursor-pointer">Pilih Semua</label>
                        </div>
                    </div>

                    <?php foreach($cartItems as $item): ?>
                    <div class="bg-white rounded-2xl border border-slate-100 p-5 flex gap-4 items-start shadow-sm hover:shadow-md transition duration-300">
                        <div class="pt-8">
                            <input type="checkbox" name="selected_items[]" value="<?php echo $item['cart_id']; ?>" 
                                   class="check-item w-5 h-5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer"
                                   data-price="<?php echo $item['harga']; ?>" 
                                   data-qty="<?php echo $item['quantity']; ?>"
                                   checked>
                        </div>
                        <div class="w-28 h-28 bg-slate-50 rounded-xl overflow-hidden flex-shrink-0 border border-slate-100">
                            <img src="../assets/images/<?php echo $item['gambar']; ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 flex flex-col justify-between h-28">
                            <div>
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-slate-800 text-base line-clamp-1 mr-4"><?php echo htmlspecialchars($item['nama_produk']); ?></h3>
                                    <button type="button" class="btn-delete text-slate-400 hover:text-red-500 transition p-1" data-id="<?php echo $item['cart_id']; ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="w-5 h-5 rounded-full bg-blue-50 flex items-center justify-center text-xs text-blue-600"><i class="fas fa-store"></i></div>
                                    <span class="text-xs font-medium text-slate-500"><?php echo htmlspecialchars($item['nama_toko']); ?></span>
                                </div>
                            </div>

                            <div class="flex justify-between items-end">
                                <p class="text-blue-600 font-extrabold text-lg">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></p>
                                <div class="flex items-center border border-slate-200 rounded-lg bg-slate-50">
                                    <button type="button" onclick="updateQty(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] - 1; ?>)" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-white rounded-l-lg transition" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>><i class="fas fa-minus text-xs"></i></button>
                                    <input type="text" value="<?php echo $item['quantity']; ?>" class="w-10 text-center text-sm font-bold text-slate-700 bg-transparent outline-none border-x border-slate-200 py-1" readonly>
                                    <button type="button" onclick="updateQty(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] + 1; ?>)" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-white rounded-r-lg transition"><i class="fas fa-plus text-xs"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="w-full lg:w-96 flex-shrink-0 animate-enter" style="animation-delay: 0.2s">
                    <div class="glass rounded-2xl p-6 sticky top-28">
                        <h3 class="font-bold text-slate-800 text-lg mb-6">Ringkasan Belanja</h3>
                        <div class="space-y-4 mb-6 border-b border-slate-100 pb-6">
                            <div class="flex justify-between text-sm text-slate-600">
                                <span>Total (<span id="total-qty">0</span> barang)</span>
                                <span class="font-bold text-slate-800" id="subtotal-display">Rp 0</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mb-8">
                            <span class="font-bold text-slate-800 text-lg">Total Tagihan</span>
                            <span class="font-extrabold text-blue-600 text-xl" id="total-display">Rp 0</span>
                        </div>
                        <button type="submit" id="btn-checkout" class="w-full btn-primary text-white font-bold py-3.5 rounded-xl shadow-lg flex justify-center items-center gap-2 transition-all">
                            Beli Sekarang <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <?php else: ?>
            <div class="glass flex flex-col items-center justify-center py-20 text-center rounded-[2.5rem] border-dashed border-2 border-slate-300">
                <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mb-6 animate-bounce">
                    <i class="fas fa-shopping-basket text-4xl text-blue-300"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Keranjangmu Kosong</h2>
                <a href="../index.php" class="btn-primary px-8 py-3 rounded-xl font-bold mt-4 inline-block">Mulai Belanja</a>
            </div>
        <?php endif; ?>
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
        });

        $(document).ready(function(){
            $('#navProfileTrigger').click(function(e){ e.stopPropagation(); $('#navProfileDropdown').slideToggle(150); $('#navChevron').toggleClass('rotate-180'); });
            $(document).click(function(){ $('#navProfileDropdown').slideUp(150); $('#navChevron').removeClass('rotate-180'); });

            $('.btn-delete').click(function(e){
                e.preventDefault();
                let cartId = $(this).data('id');
                Swal.fire({ title: 'Hapus?', text: "Barang akan dihapus dari keranjang.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Hapus' }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('../api/cart.php', { action: 'delete', cart_id: cartId }, function(res){
                            if(res.status === 'success') location.reload();
                        }, 'json');
                    }
                });
            });

            function calculateTotal() {
                let total = 0;
                let count = 0;
                $('.check-item:checked').each(function() {
                    let price = parseFloat($(this).data('price'));
                    let qty = parseInt($(this).data('qty'));
                    total += price * qty;
                    count++;
                });

                let formatted = 'Rp ' + total.toLocaleString('id-ID');
                $('#subtotal-display').text(formatted);
                $('#total-display').text(formatted);
                $('#total-qty').text(count);

                if(count === 0) {
                    $('#btn-checkout').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                } else {
                    $('#btn-checkout').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                }
            }

            $('#checkAll').change(function() {
                $('.check-item').prop('checked', $(this).prop('checked'));
                calculateTotal();
            });

            $('.check-item').change(function() {
                $('#checkAll').prop('checked', $('.check-item:checked').length === $('.check-item').length);
                calculateTotal();
            });

            calculateTotal();
        });

        function updateQty(cartId, newQty) {
            if(newQty < 1) return;
            $.post('../api/cart.php', { action: 'update_qty', cart_id: cartId, quantity: newQty }, function(res){ if(res.status === 'success') location.reload(); }, 'json');
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