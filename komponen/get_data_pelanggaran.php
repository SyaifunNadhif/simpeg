<?php
// ajax/get_data_pelanggaran.php
session_start();
include "../dist/koneksi.php"; // Sesuaikan path koneksi kamu

// 1. FILTER UNIT KERJA (Wajib Ada)
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$kode_cabang_session = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$where_unit = '';

if ($hak_akses === 'kepala') {
    $unit = mysqli_real_escape_string($conn, $kode_cabang_session);
    $where_unit = "AND j.unit_kerja = '$unit'";
}

// 2. AMBIL PARAMETER TAHUN
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// 3. QUERY DATA PER BULAN (JAN - DES)
// Kita siapkan array kosong 1-12 biar bulan yang kosong tetap muncul angka 0
$data_final = [];
for ($i = 1; $i <= 12; $i++) {
    $data_final[$i] = 0;
}

// Query Strict (Pegawai Aktif + Jabatan Aktif)
$sql = "SELECT MONTH(h.tgl_sk) AS bulan, COUNT(DISTINCT h.id_peg) AS total 
        FROM tb_hukuman h
        JOIN tb_pegawai p ON h.id_peg = p.id_peg
        JOIN tb_jabatan j ON h.id_peg = j.id_peg
        WHERE YEAR(h.tgl_sk) = '$tahun'
        AND p.status_aktif = 1 
        AND j.status_jab = 'Aktif'
        $where_unit
        GROUP BY MONTH(h.tgl_sk)";

$hasil = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($hasil)) {
    $bln = (int)$row['bulan'];
    $data_final[$bln] = (int)$row['total'];
}

// 4. KIRIM JSON
// Ubah key array jadi index biasa untuk JS
$response = [
    'labels' => ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"],
    'data'   => array_values($data_final)
];

header('Content-Type: application/json');
echo json_encode($response);
?>