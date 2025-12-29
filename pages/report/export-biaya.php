<?php
// FILE: pages/report/export-biaya.php

// 1. Matikan error reporting agar file download tidak corrupt
error_reporting(0);

// 2. Include Koneksi & Library
include '../../dist/koneksi.php';

// Ambil Parameter
$type  = isset($_GET['type']) ? $_GET['type'] : 'print';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Query Data
$where_tahun = ($tahun == 'Semua') ? "" : " AND tahun = '$tahun'";
$query = "SELECT diklat, penyelenggara, tahun, COUNT(id_peg) as jumlah_peserta, SUM(biaya) as total_biaya_kegiatan
          FROM tb_diklat 
          WHERE 1=1 $where_tahun
          GROUP BY diklat, penyelenggara, tahun
          ORDER BY total_biaya_kegiatan DESC";
$result = mysqli_query($conn, $query);

// ==========================================================
// KITA GUNAKAN OUTPUT BUFFERING (OB) UNTUK MENYIMPAN HTML
// ==========================================================
ob_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Biaya Diklat</title>
    <style>
        body { font-family: sans-serif; font-size: 11pt; color: #000; }
        
        /* Layout Header */
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .header h4 { margin: 5px 0 0; font-size: 12pt; font-weight: normal; }
        .line { border-bottom: 2px solid #000; margin-top: 10px; margin-bottom: 20px; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; vertical-align: middle; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        
        /* Utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .grand-total { background-color: #eee; font-weight: bold; }
    </style>
</head>
<body <?= ($type == 'print') ? 'onload="window.print()"' : '' ?>>

    <div class="header">
        <h2>Rekapitulasi Biaya Diklat Pegawai</h2>
        <h4>Periode Tahun: <?= $tahun ?></h4>
    </div>
    <div class="line"></div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Kegiatan</th>
                <th>Penyelenggara</th>
                <th width="10%">Tahun</th>
                <th width="10%">Peserta</th>
                <th width="20%">Total Biaya</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            $grand_total = 0;
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) { 
                    $grand_total += $row['total_biaya_kegiatan'];
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= $row['diklat'] ?></td>
                <td><?= $row['penyelenggara'] ?></td>
                <td class="text-center"><?= $row['tahun'] ?></td>
                <td class="text-center"><?= $row['jumlah_peserta'] ?></td>
                <td class="text-right"><?= number_format($row['total_biaya_kegiatan'], 0, ',', '.') ?></td>
            </tr>
            <?php 
                } 
            } else {
                echo '<tr><td colspan="6" class="text-center">Data tidak ditemukan</td></tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right grand-total">GRAND TOTAL</td>
                <td class="text-right grand-total"><?= number_format($grand_total, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>
    
    <div style="margin-top: 30px; text-align: right; font-size: 10pt;">
        <p>Dicetak pada: <?= date('d-m-Y H:i') ?></p>
    </div>

</body>
</html>

<?php
// ==========================================================
// AMBIL KONTEN HTML DARI BUFFER
// ==========================================================
$html = ob_get_clean();

// ==========================================================
// LOGIKA OUTPUT BERDASARKAN TYPE
// ==========================================================

if ($type == 'pdf') {
    // --- MODE PDF (MPDF) ---
    // Pastikan path ini sesuai dengan lokasi folder mpdf kamu
    include '../../plugins/mpdf/mpdf.php';
    
    // Setting Kertas A4 Landscape (biar lebar) atau Portrait ('A4-P')
    $mpdf = new mPDF('utf-8', 'A4'); 
    
    // Tulis HTML
    $mpdf->WriteHTML($html);
    
    // Output 'D' = Force Download
    $nama_file = 'Rekap_Biaya_Diklat_'.$tahun.'.pdf';
    $mpdf->Output($nama_file, 'D'); 
    exit;

} elseif ($type == 'excel') {
    // --- MODE EXCEL ---
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=Rekap_Biaya_Diklat_$tahun.xls");
    echo $html;
    exit;

} else {
    // --- MODE PRINT / PREVIEW ---
    echo $html;
}
?>