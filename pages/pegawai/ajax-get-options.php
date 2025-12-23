<?php
include "../../dist/koneksi.php";

$type = isset($_POST['type']) ? $_POST['type'] : '';

// --- 1. GET OPSI DIVISI / UNIT DETAIL ---
if ($type == 'get_divisi') {
    $kode_kantor = mysqli_real_escape_string($conn, $_POST['kode_kantor']);
    
    // Ambil Info Level Kantor
    $qK = mysqli_query($conn, "SELECT level, kode_cabang, nama_kantor FROM tb_kantor WHERE kode_kantor_detail = '$kode_kantor'");
    $dK = mysqli_fetch_assoc($qK);
    
    if(!$dK) { echo "<option value=''>-- Kantor Tidak Valid --</option>"; exit; }

    $level    = $dK['level'];
    $cabangID = $dK['kode_cabang'];

    echo "<option value=''>-- Semua Unit / Divisi --</option>";

    // JIKA KANTOR CABANG (KC) -> Tampilkan KK + Divisi
    if ($level == 'KC') {
        // A. Group Unit Fisik (Termasuk Induk & Kantor Kas)
        echo "<optgroup label='Unit Fisik (Kantor Kas)'>";
        echo "<option value='$kode_kantor'>$dK[nama_kantor] (Induk)</option>"; // Diri sendiri
        
        $sqlKK = "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor 
                  WHERE kode_cabang = '$cabangID' AND level = 'KK' 
                  ORDER BY nama_kantor ASC";
        $qKK = mysqli_query($conn, $sqlKK);
        while ($kk = mysqli_fetch_assoc($qKK)) {
            echo "<option value='".$kk['kode_kantor_detail']."'>".$kk['nama_kantor']."</option>";
        }
        echo "</optgroup>";

        // B. Group Bagian / Divisi (Seksi di KC)
        echo "<optgroup label='Bagian / Divisi'>";
        $sqlDiv = "SELECT DISTINCT nama_unit_kerja FROM tb_master_jabatan 
                   WHERE lingkup = 'KC' AND nama_unit_kerja != '' 
                   ORDER BY nama_unit_kerja ASC";
        $qDiv = mysqli_query($conn, $sqlDiv);
        while ($d = mysqli_fetch_assoc($qDiv)) {
            echo "<option value='".$d['nama_unit_kerja']."'>".$d['nama_unit_kerja']."</option>";
        }
        echo "</optgroup>";
    } 
    // JIKA KP / KANWIL -> Hanya Tampilkan Divisi
    else {
        $sql = "SELECT DISTINCT nama_unit_kerja FROM tb_master_jabatan 
                WHERE lingkup = '$level' AND nama_unit_kerja != '' 
                ORDER BY nama_unit_kerja ASC";
        $query = mysqli_query($conn, $sql);
        while ($r = mysqli_fetch_assoc($query)) {
            echo "<option value='".$r['nama_unit_kerja']."'>".$r['nama_unit_kerja']."</option>";
        }
    }
}

// --- 2. GET OPSI JABATAN ---
if ($type == 'get_jabatan') {
    $kode_kantor = mysqli_real_escape_string($conn, $_POST['kode_kantor']);
    $divisi_val  = mysqli_real_escape_string($conn, $_POST['divisi']); // Bisa ID Kantor (Angka) atau Nama Divisi (Huruf)

    // Cek Level Kantor
    $qK = mysqli_query($conn, "SELECT level FROM tb_kantor WHERE kode_kantor_detail = '$kode_kantor'");
    $dK = mysqli_fetch_assoc($qK);
    $level = $dK ? $dK['level'] : '';

    // Normalisasi Level: KK dianggap KC di master jabatan
    if ($level == 'KK') { $level = 'KC'; }

    echo "<option value=''>-- Pilih Jabatan --</option>";

    $whereAdditional = "";

    // A. Jika user memilih Unit Fisik (Angka: misal Kode Kantor Kas)
    if (is_numeric($divisi_val)) {
        // Logic: Jika master jabatan Anda membedakan jabatan KK, sesuaikan WHERE ini.
        // Jika tidak, biarkan default lingkup = KC.
        $whereAdditional = ""; 
    } 
    // B. Jika user memilih Nama Divisi (Huruf: misal 'SDM', 'KREDIT')
    else if ($divisi_val != '') {
        $whereAdditional = "AND nama_unit_kerja = '$divisi_val'";
    }

    $sql = "SELECT DISTINCT nama_jabatan 
            FROM tb_master_jabatan 
            WHERE lingkup = '$level' 
            $whereAdditional
            ORDER BY nama_jabatan ASC";
    
    $query = mysqli_query($conn, $sql);
    while ($r = mysqli_fetch_assoc($query)) {
        echo "<option value='".$r['nama_jabatan']."'>".$r['nama_jabatan']."</option>";
    }
}
?>