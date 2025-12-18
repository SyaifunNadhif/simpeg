<?php
// File: komponen/get_data_pelanggaran.php
// Pastikan file ini ada di folder "komponen" agar sesuai dengan pemanggilan JS sebelumnya

session_start();

// Cek path koneksi. Jika file ini ada di folder 'komponen',
// dan 'dist' ada di root, maka path "../dist/koneksi.php" SUDAH BENAR.
include "../dist/koneksi.php"; 

// --- 1. KEAMANAN AKSES (Anti Maling Data Cabang Lain) ---
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$kode_cabang_session = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$where_unit = '';

if ($hak_akses === 'kepala') {
    // Sanitasi ekstra walaupun session relatif aman
    $unit = mysqli_real_escape_string($conn, $kode_cabang_session);
    $where_unit = "AND j.unit_kerja = '$unit'";
}

// --- 2. PARAMETER TAHUN (Anti SQL Injection) ---
// Casting (int) membuat input '2025; DROP TABLE' berubah jadi angka 2025 saja. AMAN.
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// --- 3. LOGIC DATA (Fill 0 for Empty Months) ---
// Inisialisasi array 1-12 dengan nilai 0
$data_final = [];
for ($i = 1; $i <= 12; $i++) {
    $data_final[$i] = 0;
}

// Query Fokus: Pegawai Aktif & Jabatan Aktif
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

if ($hasil) {
    while ($row = mysqli_fetch_assoc($hasil)) {
        $bln = (int)$row['bulan'];
        // Update nilai array bulan yang ada datanya
        $data_final[$bln] = (int)$row['total'];
    }
}

// --- 4. OUTPUT JSON ---
$response = [
    'labels' => ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"],
    // array_values memastikan indeks array direset jadi 0,1,2... agar terbaca array di JS
    'data'   => array_values($data_final)
];

header('Content-Type: application/json');
echo json_encode($response);
exit; // Biasakan exit setelah kirim JSON
?>