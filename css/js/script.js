// Sistem Deteksi Stunting - Main JavaScript File

class StuntingApp {
    constructor() {
        this.init();
    }

    init() {
        this.initializeEventListeners();
        this.initializeComponents();
        this.setupServiceWorker();
    }

    // Initialize all event listeners
    initializeEventListeners() {
        // Form validation
        this.setupFormValidation();
        
        // Modal handlers
        this.setupModalHandlers();
        
        // Notification handlers
        this.setupNotificationHandlers();
        
        // Print handlers
        this.setupPrintHandlers();
        
        // Chart handlers
        this.setupChartHandlers();
    }

    // Initialize components
    initializeComponents() {
        this.initializeDataTables();
        this.initializeCharts();
        this.initializePrintFunctionality();
    }

    // Form Validation
    setupFormValidation() {
        const forms = document.querySelectorAll('form[needs-validation]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.showNotification('error', 'Harap isi semua field yang wajib diisi.');
                }
            });
        });

        // Real-time validation
        const inputs = document.querySelectorAll('.form-input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
        });
    }

    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    validateField(field) {
        const value = field.value.trim();
        const fieldName = field.getAttribute('name') || 'Field';
        
        // Remove existing validation classes
        field.classList.remove('border-red-500', 'border-green-500');
        
        if (!value) {
            field.classList.add('border-red-500');
            this.showFieldError(field, `${fieldName} tidak boleh kosong.`);
            return false;
        }
        
        // Specific validations based on field type
        switch(field.type) {
            case 'number':
                if (field.min && parseFloat(value) < parseFloat(field.min)) {
                    field.classList.add('border-red-500');
                    this.showFieldError(field, `${fieldName} minimal ${field.min}.`);
                    return false;
                }
                if (field.max && parseFloat(value) > parseFloat(field.max)) {
                    field.classList.add('border-red-500');
                    this.showFieldError(field, `${fieldName} maksimal ${field.max}.`);
                    return false;
                }
                break;
                
            case 'email':
                if (!this.isValidEmail(value)) {
                    field.classList.add('border-red-500');
                    this.showFieldError(field, 'Format email tidak valid.');
                    return false;
                }
                break;
        }
        
        field.classList.add('border-green-500');
        this.clearFieldError(field);
        return true;
    }

    showFieldError(field, message) {
        this.clearFieldError(field);
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-500 text-xs mt-1';
        errorDiv.textContent = message;
        errorDiv.id = `error-${field.name}`;
        
        field.parentNode.appendChild(errorDiv);
    }

    clearFieldError(field) {
        const existingError = document.getElementById(`error-${field.name}`);
        if (existingError) {
            existingError.remove();
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Modal Handlers
    setupModalHandlers() {
        // Close modal on overlay click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                this.closeModal(e.target);
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal(modalElement) {
        if (modalElement) {
            modalElement.classList.add('hidden');
            modalElement.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    }

    closeAllModals() {
        const modals = document.querySelectorAll('.modal-overlay');
        modals.forEach(modal => {
            this.closeModal(modal);
        });
    }

    // Notification System
    setupNotificationHandlers() {
        // Auto-hide notifications after 5 seconds
        const notifications = document.querySelectorAll('[data-auto-hide]');
        notifications.forEach(notification => {
            setTimeout(() => {
                this.hideNotification(notification);
            }, 5000);
        });
    }

    showNotification(type, message, duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white ${
            type === 'success' ? 'bg-green-500' :
            type === 'error' ? 'bg-red-500' :
            type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
        }`;
        
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'
                } mr-2"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        if (duration > 0) {
            setTimeout(() => {
                this.hideNotification(notification);
            }, duration);
        }
    }

    hideNotification(notification) {
        if (notification && notification.parentNode) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }

    // Print Functionality
    setupPrintHandlers() {
        const printButtons = document.querySelectorAll('[data-print]');
        printButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.printElement(button.getAttribute('data-print'));
            });
        });
    }

    printElement(elementId) {
        const element = document.getElementById(elementId);
        if (!element) {
            this.showNotification('error', 'Element untuk print tidak ditemukan.');
            return;
        }

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Document</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .no-print { display: none; }
                    .print-break { page-break-after: always; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f5f5f5; }
                </style>
            </head>
            <body>
                ${element.innerHTML}
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(() => window.close(), 500);
                    }
                <\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
    }

    initializePrintFunctionality() {
        window.printResult = function(balitaId) {
            if (balitaId) {
                window.open(`../backend/export_pdf.php?balita_id=${balitaId}`, '_blank');
            } else {
                app.showNotification('error', 'ID balita tidak valid untuk print.');
            }
        };
    }

    // Chart Handlers
    setupChartHandlers() {
        // Initialize charts if Chart.js is available
        if (typeof Chart !== 'undefined') {
            this.initializeCharts();
        }
    }

    initializeCharts() {
        const chartElements = document.querySelectorAll('[data-chart]');
        
        chartElements.forEach(element => {
            const chartType = element.getAttribute('data-chart-type') || 'bar';
            const chartData = JSON.parse(element.getAttribute('data-chart-data') || '{}');
            
            new Chart(element, {
                type: chartType,
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    }
                }
            });
        });
    }

    // DataTables Initialization
    initializeDataTables() {
        if (typeof $.fn.DataTable !== 'undefined') {
            $('.data-table').DataTable({
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 hingga 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                },
                pageLength: 10,
                responsive: true
            });
        }
    }

    // Service Worker for PWA functionality
    setupServiceWorker() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered: ', registration);
                })
                .catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
        }
    }

    // Utility Functions
    formatDate(date) {
        return new Date(date).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // AJAX Helper
    async ajaxRequest(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        };

        const mergedOptions = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, mergedOptions);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('AJAX Request failed:', error);
            this.showNotification('error', 'Terjadi kesalahan saat memuat data.');
            throw error;
        }
    }

    // Stunting Calculation
    calculateStuntingRisk(balitaData) {
        // Implementasi perhitungan risiko stunting berdasarkan standar WHO
        const { umur, tinggi_badan, berat_badan, lingkar_lengan, jenis_kelamin } = balitaData;
        
        let riskScore = 0;
        
        // Parameter tinggi badan berdasarkan umur
        if (tinggi_badan < this.getStandardHeight(umur, jenis_kelamin)) {
            riskScore += 3;
        }
        
        // Parameter berat badan berdasarkan umur
        if (berat_badan < this.getStandardWeight(umur, jenis_kelamin)) {
            riskScore += 2;
        }
        
        // Parameter lingkar lengan
        if (lingkar_lengan < 12.5 && umur >= 6) {
            riskScore += 2;
        }
        
        // Parameter tambahan
        if (umur < 24 && tinggi_badan < 80) {
            riskScore += 1;
        }
        
        return riskScore >= 3 ? 'Stunting' : 'Normal';
    }

    getStandardHeight(umur, jenis_kelamin) {
        // Standar tinggi badan minimal berdasarkan WHO
        const standards = {
            'L': [60, 65, 70, 75, 80, 85, 88, 90, 92, 94, 96, 98, 100],
            'P': [58, 63, 68, 72, 77, 82, 85, 87, 89, 91, 93, 95, 97]
        };
        
        const index = Math.min(Math.floor(umur / 6), 12);
        return standards[jenis_kelamin][index];
    }

    getStandardWeight(umur, jenis_kelamin) {
        // Standar berat badan minimal berdasarkan WHO
        const standards = {
            'L': [4.5, 6.0, 7.5, 8.5, 9.5, 10.5, 11.0, 11.5, 12.0, 12.5, 13.0, 13.5, 14.0],
            'P': [4.0, 5.5, 7.0, 8.0, 9.0, 10.0, 10.5, 11.0, 11.5, 12.0, 12.5, 13.0, 13.5]
        };
        
        const index = Math.min(Math.floor(umur / 6), 12);
        return standards[jenis_kelamin][index];
    }

    // Nutrition Recommendation
    generateNutritionRecommendation(balitaData) {
        const { hasil, tinggi_badan, berat_badan, umur } = balitaData;
        let recommendations = [];
        
        if (hasil === 'Stunting') {
            recommendations.push("💊 Konsultasi dengan ahli gizi segera");
            recommendations.push("🥛 Tingkatkan asupan protein (telur, ikan, daging)");
            recommendations.push("🌾 Berikan makanan kaya zinc (kacang-kacangan, biji-bijian)");
            
            if (berat_badan < this.getStandardWeight(umur, balitaData.jenis_kelamin)) {
                recommendations.push("⚖️ Tingkatkan frekuensi makan menjadi 5-6 kali/hari");
                recommendations.push("🥑 Berikan makanan padat energi (alpukat, pisang)");
            }
            
            if (tinggi_badan < this.getStandardHeight(umur, balitaData.jenis_kelamin)) {
                recommendations.push("🥛 Perbanyak asupan kalsium (susu, yogurt)");
                recommendations.push("☀️ Pastikan cukup vitamin D (paparan sinar matahari pagi)");
            }
        } else {
            recommendations.push("✅ Pertahankan pola makan gizi seimbang");
            recommendations.push("🍎 Lanjutkan pemberian ASI/makanan pendamping");
            recommendations.push("📊 Lakukan pemantauan rutin setiap bulan");
        }
        
        return recommendations;
    }
}

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.app = new StuntingApp();
});

// Global functions for HTML onclick attributes
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.querySelector(`[onclick="togglePasswordVisibility('${inputId}')"] i`);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StuntingApp;
}