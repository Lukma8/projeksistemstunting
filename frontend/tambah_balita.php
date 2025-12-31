<?php
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Ambil data orang tua untuk dropdown
$query_ortu = "SELECT id, nama_lengkap, username FROM users WHERE role = 'orang_tua' ORDER BY nama_lengkap";
$stmt_ortu = $db->prepare($query_ortu);
$stmt_ortu->execute();
$orang_tua_data = $stmt_ortu->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_POST) {
    try {
        $nama = $_POST['nama'];
        $orang_tua_id = $_POST['orang_tua_id'] ?? null;
        $umur = $_POST['umur'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tinggi_badan = $_POST['tinggi_badan'];
        $berat_badan = $_POST['berat_badan'];
        $lingkar_kepala = $_POST['lingkar_kepala'] ?? null;
        $lingkar_lengan = $_POST['lingkar_lengan'] ?? null;
        $tanggal_cek = $_POST['tanggal_cek'];

        // Validasi input
        $errors = [];
        
        if (empty($nama)) {
            $errors[] = "Nama balita harus diisi";
        }
        
        if (empty($umur) || $umur < 0 || $umur > 60) {
            $errors[] = "Umur harus antara 0-60 bulan";
        }
        
        if (empty($tinggi_badan) || $tinggi_badan < 0) {
            $errors[] = "Tinggi badan harus diisi dengan nilai positif";
        }
        
        if (empty($berat_badan) || $berat_badan < 0) {
            $errors[] = "Berat badan harus diisi dengan nilai positif";
        }

        if (empty($errors)) {
            // Hitung status stunting berdasarkan umur dan tinggi badan
            $status = hitungStatusStunting($umur, $tinggi_badan, $jenis_kelamin);
            
            $query = "INSERT INTO balita 
                     (nama, orang_tua_id, umur, jenis_kelamin, tinggi_badan, berat_badan, 
                      lingkar_kepala, lingkar_lengan, hasil, tanggal_cek) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([
                $nama, $orang_tua_id, $umur, $jenis_kelamin, $tinggi_badan, $berat_badan,
                $lingkar_kepala, $lingkar_lengan, $status, $tanggal_cek
            ])) {
                $_SESSION['success'] = "Data balita berhasil ditambahkan";
                header("Location: data_balita.php");
                exit();
            } else {
                $_SESSION['error'] = "Gagal menambahkan data balita";
            }
        } else {
            $_SESSION['error'] = implode("<br>", $errors);
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Fungsi untuk menghitung status stunting
function hitungStatusStunting($umur, $tinggi_badan, $jenis_kelamin) {
    // Standar tinggi minimal berdasarkan umur (contoh sederhana)
    $standar_minimal = [
        'L' => [45, 61, 71, 76, 81, 85, 88, 91, 94, 97, 99], // Laki-laki 0-60 bulan
        'P' => [45, 60, 69, 74, 79, 83, 86, 89, 92, 95, 97]  // Perempuan 0-60 bulan
    ];
    
    // Tentukan index berdasarkan umur (setiap 6 bulan)
    $index = min(floor($umur / 6), 10);
    $tinggi_standar = $standar_minimal[$jenis_kelamin][$index] ?? 75;
    
    // Jika tinggi badan kurang dari standar, dianggap stunting
    if ($tinggi_badan < $tinggi_standar) {
        return "Stunting";
    } else {
        return "Normal";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Balita - Sistem Stunting</title>
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
            
            <a href="data_pengguna.php" class="nav-item flex items-center space-x-3 px-3 py-3 text-slate-300 hover:text-white text-sm font-medium">
                <i class="fas fa-users w-5 text-center"></i>
                <span class="sidebar-text">Data Pengguna</span>
            </a>
            
            <a href="laporan.php" class="nav-item flex items-center space-x-3 px-3 py-3 text-slate-300 hover:text-white text-sm font-medium">
                <i class="fas fa-file-alt w-5 text-center"></i>
                <span class="sidebar-text">Laporan</span>
            </a>
        </nav>

        <!-- Bottom Section -->
        <div class="p-4 border-t border-slate-700">
            <a href="../backend/logout.php" class="nav-item flex items-center space-x-3 px-3 py-2 text-slate-300 hover:text-white text-sm font-medium">
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
                    <h1 class="text-xl font-semibold text-gray-800">Tambah Data Balita</h1>
                    <p class="text-gray-600 text-sm">Tambah data pemeriksaan balita baru</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-clock mr-2"></i>
                        <span><?php echo date('d M Y, H:i'); ?></span>
                    </div>
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium"><?php echo substr($_SESSION['nama_lengkap'], 0, 1); ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Form Card -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Form Data Balita</h2>
                        <p class="text-sm text-gray-600">Isi form berikut untuk menambah data balita</p>
                    </div>

                    <form method="POST" class="p-6 space-y-6">
                        <!-- Notifications -->
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Grid Layout -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nama Balita -->
                            <div>
                                <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Balita <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="nama" name="nama" required
                                       value="<?php echo $_POST['nama'] ?? ''; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="Masukkan nama balita">
                            </div>

                            <!-- Orang Tua -->
                            <div>
                                <label for="orang_tua_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Orang Tua
                                </label>
                                <select id="orang_tua_id" name="orang_tua_id"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                    <option value="">Pilih Orang Tua (Opsional)</option>
                                    <?php foreach ($orang_tua_data as $ortu): ?>
                                        <option value="<?php echo $ortu['id']; ?>" 
                                                <?php echo ($_POST['orang_tua_id'] ?? '') == $ortu['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ortu['nama_lengkap']); ?> (<?php echo $ortu['username']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Umur -->
                            <div>
                                <label for="umur" class="block text-sm font-medium text-gray-700 mb-2">
                                    Umur (bulan) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="umur" name="umur" required min="0" max="60"
                                       value="<?php echo $_POST['umur'] ?? ''; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="0-60 bulan">
                            </div>

                            <!-- Jenis Kelamin -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Jenis Kelamin <span class="text-red-500">*</span>
                                </label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="jenis_kelamin" value="L" required
                                               <?php echo ($_POST['jenis_kelamin'] ?? '') == 'L' ? 'checked' : ''; ?>
                                               class="text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-gray-700">Laki-laki</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="jenis_kelamin" value="P" required
                                               <?php echo ($_POST['jenis_kelamin'] ?? '') == 'P' ? 'checked' : ''; ?>
                                               class="text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-gray-700">Perempuan</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Tinggi Badan -->
                            <div>
                                <label for="tinggi_badan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tinggi Badan (cm) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="tinggi_badan" name="tinggi_badan" required step="0.1" min="0"
                                       value="<?php echo $_POST['tinggi_badan'] ?? ''; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="Contoh: 65.5">
                            </div>

                            <!-- Berat Badan -->
                            <div>
                                <label for="berat_badan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Berat Badan (kg) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="berat_badan" name="berat_badan" required step="0.1" min="0"
                                       value="<?php echo $_POST['berat_badan'] ?? ''; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="Contoh: 7.2">
                            </div>

                            <!-- Lingkar Kepala -->
                            <div>
                                <label for="lingkar_kepala" class="block text-sm font-medium text-gray-700 mb-2">
                                    Lingkar Kepala (cm)
                                </label>
                                <input type="number" id="lingkar_kepala" name="lingkar_kepala" step="0.1" min="0"
                                       value="<?php echo $_POST['lingkar_kepala'] ?? ''; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="Optional">
                            </div>

                            <!-- Lingkar Lengan -->
                            <div>
                                <label for="lingkar_lengan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Lingkar Lengan (cm)
                                </label>
                                <input type="number" id="lingkar_lengan" name="lingkar_lengan" step="0.1" min="0"
                                       value="<?php echo $_POST['lingkar_lengan'] ?? ''; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="Optional">
                            </div>

                            <!-- Tanggal Pemeriksaan -->
                            <div class="md:col-span-2">
                                <label for="tanggal_cek" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Pemeriksaan <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="tanggal_cek" name="tanggal_cek" required
                                       value="<?php echo $_POST['tanggal_cek'] ?? date('Y-m-d'); ?>"
                                       class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <a href="data_balita.php" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>Simpan Data
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Info Card -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <h3 class="font-medium text-blue-800">Informasi Penting</h3>
                            <ul class="text-sm text-blue-700 mt-2 space-y-1">
                                <li>• Pastikan data yang dimasukkan sudah benar dan akurat</li>
                                <li>• Umur dihitung dalam bulan (0-60 bulan)</li>
                                <li>• Tinggi badan diukur dalam centimeter (cm)</li>
                                <li>• Berat badan diukur dalam kilogram (kg)</li>
                                <li>• Status stunting akan dihitung secara otomatis berdasarkan standar WHO</li>
                                <li>• Orang tua bersifat opsional, bisa dipilih nanti</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
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

        // Set default date to today
        document.getElementById('tanggal_cek').value = new Date().toISOString().substr(0, 10);

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const umur = document.getElementById('umur').value;
            const tinggi = document.getElementById('tinggi_badan').value;
            const berat = document.getElementById('berat_badan').value;
            
            if (umur < 0 || umur > 60) {
                alert('Umur harus antara 0-60 bulan');
                e.preventDefault();
                return false;
            }
            
            if (tinggi <= 0) {
                alert('Tinggi badan harus lebih dari 0');
                e.preventDefault();
                return false;
            }
            
            if (berat <= 0) {
                alert('Berat badan harus lebih dari 0');
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>