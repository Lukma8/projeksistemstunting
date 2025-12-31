<?php
// HAPUS session_start() di sini karena sudah ada di database.php
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

date_default_timezone_set('Asia/Jakarta');

$database = new Database();
$db = $database->getConnection();

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Cek apakah data ada sebelum menghapus
    $check_query = "SELECT * FROM balita WHERE id = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$delete_id]);
    $balita = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($balita) {
        $query = "DELETE FROM balita WHERE id = ?";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$delete_id])) {
            $_SESSION['success'] = "Data balita " . htmlspecialchars($balita['nama']) . " berhasil dihapus";
        } else {
            $_SESSION['error'] = "Gagal menghapus data balita";
        }
    } else {
        $_SESSION['error'] = "Data balita tidak ditemukan";
    }
    header("Location: data_balita.php");
    exit();
}

// Handle search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Set default tanggal jika kosong (1 bulan terakhir)
if (empty($start_date)) {
    $start_date = date('Y-m-01'); // Tanggal 1 bulan ini
}
if (empty($end_date)) {
    $end_date = date('Y-m-d'); // Hari ini
}

$query = "SELECT 
    b.*, 
    u.nama_lengkap as orang_tua,
    u.username as username_ortu
    FROM balita b
    LEFT JOIN users u ON b.orang_tua_id = u.id
    WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (b.nama LIKE ? OR u.nama_lengkap LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($filter_status)) {
    $query .= " AND b.hasil = ?";
    $params[] = $filter_status;
}

// Filter tanggal
if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND b.tanggal_cek BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

$query .= " ORDER BY b.tanggal_cek DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$balita_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle PDF Export - Redirect ke cetak_pdf_all.php dengan parameter filter
if (isset($_GET['export']) && $_GET['export'] == 'pdf') {
    // Redirect ke cetak_pdf_all.php dengan parameter filter
    $query_params = http_build_query([
        'search' => $search,
        'status' => $filter_status,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'export_type' => 'filtered',
        'source' => 'data_balita'
    ]);
    header("Location: ../backend/export_pdf_all.php?" . $query_params);
    exit();
}

// Handle Excel Export
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    exportToExcel($balita_data, $search, $filter_status, $start_date, $end_date);
    exit();
}

