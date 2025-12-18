<?php
// dashboard.php
// Pastikan tidak ada session_start() double kalau di index.php sudah ada
?>

<?php include 'komponen/alert-welcome.php'; ?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Dashboard</h1>
      </div>
      <!-- <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
        </ol>
      </div> -->
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <div id="dashboard-content">
      <?php include 'komponen/statistik-box.php'; ?>
      <?php include 'komponen/chart-masakerja.php'; ?>
    </div>

  </div>
</section>

<link href="plugins/select2/css/select2.min.css" rel="stylesheet" />
<script src="plugins/select2/js/select2.min.js"></script>

<style>
/* Style kustom tetap dipertahankan */
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
  // Cek dulu apakah element select ada biar console gak merah kalau filternya lagi di-hidden
  if ($('.select2').length > 0) {
      $('.select2').select2({
        placeholder: "Pilih Kantor Cabang",
        allowClear: true,
        minimumResultsForSearch: 5
      });
  }

  $('#filter_unit_dashboard').change(function () {
    const unit = $(this).val();
    
    // Kasih indikator loading dikit biar user tau
    $('#dashboard-content').css('opacity', '0.5');

    $.get('komponen/dashboard-filter.php', { unit_kerja: unit }, function (data) {
      $('#dashboard-content').html(data).css('opacity', '1');
    }).fail(function() {
        alert('Gagal mengambil data filter.');
        $('#dashboard-content').css('opacity', '1');
    });
  });
});
</script>