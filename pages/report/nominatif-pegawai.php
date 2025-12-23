<?php
/*********************************************************
 * FILE     : pages/report/laporan-nominatif-pegawai.php
 * MODULE   : View Laporan (Full Features + Custom Excel)
 * FOLDER   : pages/report/
 *********************************************************/

// Pastikan path koneksi benar (relatif terhadap home-admin.php)
include "dist/koneksi.php";

// Logic Hak Akses
$hak_akses = isset($_SESSION['hak_akses']) ? $_SESSION['hak_akses'] : '';
$kode_kantor_user = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$is_kepala = ($hak_akses == 'kepala');

// --- 1. DROPDOWN KANTOR (FILTER: HANYA KP, KANWIL, KC) ---
$opt_kantor = "";
if ($is_kepala) {
    // Kepala hanya lihat unit sendiri
    $qKantor = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor WHERE kode_kantor_detail = '$kode_kantor_user'");
} else {
    // Admin lihat KP, Kanwil, Cabang (Tanpa Kantor Kas)
    $qKantor = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor 
                                    FROM tb_kantor 
                                    WHERE level IN ('KP', 'KANWIL', 'KC') 
                                    ORDER BY kode_kantor_detail ASC");
}

while ($k = mysqli_fetch_assoc($qKantor)) {
    $sel = ($is_kepala) ? 'selected' : '';
    $opt_kantor .= "<option value='".$k['kode_kantor_detail']."' $sel>".$k['nama_kantor']."</option>";
}

