<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .nav-item.active { background-color: #2563EB; color: white; }
        
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-100 font-sans h-screen overflow-hidden flex">

    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shadow-2xl z-20">
        <div class="h-16 flex items-center px-6 border-b border-slate-800 bg-slate-950">
            <i class="fas fa-shield-alt text-blue-500 text-xl mr-3"></i>
            <span class="text-lg font-bold text-white tracking-wide">AdminPanel</span>
        </div>

        <nav class="flex-1 py-6 space-y-1 px-3 overflow-y-auto">
            <button onclick="switchTab('dashboard')" id="nav-dashboard" class="nav-item w-full flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-800 hover:text-white mb-1">
                <i class="fas fa-chart-line w-6"></i> <span class="font-medium">Dashboard</span>
            </button>
            <button onclick="switchTab('users')" id="nav-users" class="nav-item w-full flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-800 hover:text-white mb-1">
                <i class="fas fa-users w-6"></i> <span class="font-medium">Manajemen User</span>
            </button>
            <button onclick="switchTab('shops')" id="nav-shops" class="nav-item w-full flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-800 hover:text-white mb-1">
                <i class="fas fa-store w-6"></i> <span class="font-medium">Manajemen Toko</span>
            </button>
            <button onclick="switchTab('products')" id="nav-products" class="nav-item w-full flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-800 hover:text-white mb-1">
                <i class="fas fa-box w-6"></i> <span class="font-medium">Semua Produk</span>
            </button>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <a href="../logout.php" class="flex items-center justify-center w-full px-4 py-2 bg-red-600/10 text-red-500 hover:bg-red-600 hover:text-white rounded-lg transition duration-200 text-sm font-bold">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shadow-sm z-10">
            <h2 id="page-title" class="text-xl font-bold text-slate-800">Overview</h2>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">A</div>
                <span class="text-sm font-medium text-slate-600">Administrator</span>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 bg-slate-50 relative" id="content-area">
            
            <div id="view-dashboard" class="content-view space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:border-blue-500 transition">
                        <div>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Users</p>
                            <h3 class="text-3xl font-bold text-slate-800" id="stat-users">...</h3>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:border-green-500 transition">
                        <div>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Pendapatan</p>
                            <h3 class="text-3xl font-bold text-slate-800" id="stat-rev">...</h3>
                        </div>
                        <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition"><i class="fas fa-wallet"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:border-purple-500 transition">
                        <div>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Toko</p>
                            <h3 class="text-3xl font-bold text-slate-800" id="stat-shops">...</h3>
                        </div>
                        <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition"><i class="fas fa-store"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:border-orange-500 transition">
                        <div>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Produk</p>
                            <h3 class="text-3xl font-bold text-slate-800" id="stat-prods">...</h3>
                        </div>
                        <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition"><i class="fas fa-box"></i></div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-lg text-slate-800 mb-4">Quick Actions</h3>
                    <div class="flex gap-4">
                        <button onclick="switchTab('users')" class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-bold hover:bg-blue-100">Kelola User</button>
                        <button onclick="switchTab('shops')" class="px-4 py-2 bg-purple-50 text-purple-600 rounded-lg text-sm font-bold hover:bg-purple-100">Lihat Toko</button>
                    </div>
                </div>
            </div>

            <div id="view-users" class="content-view hidden">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-bold text-lg">Daftar Pengguna</h3>
                        <button onclick="loadUsers()" class="text-sm text-blue-600 hover:underline"><i class="fas fa-sync"></i> Refresh</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                                <tr>
                                    <th class="p-4">ID</th>
                                    <th class="p-4">Nama / Username</th>
                                    <th class="p-4">Saldo</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-users-body" class="text-sm text-slate-700 divide-y divide-slate-100">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="view-shops" class="content-view hidden">
                 <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-bold text-lg">Daftar Toko</h3>
                        <button onclick="loadShops()" class="text-sm text-blue-600 hover:underline"><i class="fas fa-sync"></i> Refresh</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                                <tr>
                                    <th class="p-4">Nama Toko</th>
                                    <th class="p-4">Pemilik</th>
                                    <th class="p-4">Produk</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-shops-body" class="text-sm text-slate-700 divide-y divide-slate-100">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="view-products" class="content-view hidden">
                 <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-bold text-lg">Semua Produk</h3>
                        <button onclick="loadProducts()" class="text-sm text-blue-600 hover:underline"><i class="fas fa-sync"></i> Refresh</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                                <tr>
                                    <th class="p-4">Produk</th>
                                    <th class="p-4">Toko</th>
                                    <th class="p-4">Harga</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-products-body" class="text-sm text-slate-700 divide-y divide-slate-100">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
    
    function switchTab(tabName) {
        
        $('.nav-item').removeClass('active bg-blue-600 text-white').addClass('hover:bg-slate-800');
        $('#nav-' + tabName).addClass('active').removeClass('hover:bg-slate-800');
        
        
        $('.content-view').addClass('hidden');
        $('#view-' + tabName).removeClass('hidden');

        
        const titles = {
            'dashboard': 'Dashboard Overview',
            'users': 'Manajemen Pengguna',
            'shops': 'Manajemen Toko',
            'products': 'Semua Produk Terdaftar'
        };
        $('#page-title').text(titles[tabName]);

        
        if(tabName === 'dashboard') loadStats();
        if(tabName === 'users') loadUsers();
        if(tabName === 'shops') loadShops();
        if(tabName === 'products') loadProducts();
    }

 

    function loadStats() {
        $.get('../api/admin.php?action=get_stats', function(res){
            if(res.status === 'success') {
                let d = res.data;
                $('#stat-users').text(d.users);
                $('#stat-rev').text('Rp ' + parseInt(d.revenue).toLocaleString('id-ID'));
                $('#stat-shops').text(d.shops);
                $('#stat-prods').text(d.products);
            }
        }, 'json');
    }

    function loadUsers() {
        $('#table-users-body').html('<tr><td colspan="4" class="p-4 text-center">Loading...</td></tr>');
        $.get('../api/admin.php?action=get_users', function(res){
            let rows = '';
            if(res.status === 'success' && res.data.length > 0) {
                res.data.forEach(u => {
                    rows += `
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 text-slate-500">#${u.id}</td>
                            <td class="p-4">
                                <div class="font-bold text-slate-800">${u.nama_lengkap}</div>
                                <div class="text-xs text-slate-500">@${u.username}</div>
                            </td>
                            <td class="p-4 font-mono font-bold text-green-600">Rp ${parseInt(u.balance).toLocaleString('id-ID')}</td>
                            <td class="p-4 text-right">
                                <button onclick="editBalance(${u.id}, '${u.nama_lengkap}')" class="bg-blue-100 text-blue-600 hover:bg-blue-200 px-3 py-1.5 rounded-lg text-xs font-bold mr-2"><i class="fas fa-wallet"></i> Saldo</button>
                                <button onclick="deleteItem('user', ${u.id})" class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1.5 rounded-lg text-xs font-bold"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                });
            } else { rows = '<tr><td colspan="4" class="p-8 text-center text-slate-400">Tidak ada user member.</td></tr>'; }
            $('#table-users-body').html(rows);
        }, 'json');
    }

    function loadShops() {
        $('#table-shops-body').html('<tr><td colspan="4" class="p-4 text-center">Loading...</td></tr>');
        $.get('../api/admin.php?action=get_shops', function(res){
            let rows = '';
            if(res.status === 'success' && res.data.length > 0) {
                res.data.forEach(s => {
                    rows += `
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 font-bold text-slate-800">${s.nama_toko}</td>
                            <td class="p-4 text-slate-600">${s.pemilik}</td>
                            <td class="p-4"><span class="bg-slate-100 px-2 py-1 rounded text-xs font-bold">${s.jumlah_produk} Produk</span></td>
                            <td class="p-4 text-right">
                                <button onclick="deleteItem('shop', ${s.id})" class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1.5 rounded-lg text-xs font-bold"><i class="fas fa-ban"></i> Banned</button>
                            </td>
                        </tr>
                    `;
                });
            } else { rows = '<tr><td colspan="4" class="p-8 text-center text-slate-400">Belum ada toko.</td></tr>'; }
            $('#table-shops-body').html(rows);
        }, 'json');
    }

    function loadProducts() {
        $('#table-products-body').html('<tr><td colspan="4" class="p-4 text-center">Loading...</td></tr>');
        $.get('../api/admin.php?action=get_products', function(res){
            let rows = '';
            if(res.status === 'success' && res.data.length > 0) {
                res.data.forEach(p => {
                    rows += `
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded bg-slate-200 overflow-hidden"><img src="../assets/images/${p.gambar}" class="w-full h-full object-cover"></div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm">${p.nama_produk}</div>
                                        <div class="text-[10px] text-slate-500 uppercase">${p.nama_kategori}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-sm text-slate-600">${p.nama_toko}</td>
                            <td class="p-4 font-bold text-slate-800">Rp ${parseInt(p.harga).toLocaleString('id-ID')}</td>
                            <td class="p-4 text-right">
                                <button onclick="deleteItem('product', ${p.id})" class="text-red-500 hover:bg-red-50 p-2 rounded transition"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                });
            } else { rows = '<tr><td colspan="4" class="p-8 text-center text-slate-400">Belum ada produk.</td></tr>'; }
            $('#table-products-body').html(rows);
        }, 'json');
    }

    

    function editBalance(userId, userName) {
        Swal.fire({
            title: 'Atur Saldo: ' + userName,
            html: `
                <select id="balType" class="swal2-input border-slate-300">
                    <option value="add">Tambah (Top Up)</option>
                    <option value="set">Set Manual (Edit)</option>
                </select>
                <input id="balAmount" type="number" class="swal2-input border-slate-300" placeholder="Nominal">
            `,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            confirmButtonColor: '#2563EB',
            preConfirm: () => {
                return { type: $('#balType').val(), amount: $('#balAmount').val() }
            }
        }).then((res) => {
            if(res.isConfirmed) {
                $.post('../api/admin.php', { action: 'update_balance', user_id: userId, ...res.value }, function(data){
                    if(data.status === 'success') {
                        Swal.fire('Berhasil', data.message, 'success').then(() => loadUsers());
                    } else { Swal.fire('Gagal', data.message, 'error'); }
                }, 'json');
            }
        });
    }

    function deleteItem(type, id) {
        let text = "";
        let action = "";
        
        if(type === 'user') { text = "User dan semua data tokonya akan dihapus!"; action = "delete_user"; }
        if(type === 'shop') { text = "Toko dan produknya akan dihapus!"; action = "delete_shop"; }
        if(type === 'product') { text = "Produk ini akan dihapus permanen."; action = "delete_product"; }

        Swal.fire({
            title: 'Yakin Hapus?', text: text, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                let data = { action: action };
                data[type + '_id'] = id; 

                $.post('../api/admin.php', data, function(res){
                    if(res.status === 'success') {
                        Swal.fire('Terhapus!', res.message, 'success');
                        if(type === 'user') loadUsers();
                        if(type === 'shop') loadShops();
                        if(type === 'product') loadProducts();
                        
                        loadStats();
                    } else { Swal.fire('Gagal', res.message, 'error'); }
                }, 'json');
            }
        })
    }

    
    $(document).ready(function(){
        switchTab('dashboard');
    });
    </script>
</body>
</html>