<?php
// File: keadaan-pegawai.php
// Versi: 2.1 - Filter Kantor Cabang, Nonaktifkan Bulan/Tahun

include "dist/koneksi.php";

// $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
// $tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$kode_cabang = isset($_GET['kode_cabang']) ? $_GET['kode_cabang'] : '';

function selected($val, $sel) { return $val == $sel ? 'selected' : ''; }
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Laporan Keadaan Pegawai</h1>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card">
    <div class="card-header">
      <form method="GET" action="">
        <input type="hidden" name="page" value="keadaan-pegawai">
        <div class="form-row align-items-end">
          <div class="col-md-4">
            <label>Kantor Cabang</label>
            <select name="kode_cabang" class="form-control">
              <option value="">-- Semua Kantor --</option>
              <?php
              $qc = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor WHERE level = 'KC' ORDER BY nama_kantor");
              while ($c = mysqli_fetch_array($qc)) {
                $sel = ($kode_cabang == $c['kode_kantor_detail']) ? 'selected' : '';
                echo "<option value='".$c['kode_kantor_detail']."' $sel>".$c['nama_kantor']."</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-8">
            <label>&nbsp;</label>
            <div class="btn-group d-flex">
              <button type="submit" class="btn btn-primary w-33">Terapkan</button>
              <a href="pages/report/print-keadaan-pegawai.php?kode_cabang=<?= $kode_cabang ?>" target="_blank" class="btn btn-success w-33">Cetak</a>
              <a href="pages/report/export-keadaan-pegawai.php?kode_cabang=<?= $kode_cabang ?>" class="btn btn-warning w-33">Export Excel</a>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="card-body">
      <?php include "pages/report/view-keadaan-pegawai.php"; ?>
    </div>
  </div>
</section>
