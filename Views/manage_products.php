<?php
session_start();
require_once '../config/Database.php';
require_once '../Controllers/ProductController.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare("SELECT * FROM shops WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$shop = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$shop){
    echo "<script>alert('Buat toko dulu!'); window.location='create_shop.php';</script>";
    exit;
}

$productController = new ProductController();
$products = $productController->getProductsByShop($shop['id']);
$stats = $productController->getShopStats($shop['id']);

$total_produk = count($products);
$total_penjualan = $stats['sold']; 
$total_pendapatan = $stats['revenue'];

$total_rating = $stats['rating'] > 0 ? number_format($stats['rating'], 1) : '0';

$nama = $_SESSION['nama'];
$role = $_SESSION['role']; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($shop['nama_toko']); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8F9FA; }
        .card-stat { transition: transform 0.2s; }
        .card-stat:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 h-16 flex justify-between items-center">
            
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 text-white p-1.5 rounded-lg">
                    <i class="fas fa-store"></i>
                </div>
                <span class="font-bold text-lg tracking-tight"><?php echo htmlspecialchars($shop['nama_toko']); ?></span>
            </div>

            <div class="flex items-center gap-4">
                <a href="../index.php" class="text-slate-500 hover:text-blue-600 text-sm font-medium">
                    <i class="fas fa-home mr-1"></i> Ke Halaman Utama
                </a>
                <div class="h-6 w-px bg-slate-200"></div>
                
                <div class="relative">
                    <button id="navProfileTrigger" class="flex items-center gap-2 hover:bg-slate-50 p-1 pr-2 rounded-full transition group cursor-pointer border border-transparent hover:border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-200">
                            <?php echo strtoupper(substr($nama, 0, 1)); ?>
                        </div>
                        <span class="text-sm font-semibold hidden md:block"><?php echo htmlspecialchars($nama); ?></span>
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
                            <a href="settings.php?from=shop" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 rounded-lg transition group">
                                <div class="w-6 text-center"><i class="fas fa-cog text-slate-400 group-hover:text-blue-500"></i></div>
                                Pengaturan
                            </a>
                            
                            <div class="h-px bg-slate-100 my-1 mx-2"></div>
                            
                            <a href="../logout.php" class="flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition group">
                                <div class="w-6 text-center"><i class="fas fa-sign-out-alt text-red-400 group-hover:text-red-500"></i></div>
                                Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 py-8 space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4 card-stat">
                <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-box"></i></div>
                <div><p class="text-slate-500 text-xs font-semibold uppercase">Total Produk</p><h3 class="text-2xl font-bold text-slate-800"><?php echo $total_produk; ?></h3></div>
            </div>
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4 card-stat">
                <div class="w-12 h-12 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-xl"><i class="fas fa-shopping-cart"></i></div>
                <div><p class="text-slate-500 text-xs font-semibold uppercase">Total Penjualan</p><h3 class="text-2xl font-bold text-slate-800"><?php echo number_format($total_penjualan); ?></h3></div>
            </div>
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4 card-stat">
                <div class="w-12 h-12 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center text-xl"><i class="fas fa-star"></i></div>
                <div><p class="text-slate-500 text-xs font-semibold uppercase">Rating Toko</p><h3 class="text-2xl font-bold text-slate-800"><?php echo $total_rating; ?></h3></div>
            </div>
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4 card-stat">
                <div class="w-12 h-12 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-xl"><i class="fas fa-wallet"></i></div>
                <div><p class="text-slate-500 text-xs font-semibold uppercase">Pendapatan</p><h3 class="text-xl font-bold text-slate-800">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h3></div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 relative overflow-hidden">
            <div class="flex flex-col md:flex-row gap-6 items-start">
                <div class="w-full md:w-64 h-32 bg-slate-800 rounded-lg overflow-hidden relative group">
                    <img src="https://source.unsplash.com/random/400x200/?tech" class="w-full h-full object-cover opacity-80" alt="Shop Banner">
                    <div class="absolute inset-0 flex items-center justify-center"><i class="fas fa-store text-white text-3xl"></i></div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h2 class="text-2xl font-bold text-slate-800"><?php echo htmlspecialchars($shop['nama_toko']); ?></h2>
                        <span class="bg-orange-100 text-orange-600 text-xs font-bold px-2 py-0.5 rounded-full border border-orange-200">Aktif</span>
                    </div>
                    <p class="text-slate-500 mb-4 text-sm max-w-2xl"><?php echo !empty($shop['deskripsi_toko']) ? htmlspecialchars($shop['deskripsi_toko']) : 'Belum ada deskripsi toko.'; ?></p>
                    <div class="flex gap-4 text-sm text-slate-500">
                        <div class="flex items-center gap-1"><i class="fas fa-map-marker-alt"></i> <?php echo !empty($shop['alamat_toko']) ? htmlspecialchars($shop['alamat_toko']) : '-'; ?></div>
                        <div class="flex items-center gap-1"><i class="fas fa-calendar"></i> Sejak 2025</div>
                    </div>
                </div>
                <button onclick="openEditShopModal()" class="border border-slate-300 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2"><i class="fas fa-edit"></i> Edit Toko</button>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <h3 class="text-lg font-bold text-slate-800">Daftar Produk</h3>
                <div class="flex gap-3">
                    <input type="text" id="searchInput" placeholder="Cari produk..." class="border border-slate-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none w-64">
                    <button onclick="$('#modalAdd').removeClass('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-md flex items-center gap-2"><i class="fas fa-plus"></i> Tambah</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                        <tr>
                            <th class="p-4 pl-6">Produk</th>
                            <th class="p-4">Harga</th>
                            <th class="p-4 text-center">Stok</th>
                            <th class="p-4 text-center">Terjual</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-right pr-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        <?php foreach($products as $p): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 pl-6">
                                <div class="flex items-center gap-4">
                                    <img src="../assets/images/<?php echo $p['gambar']; ?>" class="w-12 h-12 rounded-lg object-cover bg-slate-100 border border-slate-200" alt="img">
                                    <div>
                                        <div class="font-bold text-slate-800"><?php echo htmlspecialchars($p['nama_produk']); ?></div>
                                        <div class="text-xs text-slate-500 mt-0.5"><?php echo htmlspecialchars($p['nama_kategori']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 font-medium">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></td>
                            <td class="p-4 text-center"><span class="bg-orange-500 text-white text-xs font-bold px-2.5 py-1 rounded-full"><?php echo $p['stok']; ?></span></td>
                            <td class="p-4 text-center font-medium text-slate-600"><?php echo $p['terjual']; ?></td>
                            <td class="p-4 text-center"><span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full border border-green-200">Aktif</span></td>
                            <td class="p-4 text-right pr-6">
                                <div class="flex justify-end gap-2">
                                    <button onclick="openEditModal(<?php echo $p['id']; ?>)" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-orange-500 transition" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button onclick="deleteProduct(<?php echo $p['id']; ?>)" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-red-500 transition" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($products)): ?>
                        <tr><td colspan="6" class="p-8 text-center text-slate-500">Belum ada produk.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalAdd" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl p-6 animate-enter">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-lg font-bold">Tambah Produk</h2>
                <button onclick="$('#modalAdd').addClass('hidden')"><i class="fas fa-times text-slate-400"></i></button>
            </div>
            <form id="formAddProduct" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <input type="text" name="nama_produk" class="w-full border p-2 rounded text-sm" placeholder="Nama Produk" required>
                <div class="grid grid-cols-2 gap-4">
                    <select name="category_id" class="w-full border p-2 rounded text-sm"><option value="1">Elektronik</option><option value="2">Pakaian</option><option value="3">Hobi</option></select>
                    <input type="number" name="stok" class="w-full border p-2 rounded text-sm" placeholder="Stok" required>
                </div>
                <input type="number" name="harga" class="w-full border p-2 rounded text-sm" placeholder="Harga (Rp)" required>
                <textarea name="deskripsi" class="w-full border p-2 rounded text-sm" rows="2" placeholder="Deskripsi"></textarea>
                <input type="file" name="gambar" class="w-full text-sm" required>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700">Simpan</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl p-6 animate-enter">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-lg font-bold">Edit Produk</h2>
                <button onclick="$('#modalEdit').addClass('hidden')"><i class="fas fa-times text-slate-400"></i></button>
            </div>
            <form id="formEditProduct" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" id="edit_product_id">
                <input type="text" name="nama_produk" id="edit_nama" class="w-full border p-2 rounded text-sm" required>
                <div class="grid grid-cols-2 gap-4">
                    <select name="category_id" id="edit_kategori" class="w-full border p-2 rounded text-sm"><option value="1">Elektronik</option><option value="2">Pakaian</option><option value="3">Hobi</option></select>
                    <input type="number" name="stok" id="edit_stok" class="w-full border p-2 rounded text-sm" required>
                </div>
                <input type="number" name="harga" id="edit_harga" class="w-full border p-2 rounded text-sm" required>
                <textarea name="deskripsi" id="edit_deskripsi" class="w-full border p-2 rounded text-sm" rows="2"></textarea>
                <div>
                    <label class="text-xs text-slate-500">Ganti Foto (Opsional)</label>
                    <input type="file" name="gambar" class="w-full text-sm">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700">Update</button>
            </form>
        </div>
    </div>

    <div id="modalEditShop" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl p-6 animate-enter">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-lg font-bold">Edit Informasi Toko</h2>
                <button onclick="$('#modalEditShop').addClass('hidden')"><i class="fas fa-times text-slate-400"></i></button>
            </div>
            <form id="formEditShop" class="space-y-4">
                <input type="hidden" name="action" value="update_shop">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Toko</label>
                    <input type="text" name="nama_toko" id="shop_nama" class="w-full border p-2 rounded text-sm focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi Toko</label>
                    <textarea name="deskripsi_toko" id="shop_deskripsi" class="w-full border p-2 rounded text-sm focus:ring-2 focus:ring-blue-500 outline-none" rows="3"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Alamat Toko</label>
                    <textarea name="alamat_toko" id="shop_alamat" class="w-full border p-2 rounded text-sm focus:ring-2 focus:ring-blue-500 outline-none" rows="2" required></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script>
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

        
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            
            $('#formAddProduct, #formEditProduct').on('submit', function(e){
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: '../api/product.php',
                    type: 'POST', data: formData, contentType: false, processData: false,
                    success: function (data) {
                        if(data.status === 'success'){
                            Swal.fire({ icon: 'success', title: 'Berhasil', showConfirmButton: false, timer: 1000 }).then(() => location.reload());
                        } else { Swal.fire('Gagal', data.message, 'error'); }
                    }
                });
            });

            $('#formEditShop').on('submit', function(e){
                e.preventDefault();
                $.ajax({
                    url: '../api/shop.php', type: 'POST', data: $(this).serialize(), dataType: 'json',
                    success: function (data) {
                        if(data.status === 'success'){
                            Swal.fire({ icon: 'success', title: 'Berhasil', showConfirmButton: false, timer: 1000 }).then(() => location.reload());
                        } else { Swal.fire('Gagal', data.message, 'error'); }
                    }
                });
            });
        });

        function openEditModal(productId) {
            $.post('../api/product.php', { action: 'get_detail', product_id: productId }, function(response) {
                if(response.status === 'success') {
                    let d = response.data;
                    $('#edit_product_id').val(d.id);
                    $('#edit_nama').val(d.nama_produk);
                    $('#edit_kategori').val(d.category_id);
                    $('#edit_stok').val(d.stok);
                    $('#edit_harga').val(d.harga);
                    $('#edit_deskripsi').val(d.deskripsi);
                    $('#modalEdit').removeClass('hidden');
                }
            }, 'json');
        }

        function openEditShopModal() {
            $.post('../api/shop.php', { action: 'get_shop' }, function(response) {
                if(response.status === 'success') {
                    let d = response.data;
                    $('#shop_nama').val(d.nama_toko);
                    $('#shop_deskripsi').val(d.deskripsi_toko);
                    $('#shop_alamat').val(d.alamat_toko);
                    $('#modalEditShop').removeClass('hidden');
                }
            }, 'json');
        }

        function deleteProduct(id) {
            Swal.fire({
                title: 'Hapus?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('../api/product.php', { action: 'delete', product_id: id }, function() { location.reload(); }, 'json');
                }
            })
        }
    </script>
</body>
</html>