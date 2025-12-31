<?php
include __DIR__ . '/../config/database.php';
require_once(__DIR__ . '/../tcpdf/tcpdf.php');

// Set timezone untuk Jakarta
date_default_timezone_set('Asia/Jakarta');

// Ambil parameter filter dari URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$export_type = isset($_GET['export_type']) ? $_GET['export_type'] : 'all';

$database = new Database();
$db = $database->getConnection();

// Query data dengan filter
$query = "SELECT 
    b.*, 
    u.nama_lengkap as orang_tua
    FROM balita b
    LEFT JOIN users u ON b.orang_tua_id = u.id
    WHERE 1=1";

$params = [];
$filter_info = [];

// Tambahkan kondisi filter
if (!empty($search)) {
    $query .= " AND (b.nama LIKE ? OR u.nama_lengkap LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $filter_info[] = "Pencarian: " . htmlspecialchars($search);
}

if (!empty($filter_status)) {
    $query .= " AND b.hasil = ?";
    $params[] = $filter_status;
    $filter_info[] = "Status: " . $filter_status;
}

if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND b.tanggal_cek BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $filter_info[] = "Periode: " . date('d M Y', strtotime($start_date)) . " - " . date('d M Y', strtotime($end_date));
}

$query .= " ORDER BY b.tanggal_cek DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$data_balita = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik berdasarkan data yang difilter
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN hasil = 'Stunting' THEN 1 ELSE 0 END) as stunting,
    SUM(CASE WHEN hasil = 'Normal' THEN 1 ELSE 0 END) as normal
    FROM balita b
    LEFT JOIN users u ON b.orang_tua_id = u.id
    WHERE 1=1";

$stats_params = [];

if (!empty($search)) {
    $stats_query .= " AND (b.nama LIKE ? OR u.nama_lengkap LIKE ?)";
    $stats_params[] = "%$search%";
    $stats_params[] = "%$search%";
}

if (!empty($filter_status)) {
    $stats_query .= " AND b.hasil = ?";
    $stats_params[] = $filter_status;
}

if (!empty($start_date) && !empty($end_date)) {
    $stats_query .= " AND b.tanggal_cek BETWEEN ? AND ?";
    $stats_params[] = $start_date;
    $stats_params[] = $end_date;
}

$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute($stats_params);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

$persentase_stunting = $stats['total'] > 0 ? round(($stats['stunting'] / $stats['total']) * 100, 1) : 0;
$persentase_normal = $stats['total'] > 0 ? round(($stats['normal'] / $stats['total']) * 100, 1) : 0;

// Buat PDF dengan orientasi Landscape
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Posyandu Sehat');
$pdf->SetAuthor('Posyandu Sehat');
$pdf->SetTitle('Laporan Data Balita');
$pdf->SetSubject('Laporan Stunting');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 15);

$pdf->AddPage();

// Waktu cetak yang lebih akurat dengan mikrotime
$microtime = microtime(true);
$current_time = date('d/m/Y H:i:s', (int)$microtime);
$milliseconds = sprintf('%03d', ($microtime - floor($microtime)) * 1000);
$current_time_with_ms = date('d/m/Y H:i:s', (int)$microtime) . '.' . $milliseconds;

// Header Laporan - di tengah
$header = '
<div style="text-align: center; margin-bottom: 8px;">
    <h1 style="color: #1e40af; font-size: 16px; font-weight: bold; margin-bottom: 3px;">LAPORAN DATA BALITA POSYANDU SEHAT</h1>
    <p style="color: #6b7280; font-size: 9px; margin-bottom: 2px;">Sistem Deteksi Stunting Terintegrasi</p>
    <p style="color: #6b7280; font-size: 8px;">Dicetak: ' . $current_time . '</p>';

// Tambahkan informasi filter jika ada
if (!empty($filter_info)) {
    $header .= '<div style="background-color: #f0f9ff; border: 0.5px solid #3b82f6; border-radius: 3px; padding: 3px; margin: 3px 0; font-size: 7px; color: #1e40af;">';
    $header .= '<strong>Filter Aktif:</strong> ' . implode(' | ', $filter_info);
    $header .= '</div>';
}

