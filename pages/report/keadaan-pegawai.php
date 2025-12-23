<?php
/*********************************************************
 * FILE     : pages/report/keadaan-pegawai.php
 * MODULE   : Laporan Keadaan Pegawai (Modern UI)
 * VERSION  : 2.5
 *********************************************************/

include "dist/koneksi.php";

// Ambil Parameter Filter
$kode_cabang = isset($_GET['kode_cabang']) ? $_GET['kode_cabang'] : '';

// Helper function untuk selected option
function selected($val, $sel) { return $val == $sel ? 'selected' : ''; }
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<style>
  /* Styling Modern & Compact */
  .card-modern { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); background: #fff; margin-bottom: 20px; }
  
  .input-modern { border-radius: 10px; border: 1px solid #e2e8f0; height: 45px; width: 100%; padding: 0 15px; background-color: #f8f9fa; }
  .select2-container--bootstrap-5 .select2-selection { border-color: #e2e8f0; background-color: #f8f9fa; border-radius: 10px; min-height: 45px; padding-top: 5px; }
  .label-filter { font-size: 0.75rem; font-weight: 700; color: #94a3b8; margin-bottom: 5px; text-transform: uppercase; display: block; }
  
  /* Buttons */
  .btn-modern { border-radius: 10px; height: 45px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; border: none; cursor: pointer; padding: 0 20px; transition: all 0.2s; }
  .btn-primary-modern { background: #3b82f6; color: white; }
  .btn-primary-modern:hover { background: #2563eb; }
  .btn-success-modern { background: #10b981; color: white; }
  .btn-success-modern:hover { background: #059669; }
  .btn-warning-modern { background: #f59e0b; color: white; }
  .btn-warning-modern:hover { background: #d97706; }
</style>

<section class="content-header pt-3 pb-2">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 style="font-weight: 800; color: #1e293b; margin-bottom: 0;">Laporan Keadaan Pegawai</h1>
        <p class="text-muted mb-0">Rekapitulasi jumlah dan status pegawai per unit kerja</p>
      </div>
    </div>
  </div>
</section>

<section class="content mt-2">
  <div class="container-fluid">
    <div class="card card-modern">
      
      <div class="card-header bg-white border-bottom pt-4 pb-4">
        <form method="GET" action="">
          <input type="hidden" name="page" value="keadaan-pegawai">
          
          <div class="row align-items-end">
            <div class="col-lg-5 col-md-6 mb-2">
              <span class="label-filter">Pilih Kantor Cabang</span>
              <select name="kode_cabang" class="form-control select2">
                <option value="">-- Semua Kantor Cabang --</option>
                <?php
                // Ambil hanya level KC (Kantor Cabang)
                $qc = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor WHERE level = 'KC' ORDER BY nama_kantor");
                while ($c = mysqli_fetch_array($qc)) {
                  $sel = ($kode_cabang == $c['kode_kantor_detail']) ? 'selected' : '';
                  echo "<option value='".$c['kode_kantor_detail']."' $sel>".$c['nama_kantor']."</option>";
                }
                ?>
              </select>
            </div>

            <div class="col-lg-7 col-md-6 mb-2">
              <div class="d-flex gap-2 justify-content-md-end justify-content-start flex-wrap">
                
                <button type="submit" class="btn btn-modern btn-primary-modern">
                  <i class="fas fa-filter mr-2"></i> Terapkan
                </button>
                
                <a href="pages/report/print-keadaan-pegawai.php?kode_cabang=<?= htmlspecialchars($kode_cabang) ?>" target="_blank" class="btn btn-modern btn-warning-modern">
                  <i class="fas fa-print mr-2"></i> Cetak PDF
                </a>
                
                <a href="pages/report/export-keadaan-pegawai.php?kode_cabang=<?= htmlspecialchars($kode_cabang) ?>" target="_blank" class="btn btn-modern btn-success-modern">
                  <i class="fas fa-file-excel mr-2"></i> Export Excel
                </a>

              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="card-body p-0">
        <?php 
          // Pastikan file view ada sebelum di-include
          $viewPath = "pages/report/view-keadaan-pegawai.php";
          if (file_exists($viewPath)) {
            include $viewPath; 
          } else {
            echo "<div class='p-4 text-center text-muted'>File view tidak ditemukan: $viewPath</div>";
          }
        ?>
      </div>

    </div>
  </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
    $('.select2').select2({
      theme: 'bootstrap-5',
      width: '100%',
      placeholder: "-- Pilih Kantor --",
      allowClear: true
    });
  });
</script>