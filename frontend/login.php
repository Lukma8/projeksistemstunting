<?php include '../config/database.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Login - Sistem Stunting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="flex justify-center mb-4">
                <div class="bg-blue-600 p-3 rounded-xl">
                    <i class="fas fa-baby text-white text-2xl"></i>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Sistem Deteksi Stunting Balita</h1>
            <p class="text-gray-600 text-lg">Pilih jenis akun untuk masuk ke sistem</p>
        </div>

        <!-- Login Options -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Login Orang Tua -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center hover:shadow-md transition duration-300">
                <div class="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-users text-green-600 text-3xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-4">Login Orang Tua</h2>
                <p class="text-gray-600 mb-6">Masuk untuk memantau perkembangan balita Anda</p>
                
                <div class="space-y-3 mb-6 text-sm text-gray-600 text-left">
                    <div class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-3"></i>
                        <span>Cek status stunting balita</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-3"></i>
                        <span>Lihat riwayat pemeriksaan</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-3"></i>
                        <span>Dapatkan rekomendasi gizi</span>
                    </div>
                </div>

                <a href="login_ortu.php" class="block w-full bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg font-semibold transition duration-300">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk sebagai Orang Tua
                </a>

                <div class="mt-4">
                    <p class="text-gray-600 text-sm">
                        Belum punya akun? 
                        <a href="register.php" class="text-green-600 hover:text-green-700 font-medium">
                            Daftar di sini
                        </a>
                    </p>
                </div>
            </div>

            <!-- Login Admin -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center hover:shadow-md transition duration-300">
                <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-user-shield text-blue-600 text-3xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-4">Login Admin</h2>
                <p class="text-gray-600 mb-6">Masuk untuk mengelola data dan sistem</p>
                
                <div class="space-y-3 mb-6 text-sm text-gray-600 text-left">
                    <div class="flex items-center">
                        <i class="fas fa-check text-blue-500 mr-3"></i>
                        <span>Kelola data balita dan pengguna</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check text-blue-500 mr-3"></i>
                        <span>Lihat statistik dan laporan</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check text-blue-500 mr-3"></i>
                        <span>Export data ke Excel/PDF</span>
                    </div>
                </div>

                <a href="login_admin.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-semibold transition duration-300">
                    <i class="fas fa-lock mr-2"></i>Masuk sebagai Admin
                </a>

                <div class="mt-4">
                    <p class="text-gray-600 text-sm">
                        Hanya untuk administrator sistem
                    </p>
                </div>
            </div>
        </div>
        <!-- Back to Home -->
        <div class="text-center mt-8">
            <a href="../index.php" class="text-gray-600 hover:text-blue-600">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>