<?php
// FILE: pages/ref-diklat/ajax-get-jenis.php

// 1. SECURITY: Cek Session Login (Wajib Paling Atas)
if (session_id() === '') session_start();
if (empty($_SESSION['id_user'])) {
    die('<option value="">Akses Ditolak</option>');
}

include "../../dist/koneksi.php";

// 2. SECURITY: Sanitasi Input (Anti SQL Injection)
// Ambil tahun dari POST, jika tidak ada pakai tahun sekarang
$tahun_raw = isset($_POST['tahun']) ? $_POST['tahun'] : date('Y');
$tahun = mysqli_real_escape_string($conn, $tahun_raw);

// Query
$sql = "SELECT DISTINCT diklat FROM tb_diklat WHERE tahun = '$tahun' ORDER BY diklat ASC";
$query = mysqli_query($conn, $sql);

echo '<option value="">- Semua Jenis -</option>';

while($row = mysqli_fetch_assoc($query)){
    // 3. SECURITY: Sanitasi Output (Anti XSS)
    // Mencegah script aneh yang mungkin tersimpan di nama diklat
    $diklat_clean = htmlspecialchars($row['diklat'], ENT_QUOTES, 'UTF-8');
    
    echo '<option value="'.$diklat_clean.'">'.$diklat_clean.'</option>';
}
?>