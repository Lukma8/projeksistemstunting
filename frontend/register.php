<?php include '../config/database.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Sistem Stunting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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

        /* Password validation styles */
        .password-match {
            border-color: #10b981 !important;
        }

        .password-mismatch {
            border-color: #ef4444 !important;
        }

        .validation-message {
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .validation-success {
            color: #10b981;
        }

        .validation-error {
            color: #ef4444;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-8">
    <div class="max-w-md w-full mx-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div class="bg-blue-600 p-3 rounded-xl">
                    <i class="fas fa-baby text-2xl text-white"></i>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Daftar Akun Baru</h1>
            <p class="text-gray-600 mt-2">Buat akun untuk memantau perkembangan balita</p>
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form id="registerForm" action="../backend/auth.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="register">
                
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Masukkan nama lengkap">
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Masukan Username">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-10"
                               placeholder="Buat password"
                               minlength="6"
                               oninput="validatePassword()">
                        <button type="button" 
                                onclick="togglePasswordVisibility('password')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="password-eye"></i>
                        </button>
                    </div>
                    <div id="password-strength" class="validation-message"></div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirm_password" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-10"
                               placeholder="Ulangi password"
                               oninput="validatePassword()">
                        <button type="button" 
                                onclick="togglePasswordVisibility('confirm_password')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="confirm-password-eye"></i>
                        </button>
                    </div>
                    <div id="password-match" class="validation-message"></div>
                </div>

                <!-- Submit Button -->
                <button type="button" onclick="validateForm()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg font-medium transition duration-300">
                    <i class="fas fa-user-plus mr-2"></i>Daftar
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-gray-600 text-sm">
                    Sudah punya akun? 
                    <a href="login.php" class="text-blue-600 hover:text-blue-700 font-medium">
                        Login di sini
                    </a>
                </p>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 rounded-lg p-4 text-center">
            <p class="text-blue-800 text-sm">
                <i class="fas fa-info-circle mr-1"></i>
                Akun ini untuk <strong>Orang Tua</strong>. Setelah mendaftar, Anda bisa memantau perkembangan balita.
            </p>
        </div>
    </div>

    <!-- Password Mismatch Modal -->
    <div id="passwordMismatchModal" class="fixed inset-0 z-50 hidden">
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
                        <h3 class="text-lg font-semibold text-gray-800">Password Tidak Sesuai</h3>
                        <p class="text-gray-600 text-sm mt-1">Konfirmasi password tidak cocok</p>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-red-500 mr-3"></i>
                            <div>
                                <p class="text-sm font-medium text-red-800">Perbaiki password Anda</p>
                                <p class="text-xs text-red-600 mt-1">Pastikan password dan konfirmasi password sama</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end p-6 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                    <button onclick="closePasswordMismatchModal()" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-animation"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full modal-animation transform transition-all">
                <!-- Header -->
                <div class="flex items-center p-6 border-b border-gray-100">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-check text-blue-500 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-800">Konfirmasi Pendaftaran</h3>
                        <p class="text-gray-600 text-sm mt-1">Lanjutkan proses pendaftaran?</p>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                            <div>
                                <p class="text-sm font-medium text-blue-800">Data yang akan didaftarkan:</p>
                                <ul class="text-xs text-blue-600 mt-2 space-y-1">
                                    <li id="confirm-nama" class="flex items-center">
                                        <i class="fas fa-user mr-2"></i>
                                        <span></span>
                                    </li>
                                    <li id="confirm-username" class="flex items-center">
                                        <i class="fas fa-at mr-2"></i>
                                        <span></span>
                                    </li>
                                    <li class="flex items-center">
                                        <i class="fas fa-user-tag mr-2"></i>
                                        <span>Role: Orang Tua</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end space-x-3 p-6 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                    <button onclick="closeConfirmationModal()" class="px-5 py-2.5 text-gray-700 hover:text-gray-900 font-medium rounded-lg border border-gray-300 hover:border-gray-400 transition duration-200">
                        Batal
                    </button>
                    <button onclick="submitForm()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 flex items-center">
                        <i class="fas fa-check mr-2"></i>
                        Ya, Daftar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(fieldId === 'password' ? 'password-eye' : 'confirm-password-eye');
            
            if (field.type === 'password') {
                field.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // Password validation
        function validatePassword() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const passwordMatch = document.getElementById('password-match');
            const passwordStrength = document.getElementById('password-strength');

            // Password strength
            if (password.value.length > 0) {
                if (password.value.length < 6) {
                    passwordStrength.innerHTML = '<span class="validation-error"><i class="fas fa-times mr-1"></i>Password minimal 6 karakter</span>';
                    password.classList.add('password-mismatch');
                    password.classList.remove('password-match');
                } else {
                    passwordStrength.innerHTML = '<span class="validation-success"><i class="fas fa-check mr-1"></i>Password cukup kuat</span>';
                    password.classList.remove('password-mismatch');
                    password.classList.add('password-match');
                }
            } else {
                passwordStrength.innerHTML = '';
                password.classList.remove('password-mismatch', 'password-match');
            }

            // Password match
            if (confirmPassword.value.length > 0) {
                if (password.value !== confirmPassword.value) {
                    passwordMatch.innerHTML = '<span class="validation-error"><i class="fas fa-times mr-1"></i>Password tidak cocok</span>';
                    confirmPassword.classList.add('password-mismatch');
                    confirmPassword.classList.remove('password-match');
                } else {
                    passwordMatch.innerHTML = '<span class="validation-success"><i class="fas fa-check mr-1"></i>Password cocok</span>';
                    confirmPassword.classList.remove('password-mismatch');
                    confirmPassword.classList.add('password-match');
                }
            } else {
                passwordMatch.innerHTML = '';
                confirmPassword.classList.remove('password-mismatch', 'password-match');
            }
        }

        // Form validation
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const namaLengkap = document.querySelector('input[name="nama_lengkap"]').value;
            const username = document.querySelector('input[name="username"]').value;

            // Check if passwords match
            if (password !== confirmPassword) {
                showPasswordMismatchModal();
                return false;
            }

            // Check password length
            if (password.length < 6) {
                showPasswordMismatchModal();
                return false;
            }

            // Show confirmation modal
            showConfirmationModal(namaLengkap, username);
        }

        // Show password mismatch modal
        function showPasswordMismatchModal() {
            const modal = document.getElementById('passwordMismatchModal');
            modal.classList.remove('hidden');
        }

        // Close password mismatch modal
        function closePasswordMismatchModal() {
            const modal = document.getElementById('passwordMismatchModal');
            modal.classList.add('hidden');
        }

        // Show confirmation modal
        function showConfirmationModal(nama, username) {
            document.getElementById('confirm-nama').querySelector('span').textContent = nama;
            document.getElementById('confirm-username').querySelector('span').textContent = username;
            
            const modal = document.getElementById('confirmationModal');
            modal.classList.remove('hidden');
        }

        // Close confirmation modal
        function closeConfirmationModal() {
            const modal = document.getElementById('confirmationModal');
            modal.classList.add('hidden');
        }

        // Submit form
        function submitForm() {
            document.getElementById('registerForm').submit();
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            const mismatchModal = document.getElementById('passwordMismatchModal');
            const confirmModal = document.getElementById('confirmationModal');
            
            if (e.target === mismatchModal) {
                closePasswordMismatchModal();
            }
            
            if (e.target === confirmModal) {
                closeConfirmationModal();
            }
        });

        // Real-time validation on page load
        document.addEventListener('DOMContentLoaded', function() {
            validatePassword();
        });
    </script>
</body>
</html>