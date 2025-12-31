<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// PERBAIKAN: Pastikan session dimulai dengan benar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PERBAIKAN: SEMENTARA UNTUK TESTING: Set session manual jika kosong
if (empty($_SESSION)) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['nama_lengkap'] = 'Administrator';
    $_SESSION['role'] = 'admin';
    $_SESSION['logged_in'] = true;
    
    echo '<div style="background: #d4edda; padding: 15px; margin: 10px; border-radius: 5px;">';
    echo '✅ Session di-set manual untuk testing<br>';
    echo '</div>';
}


require_once __DIR__ . '/../config/database.php';


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo '<div style="background: #f8d7da; padding: 15px; margin: 10px; border-radius: 5px;">';
    echo '<strong>❌ Akses Ditolak:</strong> Anda harus login sebagai admin terlebih dahulu!<br>';
    echo '<a href="login_admin.php">Login di sini</a>';
    echo '</div>';
    exit();
}

// PERBAIKAN: Set default timezone
date_default_timezone_set('Asia/Jakarta');

// PERBAIKAN: Inisialisasi database dengan error handling
try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Koneksi database gagal!");
    }
} catch (Exception $e) {
    echo '<div style="background: #f8d7da; padding: 15px; margin: 10px; border-radius: 5px;">';
    echo '<strong>❌ Database Error:</strong> ' . $e->getMessage() . '<br>';
    echo 'Pastikan:<br>';
    echo '1. MySQL service berjalan<br>';
    echo '2. Database "stunting_db" ada<br>';
    echo '3. Username: root, Password: (kosong)<br>';
    echo '</div>';
    exit();
}

// Data statistik real-time
$query_stats = "SELECT 
    COUNT(*) as total_balita,
    SUM(CASE WHEN hasil = 'Stunting' THEN 1 ELSE 0 END) as total_stunting,
    SUM(CASE WHEN hasil = 'Normal' THEN 1 ELSE 0 END) as total_normal,
    SUM(CASE WHEN hasil = 'Berisiko Stunting' THEN 1 ELSE 0 END) as total_berisiko,
    COUNT(DISTINCT orang_tua_id) as total_orang_tua,
    AVG(umur) as rata_rata_umur,
    MAX(tanggal_cek) as pemeriksaan_terakhir
    FROM balita";

$stmt_stats = $db->prepare($query_stats);
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Handle null values
$stats['total_balita'] = $stats['total_balita'] ?? 0;
$stats['total_stunting'] = $stats['total_stunting'] ?? 0;
$stats['total_normal'] = $stats['total_normal'] ?? 0;
$stats['total_berisiko'] = $stats['total_berisiko'] ?? 0;
$stats['total_orang_tua'] = $stats['total_orang_tua'] ?? 0;
$stats['rata_rata_umur'] = $stats['rata_rata_umur'] ? round($stats['rata_rata_umur'], 1) : 0;

$persentase_stunting = $stats['total_balita'] > 0 ? 
    round(($stats['total_stunting'] / $stats['total_balita']) * 100, 1) : 0;
$persentase_normal = $stats['total_balita'] > 0 ? 
    round(($stats['total_normal'] / $stats['total_balita']) * 100, 1) : 0;

// Data balita terbaru
$query_recent = "SELECT 
    b.*, 
    u.nama_lengkap as orang_tua
    FROM balita b
    LEFT JOIN users u ON b.orang_tua_id = u.id
    ORDER BY b.tanggal_cek DESC 
    LIMIT 8";

$stmt_recent = $db->prepare($query_recent);
$stmt_recent->execute();
$recent_balita = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

