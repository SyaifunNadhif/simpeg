<?php
// dashboard.php
// Pastikan tidak ada session_start() disini jika di file induk (index.php) sudah ada.
?>

<?php include 'komponen/alert-welcome.php'; ?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-12">
        <h1 class="m-0 fw-bold text-dark" style="font-size: 1.8rem;">Dashboard</h1>
        <p class="text-muted small mb-0">Overview Data Kepegawaian & Statistik</p>
      </div>
      </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <div id="dashboard-content">
      
      <?php include 'komponen/statistik-box.php'; ?>
      
      <?php include 'komponen/chart-masakerja.php'; ?>
      
      <div class="row match-height">
        <div class="col-lg-5 col-md-12 mb-4">
          <?php include 'komponen/chart-pie-jk.php'; ?>
        </div>
        <div class="col-lg-7 col-md-12 mb-4">
          <?php include 'komponen/chart-bar-pendidikan.php'; ?>
        </div>
      </div>

      <div class="row match-height">
        <div class="col-md-6 mb-4">
          <?php include 'komponen/chart-bar-status.php'; ?>
        </div>
        <div class="col-md-6 mb-4">
          <?php include 'komponen/chart-line-pelanggaran.php'; ?>
        </div>
      </div>
      
      <?php include 'komponen/chart-bar-jabatan.php'; ?>

      <?php include 'komponen/tabel-pensiun.php'; ?>
      
      <div class="row match-height">
        <div class="col-md-6 mb-4">
          <?php include 'komponen/tabel-keterisian-eksekutif.php'; ?>
        </div>
        <div class="col-md-6 mb-4">
          <?php include 'komponen/tabel-keterisian-struktural.php'; ?>
        </div>
      </div>
      
    </div> </div>
</section>

<style>
/* CSS Dashboard */
.content-header h1 {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
}
.row.match-height {
  display: flex;
  flex-wrap: wrap;
}
.row.match-height > [class*='col-'] {
  display: flex;
  flex-direction: column;
}
.row.match-height > [class*='col-'] > .card {
  flex: 1;
  width: 100%;
}
</style>

<link href="plugins/select2/css/select2.min.css" rel="stylesheet" />

<script src="plugins/select2/js/select2.min.js"></script>

<script>
window.addEventListener('load', function() {
    
    // Cek jQuery
    if (typeof $ !== 'undefined') {
        
        // 1. Inisialisasi Select2 Awal
        function initPlugins() {
             // Cek dulu apakah element select ada, biar console gak error
             if($('.select2').length > 0){
                 $('.select2').select2({
                    placeholder: "Pilih Kantor Cabang",
                    allowClear: true,
                    width: '100%', // Tambahan biar responsif
                    minimumResultsForSearch: 5
                 });
             }
        }

        // Jalankan saat pertama load
        initPlugins();

        // 2. Event Filter Dashboard
        // Menggunakan 'on change' pada document agar elemen dinamis tetap terdeteksi
        $(document).on('change', '#filter_unit_dashboard', function () {
          const unit = $(this).val();
          
          $('#dashboard-content').css('opacity', '0.5');
          
          $.get('komponen/dashboard-filter.php', { unit_kerja: unit }, function (data) {
            
            // Replace konten dashboard
            $('#dashboard-content').html(data).css('opacity', '1');
            
            // PENTING BROTHER:
            // Karena konten diganti via AJAX, script Chart.js yang ada di dalam
            // file-file komponen/chart-xxx.php HARUS ikut ter-load lagi di dalam 'data'.
            // Jika chart hilang setelah filter, pastikan 'dashboard-filter.php' 
            // me-return HTML lengkap beserta tag <script> chart-nya.
            
            // Re-init plugin (seperti tooltip, atau datatable jika ada di dalam dashboard)
            initPlugins(); 
            
          }).fail(function() {
             // Pakai SweetAlert bawaan AdminLTE/Local kalau ada, atau alert biasa
             alert('Gagal memuat data filter. Cek koneksi atau log error.');
             $('#dashboard-content').css('opacity', '1');
          });
        });

    } else {
        console.error("jQuery belum diload! Pastikan script jQuery ada di footer.");
    }
});
</script>