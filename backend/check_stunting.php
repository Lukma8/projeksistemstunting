<?php
include '../config/database.php';

if ($_POST['action'] == 'check_stunting') {
    $database = new Database();
    $db = $database->getConnection();
    
    // Ambil data dari form
    $nama = $_POST['nama'];
    $umur = $_POST['umur'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tinggi_badan = $_POST['tinggi_badan'];
    $berat_badan = $_POST['berat_badan'];
    $lingkar_kepala = $_POST['lingkar_kepala'];
    $lingkar_lengan = $_POST['lingkar_lengan'];
    $orang_tua_id = $_SESSION['user_id'];
    
    // Standar WHO sederhana (contoh)
    $hasil = 'Normal';
    $saran = '';
    
    // Logika deteksi stunting sederhana
    if ($tinggi_badan < getStandarTinggi($umur, $jenis_kelamin)) {
        $hasil = 'Stunting';
    }
    
    // Tambahkan faktor lingkar lengan
    if ($lingkar_lengan < 11.5 && $umur >= 6) {
        $hasil = 'Stunting';
    }
    
    // Generate saran berdasarkan hasil
    if ($hasil == 'Stunting') {
        $saran = generateSaran($tinggi_badan, $berat_badan, $lingkar_lengan, $umur);
    } else {
        $saran = "Balita dalam kondisi normal. Pertahankan asupan gizi seimbang dan lakukan pemeriksaan rutin.";
    }
    
    // Simpan ke database
    $query = "INSERT INTO balita (nama, umur, jenis_kelamin, tinggi_badan, berat_badan, 
              lingkar_kepala, lingkar_lengan, hasil, saran, orang_tua_id, tanggal_cek) 
              VALUES (:nama, :umur, :jenis_kelamin, :tinggi_badan, :berat_badan, 
              :lingkar_kepala, :lingkar_lengan, :hasil, :saran, :orang_tua_id, CURDATE())";
    
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
    $stmt->bindParam(':orang_tua_id', $orang_tua_id);
    
    if ($stmt->execute()) {
        $balita_id = $db->lastInsertId();
        echo json_encode([
            'success' => true,
            'hasil' => $hasil,
            'saran' => $saran,
            'nama' => $nama,
            'umur' => $umur,
            'tinggi_badan' => $tinggi_badan,
            'berat_badan' => $berat_badan,
            'balita_id' => $balita_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menyimpan data'
        ]);
    }
    exit();
}

function getStandarTinggi($umur, $jenis_kelamin) {
    // Standar tinggi badan minimal WHO berdasarkan umur (contoh sederhana)
    $standar = [
        'L' => [60, 65, 70, 75, 80, 85, 88, 90, 92, 94, 96, 98, 100],
        'P' => [58, 63, 68, 72, 77, 82, 85, 87, 89, 91, 93, 95, 97]
    ];
    
    $index = min(floor($umur / 6), 12);
    return $standar[$jenis_kelamin][$index];
}

function generateSaran($tinggi, $berat, $lingkar_lengan, $umur) {
    $saran = "Balita terdeteksi berisiko stunting. Rekomendasi:\n\n";
    
    // Saran berdasarkan parameter
    if ($tinggi < 70 && $umur >= 12) {
        $saran .= "• Tingkatkan asupan protein untuk pertumbuhan tinggi badan\n";
        $saran .= "• Berikan makanan kaya kalsium dan vitamin D\n";
    }
    
    if ($berat < 8 && $umur >= 12) {
        $saran .= "• Tingkatkan frekuensi makan menjadi 5-6 kali sehari\n";
        $saran .= "• Berikan makanan padat energi seperti alpukat, pisang, telur\n";
    }
    
    if ($lingkar_lengan < 12.5) {
        $saran .= "• Perbaiki asupan gizi makro (karbohidrat, protein, lemak)\n";
        $saran .= "• Berikan suplementasi zinc dan multivitamin jika diperlukan\n";
    }
    
    $saran .= "\nKonsultasikan dengan tenaga kesehatan untuk penanganan lebih lanjut.";
    return $saran;
}
?>