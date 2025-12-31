<?php
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Ambil ID dari parameter URL
$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id)) {
    $_SESSION['error'] = "ID balita tidak valid";
    header("Location: data_balita.php");
    exit();
}

// Ambil data balita berdasarkan ID
$query = "SELECT 
    b.*, 
    u.nama_lengkap as orang_tua,
    u.id as orang_tua_id
    FROM balita b
    LEFT JOIN users u ON b.orang_tua_id = u.id
    WHERE b.id = ?";
    
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$balita = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$balita) {
    $_SESSION['error'] = "Data balita tidak ditemukan";
    header("Location: data_balita.php");
    exit();
}

// Ambil daftar orang tua untuk dropdown
$query_ortu = "SELECT id, nama_lengkap, username FROM users WHERE role = 'orang_tua' ORDER BY nama_lengkap";
$stmt_ortu = $db->prepare($query_ortu);
$stmt_ortu->execute();
$orang_tua_list = $stmt_ortu->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'] ?? '';
    $orang_tua_id = $_POST['orang_tua_id'] ?? '';
    $umur = $_POST['umur'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tinggi_badan = $_POST['tinggi_badan'] ?? '';
    $berat_badan = $_POST['berat_badan'] ?? '';
    $lingkar_kepala = $_POST['lingkar_kepala'] ?? '';
    $lingkar_lengan = $_POST['lingkar_lengan'] ?? '';
    $tanggal_cek = $_POST['tanggal_cek'] ?? '';

    // Validasi input
    $errors = [];
    
    if (empty($nama)) $errors[] = "Nama balita harus diisi";
    if (empty($umur) || $umur < 0 || $umur > 60) $errors[] = "Umur harus antara 0-60 bulan";
    if (empty($tinggi_badan) || $tinggi_badan <= 0) $errors[] = "Tinggi badan harus diisi";
    if (empty($berat_badan) || $berat_badan <= 0) $errors[] = "Berat badan harus diisi";
    if (empty($tanggal_cek)) $errors[] = "Tanggal pemeriksaan harus diisi";

    if (empty($errors)) {
        try {
            // Hitung status stunting berdasarkan umur, tinggi, dan berat badan
            // (Anda perlu menyesuaikan dengan algoritma deteksi stunting yang digunakan)
            $hasil = hitungStatusStunting($umur, $tinggi_badan, $berat_badan);
            
            $update_query = "UPDATE balita SET 
                nama = ?,
                orang_tua_id = ?,
                umur = ?,
                jenis_kelamin = ?,
                tinggi_badan = ?,
                berat_badan = ?,
                lingkar_kepala = ?,
                lingkar_lengan = ?,
                tanggal_cek = ?,
                hasil = ?
                WHERE id = ?";
                
            $stmt_update = $db->prepare($update_query);
            $success = $stmt_update->execute([
                $nama,
                $orang_tua_id ?: null,
                $umur,
                $jenis_kelamin,
                $tinggi_badan,
                $berat_badan,
                $lingkar_kepala,
                $lingkar_lengan,
                $tanggal_cek,
                $hasil,
                $id
            ]);
            
            if ($success) {
                $_SESSION['success'] = "Data balita berhasil diperbarui";
                header("Location: data_balita.php");
                exit();
            } else {
                $_SESSION['error'] = "Gagal memperbarui data balita";
            }
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Fungsi untuk menghitung status stunting (contoh sederhana)
function hitungStatusStunting($umur, $tinggi_badan, $berat_badan) {
    // Ini adalah contoh sederhana, sesuaikan dengan standar WHO atau yang digunakan di sistem Anda
    $z_score = hitungZScore($umur, $tinggi_badan);
    
    if ($z_score < -3) {
        return 'Stunting';
    } elseif ($z_score < -2) {
        return 'Berisiko Stunting';
    } else {
        return 'Normal';
    }
}

// Fungsi contoh untuk menghitung Z-score (harus disesuaikan dengan standar yang benar)
function hitungZScore($umur, $tinggi_badan) {
    // Contoh sederhana - GANTI dengan perhitungan yang sesuai dengan standar WHO
    $median_tinggi = 80; // Contoh median untuk umur tertentu
    $std_dev = 5; // Contoh standard deviation
    
    return ($tinggi_badan - $median_tinggi) / $std_dev;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Balita - Sistem Stunting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="sidebar fixed inset-y-0 left-0 z-50 w-64 flex flex-col bg-gray-800">
        <!-- Logo & Toggle -->
        <div class="p-4 border-b border-gray-700 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-500 p-2 rounded-lg">
                    <i class="fas fa-baby text-white text-lg"></i>
                </div>
                <div>
                    <span class="text-lg font-bold text-white">Posyandu</span>
                    <p class="text-gray-400 text-xs">Admin Panel</p>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="p-4 border-b border-gray-700">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-500 w-10 h-10 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-shield text-white text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></p>
                    <p class="text-gray-400 text-xs truncate">Administrator</p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-4 space-y-2">
            <a href="dashboard_admin.php" class="flex items-center space-x-3 px-3 py-3 text-gray-300 hover:text-white hover:bg-gray-700 text-sm font-medium rounded-lg">
                <i class="fas fa-chart-pie w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="data_balita.php" class="flex items-center space-x-3 px-3 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg">
                <i class="fas fa-baby w-5 text-center"></i>
                <span>Data Balita</span>
            </a>

            <a href="data_pengguna.php" class="flex items-center space-x-3 px-3 py-3 text-gray-300 hover:text-white hover:bg-gray-700 text-sm font-medium rounded-lg">
                <i class="fas fa-users w-5 text-center"></i>
                <span>Data Pengguna</span>
            </a>
        </nav>

        <!-- Bottom Section -->
        <div class="mt-auto p-4 border-t border-gray-700">
            <a href="../backend/logout.php" class="flex items-center space-x-3 px-3 py-2 text-gray-300 hover:text-white hover:bg-gray-700 text-sm font-medium rounded-lg">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content ml-64 min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white border-b border-gray-200">
            <div class="flex justify-between items-center py-4 px-6">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800">Edit Data Balita</h1>
                    <p class="text-gray-600 text-sm">Perbarui informasi balita</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-clock mr-2"></i>
                        <span><?php echo date('d M Y, H:i'); ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Notification -->
                <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Edit Form -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nama Balita -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Balita *</label>
                                <input type="text" name="nama" value="<?php echo htmlspecialchars($balita['nama']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>

                            <!-- Orang Tua -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Orang Tua</label>
                                <select name="orang_tua_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Pilih Orang Tua</option>
                                    <?php foreach ($orang_tua_list as $ortu): ?>
                                    <option value="<?php echo $ortu['id']; ?>" 
                                        <?php echo ($balita['orang_tua_id'] == $ortu['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ortu['nama_lengkap']); ?> (<?php echo htmlspecialchars($ortu['username']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Umur -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Umur (bulan) *</label>
                                <input type="number" name="umur" value="<?php echo htmlspecialchars($balita['umur']); ?>" 
                                       min="0" max="60"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>

                            <!-- Jenis Kelamin -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin *</label>
                                <select name="jenis_kelamin" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="L" <?php echo ($balita['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="P" <?php echo ($balita['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>

                            <!-- Tinggi Badan -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tinggi Badan (cm) *</label>
                                <input type="number" name="tinggi_badan" value="<?php echo htmlspecialchars($balita['tinggi_badan']); ?>" 
                                       step="0.01" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>

                            <!-- Berat Badan -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Berat Badan (kg) *</label>
                                <input type="number" name="berat_badan" value="<?php echo htmlspecialchars($balita['berat_badan']); ?>" 
                                       step="0.01" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>

                            <!-- Lingkar Kepala -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lingkar Kepala (cm)</label>
                                <input type="number" name="lingkar_kepala" value="<?php echo htmlspecialchars($balita['lingkar_kepala']); ?>" 
                                       step="0.01" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Lingkar Lengan -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lingkar Lengan (cm)</label>
                                <input type="number" name="lingkar_lengan" value="<?php echo htmlspecialchars($balita['lingkar_lengan']); ?>" 
                                       step="0.01" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Tanggal Pemeriksaan -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pemeriksaan *</label>
                                <input type="date" name="tanggal_cek" value="<?php echo htmlspecialchars($balita['tanggal_cek']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                        </div>

                        <!-- Current Status -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Status Saat Ini</h3>
                            <div class="flex items-center space-x-4">
                                <span class="px-3 py-1 text-sm font-medium rounded-full 
                                    <?php echo $balita['hasil'] == 'Stunting' ? 'bg-red-100 text-red-800' : 
                                          ($balita['hasil'] == 'Normal' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                    <?php echo $balita['hasil']; ?>
                                </span>
                                <span class="text-sm text-gray-600">Status akan dihitung ulang setelah data diperbarui</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="data_balita.php" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                                Batal
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Real-time validation atau fitur tambahan bisa ditambahkan di sini
        console.log('Edit data balita ID: <?php echo $id; ?>');
    </script>
</body>
</html>