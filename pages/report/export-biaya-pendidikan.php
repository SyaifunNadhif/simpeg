<?php
// FILE: pages/report/export-biaya.php

if (session_id() == '') session_start();
// Sesuaikan path koneksi database kamu
include '../../dist/koneksi.php'; 

// 1. AMBIL PARAMETER (FILTER)
$tahun   = isset($_GET['tahun']) ? $_GET['tahun'] : 'Semua';
$kuartal = isset($_GET['kuartal']) ? $_GET['kuartal'] : 'Semua';
$type    = isset($_GET['type']) ? $_GET['type'] : 'print';

// 2. QUERY DATA (LOGIC SAMA PERSIS DENGAN AJAX)
// Kita JOIN agar kolom Kategori Pengembangan muncul
$baseQuery = " FROM tb_biaya_pendidikan b
               LEFT JOIN tb_ref_pengembangan r ON b.kode_pengembangan = r.kode_sandi
               WHERE b.tgl_pengembangan_sdm IS NOT NULL AND b.tgl_pengembangan_sdm != '0000-00-00' ";

// Filter Tahun
if ($tahun !== 'Semua') {
    $safe_tahun = mysqli_real_escape_string($conn, $tahun);
    $baseQuery .= " AND YEAR(b.tgl_pengembangan_sdm) = '$safe_tahun' ";
}

// Filter Kuartal
if ($kuartal !== 'Semua') {
    if($kuartal == '1') $baseQuery .= " AND MONTH(b.tgl_pengembangan_sdm) BETWEEN 1 AND 3 ";
    if($kuartal == '2') $baseQuery .= " AND MONTH(b.tgl_pengembangan_sdm) BETWEEN 4 AND 6 ";
    if($kuartal == '3') $baseQuery .= " AND MONTH(b.tgl_pengembangan_sdm) BETWEEN 7 AND 9 ";
    if($kuartal == '4') $baseQuery .= " AND MONTH(b.tgl_pengembangan_sdm) BETWEEN 10 AND 12 ";
}

$sql = "SELECT b.*, r.kategori as nama_kategori $baseQuery ORDER BY b.tgl_pengembangan_sdm DESC";
$query = mysqli_query($conn, $sql);

// Tampung Data
$data_export = [];
$total_peserta = 0;
$grand_total_biaya = 0;

if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $total_peserta += $row['jumlah_sdm'];
        $grand_total_biaya += $row['total_biaya'];
        
        // Cek Nama Kategori (Fallback ke Kode jika nama kosong)
        $row['kategori_fix'] = !empty($row['nama_kategori']) ? $row['nama_kategori'] : $row['kode_pengembangan'];
        $data_export[] = $row;
    }
}

// 3. HEADER EXCEL
if ($type == 'excel') {
    $filename = "Rekap_Biaya_" . date('Ymd_His') . ".xls";
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Biaya Pendidikan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        
        th { 
            background-color: #1e3c72; color: #fff; padding: 8px; 
            border: 1px solid #000; text-align: center; vertical-align: middle;
        }
        
        td { padding: 6px; border: 1px solid #000; vertical-align: top; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .header p { margin: 5px 0 0 0; font-size: 12px; }

        /* Style khusus PDF/Print */
        <?php if($type == 'pdf') : ?>
        .btn-print { 
            display:inline-block; padding:10px 20px; background:#28a745; color:#fff; 
            text-decoration:none; border-radius:5px; margin-bottom:20px; font-weight:bold; cursor: pointer; border: none;
        }
        @media print { 
            .no-print { display: none; } 
            @page { size: landscape; margin: 1cm; } 
        }
        <?php endif; ?>
    </style>
</head>
<body>

    <?php if($type == 'pdf') : ?>
    <div class="no-print" style="text-align: right; padding: 10px;">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak / Simpan PDF</button>
    </div>
    <?php endif; ?>

    <div class="header">
        <h2>LAPORAN REKAPITULASI BIAYA PENDIDIKAN</h2>
        <p>
            Tahun: <?= $tahun ?> | 
            Kuartal: <?= ($kuartal == 'Semua') ? 'Semua' : 'Q'.$kuartal ?>
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Kategori Pengembangan</th>
                <th>Nama Kegiatan / Diklat</th>
                <th>Penyelenggara</th>
                <th width="10%">Tanggal</th>
                <th width="8%">Peserta</th>
                <th width="15%">Total Biaya</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            foreach ($data_export as $row): 
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['kategori_fix']) ?></td>
                <td><?= htmlspecialchars($row['pengembangan_sdm']) ?></td>
                <td><?= htmlspecialchars($row['pihak_pelaksana']) ?></td>
                <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_pengembangan_sdm'])) ?></td>
                
                <td class="text-center">
                    <?php if($type == 'excel'): ?>
                        <?= $row['jumlah_sdm'] ?>
                    <?php else: ?>
                        <?= $row['jumlah_sdm'] ?> Org
                    <?php endif; ?>
                </td>

                <td class="text-right">
                    <?php if($type == 'excel'): ?>
                        <?= $row['total_biaya'] ?>
                    <?php else: ?>
                        Rp <?= number_format($row['total_biaya'], 0, ',', '.') ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if(empty($data_export)): ?>
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px;">Data tidak ditemukan untuk periode ini.</td>
            </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="5" class="text-right">GRAND TOTAL</td>
                
                <td class="text-center">
                    <?php if($type == 'excel'): ?>
                        <?= $total_peserta ?>
                    <?php else: ?>
                        <?= number_format($total_peserta) ?> Org
                    <?php endif; ?>
                </td>

                <td class="text-right">
                    <?php if($type == 'excel'): ?>
                        <?= $grand_total_biaya ?>
                    <?php else: ?>
                        Rp <?= number_format($grand_total_biaya, 0, ',', '.') ?>
                    <?php endif; ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <br>
    <div style="width: 100%;">
        <table style="border: none; margin-top: 20px;">
            <tr style="border: none;">
                <td style="border: none; width: 70%;"></td>
                <td style="border: none; text-align: center;">
                    Dicetak pada: <?= date('d-m-Y H:i') ?><br><br><br><br>
                    ( Admin HRD )
                </td>
            </tr>
        </table>
    </div>

</body>
</html>