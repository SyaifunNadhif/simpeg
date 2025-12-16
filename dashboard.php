<?php
// dashboard.php
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

      <!-- ini yang bisa membuat error -->
      <?php include 'komponen/tabel-pensiun.php'; ?>
      
      <div class="row match-height">
        <div class="col-md-6 mb-4">
          <?php include 'komponen/tabel-keterisian-eksekutif.php'; ?>
        </div>
        <div class="col-md-6 mb-4">
          <?php include 'komponen/tabel-keterisian-struktural.php'; ?>
        </div>
      </div>
      
    </div>

  </div>
</section>

<style>
/* CSS Agar Tampilan Dashboard Lebih Rapih */
.content-header h1 {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
}

/* FIX: Match Height Flexbox Logic */
.row.match-height {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  flex-wrap: wrap;
}

.row.match-height > [class*='col-'] {
  display: flex;
  flex-direction: column;
}

/* Agar card mengisi penuh tinggi kolom */
.row.match-height > [class*='col-'] > .card {
  flex: 1;
  width: 100%; /* Pastikan lebar full */
}
</style>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Menggunakan window.onload untuk memastikan jQuery & AdminLTE sudah ter-load sempurna dari Footer
window.addEventListener('load', function() {
    
    // Cek apakah jQuery terbaca
    if (typeof $ !== 'undefined') {
        
        // Inisialisasi Select2
        $('.select2').select2({
          placeholder: "Pilih Kantor Cabang",
          allowClear: true,
          minimumResultsForSearch: 5
        });

        // Event Filter
        $('#filter_unit_dashboard').change(function () {
          const unit = $(this).val();
          
          // Efek loading sederhana biar user tau proses berjalan
          $('#dashboard-content').css('opacity', '0.5');
          
          $.get('komponen/dashboard-filter.php', { unit_kerja: unit }, function (data) {
            $('#dashboard-content').html(data).css('opacity', '1');
            
            // Re-init plugin jika konten di-refresh via AJAX
            // (Opsional, tergantung isi dashboard-filter.php)
          }).fail(function() {
             alert('Gagal memuat data filter.');
             $('#dashboard-content').css('opacity', '1');
          });
        });

    } else {
        console.error("jQuery belum diload! Pastikan script jQuery ada di footer.");
    }
});
</script>