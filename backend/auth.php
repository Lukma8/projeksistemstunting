<?php
// backend/auth.php
session_start();
require_once __DIR__ . '/../config/database.php';

// Fungsi untuk mengembalikan respons JSON
function sendJsonResponse($status, $message, $redirect = null) {
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($redirect) {
        $response['redirect'] = $redirect;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Fungsi untuk menangani redirect biasa (non-AJAX)
function handleRedirect($url, $error = null) {
    if ($error) {
        $_SESSION['error'] = $error;
    }
    header("Location: $url");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Debug: log apa yang diterima
error_log("Auth attempt - POST data: " . print_r($_POST, true));

// Cek apakah request dari AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Periksa action yang dikirim
$action = $_POST['action'] ?? '';

if ($action === 'register') {
    // ========== PROSES REGISTER ==========
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi
    if (empty($nama_lengkap) || empty($username) || empty($password)) {
        if ($isAjax) {
            sendJsonResponse('error', 'Semua field wajib diisi!');
        } else {
            handleRedirect('../frontend/register.php', 'Semua field wajib diisi!');
        }
    }
    
    if ($password !== $confirm_password) {
        if ($isAjax) {
            sendJsonResponse('error', 'Password dan konfirmasi password tidak cocok!');
        } else {
            handleRedirect('../frontend/register.php', 'Password dan konfirmasi password tidak cocok!');
        }
    }
    
    if (strlen($password) < 6) {
        if ($isAjax) {
            sendJsonResponse('error', 'Password minimal 6 karakter!');
        } else {
            handleRedirect('../frontend/register.php', 'Password minimal 6 karakter!');
        }
    }
    
    try {
        // Cek username sudah ada
        $checkQuery = "SELECT id FROM users WHERE username = :username";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            if ($isAjax) {
                sendJsonResponse('error', 'Username sudah digunakan!');
            } else {
                handleRedirect('../frontend/register.php', 'Username sudah digunakan!');
            }
        }
        
        // Insert user baru
        $insertQuery = "INSERT INTO users (nama_lengkap, username, password, role) 
                        VALUES (:nama_lengkap, :username, :password, 'orang_tua')";
        $insertStmt = $db->prepare($insertQuery);
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insertStmt->bindParam(':nama_lengkap', $nama_lengkap);
        $insertStmt->bindParam(':username', $username);
        $insertStmt->bindParam(':password', $hashed_password);
        
        if ($insertStmt->execute()) {
            // Ambil ID user yang baru dibuat
            $user_id = $db->lastInsertId();
            
            // Set session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['role'] = 'orang_tua';
            $_SESSION['logged_in'] = true;
            
            if ($isAjax) {
                sendJsonResponse('success', 'Registrasi berhasil!', '../frontend/dashboard_ortu.php');
            } else {
                handleRedirect('../frontend/dashboard_ortu.php');
            }
        } else {
            if ($isAjax) {
                sendJsonResponse('error', 'Gagal mendaftar. Silakan coba lagi.');
            } else {
                handleRedirect('../frontend/register.php', 'Gagal mendaftar. Silakan coba lagi.');
            }
        }
    } catch (Exception $e) {
        error_log("Register error: " . $e->getMessage());
        if ($isAjax) {
            sendJsonResponse('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
        } else {
            handleRedirect('../frontend/register.php', 'Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }
    
} else {
    // ========== PROSES LOGIN ==========
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validasi input
        if (empty($username) || empty($password)) {
            if ($isAjax) {
                sendJsonResponse('error', 'Username dan password harus diisi!');
            } else {
                handleRedirect('../frontend/login.php', 'Username dan password harus diisi!');
            }
        }
        
        try {
            // Query untuk cek user
            $query = "SELECT * FROM users WHERE username = :username";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verifikasi password
                $login_success = false;
                
                // Coba plain text
                if ($password == $user['password']) {
                    $login_success = true;
                } 
                // Coba hash password
                else if (password_verify($password, $user['password'])) {
                    $login_success = true;
                }
                
                if ($login_success) {
                    // Password cocok
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? $user['username'];
                    $_SESSION['role'] = $user['role'] ?? 'orang_tua';
                    $_SESSION['logged_in'] = true;
                    
                    // Tentukan redirect berdasarkan role
                    $redirect_url = ($user['role'] == 'admin') ? 
                        '../frontend/dashboard_admin.php' : 
                        '../frontend/dashboard_ortu.php';
                    
                    if ($isAjax) {
                        sendJsonResponse('success', 'Login berhasil!', $redirect_url);
                    } else {
                        handleRedirect($redirect_url);
                    }
                } else {
                    if ($isAjax) {
                        sendJsonResponse('error', 'Password salah!');
                    } else {
                        handleRedirect('../frontend/login.php', 'Password salah!');
                    }
                }
            } else {
                if ($isAjax) {
                    sendJsonResponse('error', 'Username tidak ditemukan!');
                } else {
                    handleRedirect('../frontend/login.php', 'Username tidak ditemukan!');
                }
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            if ($isAjax) {
                sendJsonResponse('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
            } else {
                handleRedirect('../frontend/login.php', 'Terjadi kesalahan sistem. Silakan coba lagi.');
            }
        }
    } else {
        // Bukan POST request
        if ($isAjax) {
            sendJsonResponse('error', 'Metode request tidak valid');
        } else {
            handleRedirect('../frontend/login.php');
        }
    }
}
?>