$header .= '</div>';

$pdf->writeHTML($header, true, false, true, false, '');

// Box statistik - lebih ringkas
$statistics = '
<div style="margin-bottom: 8px; text-align: center;">
    <table border="0.5" cellpadding="4" cellspacing="0" style="margin: 0 auto; border-collapse: collapse; width: 80%; background-color: #f8fafc; font-size: 8px;">
        <tr>
            <td width="25%" style="border: 0.5px solid #3b82f6; background-color: #3b82f6; color: white; font-weight: bold; padding: 4px;">TOTAL</td>
            <td width="25%" style="border: 0.5px solid #dc2626; background-color: #dc2626; color: white; font-weight: bold; padding: 4px;">STUNTING</td>
            <td width="25%" style="border: 0.5px solid #16a34a; background-color: #16a34a; color: white; font-weight: bold; padding: 4px;">NORMAL</td>
            <td width="25%" style="border: 0.5px solid #3b82f6; background-color: #3b82f6; color: white; font-weight: bold; padding: 4px;">PERSENTASE</td>
        </tr>
        <tr>
            <td style="border: 0.5px solid #d1d5db; text-align: center; font-weight: bold; padding: 4px; background-color: #ffffff;">' . $stats['total'] . '</td>
            <td style="border: 0.5px solid #d1d5db; text-align: center; font-weight: bold; padding: 4px; background-color: #fef2f2; color: #dc2626;">' . $stats['stunting'] . '</td>
            <td style="border: 0.5px solid #d1d5db; text-align: center; font-weight: bold; padding: 4px; background-color: #f0fdf4; color: #16a34a;">' . $stats['normal'] . '</td>
            <td style="border: 0.5px solid #d1d5db; text-align: center; font-weight: bold; padding: 4px; background-color: #ffffff;">
                <span style="color: #dc2626;">' . $persentase_stunting . '%</span> / 
                <span style="color: #16a34a;">' . $persentase_normal . '%</span>
            </td>
        </tr>
    </table>
</div>';

$pdf->writeHTML($statistics, true, false, true, false, '');

// Buat tabel lengkap dalam satu HTML
$html = '
<table border="0.5" cellpadding="3" cellspacing="0" style="border-collapse: collapse; width: 100%; font-size: 7px; margin-bottom: 5px;">
    <!-- Header Table -->
    <tr style="background-color: #3b82f6; color: white;">
        <th width="4%" style="border: 0.5px solid #1e40af; padding: 3px; text-align: center;">No</th>
        <th width="18%" style="border: 0.5px solid #1e40af; padding: 3px; text-align: center;">Nama Balita</th>
        <th width="18%" style="border: 0.5px solid #1e40af; padding: 3px; text-align: center;">Orang Tua</th>
        <th width="6%" style="border: 0.5px solid #1e40af; padding: 3px; text-align: center;">Umur</th>
        <th width="4%" style="border: 0.5px solid #1e40af; padding: 3px; text-align: center;">JK</th>
        <th width="8%" style="border: 0.5px solid #1e40af; padding: 3px; text-align: center;">Tinggi</th>
        <th width="8%" style="border: 0.5px solid #1e40af; padding: 3px; text-align: center;">Berat</th>
        <th width="8%" style="border: 0.5px solid #1e40af; padding: 3px; text-align: center;">L. Kepala</th>
        <th width="8%" style="border: 0.5px solid #1e40af; padding: 3px; text-align: center;">L. Lengan</th>
        <th width="8%" style="border: 0.5px solid #1e40af; padding: 3px; text-align: center;">Status</th>
        <th width="10%" style="border: 0.5px solid #1e40af; padding: 3px; text-align: center;">Tanggal</th>
    </tr>';