// Excel Export Function
function exportToExcel($balita_data, $search, $filter_status, $start_date, $end_date) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="data_balita_' . date('Y-m-d_His') . '.xls"');
    header('Cache-Control: max-age=0');
    
    echo '<table border="1">';
    echo '<tr><th colspan="9" style="background-color:#3b82f6;color:white;font-size:16px;">DATA BALITA POSYANDU SEHAT</th></tr>';
    echo '<tr><th colspan="9" style="background-color:#3b82f6;color:white;">Sistem Deteksi Stunting Terintegrasi</th></tr>';
    echo '<tr><td colspan="9"></td></tr>';
    echo '<tr><td colspan="2"><b>Tanggal Export:</b></td><td colspan="7">' . date('d/m/Y H:i:s') . '</td></tr>';
    echo '<tr><td colspan="2"><b>Total Data:</b></td><td colspan="7">' . count($balita_data) . ' record</td></tr>';
    
    if (!empty($search)) {
        echo '<tr><td colspan="2"><b>Pencarian:</b></td><td colspan="7">' . htmlspecialchars($search) . '</td></tr>';
    }
    
    if (!empty($filter_status)) {
        echo '<tr><td colspan="2"><b>Filter Status:</b></td><td colspan="7">' . $filter_status . '</td></tr>';
    }
    
    if (!empty($start_date) && !empty($end_date)) {
        echo '<tr><td colspan="2"><b>Periode:</b></td><td colspan="7">' . date('d M Y', strtotime($start_date)) . ' - ' . date('d M Y', strtotime($end_date)) . '</td></tr>';
    }
    
    echo '<tr><td colspan="9"></td></tr>';
    
    // Table header
    echo '<tr style="background-color:#1e293b;color:white;font-weight:bold;">';
    echo '<th>No</th>';
    echo '<th>Nama Balita</th>';
    echo '<th>Orang Tua</th>';
    echo '<th>Umur</th>';
    echo '<th>JK</th>';
    echo '<th>Tinggi Badan</th>';
    echo '<th>Berat Badan</th>';
    echo '<th>Status</th>';
    echo '<th>Tanggal</th>';
    echo '</tr>';
    
    // Table data
    $no = 1;
    foreach($balita_data as $data) {
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . htmlspecialchars($data['nama']) . '</td>';
        echo '<td>' . htmlspecialchars($data['orang_tua']) . '</td>';
        echo '<td>' . $data['umur'] . ' bln</td>';
        echo '<td>' . $data['jenis_kelamin'] . '</td>';
        echo '<td>' . $data['tinggi_badan'] . ' cm</td>';
        echo '<td>' . $data['berat_badan'] . ' kg</td>';
        echo '<td style="color:' . ($data['hasil'] == 'Stunting' ? 'red' : 'green') . ';font-weight:bold;">' . $data['hasil'] . '</td>';
        echo '<td>' . date('d M Y', strtotime($data['tanggal_cek'])) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Balita - Sistem Stunting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        
        .sidebar { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); }
        .sidebar.collapsed { width: 70px; }
        .sidebar.collapsed .sidebar-text { display: none; }
        .sidebar.collapsed .logo-text { display: none; }
        .sidebar.collapsed .user-info { display: none; }
        .main-content { transition: all 0.3s ease; }
        .nav-item { transition: all 0.2s ease; border-radius: 8px; }
        .nav-item:hover { background: rgba(255, 255, 255, 0.1); }
        .nav-item.active { background: #3b82f6; }
        .stat-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); }
        
        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Animation for modal */
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-animation {
            animation: modalFadeIn 0.3s ease-out;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="sidebar fixed inset-y-0 left-0 z-50 w-64 flex flex-col">
        <!-- Logo & Toggle -->
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-500 p-2 rounded-lg">
                    <i class="fas fa-baby text-white text-lg"></i>
                </div>
                <div class="logo-text">
                    <span class="text-lg font-bold text-white">Posyandu</span>
                    <p class="text-slate-400 text-xs">Admin Panel</p>
                </div>
            </div>
            <button id="sidebarToggle" class="text-slate-400 hover:text-white transition duration-200">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <!-- User Info -->
        <div class="p-4 border-b border-slate-700 user-info">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-500 w-10 h-10 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-shield text-white text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate"><?php echo $_SESSION['nama_lengkap']; ?></p>
                    <p class="text-slate-400 text-xs truncate">Admin</p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-4 space-y-1">
            <a href="dashboard_admin.php" class="nav-item flex items-center space-x-3 px-3 py-3 text-slate-300 hover:text-white text-sm font-medium">
                <i class="fas fa-chart-pie w-5 text-center"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>
            
            <a href="data_balita.php" class="nav-item active flex items-center space-x-3 px-3 py-3 text-white text-sm font-medium">
                <i class="fas fa-baby w-5 text-center"></i>
                <span class="sidebar-text">Data Balita</span>
                <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded ml-auto sidebar-text"><?php echo count($balita_data); ?></span>
            </a>
            
            <a href="data_pengguna.php" class="nav-item flex items-center space-x-3 px-3 py-3 text-slate-300 hover:text-white text-sm font-medium">
                <i class="fas fa-users w-5 text-center"></i>
                <span class="sidebar-text">Data Pengguna</span>
            </a>
        </nav>

        <!-- Bottom Section - Logout moved to bottom -->
        <div class="mt-auto p-4 border-t border-slate-700">
            <a href="#" id="logoutBtn" class="nav-item flex items-center space-x-3 px-3 py-2 text-slate-300 hover:text-white text-sm font-medium">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span class="sidebar-text">Logout</span>
            </a>
        </div>
    </div>

     <div class="p-6">
                
    <!-- Main Content -->
    <div class="main-content ml-64 min-h-screen transition-all duration-300">
        <!-- Top Bar -->
        <header class="bg-white border-b border-gray-200">
            <div class="flex justify-between items-center py-4 px-6">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800">Data Balita</h1>
                    <p class="text-gray-600 text-sm">Kelola data pemeriksaan balita</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-clock mr-2"></i>
                        <span id="currentTime">Memuat waktu...</span>
                    </div>
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium"><?php echo substr($_SESSION['nama_lengkap'], 0, 1); ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="p-6">
            <!-- Action Bar -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 space-y-4 lg:space-y-0">
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                    <!-- Button Tambah Balita -->
                    <a href="tambah_balita.php" id="tambahBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Data Balita</span>
                    </a>
                </div>
                
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                    <!-- Export Buttons -->
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'pdf'])); ?>" 
                       class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                        <i class="fas fa-file-pdf"></i>
                        <span>Export PDF</span>
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'excel'])); ?>" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                        <i class="fas fa-file-excel"></i>
                        <span>Export Excel</span>
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Data</h3>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nama balita atau orang tua..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Status</option>
                            <option value="Stunting" <?php echo $filter_status == 'Stunting' ? 'selected' : ''; ?>>Stunting</option>
                            <option value="Normal" <?php echo $filter_status == 'Normal' ? 'selected' : ''; ?>>Normal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200 flex-1">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <?php if (!empty($search) || !empty($filter_status) || !empty($start_date) || !empty($end_date)): ?>
                        <a href="data_balita.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Reset</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Info Filter -->
            <?php if (!empty($search) || !empty($filter_status) || !empty($start_date) || !empty($end_date)): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <div>
                            <p class="text-sm font-medium text-blue-800">Filter Aktif:</p>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <?php if (!empty($search)): ?>
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    <i class="fas fa-search mr-1"></i>Pencarian: "<?php echo htmlspecialchars($search); ?>"
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($filter_status)): ?>
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    <i class="fas fa-filter mr-1"></i>Status: <?php echo $filter_status; ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($start_date) && !empty($end_date)): ?>
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    <i class="fas fa-calendar mr-1"></i>Periode: <?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-sm text-blue-600">
                        Total: <span class="font-semibold"><?php echo count($balita_data); ?> data</span>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="text-sm text-gray-600 mb-6">
                Total: <span class="font-semibold"><?php echo count($balita_data); ?> data</span>
                <span class="text-gray-400 ml-2">(Periode: <?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?>)</span>
            </div>
            <?php endif; ?>

            <!-- Data Table -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Balita</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orang Tua</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tinggi Badan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berat Badan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($balita_data)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                    <p>Tidak ada data balita</p>
                                    <?php if (!empty($search) || !empty($filter_status) || !empty($start_date) || !empty($end_date)): ?>
                                    <p class="text-sm mt-2">Coba ubah pencarian atau filter</p>
                                    <a href="data_balita.php" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                                        <i class="fas fa-times mr-1"></i>Reset Filter
                                    </a>
                                    <?php else: ?>
                                    <a href="tambah_balita.php" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                                        <i class="fas fa-plus mr-1"></i>Tambah Data Balita
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($balita_data as $balita): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="bg-blue-100 w-8 h-8 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-baby text-blue-600 text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($balita['nama']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo $balita['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($balita['orang_tua']); ?></div>
                                    <div class="text-xs text-gray-500">@<?php echo htmlspecialchars($balita['username_ortu']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $balita['umur']; ?> bln
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $balita['tinggi_badan']; ?> cm
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $balita['berat_badan']; ?> kg
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $balita['hasil'] == 'Stunting' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo $balita['hasil']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d M Y', strtotime($balita['tanggal_cek'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <!-- Button Edit -->
                                        <a href="edit_balita.php?id=<?php echo $balita['id']; ?>" 
                                           class="edit-btn text-blue-600 hover:text-blue-900 px-2 py-1 rounded transition duration-200"
                                           data-id="<?php echo $balita['id']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <!-- Button Hapus dengan konfirmasi -->
                                        <button type="button" 
                                           class="delete-btn text-red-600 hover:text-red-900 px-2 py-1 rounded transition duration-200"
                                           data-id="<?php echo $balita['id']; ?>"
                                           data-nama="<?php echo htmlspecialchars($balita['nama']); ?>">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4 modal-animation">
            <div class="flex items-center mb-4">
                <div class="bg-red-100 p-3 rounded-full mr-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Konfirmasi Hapus Data</h3>
                    <p class="text-gray-600 text-sm mt-1">Data yang dihapus tidak dapat dikembalikan</p>
                </div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <div class="flex items-center">
                    <i class="fas fa-baby text-red-500 mr-2"></i>
                    <span class="font-medium text-red-800" id="deleteNamaBalita"></span>
                </div>
                <div class="text-sm text-red-600 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    <span>Semua data pemeriksaan balita ini akan dihapus permanen</span>
                </div>
            </div>
            <div class="flex justify-end space-x-3">
                <button id="cancelDelete" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition duration-200 font-medium">
                    Batal
                </button>
                <a href="#" id="confirmDelete" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200 font-medium flex items-center">
                    <i class="fas fa-trash mr-2"></i>
                    Ya, Hapus Data
                </a>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 max-w-sm mx-4 modal-animation">
            <div class="flex items-center mb-4">
                <div class="bg-red-100 p-3 rounded-full mr-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Konfirmasi Logout</h3>
            </div>
            <p class="text-gray-600 mb-6">Apakah Anda yakin ingin keluar dari sistem?</p>
            <div class="flex justify-end space-x-3">
                <button id="cancelLogout" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition duration-200 font-medium">
                    Batal
                </button>
                <a href="../backend/logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200 font-medium">
                    Ya, Logout
                </a>
            </div>
        </div>
    </div>

    <script>
        // Update waktu real-time
        function updateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Jakarta' // Sesuaikan dengan timezone Indonesia
            };
            const formatter = new Intl.DateTimeFormat('id-ID', options);
            document.getElementById('currentTime').textContent = formatter.format(now);
        }

        // Update waktu setiap detik
        updateTime();
        setInterval(updateTime, 1000);

        // Sidebar Toggle
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const sidebarToggle = document.getElementById('sidebarToggle');
        let isCollapsed = false;

        sidebarToggle.addEventListener('click', function() {
            isCollapsed = !isCollapsed;
            
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.remove('ml-64');
                mainContent.classList.add('ml-16');
                sidebarToggle.innerHTML = '<i class="fas fa-chevron-right"></i>';
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('ml-16');
                mainContent.classList.add('ml-64');
                sidebarToggle.innerHTML = '<i class="fas fa-chevron-left"></i>';
            }
        });

        // Delete Confirmation
        const deleteModal = document.getElementById('deleteModal');
        const deleteNamaBalita = document.getElementById('deleteNamaBalita');
        const cancelDelete = document.getElementById('cancelDelete');
        const confirmDelete = document.getElementById('confirmDelete');
        let currentDeleteId = null;

        // Handle delete button clicks
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nama = this.getAttribute('data-nama');
                
                currentDeleteId = id;
                deleteNamaBalita.textContent = nama;
                deleteModal.classList.remove('hidden');
            });
        });

        // Cancel delete
        cancelDelete.addEventListener('click', function() {
            deleteModal.classList.add('hidden');
            currentDeleteId = null;
        });

        // Confirm delete - FIXED: Now properly redirects to delete URL
        confirmDelete.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentDeleteId) {
                window.location.href = `data_balita.php?delete_id=${currentDeleteId}`;
            }
        });

        // Close modal when clicking outside
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                deleteModal.classList.add('hidden');
                currentDeleteId = null;
            }
        });

        // Handle button tambah balita
        document.getElementById('tambahBtn').addEventListener('click', function(e) {
            const btn = this;
            const originalHTML = btn.innerHTML;
            
            // Tampilkan loading
            btn.innerHTML = '<div class="loading-spinner"></div> Memuat...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            
            // Cek apakah halaman tambah_balita.php ada
            setTimeout(() => {
                fetch('tambah_balita.php')
                    .then(response => {
                        if (response.ok) {
                            // Jika halaman ada, lanjutkan navigasi
                            window.location.href = 'tambah_balita.php';
                        } else {
                            // Jika halaman tidak ada, tampilkan error
                            showNotification('Halaman tambah balita tidak ditemukan', 'error');
                            btn.innerHTML = originalHTML;
                            btn.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    })
                    .catch(error => {
                        showNotification('Terjadi kesalahan: ' + error.message, 'error');
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    });
            }, 500);
        });

        // Handle button edit
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const originalHTML = this.innerHTML;
                
                // Tampilkan loading
                this.innerHTML = '<div class="loading-spinner"></div>';
                this.classList.add('opacity-50', 'cursor-not-allowed');
                
                // Cek apakah halaman edit_balita.php ada
                setTimeout(() => {
                    fetch(`edit_balita.php?id=${id}`)
                        .then(response => {
                            if (response.ok) {
                                // Jika halaman ada, lanjutkan navigasi
                                window.location.href = `edit_balita.php?id=${id}`;
                            } else {
                                // Jika halaman tidak ada, tampilkan error
                                showNotification('Halaman edit balita tidak ditemukan', 'error');
                                this.innerHTML = originalHTML;
                                this.classList.remove('opacity-50', 'cursor-not-allowed');
                            }
                        })
                        .catch(error => {
                            showNotification('Terjadi kesalahan: ' + error.message, 'error');
                            this.innerHTML = originalHTML;
                            this.classList.remove('opacity-50', 'cursor-not-allowed');
                        });
                }, 500);
            });
        });

        // Logout Confirmation
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutModal = document.getElementById('logoutModal');
        const cancelLogout = document.getElementById('cancelLogout');

        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            logoutModal.classList.remove('hidden');
        });

        cancelLogout.addEventListener('click', function() {
            logoutModal.classList.add('hidden');
        });

        // Close modal when clicking outside
        logoutModal.addEventListener('click', function(e) {
            if (e.target === logoutModal) {
                logoutModal.classList.add('hidden');
            }
        });

        // Notification handler
        <?php if (isset($_SESSION['success'])): ?>
            showNotification('<?php echo $_SESSION['success']; ?>', 'success');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            showNotification('<?php echo $_SESSION['error']; ?>', 'error');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        function showNotification(message, type) {
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-4 py-3 ${bgColor} text-white rounded-lg shadow-lg`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${icon} mr-2"></i>
                    <span class="font-medium">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 4000);
        }

        // Debug info
        console.log('Total data balita: <?php echo count($balita_data); ?>');
    </script>
</body>
</html>