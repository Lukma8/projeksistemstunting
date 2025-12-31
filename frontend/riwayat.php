<?php
include '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'orang_tua') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM balita WHERE orang_tua_id = :user_id ORDER BY tanggal_cek DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemeriksaan - Sistem Stunting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        
        .action-btn {
            transition: all 0.2s ease-in-out;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
        }
        
        /* Styling untuk modal preview */
        .pdf-preview-modal {
            backdrop-filter: blur(5px);
        }
        
        .pdf-preview-container {
            max-height: 80vh;
            overflow-y: auto;
        }
        
        /* Loading animation */
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Custom scrollbar untuk iframe container */
        .iframe-container {
            position: relative;
            width: 100%;
            height: 600px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .iframe-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Notification styles */
        .custom-notification {
            transition: all 0.3s ease-in-out;
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
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-history mr-2 text-blue-600"></i>Riwayat Pemeriksaan
                </h1>
                <a href="cek_stunting.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Pemeriksaan Baru
                </a>
            </div>

            <?php if (count($riwayat) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full bg-white">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Nama Balita</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Umur</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Tinggi</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Berat</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Tanggal</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($riwayat as $data): ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="bg-blue-100 w-8 h-8 rounded-full flex items-center justify-center mr-3">
                                                <i class="fas fa-baby text-blue-600 text-xs"></i>
                                            </div>
                                            <span class="font-medium text-gray-900"><?php echo htmlspecialchars($data['nama']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700"><?php echo $data['umur']; ?> bulan</td>
                                    <td class="px-4 py-3 text-gray-700"><?php echo $data['tinggi_badan']; ?> cm</td>
                                    <td class="px-4 py-3 text-gray-700"><?php echo $data['berat_badan']; ?> kg</td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $data['hasil'] == 'Stunting' ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-green-100 text-green-800 border border-green-200'; ?>">
                                            <i class="fas <?php echo $data['hasil'] == 'Stunting' ? 'fa-exclamation-triangle mr-1' : 'fa-check-circle mr-1'; ?>"></i>
                                            <?php echo $data['hasil']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo date('d M Y', strtotime($data['tanggal_cek'])); ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex space-x-2">
                                            <!-- Button Preview PDF -->
                                            <button onclick="previewPdf(<?php echo $data['id']; ?>, '<?php echo htmlspecialchars($data['nama']); ?>')" 
                                                    class="action-btn bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg text-sm transition duration-200 group"
                                                    title="Preview PDF">
                                                <i class="fas fa-eye group-hover:scale-110 transition-transform"></i>
                                            </button>
                                            <!-- Button Download PDF -->
                                            <button onclick="downloadPdf(<?php echo $data['id']; ?>, '<?php echo htmlspecialchars($data['nama']); ?>')" 
                                                    class="action-btn bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg text-sm transition duration-200 group"
                                                    title="Download PDF">
                                                <i class="fas fa-download group-hover:scale-110 transition-transform"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum Ada Riwayat Pemeriksaan</h3>
                    <p class="text-gray-500 mb-6">Lakukan pemeriksaan pertama untuk melihat riwayat di sini.</p>
                    <a href="cek_stunting.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-300 inline-flex items-center gap-2">
                        <i class="fas fa-stethoscope"></i>
                        Cek Stunting Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- PDF Preview Modal -->
    <div id="pdfPreviewModal" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center z-50 p-4 pdf-preview-modal">
        <div class="bg-white rounded-xl w-full max-w-6xl mx-auto shadow-2xl">
            <!-- Modal Header -->
            <div class="flex justify-between items-center border-b border-gray-200 px-6 py-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                        Preview Laporan PDF
                    </h3>
                    <p class="text-sm text-gray-600 mt-1" id="previewFileName">
                        Memuat preview laporan...
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="openPdfInNewTab()" 
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2 text-sm">
                        <i class="fas fa-external-link-alt"></i>
                        Buka di Tab Baru
                    </button>
                    <button onclick="closePdfPreviewModal()" 
                            class="text-gray-500 hover:text-gray-700 transition duration-200 p-2">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Modal Content -->
            <div class="pdf-preview-container">
                <div id="pdfPreviewContent" class="p-6">
                    <!-- Loading State -->
                    <div id="pdfLoading" class="flex flex-col items-center justify-center py-12">
                        <div class="loading-spinner mb-4"></div>
                        <p class="text-gray-600 text-lg">Memuat preview PDF...</p>
                        <p class="text-gray-500 text-sm mt-2">Harap tunggu sebentar</p>
                    </div>
                    
                    <!-- PDF Content will be loaded here -->
                    <div id="pdfContent" class="hidden">
                        <div class="iframe-container">
                            <!-- PDF will be embedded here -->
                        </div>
                    </div>
                    
                    <!-- Error State -->
                    <div id="pdfError" class="hidden flex flex-col items-center justify-center py-12 text-center">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">Gagal Memuat Preview</h4>
                        <p class="text-gray-600 mb-4">Terjadi kesalahan saat memuat preview PDF.</p>
                        <div class="flex gap-2">
                            <button onclick="retryPreview()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-redo mr-2"></i>Coba Lagi
                            </button>
                            <button onclick="openPdfInNewTab()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-external-link-alt mr-2"></i>Buka di Tab Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-xl">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <span>Preview mungkin membutuhkan waktu beberapa detik untuk dimuat</span>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="closePdfPreviewModal()" 
                                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200 font-medium">
                            Tutup
                        </button>
                        <button onclick="downloadFromPreview()" 
                                class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition duration-200 font-medium flex items-center gap-2">
                            <i class="fas fa-download"></i>
                            Download PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Confirmation Modal -->
    <div id="downloadConfirmModal" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-auto shadow-2xl">
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-download text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Download Laporan PDF</h3>
                <p class="text-gray-600 mb-4" id="downloadConfirmText">
                    Apakah Anda yakin ingin mendownload laporan pemeriksaan?
                </p>
                <div class="flex gap-3 justify-center">
                    <button onclick="closeDownloadConfirmModal()" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition duration-200 flex-1">
                        Batal
                    </button>
                    <button onclick="proceedDownload()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2 transition duration-200 flex-1 justify-center">
                        <i class="fas fa-download"></i>
                        Ya, Download
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPdfId = null;
        let currentPdfName = '';

        // Function to preview PDF
        function previewPdf(id, nama) {
            currentPdfId = id;
            currentPdfName = nama;
            
            // Show modal
            const modal = document.getElementById('pdfPreviewModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Update file name
            document.getElementById('previewFileName').textContent = `Laporan: ${nama}`;
            
            // Show loading state
            document.getElementById('pdfLoading').classList.remove('hidden');
            document.getElementById('pdfContent').classList.add('hidden');
            document.getElementById('pdfError').classList.add('hidden');
            
            // Load PDF preview
            loadPdfPreview(id);
        }

        // Function to load PDF preview - SIMPLIFIED
        function loadPdfPreview(id) {
            const iframeContainer = document.querySelector('#pdfContent .iframe-container');
            iframeContainer.innerHTML = `
                <iframe 
                    src="../backend/export_pdf.php?balita_id=${id}&preview=true" 
                    width="100%" 
                    height="100%"
                    style="border: none;"
                    onload="onPdfLoad()"
                    onerror="onPdfError()"
                    title="PDF Preview - ${currentPdfName}">
                </iframe>
            `;
        }

        // PDF load event handlers
        function onPdfLoad() {
            console.log('PDF loaded successfully');
            document.getElementById('pdfLoading').classList.add('hidden');
            document.getElementById('pdfContent').classList.remove('hidden');
        }

        function onPdfError() {
            console.error('Failed to load PDF');
            document.getElementById('pdfLoading').classList.add('hidden');
            document.getElementById('pdfError').classList.remove('hidden');
        }

        function retryPreview() {
            document.getElementById('pdfError').classList.add('hidden');
            document.getElementById('pdfLoading').classList.remove('hidden');
            loadPdfPreview(currentPdfId);
        }

        // Function to open PDF in new tab - SIMPLIFIED AND FIXED
        function openPdfInNewTab() {
            if (!currentPdfId) {
                showNotification('Tidak ada PDF yang dipilih', 'error');
                return;
            }

            // Simple direct approach - just open the URL in new tab
            const pdfUrl = `../backend/export_pdf.php?balita_id=${currentPdfId}&preview=true`;
            
            // Try to open in new tab
            const newTab = window.open(pdfUrl, '_blank');
            
            if (!newTab) {
                // Popup blocked - show user how to allow
                showNotification(`
                    Popup diblokir! Silakan:<br>
                    1. Izinkan popup untuk situs ini, atau<br>
                    2. Klik kanan → "Buka link di tab baru"
                `, 'error', 6000);
                
                // Create a visible link as fallback
                const fallbackLink = document.createElement('a');
                fallbackLink.href = pdfUrl;
                fallbackLink.target = '_blank';
                fallbackLink.style.display = 'none';
                fallbackLink.textContent = 'Buka PDF';
                document.body.appendChild(fallbackLink);
                
                // Show instruction to user
                setTimeout(() => {
                    if (confirm('Popup diblokir. Klik OK untuk membuka PDF di tab baru, atau batalkan untuk menyalin link.')) {
                        fallbackLink.click();
                    } else {
                        // Copy link to clipboard as alternative
                        navigator.clipboard.writeText(pdfUrl).then(() => {
                            showNotification('Link telah disalin ke clipboard!', 'info');
                        });
                    }
                    document.body.removeChild(fallbackLink);
                }, 100);
            } else {
                showNotification('Membuka PDF di tab baru...', 'success');
            }
        }

        // Function to download PDF
        function downloadPdf(id, nama) {
            currentPdfId = id;
            currentPdfName = nama;
            
            document.getElementById('downloadConfirmText').innerHTML = 
                `Apakah Anda yakin ingin mendownload laporan pemeriksaan untuk <strong>${nama}</strong>?`;
            
            document.getElementById('downloadConfirmModal').classList.remove('hidden');
            document.getElementById('downloadConfirmModal').classList.add('flex');
        }

        // Function to download from preview modal
        function downloadFromPreview() {
            if (currentPdfId) {
                triggerDownload(currentPdfId);
                closePdfPreviewModal();
            }
        }

        // Function to proceed with download
        function proceedDownload() {
            if (currentPdfId) {
                triggerDownload(currentPdfId);
                closeDownloadConfirmModal();
            }
        }

        // Function to trigger download - SIMPLIFIED
        function triggerDownload(id) {
            const downloadUrl = `../backend/export_pdf.php?balita_id=${id}`;
            
            // Simple direct download approach
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = `laporan_stunting_${currentPdfName}_${new Date().toISOString().split('T')[0]}.pdf`;
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showNotification('Download dimulai...', 'success');
        }

        // Function to show notification
        function showNotification(message, type = 'info', duration = 4000) {
            // Remove existing notifications
            document.querySelectorAll('.custom-notification').forEach(notif => notif.remove());
            
            const notification = document.createElement('div');
            notification.className = `custom-notification fixed top-4 right-4 p-4 rounded-lg shadow-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.innerHTML = `
                <div class="flex items-center gap-3">
                    <i class="fas ${
                        type === 'success' ? 'fa-check-circle' : 
                        type === 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle'
                    }"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, duration);
        }

        // Modal close functions
        function closePdfPreviewModal() {
            document.getElementById('pdfPreviewModal').classList.add('hidden');
            document.getElementById('pdfPreviewModal').classList.remove('flex');
            
            // Clean up iframe
            const iframeContainer = document.querySelector('#pdfContent .iframe-container');
            if (iframeContainer) {
                iframeContainer.innerHTML = '';
            }
        }

        function closeDownloadConfirmModal() {
            document.getElementById('downloadConfirmModal').classList.add('hidden');
            document.getElementById('downloadConfirmModal').classList.remove('flex');
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.id === 'pdfPreviewModal') closePdfPreviewModal();
            if (e.target.id === 'downloadConfirmModal') closeDownloadConfirmModal();
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePdfPreviewModal();
                closeDownloadConfirmModal();
            }
        });

        // Prevent modal content from closing modal when clicked
        document.querySelectorAll('#pdfPreviewModal > div, #downloadConfirmModal > div').forEach(element => {
            element.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html>