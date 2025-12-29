<?php
/*********************************************************
 * FILE    : pages/report/laporan-nominatif-pegawai.php
 * MODULE  : View Laporan (White Header Text Fixed)
 *********************************************************/

include "dist/koneksi.php";

// --- LOGIC PHP ---
$hak_akses = isset($_SESSION['hak_akses']) ? $_SESSION['hak_akses'] : '';
$kode_kantor_user = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$is_kepala = ($hak_akses == 'kepala');

// 1. Opsi Kantor
$opt_kantor = "";
if ($is_kepala) {
    $qKantor = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor WHERE kode_kantor_detail = '$kode_kantor_user'");
} else {
    $qKantor = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor WHERE level IN ('KP', 'KANWIL', 'KC') ORDER BY kode_kantor_detail ASC");
}
while ($k = mysqli_fetch_assoc($qKantor)) {
    $sel = ($is_kepala) ? 'selected' : '';
    $opt_kantor .= "<option value='".$k['kode_kantor_detail']."' $sel>".$k['nama_kantor']."</option>";
}

// 2. Opsi Status
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
    /* Global Style */
    body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }

    /* Card Styling */
    .card-modern {
        border: none;
        border-radius: 12px;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
        background: #fff;
        overflow: hidden;
    }

    /* Filter Label */
    .filter-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #6c757d;
        margin-bottom: 5px;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Select2 Height Fix */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        padding-top: 5px;
        border-radius: 6px;
        border: 1px solid #ced4da;
    }
    
    /* Button Styles */
    .btn-action {
        height: 38px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border: none;
        padding: 0 20px;
        color: white !important;
        width: 100%;
        background-color: #10b981 !important; /* Hijau Excel */
    }
    
    .btn-action:hover {
        background-color: #059669 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    /* --- TABLE STYLING FIX (HEADER PUTIH) --- */
    .table-responsive { padding: 0; }
    
    table.dataTable thead th {
        background: linear-gradient(45deg, #1e3c72, #2a5298); /* Background Biru */
        color: #ffffff !important; /* TEKS PUTIH WAJIB */
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        border: none;
        padding: 12px 15px !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }
    
    table.dataTable tbody td {
        padding: 10px 15px !important;
        vertical-align: middle;
        color: #333;
        border-bottom: 1px solid #eee;
        font-size: 0.9rem;
    }
    
    table.dataTable tbody tr:hover {
        background-color: #f8faff;
    }

    /* Search Box Custom */
    .dataTables_filter input {
        border-radius: 20px;
        padding: 5px 15px;
        border: 1px solid #ddd;
        height: 35px;
        width: 250px !important;
        margin-left: 10px;
    }
</style>

<section class="content-header pt-4 pb-3">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 style="font-weight: 800; color: #1e293b;">Laporan Nominatif Pegawai</h1>
                <p class="text-muted small mb-0">Rekapitulasi data pegawai aktif per unit kerja</p>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-modern">
            
            <div class="card-body bg-white border-bottom pb-4 pt-4">
                <div class="row align-items-end">
                    
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <span class="filter-label">STATUS PEGAWAI</span>
                        <select id="filter_status" class="form-control select2-status">
                            <option value="">-- Semua Status --</option>
                            <?= $opt_status ?>
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <span class="filter-label">KANTOR CABANG</span>
                        <select id="filter_unit" class="form-control select2-status" <?= $is_kepala ? 'disabled' : '' ?>>
                            <option value="">-- Semua Kantor --</option>
                            <?= $opt_kantor ?>
                        </select>
                    </div>

                    <div class="col-md-4 col-sm-6 mb-3 mb-md-0">
                        <span class="filter-label">JABATAN</span>
                        <select id="filter_jabatan" class="form-control select2-jabatan" disabled>
                            <option value="">-- Pilih Kantor Terlebih Dahulu --</option>
                        </select>
                    </div>

                    <div class="col-md-2 col-sm-6">
                        <span class="filter-label" style="opacity: 0;">ACTION</span>
                        <button type="button" id="directExportExcel" class="btn-action">
                            <i class="fas fa-file-excel mr-2"></i> Export Excel
                        </button>
                    </div>

                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="nominatifTableAjax" class="table w-100 mb-0" style="width: 100%;">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">NO</th>
                                <th>NAMA PEGAWAI</th>
                                <th>NIP / NIK</th>
                                <th>JABATAN</th>
                                <th>UNIT KERJA</th>
                                <th class="text-center">STATUS</th>
                                <th class="text-center">TMT JABATAN</th>
                                <th>PENDIDIKAN</th>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function () {
    
    // 1. Setup Select2
    $('.select2-status').select2({ theme: 'bootstrap-5', width: '100%' });
    $('.select2-jabatan').select2({ theme: 'bootstrap-5', width: '100%', placeholder: "-- Pilih Jabatan --" });

    // 2. Setup DataTable AJAX
    var table = $('#nominatifTableAjax').DataTable({
        processing: true, 
        serverSide: true,
        ajax: {
            url: "pages/report/ajax-nominatif-pegawai.php",
            type: "GET",
            data: function(d) {
                d.status_kepeg = $('#filter_status').val();
                d.unit_kerja   = $('#filter_unit').val();
                d.jabatan      = $('#filter_jabatan').val();
            }
        },
        columns: [
            { data: "no", className: "text-center" },
            { data: "nama", className: "font-weight-bold text-dark" },
            { data: "nip" },
            { data: "jabatan", className: "text-primary font-weight-bold" },
            { data: "unit_kerja" },
            { data: "status", className: "text-center" }, // Status Plain Text
            { data: "tmt", className: "text-center" },
            { data: "pendidikan" }
        ],
        language: {
            search: "",
            searchPlaceholder: "Cari Nama / NIP...",
            lengthMenu: "Tampil _MENU_ baris",
            processing: '<div class="spinner-border text-primary text-sm" role="status"><span class="sr-only">Loading...</span></div> Memuat Data...',
            zeroRecords: "<div class='text-center py-5 text-muted'><i class='fas fa-search mb-3' style='font-size:3rem; opacity:0.3'></i><br>Data tidak ditemukan</div>",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            paginate: { first: "«", last: "»", next: "›", previous: "‹" }
        },
        pageLength: 10,
        dom: "<'row p-3 align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row p-3 align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        order: []
    });

    // 3. Logic Filter Bertingkat (Unit -> Jabatan)
    $('#filter_unit').on('change', function(){
        var kodeKantor = $(this).val();
        
        // Reset dropdown jabatan
        $('#filter_jabatan').html('<option value="">-- Memuat... --</option>').prop('disabled', true).trigger('change');
        
        // Reload tabel
        table.ajax.reload();

        if(kodeKantor) {
            $.ajax({
                url: 'pages/report/ajax-get-options.php', 
                type: 'POST',
                data: { type: 'get_jabatan', kode_kantor: kodeKantor },
                success: function(resp) {
                    $('#filter_jabatan').html(resp).prop('disabled', false).trigger('change');
                },
                error: function() {
                    $('#filter_jabatan').html('<option value="">Gagal memuat jabatan</option>');
                }
            });
        } else {
            $('#filter_jabatan').html('<option value="">-- Pilih Kantor Terlebih Dahulu --</option>').prop('disabled', true).trigger('change');
        }
    });

    // Auto-trigger jika user kepala
    if($('#filter_unit').val()){ $('#filter_unit').trigger('change'); }

    // Reload tabel saat filter lain berubah
    $('#filter_status, #filter_jabatan').on('change', function() { 
        table.ajax.reload(); 
    });

    // 4. Tombol Excel
    $('#directExportExcel').on('click', function() {
        var p_status = $('#filter_status').val();
        var p_unit   = $('#filter_unit').val();
        var p_jab    = $('#filter_jabatan').val();

        var exportUrl = 'pages/report/export-nominatif-excel.php?status_kepeg=' + encodeURIComponent(p_status) + 
                        '&unit_kerja=' + encodeURIComponent(p_unit) + 
                        '&jabatan=' + encodeURIComponent(p_jab);
        
        window.open(exportUrl, '_blank');
    });

  });
</script>