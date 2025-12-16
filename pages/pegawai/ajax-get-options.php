<?php
include "../../dist/koneksi.php";

$type = isset($_POST['type']) ? $_POST['type'] : '';

// --- 1. AMBIL DAFTAR DIVISI BERDASARKAN KANTOR ---
if ($type == 'get_divisi') {
    $kode_kantor = mysqli_real_escape_string($conn, $_POST['kode_kantor']);
    
    // A. Cari LEVEL kantor
    $cekLevel = mysqli_query($conn, "SELECT level FROM tb_kantor WHERE kode_kantor_detail = '$kode_kantor'");
    $dLevel   = mysqli_fetch_assoc($cekLevel);
    $level    = $dLevel['level']; // Contoh: 'KC', 'KP', 'KANWIL', 'KK'

    // --- LOGIKA BARU: Jika KK, anggap sebagai KC ---
    if ($level == 'KK') {
        $level = 'KC';
    }
    // -----------------------------------------------

    echo "<option value=''>-- Pilih Divisi / Bagian --</option>";
    
    if ($level) {
        $sql = "SELECT DISTINCT nama_unit_kerja 
                FROM tb_master_jabatan 
                WHERE lingkup = '$level' 
                AND nama_unit_kerja IS NOT NULL 
                AND nama_unit_kerja != ''
                ORDER BY nama_unit_kerja ASC";
        
        $query = mysqli_query($conn, $sql);
        while ($r = mysqli_fetch_assoc($query)) {
            echo "<option value='".$r['nama_unit_kerja']."'>".$r['nama_unit_kerja']."</option>";
        }
    }
}

// --- 2. AMBIL JABATAN BERDASARKAN KANTOR & DIVISI ---
if ($type == 'get_jabatan') {
    $kode_kantor = mysqli_real_escape_string($conn, $_POST['kode_kantor']);
    $divisi      = mysqli_real_escape_string($conn, $_POST['divisi']);

    // A. Cari LEVEL kantor lagi
    $cekLevel = mysqli_query($conn, "SELECT level FROM tb_kantor WHERE kode_kantor_detail = '$kode_kantor'");
    $dLevel   = mysqli_fetch_assoc($cekLevel);
    $level    = $dLevel['level'];

    // --- LOGIKA BARU: Jika KK, anggap sebagai KC ---
    if ($level == 'KK') {
        $level = 'KC';
    }
    // -----------------------------------------------

    echo "<option value=''>-- Pilih Jabatan --</option>";

    if ($level && $divisi) {
        $sql = "SELECT DISTINCT nama_jabatan 
                FROM tb_master_jabatan 
                WHERE lingkup = '$level' 
                AND nama_unit_kerja = '$divisi'
                ORDER BY nama_jabatan ASC";
        
        $query = mysqli_query($conn, $sql);
        while ($r = mysqli_fetch_assoc($query)) {
            echo "<option value='".$r['nama_jabatan']."'>".$r['nama_jabatan']."</option>";
        }
    }
}
?>