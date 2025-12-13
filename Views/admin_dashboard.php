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
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .nav-item.active { background-color: #F1F5F9; color: #2563EB; font-weight: 700; border-right: 3px solid #2563EB; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body class="bg-white h-screen overflow-hidden flex text-slate-800">

    <aside class="w-64 border-r border-slate-100 flex flex-col bg-white z-20">
        <div class="h-20 flex items-center px-8 border-b border-slate-50">
            <span class="text-xl font-extrabold text-slate-900 tracking-tight">Market<span class="text-blue-600">Admin</span></span>
        </div>

        <nav class="flex-1 py-6 space-y-1 px-4">
            <button onclick="switchTab('dashboard')" id="nav-dashboard" class="nav-item w-full flex items-center px-4 py-3.5 rounded-xl transition text-slate-500 hover:bg-slate-50 mb-1">
                <i class="fas fa-home w-6"></i> <span>Overview</span>
            </button>
            <button onclick="switchTab('users')" id="nav-users" class="nav-item w-full flex items-center px-4 py-3.5 rounded-xl transition text-slate-500 hover:bg-slate-50 mb-1">
                <i class="fas fa-users w-6"></i> <span>Pengguna</span>
            </button>
            <button onclick="switchTab('shops')" id="nav-shops" class="nav-item w-full flex items-center px-4 py-3.5 rounded-xl transition text-slate-500 hover:bg-slate-50 mb-1">
                <i class="fas fa-store w-6"></i> <span>Daftar Toko</span>
            </button>
            <button onclick="switchTab('products')" id="nav-products" class="nav-item w-full flex items-center px-4 py-3.5 rounded-xl transition text-slate-500 hover:bg-slate-50 mb-1">
                <i class="fas fa-box w-6"></i> <span>Produk</span>
            </button>
        </nav>

        <div class="p-4 border-t border-slate-50">
            <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-xl transition font-bold text-sm">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50">
        <header class="h-20 flex items-center justify-between px-8 bg-white/80 backdrop-blur-sm border-b border-slate-100">
            <div>
                <h2 id="page-title" class="text-xl font-bold text-slate-800">Dashboard</h2>
                <p class="text-xs text-slate-400">Selamat datang kembali, Administrator.</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">A</div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 relative" id="content-area">
            
            <div id="view-dashboard" class="content-view space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Total Pendapatan</p>
                        <h3 class="text-2xl font-extrabold text-slate-800" id="stat-rev">...</h3>
                        <div class="mt-2 text-xs text-green-500 font-bold flex items-center gap-1"><i class="fas fa-arrow-up"></i> Realtime</div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Total Pengguna</p>
                        <h3 class="text-2xl font-extrabold text-slate-800" id="stat-users">...</h3>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Toko Aktif</p>
                        <h3 class="text-2xl font-extrabold text-slate-800" id="stat-shops">...</h3>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Total Produk</p>
                        <h3 class="text-2xl font-extrabold text-slate-800" id="stat-prods">...</h3>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                        <h3 class="font-bold text-lg text-slate-800">Transaksi Terakhir</h3>
                        <span class="text-xs text-slate-400">5 Transaksi terbaru</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                                <tr>
                                    <th class="p-4 pl-6">Invoice</th>
                                    <th class="p-4">Pembeli</th>
                                    <th class="p-4">Total</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right pr-6">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody id="table-recent-orders" class="text-sm text-slate-600 divide-y divide-slate-50"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="view-users" class="content-view hidden">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                        <h3 class="font-bold text-lg">Manajemen Pengguna</h3>
                        <button onclick="loadUsers()" class="text-blue-600 text-sm font-bold hover:bg-blue-50 px-3 py-1 rounded-lg transition"><i class="fas fa-sync-alt mr-1"></i> Refresh</button>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                            <tr><th class="p-4">Info User</th><th class="p-4">Saldo</th><th class="p-4 text-right">Aksi</th></tr>
                        </thead>
                        <tbody id="table-users-body" class="text-sm text-slate-600 divide-y divide-slate-50"></tbody>
                    </table>
                </div>
            </div>

            <div id="view-shops" class="content-view hidden">
                 <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                        <h3 class="font-bold text-lg">Manajemen Toko</h3>
                        <button onclick="loadShops()" class="text-blue-600 text-sm font-bold hover:bg-blue-50 px-3 py-1 rounded-lg transition"><i class="fas fa-sync-alt mr-1"></i> Refresh</button>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                            <tr><th class="p-4">Nama Toko</th><th class="p-4">Pemilik</th><th class="p-4 text-center">Produk</th><th class="p-4 text-right">Aksi</th></tr>
                        </thead>
                        <tbody id="table-shops-body" class="text-sm text-slate-600 divide-y divide-slate-50"></tbody>
                    </table>
                </div>
            </div>

            <div id="view-products" class="content-view hidden">
                 <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                        <h3 class="font-bold text-lg">Semua Produk</h3>
                        <button onclick="loadProducts()" class="text-blue-600 text-sm font-bold hover:bg-blue-50 px-3 py-1 rounded-lg transition"><i class="fas fa-sync-alt mr-1"></i> Refresh</button>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                            <tr><th class="p-4">Produk</th><th class="p-4">Toko</th><th class="p-4">Harga</th><th class="p-4 text-right">Aksi</th></tr>
                        </thead>
                        <tbody id="table-products-body" class="text-sm text-slate-600 divide-y divide-slate-50"></tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <script>
    function handleError(jqXHR, textStatus, errorThrown) {
        console.error("AJAX Error: ", textStatus, errorThrown, jqXHR.responseText);
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: 'Gagal memuat data. Cek console browser untuk detail.',
            footer: 'Status: ' + jqXHR.status + ' ' + errorThrown
        });
    }

    function switchTab(tabName) {
        $('.nav-item').removeClass('active');
        $('#nav-' + tabName).addClass('active');
        $('.content-view').addClass('hidden');
        $('#view-' + tabName).removeClass('hidden');

        const titles = { 'dashboard': 'Dashboard Overview', 'users': 'Data Pengguna', 'shops': 'Data Toko', 'products': 'Data Produk' };
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
                $('#stat-rev').text('Rp ' + parseInt(d.revenue || 0).toLocaleString('id-ID'));
                $('#stat-shops').text(d.shops);
                $('#stat-prods').text(d.products);
            }
        }, 'json').fail(handleError);

        $.get('../api/admin.php?action=get_recent_orders', function(res){
            let rows = '';
            if(res.status === 'success' && res.data.length > 0) {
                res.data.forEach(o => {
                    rows += `
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 pl-6 font-mono text-xs font-bold text-slate-500">${o.invoice_number}</td>
                            <td class="p-4 font-bold text-slate-800">${o.nama_lengkap}</td>
                            <td class="p-4 text-blue-600 font-bold">Rp ${parseInt(o.total_harga).toLocaleString('id-ID')}</td>
                            <td class="p-4"><span class="bg-green-100 text-green-700 px-2 py-1 rounded text-[10px] uppercase font-bold tracking-wide">${o.status}</span></td>
                            <td class="p-4 text-right pr-6 text-slate-400 text-xs">${o.created_at}</td>
                        </tr>
                    `;
                });
            } else { rows = '<tr><td colspan="5" class="p-6 text-center text-slate-400">Belum ada transaksi.</td></tr>'; }
            $('#table-recent-orders').html(rows);
        }, 'json').fail(handleError);
    }

    function loadUsers() {
        $('#table-users-body').html('<tr><td colspan="3" class="p-4 text-center">Loading...</td></tr>');
        $.get('../api/admin.php?action=get_users', function(res){
            let rows = '';
            if(res.status === 'success' && res.data.length > 0) {
                res.data.forEach(u => {
                    rows += `
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4">
                                <div class="font-bold text-slate-800">${u.nama_lengkap}</div>
                                <div class="text-xs text-slate-400">@${u.username}</div>
                            </td>
                            <td class="p-4 font-mono text-slate-600">Rp ${parseInt(u.balance).toLocaleString('id-ID')}</td>
                            <td class="p-4 text-right">
                                <button onclick="editBalance(${u.id}, '${u.nama_lengkap}')" class="text-blue-600 hover:bg-blue-50 p-2 rounded mr-1"><i class="fas fa-wallet"></i></button>
                                <button onclick="deleteItem('user', ${u.id})" class="text-red-500 hover:bg-red-50 p-2 rounded"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                });
            } else { rows = '<tr><td colspan="3" class="p-8 text-center text-slate-400">Data kosong.</td></tr>'; }
            $('#table-users-body').html(rows);
        }, 'json').fail(handleError);
    }

    function loadShops() {
        $.get('../api/admin.php?action=get_shops', function(res){
            let rows = '';
            if(res.status === 'success' && res.data.length > 0) {
                res.data.forEach(s => {
                    rows += `
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 font-bold text-slate-800">${s.nama_toko}</td>
                            <td class="p-4 text-slate-500">${s.pemilik}</td>
                            <td class="p-4 text-center"><span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs font-bold">${s.jumlah_produk}</span></td>
                            <td class="p-4 text-right"><button onclick="deleteItem('shop', ${s.id})" class="text-red-500 hover:bg-red-50 p-2 rounded"><i class="fas fa-ban"></i></button></td>
                        </tr>`;
                });
            }
            $('#table-shops-body').html(rows);
        }, 'json').fail(handleError);
    }

    function loadProducts() {
        $.get('../api/admin.php?action=get_products', function(res){
            let rows = '';
            if(res.status === 'success' && res.data.length > 0) {
                res.data.forEach(p => {
                    rows += `
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 flex items-center gap-3">
                                <div class="w-8 h-8 rounded bg-slate-200 overflow-hidden"><img src="../assets/images/${p.gambar}" class="w-full h-full object-cover"></div>
                                <span class="font-bold text-slate-800 text-sm line-clamp-1">${p.nama_produk}</span>
                            </td>
                            <td class="p-4 text-xs text-slate-500">${p.nama_toko}</td>
                            <td class="p-4 text-sm font-bold">Rp ${parseInt(p.harga).toLocaleString('id-ID')}</td>
                            <td class="p-4 text-right"><button onclick="deleteItem('product', ${p.id})" class="text-red-500 hover:bg-red-50 p-2 rounded"><i class="fas fa-trash"></i></button></td>
                        </tr>`;
                });
            }
            $('#table-products-body').html(rows);
        }, 'json').fail(handleError);
    }

    function editBalance(userId, userName) {
        Swal.fire({
            title: 'Atur Saldo', text: userName,
            html: `<select id="balType" class="swal2-input"><option value="add">Tambah (Top Up)</option><option value="set">Set Manual</option></select><input id="balAmount" type="number" class="swal2-input" placeholder="Nominal">`,
            showCancelButton: true, confirmButtonText: 'Simpan', confirmButtonColor: '#2563EB',
            preConfirm: () => { return { type: $('#balType').val(), amount: $('#balAmount').val() } }
        }).then((res) => {
            if(res.isConfirmed) {
                $.post('../api/admin.php', { action: 'update_balance', user_id: userId, ...res.value }, function(data){
                    Swal.fire(data.status==='success'?'Berhasil':'Gagal', data.message, data.status).then(()=>loadUsers());
                }, 'json').fail(handleError);
            }
        });
    }

    function deleteItem(type, id) {
        let action = (type === 'user') ? 'delete_user' : (type === 'shop' ? 'delete_shop' : 'delete_product');
        Swal.fire({ title: 'Yakin hapus?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Hapus' }).then((result) => {
            if (result.isConfirmed) {
                let data = { action: action }; data[type + '_id'] = id;
                $.post('../api/admin.php', data, function(res){
                    if(res.status === 'success') { 
                        Swal.fire('Terhapus', res.message, 'success'); 
                        if(type==='user') loadUsers(); else if(type==='shop') loadShops(); else loadProducts();
                        loadStats();
                    }
                }, 'json').fail(handleError);
            }
        })
    }

    $(document).ready(function(){ switchTab('dashboard'); });
    </script>
</body>
</html>