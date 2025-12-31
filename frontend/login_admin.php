<?php include '../config/database.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sistem Stunting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-8">
    <div class="max-w-md w-full mx-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div class="bg-blue-600 p-3 rounded-xl">
                    <i class="fas fa-user-shield text-white text-2xl"></i>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Login Administrator</h1>
            <p class="text-gray-600 mt-2">Akses terbatas untuk pengelola sistem</p>
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <!-- Error/Success Messages -->
            <div id="message-container"></div>

            <!-- Login Form -->
            <form id="loginForm" class="space-y-4">
                <input type="hidden" name="action" value="login">
                
                <!-- Username -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username Admin</label>
                    <input type="text" name="username" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Masukkan username admin">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Admin</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-10"
                               placeholder="Masukkan password admin">
                        <button type="button" 
                                onclick="togglePasswordVisibility('password')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                            <i class="fas fa-eye" id="password-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="loginBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg font-medium transition duration-300">
                    <i class="fas fa-lock mr-2"></i>Masuk sebagai Admin
                </button>
                <div id="loading" class="hidden text-center mt-2">
                    <i class="fas fa-spinner fa-spin text-blue-600"></i> Memproses...
                </div>
            </form>

            <!-- Links -->
            <div class="mt-6 space-y-3 text-center">
                <p class="text-gray-600 text-sm">
                    <a href="login.php" class="text-gray-600 hover:text-blue-600">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke pilihan login
                    </a>
                </p>
                <p class="text-gray-600 text-sm">
                    <a href="../index.php" class="text-gray-600 hover:text-blue-600">
                        <i class="fas fa-home mr-1"></i>Kembali ke Beranda
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

        // Handle form submission with AJAX
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                const loginBtn = $('#loginBtn');
                const loading = $('#loading');
                
                // Show loading, disable button
                loginBtn.prop('disabled', true);
                loading.removeClass('hidden');
                
                // Clear previous messages
                $('#message-container').html('');
                
                // Send AJAX request
                $.ajax({
                    url: '../backend/auth_admin.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        console.log('Response:', response);
                        
                        if (response.status === 'success') {
                            // Show success message
                            $('#message-container').html(`
                                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    ${response.message} - Mengalihkan...
                                </div>
                            `);
                            
                            // Redirect after 1 second
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                            
                        } else {
                            // Show error message
                            $('#message-container').html(`
                                <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    ${response.message}
                                </div>
                            `);
                            
                            // Re-enable button
                            loginBtn.prop('disabled', false);
                            loading.addClass('hidden');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        
                        $('#message-container').html(`
                            <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Terjadi kesalahan sistem. Silakan coba lagi.
                            </div>
                        `);
                        
                        // Re-enable button
                        loginBtn.prop('disabled', false);
                        loading.addClass('hidden');
                    }
                });
            });
        });
    </script>
</body>
</html>