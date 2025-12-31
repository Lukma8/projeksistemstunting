<?php include '../config/database.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Orang Tua - Sistem Stunting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-8">
    <div class="max-w-md w-full mx-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div class="bg-green-600 p-3 rounded-xl">
                    <i class="fas fa-users text-white text-2xl"></i>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Login Orang Tua</h1>
            <p class="text-gray-600 mt-2">Masuk untuk memantau perkembangan balita</p>
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <!-- Message Container -->
            <div id="message-container"></div>

            <form id="loginForm" class="space-y-4">
                <input type="hidden" name="action" value="login">
                
                <!-- Username -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="Masukkan username">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 pr-10"
                               placeholder="Masukkan password">
                        <button type="button" 
                                onclick="togglePasswordVisibility('password')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                            <i class="fas fa-eye" id="password-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="loginBtn" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg font-medium transition duration-300">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                </button>
                <div id="loading" class="hidden text-center mt-2">
                    <i class="fas fa-spinner fa-spin text-green-600"></i> Memproses...
                </div>
            </form>

            <!-- Links -->
            <div class="mt-6 space-y-3 text-center">
                <p class="text-gray-600 text-sm">
                    Belum punya akun? 
                    <a href="register.php" class="text-green-600 hover:text-green-700 font-medium">
                        Daftar di sini
                    </a>
                </p>
                <p class="text-gray-600 text-sm">
                    <a href="login.php" class="text-gray-600 hover:text-blue-600">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke pilihan login
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const eyeIcon = document.getElementById('password-eye');
            
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

        // AJAX Login Handler
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                const loginBtn = $('#loginBtn');
                const loading = $('#loading');
                
                loginBtn.prop('disabled', true);
                loading.removeClass('hidden');
                $('#message-container').html('');
                
                $.ajax({
                    url: '../backend/auth.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        console.log('Login Response:', response);
                        
                        if (response.status === 'success') {
                            $('#message-container').html(`
                                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    ${response.message}
                                </div>
                            `);
                            
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                            
                        } else {
                            $('#message-container').html(`
                                <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    ${response.message}
                                </div>
                            `);
                            
                            loginBtn.prop('disabled', false);
                            loading.addClass('hidden');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        
                        $('#message-container').html(`
                            <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Koneksi gagal. Periksa koneksi internet Anda.
                            </div>
                        `);
                        
                        loginBtn.prop('disabled', false);
                        loading.addClass('hidden');
                    }
                });
            });
        });
    </script>
</body>
</html>