// Data rows
if (empty($data_balita)) {
    // Jika tidak ada data
    $html .= '
    <tr>
        <td colspan="11" style="border: 0.5px solid #d1d5db; padding: 10px; text-align: center; background-color: #f8fafc;">
            <span style="color: #6b7280; font-style: italic;">Tidak ada data balita yang sesuai dengan filter</span>
        </td>
    </tr>';
} else {
    $counter = 0;
    foreach ($data_balita as $balita) {
        $counter++;
        
        // Warna baris bergantian
        $bg_color = ($counter % 2 == 0) ? '#f8fafc' : '#ffffff';
        
        // Warna status
        $status_color = ($balita['hasil'] == 'Stunting') ? '#dc2626' : '#16a34a';
        $status_bg = ($balita['hasil'] == 'Stunting') ? '#fef2f2' : '#f0fdf4';
        
        $html .= '
        <tr style="background-color: ' . $bg_color . ';">
            <td width="4%" style="border: 0.5px solid #d1d5db; padding: 3px; text-align: center;">' . $counter . '</td>
            <td width="18%" style="border: 0.5px solid #d1d5db; padding: 3px;">' . htmlspecialchars($balita['nama']) . '</td>
            <td width="18%" style="border: 0.5px solid #d1d5db; padding: 3px;">' . htmlspecialchars($balita['orang_tua']) . '</td>
            <td width="6%" style="border: 0.5px solid #d1d5db; padding: 3px; text-align: center;">' . $balita['umur'] . ' bln</td>
            <td width="4%" style="border: 0.5px solid #d1d5db; padding: 3px; text-align: center;">' . $balita['jenis_kelamin'] . '</td>
            <td width="8%" style="border: 0.5px solid #d1d5db; padding: 3px; text-align: center;">' . $balita['tinggi_badan'] . ' cm</td>
            <td width="8%" style="border: 0.5px solid #d1d5db; padding: 3px; text-align: center;">' . $balita['berat_badan'] . ' kg</td>
            <td width="8%" style="border: 0.5px solid #d1d5db; padding: 3px; text-align: center;">' . $balita['lingkar_kepala'] . ' cm</td>
            <td width="8%" style="border: 0.5px solid #d1d5db; padding: 3px; text-align: center;">' . $balita['lingkar_lengan'] . ' cm</td>
            <td width="8%" style="border: 0.5px solid #d1d5db; padding: 3px; text-align: center; background-color: ' . $status_bg . '; color: ' . $status_color . '; font-weight: bold;">' . $balita['hasil'] . '</td>
            <td width="10%" style="border: 0.5px solid #d1d5db; padding: 3px; text-align: center;">' . date('d/m/Y', strtotime($balita['tanggal_cek'])) . '</td>
        </tr>';
    }
}

$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Footer dengan waktu yang lebih akurat
$pdf->SetY($pdf->GetY() + 5);
$pdf->SetFont('helvetica', 'I', 7);
$pdf->SetTextColor(100, 100, 100);

// Waktu akhir pembuatan PDF
$end_microtime = microtime(true);
$processing_time = round(($end_microtime - $microtime) * 1000, 2); // dalam milidetik
$footer_time = date('d F Y H:i:s', (int)$end_microtime);
$footer_text = 'Laporan ini dibuat otomatis oleh Sistem Deteksi Stunting Posyandu Sehat. ';

// Tambahkan info filter di footer
if (!empty($filter_info)) {
    $footer_text .= 'Data difilter berdasarkan: ' . implode(', ', $filter_info) . '. ';
}

$footer_text .= 'Total Data: ' . count($data_balita) . ' | Dicetak: ' . $footer_time . ' (Waktu pembuatan: ' . $processing_time . ' ms)';
$pdf->Cell(0, 4, $footer_text, 0, 1, 'C');

// Generate nama file berdasarkan filter dengan timestamp yang akurat
$filename = 'laporan_balita_';
if (!empty($filter_status)) {
    $filename .= strtolower($filter_status) . '_';
}
if (!empty($start_date) && !empty($end_date)) {
    $filename .= date('Y-m-d', strtotime($start_date)) . '_to_' . date('Y-m-d', strtotime($end_date)) . '_';
}
// Tambahkan timestamp dengan mikrotime untuk lebih unik
$filename .= date('Y-m-d_His_', (int)$microtime) . $milliseconds . '.pdf';

$pdf->Output($filename, 'D');
?>