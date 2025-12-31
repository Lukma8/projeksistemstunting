<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deteksi Dini Stunting Balita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS tetap sama */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .nav-button {
            transition: all 0.3s ease;
        }
        
        .nav-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .fixed-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        
        .quick-nav-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .quick-nav-card:hover {
            border-color: #3b82f6;
            transform: translateY(-5px);
        }
        
        .section-active {
            color: #3b82f6;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Fixed Navigation Bar -->
    <div id="fixedNav" class="fixed top-0 left-0 right-0 z-50 hidden fixed-nav shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-baby text-xl text-blue-600"></i>
                    <span class="text-lg font-bold text-gray-800">Posyandu Sehat</span>
                </div>
                
                <div class="flex space-x-6">
                    <a href="#tentang-stunting" class="nav-link text-gray-700 hover:text-blue-600 text-sm font-medium">Tentang Stunting</a>
                    <a href="#pencegahan-stunting" class="nav-link text-gray-700 hover:text-blue-600 text-sm font-medium">Cara Mencegah</a>
                    <a href="#tanda-stunting" class="nav-link text-gray-700 hover:text-blue-600 text-sm font-medium">Tanda-Tanda</a>
                    <a href="#fitur-utama" class="nav-link text-gray-700 hover:text-blue-600 text-sm font-medium">Fitur Sistem</a>
                    <!-- PERBAIKAN: Link login yang benar -->
                    <a href="frontend/login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300">
                        <i class="fas fa-sign-in-alt mr-1"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <nav class="bg-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-baby text-2xl text-blue-600"></i>
                    <span class="text-xl font-bold text-gray-800">Posyandu Sehat</span>
                </div>
                <div class="space-x-4">
                    <!-- PERBAIKAN: Link login yang benar -->
                    <a href="frontend/login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20 relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0">
            <div class="absolute -top-40 -right-32 w-80 h-80 bg-white bg-opacity-10 rounded-full"></div>
            <div class="absolute -bottom-40 -left-32 w-80 h-80 bg-white bg-opacity-10 rounded-full"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
            <h1 class="text-5xl font-bold mb-6">
                Deteksi Dini <span class="text-yellow-300">Stunting</span> Balita
            </h1>
            <p class="text-xl mb-8 max-w-3xl mx-auto">
                Sistem monitoring dan deteksi dini stunting untuk balita dengan rekomendasi penanganan yang tepat
            </p>
            <div class="space-x-4 mb-16">
                <!-- PERBAIKAN: Link register yang benar -->
                <a href="frontend/register.php" class="bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-lg font-semibold text-lg transition duration-300">
                    <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                </a>
                <a href="#tentang-stunting" class="bg-white hover:bg-gray-100 text-blue-600 px-8 py-3 rounded-lg font-semibold text-lg transition duration-300">
                    <i class="fas fa-info-circle mr-2"></i>Pelajari Stunting
                </a>
            </div>

            <!-- Quick Navigation Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                <a href="#pencegahan-stunting" class="quick-nav-card bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-6 rounded-xl border border-white border-opacity-30">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-white bg-opacity-20 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-shield-alt text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2">Cara Mencegah</h3>
                        <p class="text-sm opacity-90">Stunting</p>
                        <div class="mt-3 text-xs opacity-75">
                            <i class="fas fa-lightbulb mr-1"></i>
                            Tips pencegahan efektif
                        </div>
                    </div>
                </a>
                
                <a href="#tanda-stunting" class="quick-nav-card bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-6 rounded-xl border border-white border-opacity-30">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-white bg-opacity-20 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-exclamation-triangle text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2">Tanda-Tanda</h3>
                        <p class="text-sm opacity-90">Stunting</p>
                        <div class="mt-3 text-xs opacity-75">
                            <i class="fas fa-eye mr-1"></i>
                            Kenali gejalanya
                        </div>
                    </div>
                </a>
                
                <a href="#fitur-utama" class="quick-nav-card bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-6 rounded-xl border border-white border-opacity-30">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-white bg-opacity-20 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-star text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2">Fitur Utama</h3>
                        <p class="text-sm opacity-90">Sistem</p>
                        <div class="mt-3 text-xs opacity-75">
                            <i class="fas fa-cogs mr-1"></i>
                            Teknologi terdepan
                        </div>
                    </div>
                </a>
            </div>

            <!-- Scroll Indicator -->
            <div class="mt-12">
                <div class="flex flex-col items-center">
                    <span class="text-sm opacity-75 mb-2">Scroll untuk eksplorasi lebih lanjut</span>
                    <div class="w-6 h-10 border-2 border-white border-opacity-50 rounded-full flex justify-center">
                        <div class="w-1 h-3 bg-white bg-opacity-50 rounded-full mt-2 animate-bounce"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Floating Action Buttons for Mobile -->
    <div class="fixed bottom-6 right-6 z-40 md:hidden">
        <div class="bg-blue-600 text-white p-4 rounded-full shadow-lg">
            <i class="fas fa-bars text-xl"></i>
        </div>
        <div id="mobileMenu" class="hidden absolute bottom-16 right-0 bg-white rounded-lg shadow-xl p-4 min-w-48">
            <div class="space-y-2">
                <a href="#pencegahan-stunting" class="flex items-center text-gray-700 hover:text-blue-600 text-sm font-medium">
                    <i class="fas fa-shield-alt mr-3 text-blue-500"></i>
                    Cara Mencegah
                </a>
                <a href="#tanda-stunting" class="flex items-center text-gray-700 hover:text-blue-600 text-sm font-medium">
                    <i class="fas fa-exclamation-triangle mr-3 text-yellow-500"></i>
                    Tanda-Tanda
                </a>
                <a href="#fitur-utama" class="flex items-center text-gray-700 hover:text-blue-600 text-sm font-medium">
                    <i class="fas fa-star mr-3 text-purple-500"></i>
                    Fitur Sistem
                </a>
                <div class="border-t pt-2">
                    <!-- PERBAIKAN: Link login mobile yang benar -->
                    <a href="frontend/login.php" class="flex items-center text-green-600 hover:text-green-700 text-sm font-medium">
                        <i class="fas fa-sign-in-alt mr-3"></i>
                        Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tentang Stunting Section -->
    <section id="tentang-stunting" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Apa Itu Stunting?</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Stunting adalah kondisi gagal tumbuh pada anak balita akibat kekurangan gizi kronis dan infeksi berulang
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                <!-- Penyebab Stunting -->
                <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                    <div class="flex items-center mb-4">
                        <div class="bg-red-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Penyebab Stunting</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <i class="fas fa-utensils text-red-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Kurang Gizi Kronis</h4>
                                <p class="text-gray-600 text-sm">Asupan gizi yang tidak mencukupi dalam waktu lama</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-baby text-red-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Pola Asuh Tidak Tepat</h4>
                                <p class="text-gray-600 text-sm">Pemberian MPASI yang tidak sesuai dan kurang stimulasi</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-tint text-red-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Sanitasi Buruk</h4>
                                <p class="text-gray-600 text-sm">Akses air bersih dan sanitasi yang terbatas</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-stethoscope text-red-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Infeksi Berulang</h4>
                                <p class="text-gray-600 text-sm">Penyakit infeksi yang terjadi berulang kali</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dampak Stunting -->
                <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                    <div class="flex items-center mb-4">
                        <div class="bg-orange-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-heartbeat text-orange-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Dampak Stunting</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <i class="fas fa-brain text-orange-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Gangguan Kognitif</h4>
                                <p class="text-gray-600 text-sm">Perkembangan otak dan kemampuan belajar terhambat</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-chart-line text-orange-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Postur Tubuh Pendek</h4>
                                <p class="text-gray-600 text-sm">Tinggi badan tidak sesuai dengan usia</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-shield-alt text-orange-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Sistem Imun Lemah</h4>
                                <p class="text-gray-600 text-sm">Mudah sakit dan rentan terhadap penyakit</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-graduation-cap text-orange-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Masa Depan Terhambat</h4>
                                <p class="text-gray-600 text-sm">Produktivitas dan kualitas hidup menurun</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pencegahan Stunting Section -->
    <section id="pencegahan-stunting" class="py-16 bg-green-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-green-800 mb-4">Cara Mencegah Stunting</h2>
                <p class="text-green-700 max-w-2xl mx-auto">Langkah-langkah penting untuk mencegah stunting pada balita</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="text-center bg-white rounded-xl p-6 shadow-lg card-hover">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-female text-green-600 text-xl"></i>
                    </div>
                    <h4 class="font-semibold text-green-800 mb-2">Gizi Ibu Hamil</h4>
                    <p class="text-green-700 text-sm">Penuhi gizi selama kehamilan untuk pertumbuhan janin optimal</p>
                </div>
                <div class="text-center bg-white rounded-xl p-6 shadow-lg card-hover">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-baby text-green-600 text-xl"></i>
                    </div>
                    <h4 class="font-semibold text-green-800 mb-2">ASI Eksklusif</h4>
                    <p class="text-green-700 text-sm">Berikan ASI hingga 6 bulan pertama kehidupan</p>
                </div>
                <div class="text-center bg-white rounded-xl p-6 shadow-lg card-hover">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-utensils text-green-600 text-xl"></i>
                    </div>
                    <h4 class="font-semibold text-green-800 mb-2">MPASI Sehat</h4>
                    <p class="text-green-700 text-sm">MPASI bergizi dan seimbang setelah 6 bulan</p>
                </div>
                <div class="text-center bg-white rounded-xl p-6 shadow-lg card-hover">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-home text-green-600 text-xl"></i>
                    </div>
                    <h4 class="font-semibold text-green-800 mb-2">Sanitasi Bersih</h4>
                    <p class="text-green-700 text-sm">Jaga kebersihan lingkungan dan personal hygiene</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tanda-Tanda Stunting Section -->
    <section id="tanda-stunting" class="py-16 bg-yellow-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-yellow-800 mb-4">Tanda-Tanda Stunting</h2>
                <p class="text-yellow-700">Kenali gejala stunting sejak dini untuk penanganan yang tepat</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white border-l-4 border-yellow-500 p-6 rounded-lg shadow-sm card-hover">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-ruler-vertical text-yellow-600 text-xl mr-3"></i>
                        <h3 class="font-bold text-gray-800">Tinggi Badan</h3>
                    </div>
                    <p class="text-gray-700 text-sm">Tinggi badan lebih pendek dari anak seusianya menurut standar WHO</p>
                </div>

                <div class="bg-white border-l-4 border-red-500 p-6 rounded-lg shadow-sm card-hover">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-weight text-red-600 text-xl mr-3"></i>
                        <h3 class="font-bold text-gray-800">Berat Badan</h3>
                    </div>
                    <p class="text-gray-700 text-sm">Berat badan tidak ideal dan sulit naik sesuai grafik pertumbuhan</p>
                </div>

                <div class="bg-white border-l-4 border-blue-500 p-6 rounded-lg shadow-sm card-hover">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-brain text-blue-600 text-xl mr-3"></i>
                        <h3 class="font-bold text-gray-800">Perkembangan</h3>
                    </div>
                    <p class="text-gray-700 text-sm">Keterlambatan dalam perkembangan motorik dan kognitif</p>
                </div>

                <div class="bg-white border-l-4 border-purple-500 p-6 rounded-lg shadow-sm card-hover">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-teeth text-purple-600 text-xl mr-3"></i>
                        <h3 class="font-bold text-gray-800">Gigi Tumbuh Lambat</h3>
                    </div>
                    <p class="text-gray-700 text-sm">Pertumbuhan gigi yang lebih lambat dari anak seusianya</p>
                </div>

                <div class="bg-white border-l-4 border-green-500 p-6 rounded-lg shadow-sm card-hover">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-face-tired text-green-600 text-xl mr-3"></i>
                        <h3 class="font-bold text-gray-800">Wajah Tampak Tua</h3>
                    </div>
                    <p class="text-gray-700 text-sm">Wajah tampak lebih tua dari usianya</p>
                </div>

                <div class="bg-white border-l-4 border-indigo-500 p-6 rounded-lg shadow-sm card-hover">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-wind text-indigo-600 text-xl mr-3"></i>
                        <h3 class="font-bold text-gray-800">Mudah Lelah</h3>
                    </div>
                    <p class="text-gray-700 text-sm">Anak mudah lelah dan kurang aktif dalam bermain</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Fitur Utama Sistem Section -->
    <section id="fitur-utama" class="py-16 bg-blue-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-blue-800 mb-4">Fitur Utama Sistem</h2>
                <p class="text-blue-700 max-w-2xl mx-auto">Fitur lengkap untuk memantau dan mencegah stunting pada balita</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div class="text-center bg-white rounded-xl p-8 shadow-lg card-hover">
                    <div class="bg-blue-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-stethoscope text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Deteksi Dini</h3>
                    <p class="text-gray-600 mb-4">Deteksi risiko stunting secara dini dengan parameter lengkap dan standar WHO</p>
                    <ul class="text-sm text-gray-600 space-y-2 text-left">
                        <li class="flex items-center">
                            <i class="fas fa-check text-blue-500 mr-2"></i>
                            Pengukuran tinggi dan berat badan
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-blue-500 mr-2"></i>
                            Analisis lingkar kepala dan lengan
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-blue-500 mr-2"></i>
                            Standar WHO yang terupdate
                        </li>
                    </ul>
                </div>
                
                <div class="text-center bg-white rounded-xl p-8 shadow-lg card-hover">
                    <div class="bg-green-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-chart-line text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Monitoring Berkala</h3>
                    <p class="text-gray-600 mb-4">Pantau perkembangan balita secara berkala dengan grafik yang mudah dipahami</p>
                    <ul class="text-sm text-gray-600 space-y-2 text-left">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Riwayat pemeriksaan lengkap
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Grafik pertumbuhan interaktif
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Notifikasi pemeriksaan rutin
                        </li>
                    </ul>
                </div>
                
                <div class="text-center bg-white rounded-xl p-8 shadow-lg card-hover">
                    <div class="bg-orange-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-utensils text-orange-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Rekomendasi Gizi</h3>
                    <p class="text-gray-600 mb-4">Dapatkan saran gizi dan perawatan yang tepat berdasarkan kondisi balita</p>
                    <ul class="text-sm text-gray-600 space-y-2 text-left">
                        <li class="flex items-center">
                            <i class="fas fa-check text-orange-500 mr-2"></i>
                            Rekomendasi makanan spesifik
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-orange-500 mr-2"></i>
                            Tips pola asuh yang tepat
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-orange-500 mr-2"></i>
                            Panduan MPASI bergizi
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Mulai Pantau Kesehatan Balita Anda</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">
                Jangan tunggu hingga terlambat. Deteksi dini stunting untuk masa depan yang lebih baik.
            </p>
            <div class="space-x-4">
                <!-- PERBAIKAN: Link register yang benar -->
                <a href="frontend/register.php" class="bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-lg font-semibold text-lg transition duration-300">
                    <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                </a>
                <!-- PERBAIKAN: Link login yang benar -->
                <a href="frontend/login.php" class="bg-white hover:bg-gray-100 text-blue-600 px-8 py-3 rounded-lg font-semibold text-lg transition duration-300">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-baby text-2xl text-blue-400"></i>
                        <span class="text-xl font-bold">Posyandu Sehat</span>
                    </div>
                    <p class="text-gray-400">
                        Sistem deteksi dini stunting balita untuk memantau perkembangan dan memberikan rekomendasi penanganan yang tepat.
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Navigasi Cepat</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#tentang-stunting" class="hover:text-white">Tentang Stunting</a></li>
                        <li><a href="#pencegahan-stunting" class="hover:text-white">Pencegahan</a></li>
                        <li><a href="#tanda-stunting" class="hover:text-white">Tanda-Tanda</a></li>
                        <li><a href="#fitur-utama" class="hover:text-white">Fitur Sistem</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Kontak</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2 text-sm"></i>
                            <span>+62 812-3456-7890</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2 text-sm"></i>
                            <span>info@posyandusehat.com</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-sm"></i>
                            <span>Jakarta, Indonesia</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400">
                <p>&copy; 2024 Sistem Deteksi Dini Stunting Balita. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Fixed Navigation Show/Hide
        const fixedNav = document.getElementById('fixedNav');
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                fixedNav.classList.remove('hidden');
            } else {
                fixedNav.classList.add('hidden');
            }
            
            // Update active nav link
            updateActiveNavLink();
        });

        // Smooth scroll untuk anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Update active navigation link
        function updateActiveNavLink() {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.nav-link');
            
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollY >= (sectionTop - 100)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active', 'section-active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active', 'section-active');
                }
            });
        }

        // Mobile menu toggle
        const mobileMenuButton = document.querySelector('.fixed.bottom-6');
        const mobileMenu = document.getElementById('mobileMenu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!mobileMenuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
                    mobileMenu.classList.add('hidden');
                }
            });
        }

        // Animasi saat scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Terapkan animasi pada elemen yang diinginkan
        document.querySelectorAll('.card-hover').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });

        // Initialize
        updateActiveNavLink();
    </script>
</body>
</html>