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
    <title>Cek Stunting - Sistem Stunting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <a href="dashboard_ortu.php" class="text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                    <a href="../backend/logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-stethoscope mr-2 text-blue-600"></i>Cek Status Stunting Balita
            </h1>

            <form id="cekStuntingForm" action="../backend/check_stunting.php" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Data Diri Balita -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Data Diri Balita</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Balita</label>
                            <input type="text" name="nama" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Umur (bulan)</label>
                            <input type="number" name="umur" min="0" max="60" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin</label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="jenis_kelamin" value="L" required class="mr-2">
                                    <span>Laki-laki</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="jenis_kelamin" value="P" required class="mr-2">
                                    <span>Perempuan</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Pengukuran -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Hasil Pengukuran</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tinggi Badan (cm)</label>
                            <input type="number" step="0.1" name="tinggi_badan" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Berat Badan (kg)</label>
                            <input type="number" step="0.1" name="berat_badan" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lingkar Kepala (cm)</label>
                            <input type="number" step="0.1" name="lingkar_kepala" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lingkar Lengan (cm)</label>
                            <input type="number" step="0.1" name="lingkar_lengan" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                    <a href="dashboard_ortu.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-300">
                        Batal
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-300">
                        <i class="fas fa-calculator mr-2"></i>Proses Pemeriksaan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Result Modal -->
    <div id="resultModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md mx-4">
            <div id="modalContent"></div>
            <div class="mt-6 flex justify-end space-x-4">
                <button onclick="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Tutup
                </button>
                <button onclick="printResult()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-print mr-2"></i>Cetak
                </button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('cekStuntingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'check_stunting');
            
            fetch('../backend/check_stunting.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult(data);
                } else {
                    alert('Terjadi kesalahan: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses data');
            });
        });

        function showResult(data) {
            const modal = document.getElementById('resultModal');
            const content = document.getElementById('modalContent');
            
            const badgeClass = data.hasil === 'Stunting' ? 
                'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
            
            content.innerHTML = `
                <h3 class="text-xl font-bold mb-4">Hasil Pemeriksaan</h3>
                <div class="mb-4 p-4 rounded-lg ${badgeClass}">
                    <h4 class="font-semibold">Status: ${data.hasil}</h4>
                </div>
                <div class="space-y-2 text-sm">
                    <p><strong>Nama:</strong> ${data.nama}</p>
                    <p><strong>Umur:</strong> ${data.umur} bulan</p>
                    <p><strong>Tinggi Badan:</strong> ${data.tinggi_badan} cm</p>
                    <p><strong>Berat Badan:</strong> ${data.berat_badan} kg</p>
                </div>
                <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
                    <h4 class="font-semibold mb-2">Saran:</h4>
                    <p class="text-sm">${data.saran}</p>
                </div>
            `;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            const modal = document.getElementById('resultModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            window.location.href = 'riwayat.php';
        }

        function printResult() {
            window.open('../backend/export_pdf.php?balita_id=' + document.getElementById('balitaId').value, '_blank');
        }
    </script>
</body>
</html>