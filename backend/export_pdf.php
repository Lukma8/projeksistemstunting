<?php
include '../config/database.php';

if (isset($_GET['balita_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM balita WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['balita_id']);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) {
        die("Data tidak ditemukan");
    }

    // Load library TCPDF
    require_once('../tcpdf/tcpdf.php');
    
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Posyandu Sehat');
    $pdf->SetAuthor('Posyandu Sehat');
    $pdf->SetTitle('Hasil Pemeriksaan - ' . $data['nama']);
    $pdf->SetSubject('Hasil Pemeriksaan Stunting');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins lebih kecil untuk menghemat space
    $pdf->SetMargins(8, 8, 8);
    $pdf->SetAutoPageBreak(TRUE, 8);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font size lebih kecil
    $pdf->SetFont('helvetica', '', 8);
    
    // Header yang sangat compact
    $header = '
    <table width="100%" style="margin-bottom: 10px;">
        <tr>
            <td width="70%">
                <h1 style="color: #1e40af; margin: 0; font-size: 12px; font-weight: bold;">
                    HASIL PEMERIKSAAN BALITA
                </h1>
                <p style="color: #6b7280; margin: 2px 0 0 0; font-size: 7px;">Posyandu Sehat</p>
            </td>
            <td width="30%" style="text-align: right;">
                <div style="font-size: 7px; color: #6b7280; line-height: 1.2;">
                    ' . date('d/m/Y') . '<br>
                    ID: #' . $data['id'] . '
                </div>
            </td>
        </tr>
    </table>
    <div style="border-bottom: 1px solid #e5e7eb; margin: 5px 0 10px 0;"></div>';
    
    // Informasi Balita - super compact
    $info_balita = '
    <div style="margin-bottom: 12px;">
        <div style="background: #1e40af; color: white; padding: 4px 8px; border-radius: 3px; margin-bottom: 6px;">
            <strong style="font-size: 9px;">INFORMASI BALITA</strong>
        </div>
        <table width="100%" cellpadding="3" style="font-size: 8px;">
            <tr>
                <td width="25%" style="color: #6b7280;"><strong>Nama</strong></td>
                <td width="75%" style="color: #1f2937;"><strong>' . htmlspecialchars($data['nama']) . '</strong></td>
            </tr>
            <tr>
                <td style="color: #6b7280;">Umur</td>
                <td style="color: #1f2937;"><strong>' . $data['umur'] . ' Bulan</strong></td>
            </tr>
            <tr>
                <td style="color: #6b7280;">Jenis Kelamin</td>
                <td style="color: #1f2937;"><strong>' . ($data['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan') . '</strong></td>
            </tr>
            <tr>
                <td style="color: #6b7280;">Tanggal Periksa</td>
                <td style="color: #1f2937;"><strong>' . date('d/m/Y', strtotime($data['tanggal_cek'])) . '</strong></td>
            </tr>
        </table>
    </div>';
    
    // Status Gizi - compact
    $status_color = $data['hasil'] == 'Stunting' ? '#dc2626' : '#059669';
    $status_bg = $data['hasil'] == 'Stunting' ? '#fef2f2' : '#f0fdf4';
    
    $status_gizi = '
    <div style="margin-bottom: 12px;">
        <div style="background: #1e40af; color: white; padding: 4px 8px; border-radius: 3px; margin-bottom: 6px;">
            <strong style="font-size: 9px;">STATUS GIZI</strong>
        </div>
        <div style="background: ' . $status_bg . '; border: 1px solid ' . $status_color . '; border-radius: 5px; padding: 8px; text-align: center;">
            <div style="font-size: 12px; font-weight: bold; color: ' . $status_color . '; margin-bottom: 3px;">
                ' . ($data['hasil'] == 'Stunting' ? 'STUNTING' : 'NORMAL') . '
            </div>
            <div style="font-size: 7px; color: ' . $status_color . '; font-weight: bold;">
                ' . ($data['hasil'] == 'Stunting' ? 'Perlu konsultasi tenaga kesehatan' : 'Kondisi balita normal') . '
            </div>
        </div>
    </div>';
    
    // Hasil Pengukuran - grid compact
    $pengukuran = '
    <div style="margin-bottom: 12px;">
        <div style="background: #1e40af; color: white; padding: 4px 8px; border-radius: 3px; margin-bottom: 6px;">
            <strong style="font-size: 9px;">HASIL PENGUKURAN</strong>
        </div>
        <table width="100%" cellpadding="5" style="font-size: 8px; text-align: center;">
            <tr>
                <td style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px;">
                    <div style="font-size: 12px; font-weight: bold; color: #1e40af; margin-bottom: 2px;">' . $data['tinggi_badan'] . '</div>
                    <div style="font-size: 7px; color: #6b7280; font-weight: bold;">TINGGI</div>
                    <div style="font-size: 6px; color: #9ca3af;">cm</div>
                </td>
                <td style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px;">
                    <div style="font-size: 12px; font-weight: bold; color: #059669; margin-bottom: 2px;">' . $data['berat_badan'] . '</div>
                    <div style="font-size: 7px; color: #6b7280; font-weight: bold;">BERAT</div>
                    <div style="font-size: 6px; color: #9ca3af;">kg</div>
                </td>
                <td style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px;">
                    <div style="font-size: 12px; font-weight: bold; color: #7c3aed; margin-bottom: 2px;">' . $data['lingkar_kepala'] . '</div>
                    <div style="font-size: 7px; color: #6b7280; font-weight: bold;">KEPALA</div>
                    <div style="font-size: 6px; color: #9ca3af;">cm</div>
                </td>
                <td style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px;">
                    <div style="font-size: 12px; font-weight: bold; color: #ea580c; margin-bottom: 2px;">' . $data['lingkar_lengan'] . '</div>
                    <div style="font-size: 7px; color: #6b7280; font-weight: bold;">LENGAN</div>
                    <div style="font-size: 6px; color: #9ca3af;">cm</div>
                </td>
            </tr>
        </table>
    </div>';
    
    // Rekomendasi - sangat compact
    $default_saran = '
    <div style="font-size: 7px; color: #92400e; line-height: 1.4;">
        <div style="margin-bottom: 4px;"><strong>•</strong> Makanan bergizi seimbang</div>
        <div style="margin-bottom: 4px;"><strong>•</strong> Pemeriksaan rutin bulanan</div>
        <div style="margin-bottom: 4px;"><strong>•</strong> Cukup protein & vitamin</div>
        <div><strong>•</strong> Jaga kebersihan lingkungan</div>
    </div>';
    
    $rekomendasi = '
    <div style="margin-bottom: 12px;">
        <div style="background: #1e40af; color: white; padding: 4px 8px; border-radius: 3px; margin-bottom: 6px;">
            <strong style="font-size: 9px;">REKOMENDASI</strong>
        </div>
        <div style="background: #fffbeb; border: 1px solid #f59e0b; border-radius: 4px; padding: 8px;">
            ' . ($data['saran'] ? nl2br(htmlspecialchars($data['saran'])) : $default_saran) . '
        </div>
    </div>';
    
    // Footer dengan TTD manual untuk kedua pihak
    $footer = '
    <div style="border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 10px;">
        <table width="100%" style="font-size: 7px;">
            <tr>
                <td width="50%" style="text-align: center; vertical-align: top;">
                    <!-- TTD Manual untuk Petugas Posyandu -->
                    <div style="margin-bottom: 25px;">
                        <strong style="color: #374151; font-size: 8px;">PETUGAS POSYANDU</strong><br>
                        <div style="margin-top: 25px; border-top: 1px solid #374151; width: 120px; margin-left: auto; margin-right: auto;">
                            &nbsp;
                        </div>
                        <div style="margin-top: 5px; color: #6b7280; font-size: 6px;">
                            Nama Terang & Cap
                        </div>
                    </div>
                </td>
                <td width="50%" style="text-align: center; vertical-align: top;">
                    <!-- TTD Manual untuk Orang Tua/Wali -->
                    <div style="margin-bottom: 25px;">
                        <strong style="color: #374151; font-size: 8px;">ORANG TUA/WALI</strong><br>
                        <div style="margin-top: 25px; border-top: 1px solid #374151; width: 120px; margin-left: auto; margin-right: auto;">
                            &nbsp;
                        </div>
                        <div style="margin-top: 5px; color: #6b7280; font-size: 6px;">
                            Nama Terang & Tanda Tangan
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        
        <div style="text-align: center; font-size: 6px; color: #6b7280; margin-top: 5px; padding-top: 5px; border-top: 1px solid #f3f4f6;">
            <strong>Dicetak:</strong> ' . date('d/m/Y H:i') . ' | <strong>Posyandu Sehat</strong>
        </div>
    </div>';
    
    // Combine all content
    $content = $header . $info_balita . $status_gizi . $pengukuran . $rekomendasi . $footer;
    
    // Write HTML content
    $pdf->writeHTML($content, true, false, true, false, '');
    
    // Check if it's preview mode
    if (isset($_GET['preview']) && $_GET['preview'] == 'true') {
        // For preview, output inline
        $pdf->Output('preview.pdf', 'I');
    } else {
        // For download, force download
        $pdf->Output('hasil_periksa_' . $data['nama'] . '_' . date('Y-m-d') . '.pdf', 'D');
    }
    
} else {
    // Jika tidak ada parameter, tampilkan error
    echo "<script>alert('Data tidak ditemukan!'); window.history.back();</script>";
    exit();
}