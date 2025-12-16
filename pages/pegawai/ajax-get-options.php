<?php
include "../../dist/koneksi.php";

$type = isset($_POST['type']) ? $_POST['type'] : '';

// --- 1. GET OPSI UNTUK DROPDOWN KE-2 (DIVISI / UNIT DETAIL) ---
if ($type == 'get_divisi') {
    $kode_kantor = mysqli_real_escape_string($conn, $_POST['kode_kantor']);
    
    // Ambil Info Kantor yang dipilih
    $qK = mysqli_query($conn, "SELECT level, kode_cabang, nama_kantor FROM tb_kantor WHERE kode_kantor_detail = '$kode_kantor'");
    $dK = mysqli_fetch_assoc($qK);
    $level = $dK['level'];
    $cabangID = $dK['kode_cabang'];

    echo "<option value=''>-- Semua Unit / Divisi --</option>";

    // SKENARIO 1: Jika yang dipilih adalah KANTOR CABANG (KC)
    // Tampilkan daftar KK di bawahnya + Divisi Logis
    if ($level == 'KC') {
        
        // A. Ambil Anak-anaknya (KK) berdasarkan kode_cabang yang sama
        echo "<optgroup label='Unit Fisik (Kantor Kas)'>";
        // Tampilkan Induknya dulu (KC itu sendiri)
        echo "<option value='$kode_kantor'>$dK[nama_kantor] (Induk)</option>";
        
        $sqlKK = "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor 
                  WHERE kode_cabang = '$cabangID' AND level = 'KK' 
                  ORDER BY nama_kantor ASC";
        $qKK = mysqli_query($conn, $sqlKK);
        while ($kk = mysqli_fetch_assoc($qKK)) {
            // Value-nya Kode Kantor Detail
            echo "<option value='".$kk['kode_kantor_detail']."'>".$kk['nama_kantor']."</option>";
        }
        echo "</optgroup>";

        // B. Ambil Divisi Logis (Opsional, jika di KC ada pembagian divisi di tb_master_jabatan)
        echo "<optgroup label='Bagian / Divisi'>";
        $sqlDiv = "SELECT DISTINCT nama_unit_kerja FROM tb_master_jabatan 
                   WHERE lingkup = 'KC' AND nama_unit_kerja IS NOT NULL AND nama_unit_kerja != '' 
                   ORDER BY nama_unit_kerja ASC";
        $qDiv = mysqli_query($conn, $sqlDiv);
        while ($d = mysqli_fetch_assoc($qDiv)) {
            // Value-nya Nama String
            echo "<option value='".$d['nama_unit_kerja']."'>".$d['nama_unit_kerja']."</option>";
        }
        echo "</optgroup>";
    }
    
    // SKENARIO 2: Jika KP / KANWIL
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

// --- 2. GET OPSI UNTUK DROPDOWN KE-3 (JABATAN) ---
if ($type == 'get_jabatan') {
    $kode_kantor = mysqli_real_escape_string($conn, $_POST['kode_kantor']);
    $divisi_val  = mysqli_real_escape_string($conn, $_POST['divisi']); // Bisa berupa Kode Kantor (Angka) atau Nama Divisi (Huruf)

    // Cek Level Kantor Utama
    $qK = mysqli_query($conn, "SELECT level FROM tb_kantor WHERE kode_kantor_detail = '$kode_kantor'");
    $dK = mysqli_fetch_assoc($qK);
    $level = $dK['level'];

    // Jika level KK, anggap KC untuk cari jabatan
    if ($level == 'KK') { $level = 'KC'; }

    echo "<option value=''>-- Pilih Jabatan --</option>";

    // Logic Filter Jabatan
    $whereDivisi = "";
    
    // Cek apakah $divisi_val adalah Kode Kantor (Angka) atau Nama Divisi (Huruf)
    if (is_numeric($divisi_val)) {
        // Jika user milih Kankas (Angka), biasanya jabatannya umum (Teller, CS, Ka Kankas)
        // Kita bisa filter jabatan yang lingkupnya KC saja
        $whereDivisi = "AND lingkup = 'KC'";
    } else if ($divisi_val != '') {
        // Jika user milih Divisi (Huruf), filter berdasarkan nama_unit_kerja
        $whereDivisi = "AND nama_unit_kerja = '$divisi_val'";
    }

    $sql = "SELECT DISTINCT nama_jabatan 
            FROM tb_master_jabatan 
            WHERE lingkup = '$level' 
            $whereDivisi
            ORDER BY nama_jabatan ASC";
    
    $query = mysqli_query($conn, $sql);
    while ($r = mysqli_fetch_assoc($query)) {
        echo "<option value='".$r['nama_jabatan']."'>".$r['nama_jabatan']."</option>";
    }
}
?>