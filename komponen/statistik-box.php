<?php
include "dist/koneksi.php";

// --- AMBIL DATA SESSION UNTUK FILTER ---
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$kode_cabang_session = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';

// --- TENTUKAN KONDISI WHERE BERDASARKAN HAK AKSES ---
$where = '';
$unit = '';

if ($hak_akses === 'kepala') {
    // Kepala: filter berdasarkan unit kerja/cabang ($unit harus di-set)
    $unit = mysqli_real_escape_string($conn, $kode_cabang_session);
    $where = "AND j.unit_kerja = '$unit'";
}
// Admin: $where tetap kosong, menampilkan semua data.

// Default Value agar tidak error Notice
$jmlpegawai = 0;
$jmlpurna = 0;
$jmlpunishment = 0;
$jmldiklat = 0;
$tahun_sekarang = date('Y'); // Dipakai untuk tampilan dan query

// 1. Total Pegawai (Filter Unit Kerja, Tanpa Filter Tahun)
// Pegawai aktif (status_jab = 'Aktif')
// PERBAIKAN QUERY JML PEGAWAI (Disarankan)
$pegawai_q = mysqli_query($conn, "SELECT COUNT(DISTINCT j.id_peg) AS total FROM tb_jabatan j WHERE j.status_jab = 'Aktif' $where");
if ($pegawai_q && $row = mysqli_fetch_assoc($pegawai_q)) {
    $jmlpegawai = $row['total']; // Hasilnya 92
}

// 2. Non Aktif (Filter Unit Kerja DAN Tahun Sekarang)
$purna = mysqli_query($conn, "SELECT a.id_peg 
    FROM tb_pegawai a
    JOIN tb_mutasi b ON a.id_peg = b.id_peg
    JOIN tb_jabatan j ON a.id_peg = j.id_peg
    WHERE a.status_aktif = 3 
      AND YEAR(b.tgl_mutasi) = YEAR(CURRENT_DATE()) 
      $where");
if ($purna) $jmlpurna = mysqli_num_rows($purna);

// 3. Pelanggaran (Filter Unit Kerja DAN Tahun Sekarang)
$punishment = mysqli_query($conn, "SELECT h.id_peg 
    FROM tb_hukuman h
    JOIN tb_jabatan j ON h.id_peg = j.id_peg
    WHERE YEAR(h.tgl_sk) = YEAR(CURRENT_DATE()) 
      $where");
if ($punishment) $jmlpunishment = mysqli_num_rows($punishment);

// 4. Diklat (Filter Unit Kerja DAN Tahun Sekarang)
// Dibuat DISTINCT untuk menghitung Diklat unik saja
$diklat = mysqli_query($conn, "SELECT DISTINCT d.diklat 
    FROM tb_diklat d
    JOIN tb_jabatan j ON d.id_peg = j.id_peg
    WHERE d.tahun = YEAR(CURRENT_DATE()) 
      $where");
if ($diklat) $jmldiklat = mysqli_num_rows($diklat);
?>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-white shadow-sm border-left-primary">
            <div class="inner">
                <h3 class="text-primary"><?= number_format($jmlpegawai, 0, ",", ".") ?></h3>
                <p class="text-muted">Total Pegawai</p>
            </div>
            <div class="icon text-primary">
                <i class="fas fa-users"></i>
            </div>
            <a href="home-admin.php?page=form-view-data-pegawai" class="small-box-footer text-primary">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-white shadow-sm border-left-danger">
            <div class="inner">
                <h3 class="text-danger"><?= number_format($jmlpurna, 0, ",", ".") ?></h3>
                <p class="text-muted">Non Aktif (<?= $tahun_sekarang ?>)</p>
            </div>
            <div class="icon text-danger">
                <i class="fas fa-user-times"></i>
            </div>
            <a href="home-admin.php?page=form-view-data-mutasi" class="small-box-footer text-danger">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-white shadow-sm border-left-warning">
            <div class="inner">
                <h3 class="text-warning"><?= number_format($jmlpunishment, 0, ",", ".") ?></h3>
                <p class="text-muted">Pelanggaran (<?= $tahun_sekarang ?>)</p>
            </div>
            <div class="icon text-warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="home-admin.php?page=form-view-data-pelanggaran" class="small-box-footer text-warning">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-white shadow-sm border-left-success">
            <div class="inner">
                <h3 class="text-success"><?= number_format($jmldiklat, 0, ",", ".") ?></h3>
                <p class="text-muted">Diklat (<?= $tahun_sekarang ?>)</p>
            </div>
            <div class="icon text-success">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <a href="home-admin.php?page=master-data-diklat" class="small-box-footer text-success">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<style>
    .small-box {
        border-radius: 8px;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08); /* Shadow diperhalus */
        background: #fff;
        overflow: hidden;
    }
    .small-box > .inner {
        padding: 20px;
        position: relative;
        z-index: 2; /* Agar teks di atas icon */
    }
    .small-box h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 5px 0;
        white-space: nowrap;
    }
    .small-box p {
        font-size: 1rem;
        font-weight: 500;
        margin-bottom: 0;
        color: #6c757d;
    }
    
    /* STYLE ICON DIPERBAIKI */
    .small-box .icon {
        position: absolute;
        top: 10px;
        right: 15px;
        z-index: 1; /* Di bawah teks */
        font-size: 70px; /* Ukuran icon diperbesar sedikit */
        opacity: 0.3; /* Transparansi dinaikkan biar lebih kelihatan (sebelumnya 0.1) */
        transition: all 0.3s linear;
    }
    
    .small-box:hover .icon {
        transform: scale(1.1); /* Efek zoom saat hover */
        opacity: 0.5; /* Lebih jelas saat dihover */
    }

    .small-box-footer {
        position: relative;
        text-align: center;
        padding: 8px 0;
        color: inherit;
        display: block;
        z-index: 10;
        background: rgba(0,0,0,0.03);
        text-decoration: none !important;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .small-box-footer:hover {
        background: rgba(0,0,0,0.06);
    }

    /* Border Kiri */
    .border-left-primary { border-left: 5px solid #007bff; }
    .border-left-danger { border-left: 5px solid #dc3545; }
    .border-left-warning { border-left: 5px solid #ffc107; }
    .border-left-success { border-left: 5px solid #28a745; }

    /* Warna Text */
    .text-primary { color: #007bff !important; }
    .text-danger { color: #dc3545 !important; }
    .text-warning { color: #ffc107 !important; }
    .text-success { color: #28a745 !important; }
</style>