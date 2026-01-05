<?php
// FILE: pages/report/ajax-rekap-biaya.php

if (session_id() == '') session_start();
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(0);

// Koneksi Database
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn) || !$conn) { 
    @include_once __DIR__ . '/../../config/koneksi.php'; 
    if(isset($koneksi)) $conn = $koneksi; 
}

// 1. Ambil Parameter Filter
$tahun   = isset($_GET['tahun']) ? $_GET['tahun'] : 'Semua';
$kuartal = isset($_GET['kuartal']) ? $_GET['kuartal'] : 'Semua';

// 2. Build Query
// Kita JOIN ke tb_ref_pengembangan untuk dapat Nama Kategorinya
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

// Eksekusi Query
$sql = "SELECT b.*, r.kategori as nama_kategori $baseQuery ORDER BY b.tgl_pengembangan_sdm DESC";
$query = mysqli_query($conn, $sql);

$data = [];
$total_peserta = 0;
$grand_total_biaya = 0;

if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $jml   = (int)$row['jumlah_sdm'];
        $biaya = (float)$row['total_biaya'];
        
        $total_peserta += $jml;
        $grand_total_biaya += $biaya;

        // Jika nama kategori kosong (karena hapus master/dll), pakai kodenya
        $kat_display = !empty($row['nama_kategori']) ? $row['nama_kategori'] : '<span class="text-muted">Kode: '.$row['kode_pengembangan'].'</span>';

        $data[] = [
            'kategori_display' => $kat_display, // Data baru (Kategori)
            'kegiatan'         => htmlspecialchars($row['pengembangan_sdm']),
            'penyelenggara'    => htmlspecialchars($row['pihak_pelaksana']),
            'tahun'            => date('Y', strtotime($row['tgl_pengembangan_sdm'])),
            'tgl_lengkap'      => date('d M Y', strtotime($row['tgl_pengembangan_sdm'])),
            'peserta'          => $jml,
            'biaya_rp'         => number_format($biaya, 0, ',', '.'),
        ];
    }
}

// Return JSON
echo json_encode([
    'status' => 'success',
    'data'   => $data,
    'total'  => [
        'peserta' => number_format($total_peserta, 0, ',', '.'),
        'biaya'   => number_format($grand_total_biaya, 0, ',', '.')
    ]
]);
?>