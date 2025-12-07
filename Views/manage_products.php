<?php
session_start();
require_once '../config/Database.php';
require_once '../Controllers/ProductController.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM shops WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$shop = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$shop){ echo "<script>alert('Buat toko dulu!'); window.location='create_shop.php';</script>"; exit; }

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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="text-slate-800 bg-slate-50">

    <nav class="glass sticky top-0 z-50 border-b border-slate-200/50">
        <div class="container mx-auto px-6 h-20 flex justify-between items-center">
            
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 text-white p-2 rounded-lg shadow-md">
                    <i class="fas fa-store"></i>
                </div>
                <span class="font-bold text-lg tracking-tight"><?php echo htmlspecialchars($shop['nama_toko']); ?></span>
            </div>

            <div class="flex items-center gap-4">
                <a href="../index.php" class="text-slate-500 hover:text-blue-600 text-sm font-medium transition">
                    <i class="fas fa-home mr-1"></i> Ke Halaman Utama
                </a>
                <div class="h-6 w-px bg-slate-200"></div>
                
                <div class="relative">
                    <button id="navProfileTrigger" class="flex items-center gap-2 hover:bg-white/50 p-1 pr-2 rounded-full transition group cursor-pointer border border-transparent hover:border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-200">
                            <?php echo strtoupper(substr($nama, 0, 1)); ?>
                        </div>
                        <span class="text-sm font-semibold hidden md:block"><?php echo htmlspecialchars($nama); ?></span>
                        <i class="fas fa-chevron-down text-xs text-slate-400 ml-1 transition" id="navChevron"></i>
                    </button>
                    <div id="navProfileDropdown" class="hidden absolute right-0 mt-3 w-64 bg-white/90 backdrop-blur-md rounded-xl shadow-xl border border-slate-100 overflow-hidden z-50 animate-enter">
                        <div class="p-4 border-b border-slate-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg"><i class="fas fa-user"></i></div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($nama); ?></p>
                                <p class="text-xs text-slate-500 capitalize"><?php echo $role; ?></p>
                            </div>
                        </div>
                        <div class="p-2 space-y-1">
                            <a href="settings.php?from=shop" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition"><i class="fas fa-cog w-5"></i> Pengaturan</a>
                            <div class="h-px bg-slate-100 my-1 mx-2"></div>
                            <a href="../logout.php" class="flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition"><i class="fas fa-sign-out-alt w-5"></i> Keluar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 py-8 space-y-8">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 animate-enter">
            <div class="glass p-6 rounded-2xl flex items-center gap-4 transition hover:-translate-y-1">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shadow-sm"><i class="fas fa-box"></i></div>
                <div><p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Produk</p><h3 class="text-2xl font-extrabold text-slate-800"><?php echo $total_produk; ?></h3></div>
            </div>
            <div class="glass p-6 rounded-2xl flex items-center gap-4 transition hover:-translate-y-1">
                <div class="w-14 h-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center text-2xl shadow-sm"><i class="fas fa-shopping-cart"></i></div>
                <div><p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Penjualan</p><h3 class="text-2xl font-extrabold text-slate-800"><?php echo number_format($total_penjualan); ?></h3></div>
            </div>
            <div class="glass p-6 rounded-2xl flex items-center gap-4 transition hover:-translate-y-1">
                <div class="w-14 h-14 rounded-2xl bg-yellow-50 text-yellow-600 flex items-center justify-center text-2xl shadow-sm"><i class="fas fa-star"></i></div>
                <div><p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Rating Toko</p><h3 class="text-2xl font-extrabold text-slate-800"><?php echo $total_rating; ?></h3></div>
            </div>
            <div class="glass p-6 rounded-2xl flex items-center gap-4 transition hover:-translate-y-1">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl shadow-sm"><i class="fas fa-wallet"></i></div>
                <div><p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Pendapatan</p><h3 class="text-xl font-extrabold text-slate-800">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h3></div>
            </div>
        </div>

        <div class="glass rounded-3xl p-6 relative overflow-hidden animate-enter" style="animation-delay: 0.1s">
            <div class="flex flex-col md:flex-row gap-8 items-start relative z-10">
                <div class="w-full md:w-64 h-40 bg-slate-800 rounded-2xl overflow-hidden relative group shadow-lg">
                    <img src="https://source.unsplash.com/random/400x200/?tech,store" class="w-full h-full object-cover opacity-70 transition duration-500 group-hover:scale-110" alt="Shop Banner">
                    <div class="absolute inset-0 flex items-center justify-center"><i class="fas fa-store text-white/80 text-4xl"></i></div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h2 class="text-3xl font-bold text-slate-800"><?php echo htmlspecialchars($shop['nama_toko']); ?></h2>
                        <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full border border-green-200 shadow-sm">Aktif</span>
                    </div>
                    <p class="text-slate-500 mb-6 text-sm max-w-2xl leading-relaxed"><?php echo !empty($shop['deskripsi_toko']) ? htmlspecialchars($shop['deskripsi_toko']) : 'Belum ada deskripsi toko.'; ?></p>
                    <div class="flex gap-6 text-sm text-slate-500">
                        <div class="flex items-center gap-2"><i class="fas fa-map-marker-alt text-red-500"></i> <?php echo !empty($shop['alamat_toko']) ? htmlspecialchars($shop['alamat_toko']) : '-'; ?></div>
                        <div class="flex items-center gap-2"><i class="fas fa-calendar text-blue-500"></i> Sejak 2025</div>
                    </div>
                </div>
                <button onclick="openEditShopModal()" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-blue-600 px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-sm flex items-center gap-2"><i class="fas fa-edit"></i> Edit Toko</button>
            </div>
        </div>

        <div class="glass rounded-3xl overflow-hidden animate-enter" style="animation-delay: 0.2s">
            <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-white/50">
                <h3 class="text-lg font-bold text-slate-800">Daftar Produk</h3>
                <div class="flex gap-3">
                    <input type="text" id="searchInput" placeholder="Cari produk..." class="input-modern rounded-xl px-4 py-2 text-sm w-64">
                    <button onclick="$('#modalAdd').removeClass('hidden')" class="btn-primary px-4 py-2 rounded-xl text-sm font-bold shadow-md flex items-center gap-2"><i class="fas fa-plus"></i> Tambah</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50 text-slate-500 text-xs uppercase font-bold tracking-wider">
                        <tr>
                            <th class="p-5 pl-8">Produk</th>
                            <th class="p-5">Harga</th>
                            <th class="p-5 text-center">Stok</th>
                            <th class="p-5 text-center">Terjual</th>
                            <th class="p-5 text-right pr-8">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700 bg-white/30">
                        <?php foreach($products as $p): ?>
                        <tr class="hover:bg-white/60 transition">
                            <td class="p-5 pl-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shadow-sm">
                                        <img src="../assets/images/<?php echo $p['gambar']; ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800"><?php echo htmlspecialchars($p['nama_produk']); ?></div>
                                        <div class="text-xs text-slate-500 mt-0.5 uppercase tracking-wide"><?php echo htmlspecialchars($p['nama_kategori']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-5 font-bold">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></td>
                            <td class="p-5 text-center"><span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full"><?php echo $p['stok']; ?></span></td>
                            <td class="p-5 text-center font-bold text-slate-600"><?php echo $p['terjual']; ?></td>
                            <td class="p-5 text-right pr-8">
                                <div class="flex justify-end gap-2">
                                    <button onclick="openEditModal(<?php echo $p['id']; ?>)" class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-white hover:text-orange-500 transition shadow-sm"><i class="fas fa-edit"></i></button>
                                    <button onclick="deleteProduct(<?php echo $p['id']; ?>)" class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-white hover:text-red-500 transition shadow-sm"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($products)): ?>
                        <tr><td colspan="6" class="p-10 text-center text-slate-400 font-medium">Belum ada produk. Tambahkan sekarang!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalAdd" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="glass bg-white rounded-3xl w-full max-w-lg shadow-2xl p-8 animate-enter">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-slate-800">Tambah Produk Baru</h2>
                <button onclick="$('#modalAdd').addClass('hidden')" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center hover:bg-slate-200"><i class="fas fa-times text-slate-500"></i></button>
            </div>
            <form id="formAddProduct" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <div><label class="text-xs font-bold text-slate-500 uppercase ml-1">Nama Produk</label><input type="text" name="nama_produk" class="w-full input-modern rounded-xl p-3 text-sm" required></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="text-xs font-bold text-slate-500 uppercase ml-1">Kategori</label><select name="category_id" class="w-full input-modern rounded-xl p-3 text-sm"><option value="1">Elektronik</option><option value="2">Pakaian</option><option value="3">Hobi</option></select></div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase ml-1">Stok</label>
                        <input type="number" name="stok" min="0" oninput="validity.valid||(value='');" class="w-full input-modern rounded-xl p-3 text-sm" required>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Harga (Rp)</label>
                    <input type="number" name="harga" min="0" oninput="validity.valid||(value='');" class="w-full input-modern rounded-xl p-3 text-sm" required>
                </div>
                <div><label class="text-xs font-bold text-slate-500 uppercase ml-1">Deskripsi</label><textarea name="deskripsi" class="w-full input-modern rounded-xl p-3 text-sm" rows="3"></textarea></div>
                <div><label class="text-xs font-bold text-slate-500 uppercase ml-1">Foto Produk</label><input type="file" name="gambar" class="w-full text-sm mt-1" required></div>
                <button type="submit" class="w-full btn-primary py-3 rounded-xl font-bold mt-2">Simpan Produk</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="glass bg-white rounded-3xl w-full max-w-lg shadow-2xl p-8 animate-enter">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-slate-800">Edit Produk</h2>
                <button onclick="$('#modalEdit').addClass('hidden')" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center hover:bg-slate-200"><i class="fas fa-times text-slate-500"></i></button>
            </div>
            <form id="formEditProduct" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" id="edit_product_id">
                
                <div><label class="text-xs font-bold text-slate-500 uppercase ml-1">Nama Produk</label><input type="text" name="nama_produk" id="edit_nama_produk" class="w-full input-modern rounded-xl p-3 text-sm" required></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="text-xs font-bold text-slate-500 uppercase ml-1">Kategori</label><select name="category_id" id="edit_category_id" class="w-full input-modern rounded-xl p-3 text-sm"><option value="1">Elektronik</option><option value="2">Pakaian</option><option value="3">Hobi</option></select></div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase ml-1">Stok</label>
                        <input type="number" name="stok" id="edit_stok" min="0" oninput="validity.valid||(value='');" class="w-full input-modern rounded-xl p-3 text-sm" required>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Harga (Rp)</label>
                    <input type="number" name="harga" id="edit_harga" min="0" oninput="validity.valid||(value='');" class="w-full input-modern rounded-xl p-3 text-sm" required>
                </div>
                <div><label class="text-xs font-bold text-slate-500 uppercase ml-1">Deskripsi</label><textarea name="deskripsi" id="edit_deskripsi" class="w-full input-modern rounded-xl p-3 text-sm" rows="3"></textarea></div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Foto Baru (Opsional)</label>
                    <input type="file" name="gambar" class="w-full text-sm mt-1">
                    <p class="text-[10px] text-slate-400 mt-1">*Biarkan kosong jika tidak ingin mengubah foto</p>
                </div>
                <button type="submit" class="w-full btn-primary py-3 rounded-xl font-bold mt-2">Update Produk</button>
            </form>
        </div>
    </div>

    <div id="modalEditShop" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="glass bg-white rounded-3xl w-full max-w-lg shadow-2xl p-8 animate-enter">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-slate-800">Edit Profil Toko</h2>
                <button onclick="$('#modalEditShop').addClass('hidden')" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center hover:bg-slate-200"><i class="fas fa-times text-slate-500"></i></button>
            </div>
            <form id="formEditShop" class="space-y-4">
                <input type="hidden" name="action" value="update_shop">
                <div><label class="text-xs font-bold text-slate-500 uppercase ml-1">Nama Toko</label><input type="text" name="nama_toko" value="<?php echo htmlspecialchars($shop['nama_toko']); ?>" class="w-full input-modern rounded-xl p-3 text-sm" required></div>
                <div><label class="text-xs font-bold text-slate-500 uppercase ml-1">Alamat</label><input type="text" name="alamat_toko" value="<?php echo htmlspecialchars($shop['alamat_toko']); ?>" class="w-full input-modern rounded-xl p-3 text-sm" required></div>
                <div><label class="text-xs font-bold text-slate-500 uppercase ml-1">Deskripsi</label><textarea name="deskripsi_toko" class="w-full input-modern rounded-xl p-3 text-sm" rows="3"><?php echo htmlspecialchars($shop['deskripsi_toko']); ?></textarea></div>
                <button type="submit" class="w-full btn-primary py-3 rounded-xl font-bold mt-2">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $('#navProfileTrigger').click(function(e){ e.stopPropagation(); $('#navProfileDropdown').slideToggle(150); $('#navChevron').toggleClass('rotate-180'); });
            $(document).click(function(){ $('#navProfileDropdown').slideUp(150); $('#navChevron').removeClass('rotate-180'); });

            $("#searchInput").on("keyup", function() { var value = $(this).val().toLowerCase(); $("tbody tr").filter(function() { $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1) }); });

            $('#formAddProduct').on('submit', function(e){ 
                e.preventDefault(); 
                var formData = new FormData(this); 
                $.ajax({ 
                    url: '../api/product.php', type: 'POST', data: formData, contentType: false, processData: false, 
                    success: function (data) { 
                        if(data.status === 'success'){ Swal.fire({ icon: 'success', title: 'Berhasil', showConfirmButton: false, timer: 1000 }).then(() => location.reload()); } else { Swal.fire('Gagal', data.message, 'error'); } 
                    } 
                }); 
            });

            $('#formEditProduct').on('submit', function(e){ 
                e.preventDefault(); 
                var formData = new FormData(this); 
                $.ajax({ 
                    url: '../api/product.php', type: 'POST', data: formData, contentType: false, processData: false, 
                    success: function (data) { 
                        if(data.status === 'success'){ Swal.fire({ icon: 'success', title: 'Updated!', showConfirmButton: false, timer: 1000 }).then(() => location.reload()); } else { Swal.fire('Gagal', data.message, 'error'); } 
                    } 
                }); 
            });
            
             $('#formEditShop').on('submit', function(e){
                e.preventDefault();
                Swal.fire('Info', 'Fitur update toko perlu disesuaikan dengan backend API Anda.', 'info');
             });
        });

        function deleteProduct(id) { 
            Swal.fire({ title: 'Hapus?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal' }).then((result) => { 
                if (result.isConfirmed) { $.post('../api/product.php', { action: 'delete', product_id: id }, function(res) { if(res.status==='success') location.reload(); else Swal.fire('Gagal', res.message, 'error'); }, 'json'); } 
            }) 
        }

        function openEditModal(id) {
            $.post('../api/product.php', { action: 'get_detail', product_id: id }, function(res){
                if(res.status === 'success') {
                    const p = res.data;
                    $('#edit_product_id').val(p.id);
                    $('#edit_nama_produk').val(p.nama_produk);
                    $('#edit_category_id').val(p.category_id);
                    $('#edit_stok').val(p.stok);
                    $('#edit_harga').val(p.harga);
                    $('#edit_deskripsi').val(p.deskripsi);
                    
                    $('#modalEdit').removeClass('hidden');
                } else {
                    Swal.fire('Error', 'Gagal mengambil data produk', 'error');
                }
            }, 'json');
        }

        function openEditShopModal() {
            $('#modalEditShop').removeClass('hidden');
        }
    </script>
</body>
</html>