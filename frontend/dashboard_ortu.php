<?php
include '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'orang_tua') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Orang Tua - Sistem Stunting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        
        /* Animasi untuk modal */
        .modal-enter {
            opacity: 0;
            transform: scale(0.95);
        }
        .modal-enter-active {
            opacity: 1;
            transform: scale(1);
            transition: opacity 200ms ease-out, transform 200ms ease-out;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-baby text-2xl text-blue-600"></i>
                    <span class="text-xl font-bold text-gray-800">Posyandu Sehat</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Halo, <?php echo $_SESSION['nama_lengkap']; ?></span>
                    <button onclick="showLogoutConfirmation()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center gap-2">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex items-center space-x-4">
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-user-circle text-4xl text-blue-600"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, <?php echo $_SESSION['nama_lengkap']; ?>!</h1>
                    <p class="text-gray-600">Pantau perkembangan kesehatan balita Anda dengan sistem deteksi stunting kami.</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <a href="cek_stunting.php" class="bg-blue-600 hover:bg-blue-700 text-white p-6 rounded-xl shadow-lg transition duration-300 transform hover:scale-105">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-stethoscope text-3xl"></i>
                    <div>
                        <h3 class="text-xl font-semibold">Cek Stunting</h3>
                        <p class="opacity-90">Periksa status gizi balita Anda</p>
                    </div>
                </div>
            </a>
            
            <a href="riwayat.php" class="bg-green-600 hover:bg-green-700 text-white p-6 rounded-xl shadow-lg transition duration-300 transform hover:scale-105">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-history text-3xl"></i>
                    <div>
                        <h3 class="text-xl font-semibold">Riwayat Pemeriksaan</h3>
                        <p class="opacity-90">Lihat hasil pemeriksaan sebelumnya</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Recent Checks -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Pemeriksaan Terakhir</h2>
            <?php
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT * FROM balita WHERE orang_tua_id = :user_id ORDER BY tanggal_cek DESC LIMIT 3";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $badge_color = $row['hasil'] == 'Stunting' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
                    ?>
                    <div class="border-l-4 <?php echo $row['hasil'] == 'Stunting' ? 'border-red-500' : 'border-green-500'; ?> bg-gray-50 p-4 mb-3 rounded-r-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-semibold text-gray-800"><?php echo $row['nama']; ?></h3>
                                <p class="text-sm text-gray-600">Umur: <?php echo $row['umur']; ?> bulan | Tanggal: <?php echo $row['tanggal_cek']; ?></p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $badge_color; ?>">
                                <?php echo $row['hasil']; ?>
                            </span>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="text-gray-600 text-center py-4">Belum ada data pemeriksaan.</p>';
            }
            ?>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-auto transform modal-enter transition-all duration-200">
            <div class="text-center">
                <!-- Icon -->
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-sign-out-alt text-red-600 text-2xl"></i>
                </div>
                
                <!-- Title & Message -->
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi Logout</h3>
                <p class="text-gray-600 mb-6">
                    Apakah Anda yakin ingin keluar dari sistem?
                </p>
                
                <!-- Action Buttons -->
                <div class="flex gap-3 justify-center">
                    <button onclick="hideLogoutConfirmation()" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition duration-200 flex-1">
                        Batal
                    </button>
                    <button onclick="proceedLogout()" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2 transition duration-200 flex-1 justify-center">
                        <i class="fas fa-sign-out-alt"></i>
                        Ya, Logout
                    </button>
                </div>
                
                <!-- Additional Info -->
                <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-xs text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Anda dapat login kembali kapan saja dengan akun yang sama.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <?php if (isset($_SESSION['success'])): ?>
    <div id="notification" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        <i class="fas fa-check-circle mr-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
    <script>
        setTimeout(() => {
            const notification = document.getElementById('notification');
            if (notification) {
                notification.style.display = 'none';
            }
        }, 3000);
    </script>
    <?php endif; ?>

    <script>
        // Logout Confirmation Functions
        function showLogoutConfirmation() {
            const modal = document.getElementById('logoutModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Add animation class
            setTimeout(() => {
                modal.querySelector('div').classList.remove('modal-enter');
            }, 10);
        }

        function hideLogoutConfirmation() {
            const modal = document.getElementById('logoutModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function proceedLogout() {
            // Redirect to logout page
            window.location.href = '../backend/logout.php';
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.id === 'logoutModal') {
                hideLogoutConfirmation();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideLogoutConfirmation();
            }
        });

        // Prevent modal content from closing modal when clicked
        document.querySelectorAll('#logoutModal > div').forEach(element => {
            element.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html>