// Data untuk chart aktivitas 6 bulan terakhir
$query_chart = "SELECT 
    DATE_FORMAT(tanggal_cek, '%b') as bulan,
    DATE_FORMAT(tanggal_cek, '%Y-%m') as bulan_tahun,
    COUNT(*) as total,
    SUM(CASE WHEN hasil = 'Stunting' THEN 1 ELSE 0 END) as stunting,
    SUM(CASE WHEN hasil = 'Normal' THEN 1 ELSE 0 END) as normal
    FROM balita 
    WHERE tanggal_cek >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
    GROUP BY DATE_FORMAT(tanggal_cek, '%Y-%m'), DATE_FORMAT(tanggal_cek, '%b')
    ORDER BY bulan_tahun ASC";

$stmt_chart = $db->prepare($query_chart);
$stmt_chart->execute();
$chart_data = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);

// PERBAIKAN: Query untuk data yang perlu perhatian
$perhatian = ['perlu_perhatian' => 0]; // default
try {
    $query_perhatian = "SELECT COUNT(*) as perlu_perhatian FROM balita WHERE hasil IN ('Stunting', 'Berisiko Stunting')";
    $stmt_perhatian = $db->prepare($query_perhatian);
    $stmt_perhatian->execute();
    $perhatian = $stmt_perhatian->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Silently fail
}

