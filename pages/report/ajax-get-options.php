<?php
// FILE: pages/report/ajax-get-options.php
if (session_id() == '') session_start();
include "../../dist/koneksi.php";

$type = isset($_POST['type']) ? $_POST['type'] : '';

// --- LOGIC: AMBIL JABATAN BERDASARKAN KANTOR ---
if ($type == 'get_jabatan') {
    $kode_kantor = mysqli_real_escape_string($conn, $_POST['kode_kantor']);
    
    echo "<option value=''>-- Semua Jabatan --</option>";

    if ($kode_kantor == '') exit;

    // 1. Cek Level Kantor yang dipilih
    $qK = mysqli_query($conn, "SELECT level, kode_cabang FROM tb_kantor WHERE kode_kantor_detail = '$kode_kantor'");
    $dK = mysqli_fetch_assoc($qK);

    // Default: Cari jabatan hanya di unit kerja tersebut
    $whereKantor = " j.unit_kerja = '$kode_kantor' ";

    // 2. Logic Cerdas: Jika yang dipilih KC (Cabang), ambil juga jabatan di KK bawahannya
    if ($dK && $dK['level'] == 'KC') {
        $cabangID = $dK['kode_cabang'];
        // Cari berdasarkan kode_cabang di tb_kantor
        $whereKantor = " k.kode_cabang = '$cabangID' "; 
    }

    // 3. Query Jabatan yang ADA PEGAWAINYA saja (Biar dropdown tidak kosong/sampah)
    $sql = "SELECT DISTINCT j.jabatan 
            FROM tb_jabatan j
            JOIN tb_pegawai p ON j.id_peg = p.id_peg
            JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
            WHERE $whereKantor 
            AND p.status_aktif = 1 
            AND j.status_jab = 'Aktif'
            ORDER BY j.jabatan ASC";

    $q = mysqli_query($conn, $sql);
    while ($r = mysqli_fetch_assoc($q)) {
        echo "<option value='".$r['jabatan']."'>".$r['jabatan']."</option>";
    }
}
?>