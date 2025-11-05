<?php
// komponen/dashboard-filter.php
include '../dist/koneksi.php';

$unit = isset($_GET['unit_kerja']) ? $_GET['unit_kerja'] : '';

// Pastikan variabel unit terbawa ke semua komponen yang di-include
// Statistik Box
include 'statistik-box.php';

// Grafik Masa Kerja
include 'chart-masakerja.php';

// Baris grafik kiri & kanan
?>
<div class="row">
  <div class="col-md-6">
    <?php $unit = $unit; include 'chart-pie-jk.php'; ?>
    <?php $unit = $unit; include 'chart-bar-status.php'; ?>
  </div>
  <div class="col-md-6">
    <?php $unit = $unit; include 'chart-bar-pendidikan.php'; ?>
    <?php $unit = $unit; include 'chart-line-pelanggaran.php'; ?>
  </div>
</div>
<?php
// Grafik Jabatan
include 'chart-bar-jabatan.php';

// Tabel Pensiun dan Keterisian Jabatan
include 'tabel-pensiun.php';
?>
<div class="row">
  <div class="col-md-6">
    <?php $unit = $unit; include 'tabel-keterisian-eksekutif.php'; ?>
  </div>
  <div class="col-md-6">
    <?php $unit = $unit; include 'tabel-keterisian-struktural.php'; ?>
  </div>
</div>
