<?php
include '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Set timezone untuk Jakarta
date_default_timezone_set('Asia/Jakarta');

$database = new Database();
$db = $database->getConnection();

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $query = "DELETE FROM users WHERE id = ? AND role = 'orang_tua'";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$delete_id])) {
        $_SESSION['success'] = "Data pengguna berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus data pengguna";
    }
    header("Location: data_pengguna.php");
    exit();
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT * FROM users WHERE role = 'orang_tua'";
$params = [];

if (!empty($search)) {
    $query .= " AND (username LIKE ? OR nama_lengkap LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$users_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total users
$query_count = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admin,
    SUM(CASE WHEN role = 'orang_tua' THEN 1 ELSE 0 END) as total_orang_tua
    FROM users";
$stmt_count = $db->prepare($query_count);
$stmt_count->execute();
$count_data = $stmt_count->fetch(PDO::FETCH_ASSOC);

// Format waktu yang akurat
$current_time = date('d M Y, H:i:s');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengguna - Sistem Stunting</title>
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
        
        /* Animation for modal */
        @keyframes modalFadeIn {
            from { 
                opacity: 0; 
                transform: translateY(-20px) scale(0.95); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }

        .modal-animation {
            animation: modalFadeIn 0.3s ease-out;
        }

        /* Backdrop animation */
        @keyframes backdropFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .backdrop-animation {
            animation: backdropFadeIn 0.2s ease-out;
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
            
            <a href="data_balita.php" class="nav-item flex items-center space-x-3 px-3 py-3 text-slate-300 hover:text-white text-sm font-medium">
                <i class="fas fa-baby w-5 text-center"></i>
                <span class="sidebar-text">Data Balita</span>
            </a>
            
            <a href="data_pengguna.php" class="nav-item active flex items-center space-x-3 px-3 py-3 text-white text-sm font-medium">
                <i class="fas fa-users w-5 text-center"></i>
                <span class="sidebar-text">Data Pengguna</span>
                <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded ml-auto sidebar-text"><?php echo $count_data['total_orang_tua']; ?></span>
            </a>
        </nav>

        <!-- Bottom Section - Logout dengan konfirmasi -->
        <div class="mt-auto p-4 border-t border-slate-700">
            <a href="#" id="logoutBtn" class="nav-item flex items-center space-x-3 px-3 py-2 text-slate-300 hover:text-white text-sm font-medium">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span class="sidebar-text">Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content ml-64 min-h-screen transition-all duration-300">
        <!-- Top Bar -->
        <header class="bg-white border-b border-gray-200">
            <div class="flex justify-between items-center py-4 px-6">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800">Data Pengguna</h1>
                    <p class="text-gray-600 text-sm">Kelola data orang tua dan admin</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-clock mr-2"></i>
                        <span id="currentTime"><?php echo $current_time; ?></span>
                    </div>
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium"><?php echo substr($_SESSION['nama_lengkap'], 0, 1); ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="p-6">
            <!-- Statistik Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="stat-card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Total Pengguna</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $count_data['total_users']; ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <i class="fas fa-users text-blue-600 text-lg"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Orang Tua</p>
                            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo $count_data['total_orang_tua']; ?></p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <i class="fas fa-user-friends text-green-600 text-lg"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Admin</p>
                            <p class="text-2xl font-bold text-purple-600 mt-1"><?php echo $count_data['total_admin']; ?></p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <i class="fas fa-user-shield text-purple-600 text-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 space-y-4 lg:space-y-0">
                <div class="flex space-x-4">
                    <!-- Search Form -->
                    <form method="GET" class="flex space-x-2">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Cari username atau nama..." 
                               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-search"></i>
                            <span>Cari</span>
                        </button>
                        <?php if (!empty($search)): ?>
                        <a href="data_pengguna.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Reset</span>
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="text-sm text-gray-600">
                    Total: <span class="font-semibold"><?php echo count($users_data); ?> orang tua</span>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Daftar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($users_data)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                    <p>Tidak ada data pengguna</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($users_data as $user): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="bg-green-100 w-8 h-8 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-green-600 text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['nama_lengkap']); ?></div>
                                            <div class="text-xs text-gray-500">@<?php echo htmlspecialchars($user['username']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $user['role'] == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo $user['role'] == 'admin' ? 'Administrator' : 'Orang Tua'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="edit_pengguna.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['role'] == 'orang_tua'): ?>
                                        <a href="data_pengguna.php?delete_id=<?php echo $user['id']; ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('Yakin ingin menghapus pengguna <?php echo htmlspecialchars($user['nama_lengkap']); ?>?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
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

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-animation"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full modal-animation transform transition-all">
                <!-- Header -->
                <div class="flex items-center p-6 border-b border-gray-100">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-800">Konfirmasi Logout</h3>
                        <p class="text-gray-600 text-sm mt-1">Anda akan keluar dari sistem</p>
                    </div>
                </div>

                <!-- Body -->

                <!-- Footer -->
                <div class="flex justify-end space-x-3 p-6 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                    <button id="cancelLogout" class="px-5 py-2.5 text-gray-700 hover:text-gray-900 font-medium rounded-lg border border-gray-300 hover:border-gray-400 transition duration-200">
                        Batal
                    </button>
                    <a href="../backend/logout.php" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Ya, Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
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

        // Real-time clock update
        function updateClock() {
            const now = new Date();
            const options = {
                timeZone: 'Asia/Jakarta',
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            };
            
            const formatter = new Intl.DateTimeFormat('id-ID', options);
            const formattedTime = formatter.format(now);
            
            // Replace comma dengan format yang lebih baik
            const finalTime = formattedTime.replace(',', ' |');
            document.getElementById('currentTime').textContent = finalTime;
        }

        // Update clock every second
        updateClock();
        setInterval(updateClock, 1000);

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
    </script>
</body>
</html>