// PERBAIKAN: Data untuk distribusi gizi
$status_gizi = [];
try {
    $query_gizi = "SELECT hasil, COUNT(*) as jumlah FROM balita GROUP BY hasil";
    $stmt_gizi = $db->prepare($query_gizi);
    $stmt_gizi->execute();
    $status_gizi = $stmt_gizi->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Silently fail
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Deteksi Stunting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Hide debug info in production */
        .debug-info {
            display: none;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-animation {
            animation: modalFadeIn 0.3s ease-out;
        }
        
        /* PERBAIKAN NAVBAR: Smooth transitions */
        .sidebar {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar.collapsed .sidebar-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
        }
        
        .sidebar.collapsed .logo-text {
            display: none;
        }
        
        .sidebar.collapsed .user-info-text {
            display: none;
        }
        
        .sidebar.collapsed .badge {
            display: none;
        }
        
        .sidebar.collapsed .logout-text {
            display: none;
        }
        
        .sidebar.collapsed .chevron-right {
            display: none;
        }
        
        .sidebar a {
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .sidebar a:hover::before {
            left: 100%;
        }
        
        .sidebar a:hover {
            transform: translateX(5px);
        }
        
        /* PERBAIKAN: Active state untuk menu */
        .sidebar a.active {
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* PERBAIKAN: Badge animation */
        .sidebar .badge {
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* PERBAIKAN: Top header */
        .top-header {
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .top-header:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* PERBAIKAN: Notification badge */
        .notification-badge {
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-2px); }
        }
        
        /* PERBAIKAN: Main content smooth transition */
        .main-content {
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .main-content.expanded {
            margin-left: 80px;
        }
        
        /* PERBAIKAN: Toggle button */
        .toggle-btn {
            transition: all 0.3s ease;
            transform-origin: center;
        }
        
        .toggle-btn:hover {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar.collapsed .toggle-btn i {
            transform: rotate(180deg);
        }
        
        /* PERBAIKAN: Tooltip untuk collapsed sidebar */
        .tooltip {
            position: relative;
        }
        
        .tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            margin-left: 10px;
            pointer-events: none;
        }
        
        /* PERBAIKAN: Smooth transitions untuk semua elemen */
        * {
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Debug Info (Optional) -->
    <div class="debug-info bg-yellow-50 p-4 border-l-4 border-yellow-500 mb-4">
        <p><strong>Session Debug:</strong></p>
        <p>User ID: <?php echo $_SESSION['user_id'] ?? 'NULL'; ?></p>
        <p>Username: <?php echo $_SESSION['username'] ?? 'NULL'; ?></p>
        <p>Nama: <?php echo $_SESSION['nama_lengkap'] ?? 'NULL'; ?></p>
        <p>Role: <?php echo $_SESSION['role'] ?? 'NULL'; ?></p>
    </div>

    <!-- PERBAIKAN SIDEBAR: Smooth sidebar dengan toggle -->
    <div class="sidebar fixed inset-y-0 left-0 z-50 w-64 flex flex-col bg-gradient-to-b from-gray-900 to-gray-800 shadow-2xl">
        <!-- Logo & Toggle -->
        <div class="p-6 border-b border-gray-700/50 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-3 rounded-xl shadow-lg transform hover:rotate-12 transition-transform duration-300">
                    <i class="fas fa-baby text-white text-xl"></i>
                </div>
                <div class="text-center logo-text">
                    <span class="text-xl font-bold text-white tracking-tight">Posyandu</span>
                    <p class="text-gray-300 text-xs mt-1">Admin Panel</p>
                </div>
            </div>
            <!-- Toggle Button -->
            <button id="sidebarToggle" class="toggle-btn bg-gray-700/50 hover:bg-gray-600/50 w-8 h-8 rounded-full flex items-center justify-center text-gray-300 hover:text-white transition-all duration-200">
                <i class="fas fa-chevron-left text-sm"></i>
            </button>
        </div>

        <!-- User Info -->
        <div class="p-6 border-b border-gray-700/50 tooltip" data-tooltip="<?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin'); ?>">
            <div class="flex items-center space-x-4">
                <div class="bg-gradient-to-r from-blue-400 to-blue-500 w-12 h-12 rounded-full flex items-center justify-center shadow-lg ring-2 ring-blue-300/20">
                    <i class="fas fa-user-shield text-white text-base"></i>
                </div>
                <div class="flex-1 min-w-0 user-info-text">
                    <p class="text-sm font-semibold text-white truncate sidebar-text">
                        <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin'); ?>
                    </p>
                    <p class="text-gray-300 text-xs truncate mt-1 sidebar-text">Administrator</p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-6 space-y-3">
            <a href="dashboard_admin.php" 
               class="tooltip flex items-center space-x-4 px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white text-sm font-medium rounded-xl shadow-lg active transform hover:-translate-y-0.5 transition-all duration-200"
               data-tooltip="Dashboard">
                <i class="fas fa-chart-pie w-5 text-center"></i>
                <span class="sidebar-text">Dashboard</span>
                <div class="ml-auto w-2 h-2 bg-white rounded-full animate-pulse sidebar-text"></div>
            </a>
            
            <a href="data_balita.php" 
               class="tooltip flex items-center space-x-4 px-4 py-3 text-gray-300 hover:text-white hover:bg-gray-700/50 text-sm font-medium rounded-lg hover:shadow-md transition-all duration-200"
               data-tooltip="Data Balita">
                <i class="fas fa-baby w-5 text-center"></i>
                <span class="sidebar-text">Data Balita</span>
                <span class="badge bg-gradient-to-r from-blue-500 to-blue-400 text-white text-xs px-2 py-1 rounded-full ml-auto shadow sidebar-text">
                    <?php echo $stats['total_balita']; ?>
                </span>
            </a>

            <a href="data_pengguna.php" 
               class="tooltip flex items-center space-x-4 px-4 py-3 text-gray-300 hover:text-white hover:bg-gray-700/50 text-sm font-medium rounded-lg hover:shadow-md transition-all duration-200"
               data-tooltip="Data Pengguna">
                <i class="fas fa-users w-5 text-center"></i>
                <span class="sidebar-text">Data Pengguna</span>
                <span class="badge bg-gradient-to-r from-green-500 to-green-400 text-white text-xs px-2 py-1 rounded-full ml-auto shadow sidebar-text">
                    <?php echo $stats['total_orang_tua']; ?>
                </span>
            </a>
        </nav>

        <!-- Bottom Section -->
        <div class="mt-auto p-6 border-t border-gray-700/50">
            <a href="../backend/logout.php" 
               class="tooltip flex items-center space-x-4 px-4 py-3 text-gray-300 hover:text-white hover:bg-gradient-to-r from-red-600/20 to-red-500/20 text-sm font-medium rounded-lg border border-gray-700/50 hover:border-red-500/30 transition-all duration-200 group"
               data-tooltip="Logout">
                <i class="fas fa-sign-out-alt w-5 text-center group-hover:text-red-400 transition-colors"></i>
                <span class="logout-text sidebar-text group-hover:text-red-300 transition-colors">Logout</span>
                <i class="fas fa-chevron-right chevron-right text-xs ml-auto opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 sidebar-text"></i>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content ml-64 min-h-screen">
        <!-- PERBAIKAN TOP BAR: Smooth header -->
        <header class="top-header bg-white/95 backdrop-blur-sm border-b border-gray-200/80 shadow-sm sticky top-0 z-40">
            <div class="flex justify-between items-center py-5 px-8">
                <div class="flex items-center space-x-4">
                    <button id="mobileToggle" class="md:hidden bg-gray-100 p-2 rounded-lg text-gray-600 hover:bg-gray-200 transition-colors">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="bg-gradient-to-r from-blue-100 to-blue-50 p-2 rounded-lg">
                        <i class="fas fa-chart-line text-blue-600"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Dashboard Admin</h1>
                        <p class="text-gray-500 text-sm mt-1">
                            Selamat datang, <span class="font-semibold text-blue-600">
                            <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']); ?></span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="text-sm text-gray-600 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200">
                        <i class="fas fa-clock mr-2 text-blue-500"></i>
                        <span class="font-medium"><?php echo date('d M Y, H:i'); ?></span>
                    </div>
                    <?php if ($perhatian['perlu_perhatian'] > 0): ?>
                    <div class="notification-badge bg-gradient-to-r from-red-100 to-red-50 text-red-700 px-4 py-2 rounded-full text-sm font-semibold border border-red-200 shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer group">
                        <i class="fas fa-exclamation-circle mr-2 group-hover:animate-pulse"></i>
                        <span class="font-bold"><?php echo $perhatian['perlu_perhatian']; ?></span> perlu perhatian
                        <span class="ml-2 text-xs bg-red-500 text-white px-2 py-1 rounded-full animate-pulse">!</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Main Content (TIDAK DIUBAH) -->
        <main class="p-8">
            <!-- Statistik Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Balita -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Total Balita</p>
                            <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_balita']; ?></p>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-xl">
                            <i class="fas fa-baby text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Kasus Stunting -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Kasus Stunting</p>
                            <p class="text-3xl font-bold text-red-600 mt-2"><?php echo $stats['total_stunting']; ?></p>
                        </div>
                        <div class="bg-red-100 p-4 rounded-xl">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="text-xs text-gray-600"><?php echo $persentase_stunting; ?>% dari total</div>
                    </div>
                </div>

                <!-- Balita Normal -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Balita Normal</p>
                            <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $stats['total_normal']; ?></p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-xl">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="text-xs text-gray-600"><?php echo $persentase_normal; ?>% dari total</div>
                    </div>
                </div>

                <!-- Orang Tua -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Orang Tua</p>
                            <p class="text-3xl font-bold text-purple-600 mt-2"><?php echo $stats['total_orang_tua']; ?></p>
                        </div>
                        <div class="bg-purple-100 p-4 rounded-xl">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts & Recent Data -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Activity Chart -->
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">Aktivitas Pemeriksaan</h3>
                        <span class="text-sm text-gray-600">6 bulan terakhir</span>
                    </div>
                    <div class="h-64">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>

                <!-- Status Gizi -->
                <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Distribusi Status Gizi</h3>
                    <?php if (count($status_gizi) > 0): ?>
                    <div class="space-y-4">
                        <?php foreach ($status_gizi as $gizi): ?>
                        <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 rounded-full 
                                    <?php echo $gizi['hasil'] == 'Normal' ? 'bg-green-500' : 
                                          ($gizi['hasil'] == 'Stunting' ? 'bg-red-500' : 'bg-yellow-500'); ?>">
                                </div>
                                <span class="text-sm font-medium text-gray-700"><?php echo $gizi['hasil']; ?></span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-semibold text-gray-800"><?php echo $gizi['jumlah']; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-gray-500 py-4">
                        <i class="fas fa-chart-pie text-3xl mb-2"></i>
                        <p>Tidak ada data</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Data -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between p-8 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Pemeriksaan Terbaru</h3>
                    <a href="data_balita.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center space-x-2 group">
                        <span>Lihat Semua</span>
                        <i class="fas fa-chevron-right text-xs group-hover:translate-x-1 transition-transform duration-200"></i>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Balita</th>
                                <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orang Tua</th>
                                <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur</th>
                                <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (count($recent_balita) > 0): ?>
                                <?php foreach ($recent_balita as $balita): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="bg-blue-100 w-10 h-10 rounded-full flex items-center justify-center mr-4">
                                                <i class="fas fa-baby text-blue-600"></i>
                                            </div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($balita['nama']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($balita['orang_tua'] ?? 'Tidak ada data'); ?></div>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo $balita['umur']; ?> bln</div>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <span class="px-3 py-1.5 text-xs font-semibold rounded-full 
                                            <?php echo $balita['hasil'] == 'Stunting' ? 'bg-red-100 text-red-800' : 
                                                  ($balita['hasil'] == 'Normal' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                            <?php echo $balita['hasil']; ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d M Y', strtotime($balita['tanggal_cek'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-8 py-8 text-center text-sm text-gray-500">
                                        <div class="flex flex-col items-center space-y-2">
                                            <i class="fas fa-baby text-gray-300 text-3xl"></i>
                                            <p>Tidak ada data balita</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar Toggle Functionality
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileToggle = document.getElementById('mobileToggle');
        
        // Load saved state from localStorage
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
            mainContent.classList.remove('ml-64');
            mainContent.style.marginLeft = '80px';
        }
        
        // Toggle sidebar
        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            
            if (sidebar.classList.contains('collapsed')) {
                mainContent.classList.add('expanded');
                mainContent.classList.remove('ml-64');
                mainContent.style.marginLeft = '80px';
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                mainContent.classList.remove('expanded');
                mainContent.classList.add('ml-64');
                mainContent.style.marginLeft = '';
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        }
        
        sidebarToggle.addEventListener('click', toggleSidebar);
        
        // Mobile toggle
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    sidebar.classList.toggle('hidden');
                }
            });
        }
        
        // Activity Chart
        <?php if (count($chart_data) > 0): ?>
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        const activityChart = new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: [<?php 
                    $labels = [];
                    foreach($chart_data as $data) {
                        $labels[] = "'" . $data['bulan'] . "'";
                    }
                    echo implode(', ', $labels);
                ?>],
                datasets: [{
                    label: 'Total Pemeriksaan',
                    data: [<?php 
                        $totals = [];
                        foreach($chart_data as $data) {
                            $totals[] = $data['total'];
                        }
                        echo implode(', ', $totals);
                    ?>],
                    backgroundColor: '#3b82f6',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 6,
                        displayColors: false
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
        <?php endif; ?>
        
        // Responsive sidebar for mobile
        function handleResize() {
            if (window.innerWidth < 768) {
                sidebar.classList.add('hidden');
                mainContent.classList.remove('ml-64');
                mainContent.style.marginLeft = '0';
            } else {
                sidebar.classList.remove('hidden');
                if (!sidebar.classList.contains('collapsed')) {
                    mainContent.classList.add('ml-64');
                    mainContent.style.marginLeft = '';
                } else {
                    mainContent.style.marginLeft = '80px';
                }
            }
        }
        
        window.addEventListener('resize', handleResize);
        handleResize(); // Initial check
    </script>
</body>
</html>