// --- 2. DROPDOWN STATUS PEGAWAI ---
$opt_status = "";
$qStatus = mysqli_query($conn, "SELECT DISTINCT status_kepeg FROM tb_pegawai WHERE status_aktif=1 ORDER BY status_kepeg ASC");
while ($s = mysqli_fetch_assoc($qStatus)) {
    $opt_status .= "<option value='".$s['status_kepeg']."'>".$s['status_kepeg']."</option>";
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<style>
  /* Styling Modern */
  .card-modern { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); background: #fff; margin-bottom: 20px; }
  table.dataTable thead th { background-color: #fff; border-bottom: 2px solid #e2e8f0 !important; color: #64748b; font-size: 0.85rem; text-transform: uppercase; padding: 15px !important; vertical-align: middle !important; white-space: nowrap; }
  
  .input-modern { border-radius: 10px; border: 1px solid #e2e8f0; height: 45px; width: 100%; padding: 0 15px; background-color: #f8f9fa; }
  .select2-container--bootstrap-5 .select2-selection { border-color: #e2e8f0; background-color: #f8f9fa; border-radius: 10px; min-height: 45px; padding-top: 5px; }
  .label-filter { font-size: 0.75rem; font-weight: 700; color: #94a3b8; margin-bottom: 5px; text-transform: uppercase; display: block; }
  
  /* Tombol Excel */
  .btn-modern { border-radius: 10px; height: 45px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; border: none; cursor: pointer; padding: 0 20px; }
  .btn-success-modern { background: #10b981; color: white; transition: all 0.3s; }
  .btn-success-modern:hover { background: #059669; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
  
  /* Sembunyikan tombol default datatables */
  .dt-buttons { display: none !important; }
</style>

<section class="content-header pt-3 pb-2">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 style="font-weight: 800; color: #1e293b; margin-bottom: 0;">Laporan Nominatif</h1>
        <p class="text-muted mb-0">Data detail pegawai aktif per unit kerja</p>
      </div>
    </div>
  </div>
</section>

<section class="content mt-2">
  <div class="container-fluid">
    <div class="card card-modern">
      
      <div class="card-body border-bottom pb-3 pt-3">
        <div class="row align-items-end">
            
            <div class="col-lg-3 col-md-6 col-12 mb-2">
              <span class="label-filter">Status Pegawai</span>
              <select id="filter_status" class="form-control select2-status">
                <option value="">-- Semua Status --</option>
                <?= $opt_status ?>
              </select>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-2">
              <span class="label-filter">Kantor / Area</span>
              <select id="filter_unit" class="form-control input-modern" <?= $is_kepala ? 'disabled' : '' ?>>
                <option value="">-- Semua Kantor --</option>
                <?= $opt_kantor ?>
              </select>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-2">
              <span class="label-filter">Jabatan</span>
              <select id="filter_jabatan" class="form-control select2-jabatan" disabled>
                <option value="">-- Pilih Kantor Dulu --</option>
              </select>
            </div>

            <div class="col-lg-3 col-md-12 col-12 mb-2 text-right">
                <button type="button" id="directExportExcel" class="btn btn-modern btn-success-modern w-100">
                    <i class="fa fa-file-excel mr-2"></i> Download Excel
                </button>
            </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="nominatifTableAjax" class="table table-hover w-100 mb-0">
            <thead>
              <tr>
                <th width="5%" class="text-center">No</th>
                <th>Nama Pegawai</th>
                <th>NIP / NIK</th>
                <th>Jabatan</th>
                <th>Unit Kerja</th>
                <th>Status</th>
                <th>TMT Jabatan</th>
                <th>Pendidikan</th>
              </tr>
            </thead>
            <tbody>
                </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function () {
    
    // 1. Init Select2 (Tema Bootstrap 5)
    $('.select2-jabatan').select2({ theme: 'bootstrap-5', width: '100%', placeholder: "-- Pilih Jabatan --", allowClear: true });
    $('.select2-status').select2({ theme: 'bootstrap-5', width: '100%', placeholder: "-- Semua Status --", allowClear: true });

    // 2. Init DataTable AJAX
    var table = $('#nominatifTableAjax').DataTable({
        processing: true, 
        serverSide: true, // Penting: Server-side processing
        ajax: {
            url: "pages/report/ajax-nominatif-pegawai.php", // Backend Data
            type: "GET",
            data: function(d) {
                // Kirim nilai filter ke backend saat reload
                d.status_kepeg = $('#filter_status').val();
                d.unit_kerja   = $('#filter_unit').val();
                d.jabatan      = $('#filter_jabatan').val();
            }
        },
        columns: [
            { data: "no", className: "text-center font-weight-bold" },
            { data: "nama" },
            { data: "nip" },
            { data: "jabatan" },
            { data: "unit_kerja" },
            { data: "status", className: "text-center" },
            { data: "tmt" },
            { data: "pendidikan" }
        ],
        dom: 'Bfrtip',
        buttons: [], // Kita pakai tombol custom excel di atas
        language: {
            search: "", searchPlaceholder: "Cari nama, nip...", 
            processing: "<div class='spinner-border text-primary' role='status'><span class='sr-only'>Loading...</span></div>",
            zeroRecords: "Data tidak ditemukan",
            info: "Hal _PAGE_ dari _PAGES_"
        },
        pageLength: 10,
        order: [] 
    });

    // Styling Search Box
    $('.dataTables_filter input').addClass('form-control input-modern').css({'width':'250px'});

    // 3. LOGIC SMART FILTER (Kantor -> Jabatan)
    $('#filter_unit').on('change', function(){
        var kodeKantor = $(this).val();
        
        // Reset Jabatan & Reload Tabel
        $('#filter_jabatan').html('<option value="">-- Loading... --</option>').prop('disabled', true).trigger('change');
        table.ajax.reload();

        if(kodeKantor) {
            // Request Ajax untuk ambil Jabatan
            $.ajax({
                url: 'pages/report/ajax-get-options.php', // Helper File
                type: 'POST',
                data: { type: 'get_jabatan', kode_kantor: kodeKantor },
                success: function(resp) {
                    $('#filter_jabatan').html(resp).prop('disabled', false).trigger('change');
                }
            });
        } else {
            // Jika pilih "Semua Kantor"
            $('#filter_jabatan').html('<option value="">-- Pilih Kantor Dulu --</option>').prop('disabled', true).trigger('change');
        }
    });

    // Trigger otomatis jika user adalah Kepala (sudah ada value kantor)
    if($('#filter_unit').val()){ $('#filter_unit').trigger('change'); }

    // Logic Auto Reload untuk Status & Jabatan
    $('#filter_status, #filter_jabatan').on('change', function() { 
        table.ajax.reload(); 
    });

    // 4. TOMBOL EXCEL (Redirect ke file Export PHP)
    $('#directExportExcel').on('click', function() {
        // Ambil nilai filter saat ini
        var f_status = $('#filter_status').val();
        var f_unit   = $('#filter_unit').val();
        var f_jab    = $('#filter_jabatan').val();

        // Bangun URL dengan parameter GET
        var url = 'pages/report/export-nominatif-excel.php?status_kepeg=' + encodeURIComponent(f_status) + 
                  '&unit_kerja=' + encodeURIComponent(f_unit) + 
                  '&jabatan=' + encodeURIComponent(f_jab);
        
        // Buka di tab baru untuk download
        window.open(url, '_blank');
    });

  });
</script>