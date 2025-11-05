<?php
// dashboard.php
?>
<!-- Alert Selamat Datang -->
<?php include 'komponen/alert-welcome.php'; ?>

<!-- Content Header -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Dashboard</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item "><a href="#">Dashboard</a></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <!-- Filter Unit Kerja 
    <div class="row mb-3">
      <div class="col-md-4">
        <select id="filter_unit_dashboard" class="form-control select2">
          <option value="">-- Semua Kantor Cabang --</option>
          </?php
            include "dist/koneksi.php";
            $units = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor WHERE level='KC' ORDER BY kode_cabang");
            while ($u = mysqli_fetch_assoc($units)) {
              echo "<option value='{$u['kode_kantor_detail']}'>{$u['nama_kantor']}</option>";
            }
          ?>
        </select>
      </div>
    </div>

    end Filter Unit Kerja -->

    <!-- Dashboard Konten Dinamis -->
    <div id="dashboard-content">
      <?php include 'komponen/statistik-box.php'; ?>
      <?php include 'komponen/chart-masakerja.php'; ?>
      <div class="row">
        <div class="col-md-6">
          <?php include 'komponen/chart-pie-jk.php'; ?>
          <?php include 'komponen/chart-bar-status.php'; ?>
        </div>
        <div class="col-md-6">
          <?php include 'komponen/chart-bar-pendidikan.php'; ?>
          <?php include 'komponen/chart-line-pelanggaran.php'; ?>
        </div>
      </div>
      <?php include 'komponen/chart-bar-jabatan.php'; ?>
      <?php include 'komponen/tabel-pensiun.php'; ?>
      <div class="row">
        <div class="col-md-6">
          <?php include 'komponen/tabel-keterisian-eksekutif.php'; ?>
        </div>
        <div class="col-md-6">
          <?php include 'komponen/tabel-keterisian-struktural.php'; ?>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- Select2 Minimal Style -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
.select2-container--default .select2-selection--single {
  height: 38px;
  padding: 4px 10px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 14px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
  line-height: 28px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
  height: 36px;
}
</style>

<script>
$(document).ready(function () {
  $('.select2').select2({
    placeholder: "Pilih Kantor Cabang",
    allowClear: true,
    minimumResultsForSearch: 5
  });

  $('#filter_unit_dashboard').change(function () {
    const unit = $(this).val();
    $.get('komponen/dashboard-filter.php', { unit_kerja: unit }, function (data) {
      $('#dashboard-content').html(data);
    });
  });
});
</script>
