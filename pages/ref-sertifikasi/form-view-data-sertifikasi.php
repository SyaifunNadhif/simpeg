<?php
/*********************************************************
 * FILE    : pages/ref-sertifikasi/master-data-sertifikasi.php
 * MODULE  : View Sertifikasi (Clean UI like Diklat)
 *********************************************************/

if (session_id() == '') session_start();
include "dist/koneksi.php";

$hak_akses   = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$kode_kantor = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$is_kepala   = ($hak_akses == 'kepala');
$tahun_default = date('Y');

// --- QUERY DROPDOWN ---
$qTahun  = mysqli_query($conn, "SELECT DISTINCT YEAR(tgl_sertifikat) as th FROM tb_sertifikasi WHERE tgl_sertifikat IS NOT NULL AND tgl_sertifikat != '0000-00-00' ORDER BY th DESC");
$qSertif = mysqli_query($conn, "SELECT DISTINCT sertifikasi FROM tb_sertifikasi WHERE sertifikasi != '' ORDER BY sertifikasi ASC");
$qKantor = mysqli_query($conn, "SELECT * FROM tb_kantor WHERE level IN ('KC','KP') ORDER BY nama_kantor ASC");
?>

<style>
    /* --- STYLE UTAMA (SAMA DENGAN DIKLAT) --- */
    .content-wrapper { background-color: #f8f9fa; }
    
    .card-clean {
        border: 1px solid #e3e6f0;
        border-radius: 10px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.05);
        background: #fff;
    }

    .card-header-clean {
        background-color: #fff;
        border-bottom: 1px solid #f1f3f9;
        padding: 20px 25px;
        border-radius: 10px 10px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap; 
        gap: 15px;
    }

    /* TYPOGRAPHY JUDUL */
    .title-text { font-size: 1.25rem; font-weight: 700; color: #2e343a; margin: 0; }
    .subtitle-text { font-size: 0.85rem; color: #858796; margin-top: 4px; display: block; }

    /* TOMBOL BUTTONS (WARNA SESUAI GAMBAR) */
    .btn-custom-home { background: #fff; border: 1px solid #d1d3e2; color: #5a5c69; padding: 7px 12px; border-radius: 8px; }
    
    /* Hijau Tosca Modern (Import) */
    .btn-custom-import { background-color: #00C9A7; border: none; color: white; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; }
    .btn-custom-import:hover { background-color: #00b394; color: white; }

    /* Ungu Modern (Tambah) */
    .btn-custom-add { background-color: #5D5FEF; border: none; color: white; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; }
    .btn-custom-add:hover { background-color: #4a4cd4; color: white; }

    /* FORM INPUT */
    .label-filter { font-size: 0.7rem; font-weight: 700; color: #b7b9cc; text-transform: uppercase; margin-bottom: 5px; display: block; letter-spacing: 0.5px; }
    .form-control-clean { border-radius: 6px; height: 38px; border: 1px solid #d1d3e2; font-size: 0.85rem; color: #6e707e; }

    /* TABLE HEADERS */
    table.dataTable thead th {
        background-color: #fff; color: #5a5c69;
        font-weight: 700; font-size: 0.8rem; text-transform: uppercase;
        border-bottom: 2px solid #e3e6f0 !important; padding: 15px !important;
    }
    table.dataTable tbody td { padding: 12px 15px !important; vertical-align: middle; font-size: 0.9rem; color: #5a5c69; border-top: 1px solid #f1f3f9; }


    /* --- FIX UI DATATABLES (Show & Search 1 Baris) --- */
    div.dataTables_wrapper div.dataTables_length label {
        display: flex !important; align-items: center !important; white-space: nowrap !important;
        margin-bottom: 0 !important; font-weight: normal !important;
    }
    div.dataTables_wrapper div.dataTables_length select {
        width: 60px !important; margin: 0 8px !important; padding: 4px;
    }
    div.dataTables_wrapper div.dataTables_filter input {
        border-radius: 6px; border: 1px solid #d1d3e2; padding: 6px 12px; outline: none; margin-left: 0.5em; width: 200px;
    }

    /* --- MOBILE RESPONSIVE (Filter 1 Baris) --- */
    @media (max-width: 768px) {
        .card-header-clean { padding: 15px; flex-direction: column; align-items: flex-start; }
        .header-actions { width: 100%; margin-top: 15px; display: flex; gap: 8px; }
        .btn-custom-import, .btn-custom-add { flex: 1; text-align: center; font-size: 0.8rem; }

        /* Filter Grid (50:50) */
        .filter-grid-mobile { padding-right: 5px !important; }
        .filter-grid-mobile:last-child { padding-left: 5px !important; padding-right: 15px !important; }

        /* Search Full Width */
        div.dataTables_wrapper div.dataTables_filter { text-align: left !important; margin-top: 10px; }
        div.dataTables_wrapper div.dataTables_filter input { width: 100% !important; margin-left: 0 !important; }
    }
</style>

<div class="content pt-4 px-3">
    
    <div class="card card-clean mb-4">
        
        <div class="card-header-clean">
            <div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-certificate text-warning fa-lg mr-2"></i>
                    <h5 class="title-text">Daftar Sertifikasi Pegawai</h5>
                </div>
                <span class="subtitle-text pl-1">Menampilkan data kompetensi & sertifikat pegawai.</span>
            </div>
            
            <div class="header-actions">
                <a href="home-admin.php" class="btn btn-custom-home shadow-sm" title="Dashboard">
                    <i class="fa fa-home"></i>
                </a>
                <a href="home-admin.php?page=form-import-data-sertifikasi" class="btn btn-custom-import shadow-sm">
                    <i class="fas fa-file-excel mr-1"></i> Import
                </a>
                <a href="home-admin.php?page=form-master-data-sertifikasi" class="btn btn-custom-add shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah Data
                </a>
            </div>
        </div>

        <div class="card-body">
            
            <div class="row mb-3">
                
                <div class="col-6 col-md-2 mb-2 filter-grid-mobile">
                    <span class="label-filter">Tahun</span>
                    <select id="filter_tahun" class="form-control form-control-clean select2bs4">
                        <option value="">- Semua -</option>
                        <?php while ($t = mysqli_fetch_assoc($qTahun)) { ?>
                            <option value="<?= $t['th'] ?>" <?= ($tahun_default == $t['th']) ? 'selected' : '' ?>><?= $t['th'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-6 col-md-3 mb-2 filter-grid-mobile">
                    <span class="label-filter">Nama Sertifikasi</span>
                    <select id="filter_sertifikasi" class="form-control form-control-clean select2bs4">
                        <option value="">- Semua Sertifikasi -</option>
                        <?php while ($s = mysqli_fetch_assoc($qSertif)) { ?>
                            <option value="<?= $s['sertifikasi'] ?>"><?= $s['sertifikasi'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-2">
                    <span class="label-filter">Unit Kerja</span>
                    <select id="filter_kantor" class="form-control form-control-clean select2bs4" <?= $is_kepala ? 'disabled' : '' ?>>
                        <option value="">- Semua Kantor -</option>
                        <?php while ($k = mysqli_fetch_assoc($qKantor)) { ?>
                            <option value="<?= $k['kode_kantor_detail'] ?>" <?= ($kode_kantor == $k['kode_kantor_detail']) ? 'selected' : '' ?>>
                                <?= $k['nama_kantor'] ?>
                            </option>
                        <?php } ?>
                    </select>
                    <?php if($is_kepala): ?><input type="hidden" id="hidden_kantor" value="<?= $kode_kantor ?>"><?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table w-100" id="tabelSertifikasiAjax">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>Nama Pegawai</th>
                            <th>Info Sertifikasi</th>
                            <th>Penyelenggara / Tgl</th>
                            <th>Unit Kerja</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="8%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    var table = $('#tabelSertifikasiAjax').DataTable({
        "processing": true,
        "serverSide": true,
        "ordering": false,
        "ajax": {
            "url": "pages/ref-sertifikasi/ajax-data-sertifikasi.php", 
            "type": "GET",
            "data": function (d) {
                d.tahun       = $('#filter_tahun').val();
                d.sertifikasi = $('#filter_sertifikasi').val();
                var kantorVal = $('#filter_kantor').val();
                if(!kantorVal && $('#hidden_kantor').length) kantorVal = $('#hidden_kantor').val();
                d.kantor = kantorVal;
            }
        },
        "columns": [
            { "data": "no", "className": "text-center font-weight-bold" },
            { "data": "nama_peg" },
            { "data": "sertifikasi" },
            { "data": "penyelenggara" },
            { "data": "unit_kerja" },
            { "data": "status", "className": "text-center" },
            { "data": "aksi", "className": "text-center" }
        ],
        "language": {
            "search": "", 
            "searchPlaceholder": "Cari data...",
            "zeroRecords": "Tidak ada data",
            "lengthMenu": "Tampil _MENU_",
            "info": "_START_ - _END_ dari _TOTAL_",
            "processing": "<div class='spinner-border text-primary spinner-border-sm'></div> Memuat..."
        },
        // Layout Custom 1 Baris (Mobile Friendly)
        "dom": "<'row'<'col-6 col-md-6'l><'col-12 col-md-6'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });

    // Auto Filter
    $('#filter_tahun, #filter_sertifikasi, #filter_kantor').change(function(){
        table.ajax.reload();
    });
});
</script>