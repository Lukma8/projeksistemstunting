<?php
// backend/auth_admin.php
session_start();
require_once __DIR__ . '/../config/database.php';

// Selalu set header JSON
header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

// Periksa method request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Metode request tidak valid'
    ]);
    exit();
}

// Ambil data
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$action = $_POST['action'] ?? '';

// Validasi input
if (empty($username) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Username dan password harus diisi!'
    ]);
    exit();
}

try {
    // Query khusus admin
    $query = "SELECT * FROM users WHERE username = :username AND role = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Username admin tidak ditemukan!'
        ]);
        exit();
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verifikasi password
    $login_success = false;
    
    // Cek plain text (untuk development)
    if ($password == $user['password']) {
        $login_success = true;
    }
    // Cek password hash
    else if (password_verify($password, $user['password'])) {
        $login_success = true;
    }
    
    if ($login_success) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Login berhasil!',
            'redirect' => '../frontend/dashboard_admin.php'
        ]);
        exit();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Password salah!'
        ]);
        exit();
    }
    
} catch (Exception $e) {
    error_log("Admin login error: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
    ]);
    exit();
}
?>