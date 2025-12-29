<?php
// FILE: pages/report/logic-rekap.php

// Cek session jika belum ada (safety)
if (session_id() == '') session_start();

// Include koneksi (Path relatif terhadap index/home-admin.php)
// Pastikan path ini benar sesuai struktur foldermu
include_once 'dist/koneksi.php';
include_once 'dist/library.php';

// 1. FILTER LOGIC
$hak_akses   = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$kode_kantor = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Siapkan variable WHERE
$where_akses = "WHERE 1=1";
if ($hak_akses !== 'admin') {
    $where_akses .= " AND id_peg IN (SELECT id_peg FROM tb_jabatan WHERE unit_kerja = '$kode_kantor' AND status_jab = 'Aktif')";
}
$where_tahun = ($tahun_pilih == 'Semua') ? "" : " AND tahun = '$tahun_pilih'";

// 2. QUERY UTAMA
$query = "SELECT 
            diklat, 
            penyelenggara, 
            tahun,
            COUNT(id_peg) as jumlah_peserta, 
            SUM(biaya) as total_biaya_kegiatan
          FROM tb_diklat 
          $where_akses $where_tahun
          GROUP BY diklat, penyelenggara, tahun
          ORDER BY total_biaya_kegiatan DESC";

$result = mysqli_query($conn, $query);

// 3. OLAH DATA (Disimpan ke Array biar View tinggal Loop)
$data_rekap = [];
$grand_total_biaya = 0;
$total_semua_peserta = 0;

if ($result) {
    while($row = mysqli_fetch_assoc($result)){
        $grand_total_biaya += $row['total_biaya_kegiatan'];
        $total_semua_peserta += $row['jumlah_peserta'];
        $data_rekap[] = $row;
    }
}

// 4. QUERY TAHUN (Untuk Dropdown)
$qTahun = mysqli_query($conn, "SELECT DISTINCT tahun FROM tb_diklat ORDER BY tahun DESC");
$list_tahun = [];
while($t = mysqli_fetch_assoc($qTahun)) {
    $list_tahun[] = $t['tahun'];
}

// Parameter untuk link export
$params = "tahun=" . $tahun_pilih;
?>