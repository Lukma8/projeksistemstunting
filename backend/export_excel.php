<?php
include __DIR__ . '/../config/database.php';

// Ambil semua data balita
$database = new Database();
$db = $database->getConnection();

$query = "SELECT 
    b.*, 
    u.nama_lengkap as orang_tua
    FROM balita b
    LEFT JOIN users u ON b.orang_tua_id = u.id
    ORDER BY b.tanggal_cek DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$data_balita = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set header untuk download Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="laporan_balita_' . date('Y-m-d') . '.xls"');

// Buat konten Excel
echo "<table border='1'>";
echo "<tr>";
echo "<th>No</th>";
echo "<th>Nama Balita</th>";
echo "<th>Orang Tua</th>";
echo "<th>Umur (bulan)</th>";
echo "<th>Jenis Kelamin</th>";
echo "<th>Tinggi Badan (cm)</th>";
echo "<th>Berat Badan (kg)</th>";
echo "<th>Lingkar Kepala (cm)</th>";
echo "<th>Lingkar Lengan (cm)</th>";
echo "<th>Status</th>";
echo "<th>Tanggal Pemeriksaan</th>";
echo "</tr>";

$no = 1;
foreach ($data_balita as $balita) {
    echo "<tr>";
    echo "<td>" . $no++ . "</td>";
    echo "<td>" . htmlspecialchars($balita['nama']) . "</td>";
    echo "<td>" . htmlspecialchars($balita['orang_tua']) . "</td>";
    echo "<td>" . $balita['umur'] . "</td>";
    echo "<td>" . ($balita['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan') . "</td>";
    echo "<td>" . $balita['tinggi_badan'] . "</td>";
    echo "<td>" . $balita['berat_badan'] . "</td>";
    echo "<td>" . $balita['lingkar_kepala'] . "</td>";
    echo "<td>" . $balita['lingkar_lengan'] . "</td>";
    echo "<td>" . $balita['hasil'] . "</td>";
    echo "<td>" . $balita['tanggal_cek'] . "</td>";
    echo "</tr>";
}

echo "</table>";
exit();
?>