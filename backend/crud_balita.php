<?php
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../frontend/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get':
        if (isset($_GET['id'])) {
            $query = "SELECT * FROM balita WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($data);
        }
        break;
        
    case 'create':
        $nama = $_POST['nama'];
        $umur = $_POST['umur'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tinggi_badan = $_POST['tinggi_badan'];
        $berat_badan = $_POST['berat_badan'];
        $lingkar_kepala = $_POST['lingkar_kepala'] ?? 0;
        $lingkar_lengan = $_POST['lingkar_lengan'] ?? 0;
        
        // Deteksi stunting sederhana
        $hasil = 'Normal';
        if ($tinggi_badan < getStandarTinggi($umur, $jenis_kelamin)) {
            $hasil = 'Stunting';
        }
        
        $saran = $hasil == 'Stunting' ? 
            "Balita terdeteksi stunting. Perlu konsultasi dengan ahli gizi." : 
            "Balita dalam kondisi normal. Pertahankan asupan gizi seimbang.";
        
        $query = "INSERT INTO balita (nama, umur, jenis_kelamin, tinggi_badan, berat_badan, 
                  lingkar_kepala, lingkar_lengan, hasil, saran, orang_tua_id, tanggal_cek) 
                  VALUES (:nama, :umur, :jenis_kelamin, :tinggi_badan, :berat_badan, 
                  :lingkar_kepala, :lingkar_lengan, :hasil, :saran, 1, CURDATE())";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':umur', $umur);
        $stmt->bindParam(':jenis_kelamin', $jenis_kelamin);
        $stmt->bindParam(':tinggi_badan', $tinggi_badan);
        $stmt->bindParam(':berat_badan', $berat_badan);
        $stmt->bindParam(':lingkar_kepala', $lingkar_kepala);
        $stmt->bindParam(':lingkar_lengan', $lingkar_lengan);
        $stmt->bindParam(':hasil', $hasil);
        $stmt->bindParam(':saran', $saran);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data balita berhasil ditambahkan!";
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan data']);
        }
        break;
        
    case 'update':
        $id = $_POST['balita_id'];
        $nama = $_POST['nama'];
        $umur = $_POST['umur'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tinggi_badan = $_POST['tinggi_badan'];
        $berat_badan = $_POST['berat_badan'];
        
        // Re-calculate hasil
        $hasil = 'Normal';
        if ($tinggi_badan < getStandarTinggi($umur, $jenis_kelamin)) {
            $hasil = 'Stunting';
        }
        
        $saran = $hasil == 'Stunting' ? 
            "Balita terdeteksi stunting. Perlu konsultasi dengan ahli gizi." : 
            "Balita dalam kondisi normal. Pertahankan asupan gizi seimbang.";
        
        $query = "UPDATE balita SET 
                  nama = :nama, umur = :umur, jenis_kelamin = :jenis_kelamin,
                  tinggi_badan = :tinggi_badan, berat_badan = :berat_badan,
                  hasil = :hasil, saran = :saran 
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':umur', $umur);
        $stmt->bindParam(':jenis_kelamin', $jenis_kelamin);
        $stmt->bindParam(':tinggi_badan', $tinggi_badan);
        $stmt->bindParam(':berat_badan', $berat_badan);
        $stmt->bindParam(':hasil', $hasil);
        $stmt->bindParam(':saran', $saran);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data balita berhasil diupdate!";
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengupdate data']);
        }
        break;
        
    case 'delete':
        if (isset($_GET['id'])) {
            $query = "DELETE FROM balita WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_GET['id']);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Data balita berhasil dihapus!";
            } else {
                $_SESSION['error'] = "Gagal menghapus data!";
            }
        }
        header("Location: ../frontend/dashboard_admin.php");
        break;
}

function getStandarTinggi($umur, $jenis_kelamin) {
    $standar = [
        'L' => [60, 65, 70, 75, 80, 85, 88, 90, 92, 94, 96, 98, 100],
        'P' => [58, 63, 68, 72, 77, 82, 85, 87, 89, 91, 93, 95, 97]
    ];
    
    $index = min(floor($umur / 6), 12);
    return $standar[$jenis_kelamin][$index];
}
?>