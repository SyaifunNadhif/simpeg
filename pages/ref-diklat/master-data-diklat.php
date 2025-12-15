<?php
/*********************************************************
 * FILE    : pages/diklat/master-data-diklat.php
 * UPDATE  : Tampilan Mobile "1 Baris" & Fix Bug UI
 *********************************************************/

// Session & Koneksi
if (session_id() == '') session_start();
include "dist/koneksi.php";

// Hak Akses
$hak_akses   = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$kode_kantor = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$is_kepala   = ($hak_akses == 'kepala');

// Filter Default
$tahun_default = date('Y');

// Query Dropdown Awal
$qTahun  = mysqli_query($conn, "SELECT DISTINCT tahun FROM tb_diklat WHERE tahun != '' ORDER BY tahun DESC");
$qDiklat = mysqli_query($conn, "SELECT DISTINCT diklat FROM tb_diklat WHERE tahun = '$tahun_default' ORDER BY diklat ASC");
$qKantor = mysqli_query($conn, "SELECT * FROM tb_kantor WHERE level IN ('KC','KP') ORDER BY nama_kantor ASC");
?>

<style>
    /* --- 1. BASE STYLE (DESKTOP) --- */
    .content-wrapper { background-color: #f8f9fa; }
    
    .card-clean {
        border: 1px solid #e3e6f0;
        border-radius: 12px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.05);
        background: #fff;
    }

    .card-header-clean {
        background-color: #fff;
        border-bottom: 1px solid #f1f3f9;
        padding: 20px 25px;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap; 
        gap: 15px;
    }

    /* TYPOGRAPHY */
    .title-text { font-size: 1.25rem; font-weight: 700; color: #2e343a; margin: 0; }
    .subtitle-text { font-size: 0.85rem; color: #858796; margin-top: 4px; display: block; }

    /* TOMBOL (Sesuai Referensi Gambar) */
    .btn-custom-home { background: #fff; border: 1px solid #d1d3e2; color: #5a5c69; padding: 7px 12px; border-radius: 8px; }
    .btn-custom-import { background-color: #1cc88a; /* Hijau seperti gambar */ border: none; color: white; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; }
    .btn-custom-add { background-color: #4e73df; /* Biru/Ungu seperti gambar */ border: none; color: white; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; }
    .btn-custom-import:hover { background-color: #17a673; color: white; }
    .btn-custom-add:hover { background-color: #2e59d9; color: white; }

    /* FORM INPUT */
    .label-filter { font-size: 0.7rem; font-weight: 700; color: #b7b9cc; text-transform: uppercase; margin-bottom: 5px; display: block; letter-spacing: 0.5px; }
    .form-control-clean { border-radius: 6px; height: 38px; border: 1px solid #d1d3e2; font-size: 0.85rem; color: #6e707e; }

    /* TABLE STYLE */
    table.dataTable thead th {
        background-color: #fff; color: #5a5c69;
        font-weight: 700; font-size: 0.8rem; text-transform: uppercase;
        border-bottom: 2px solid #e3e6f0 !important; padding: 15px !important;
    }
    table.dataTable tbody td { padding: 12px 15px !important; vertical-align: middle; font-size: 0.9rem; color: #5a5c69; border-top: 1px solid #f1f3f9; }


    /* --- 2. FIX UI DATATABLES (SHOW ENTRIES & SEARCH) --- */
    
    /* Wrapper agar kontrol tabel rapi */
    .dataTables_wrapper .row:first-child {
        align-items: center;
        margin-bottom: 10px;
        padding: 0 5px;
    }

    /* Show entries: Paksa sebaris */
    div.dataTables_wrapper div.dataTables_length label {
        font-weight: normal;
        text-align: left;
        white-space: nowrap;
        margin-bottom: 0;
        display: flex;
        align-items: center;
    }
    div.dataTables_wrapper div.dataTables_length select {
        width: 60px;
        margin: 0 8px;
        border-radius: 4px;
        border: 1px solid #d1d3e2;
        padding: 4px;
    }

    /* Search Box styling */
    div.dataTables_wrapper div.dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #d1d3e2;
        padding: 6px 12px;
        outline: none;
        margin-left: 0.5em;
        width: 200px; /* Default desktop */
    }


    /* --- 3. MOBILE RESPONSIVE (INI SOLUSINYA) --- */
    @media (max-width: 768px) {
        
        /* HEADER: Tombol Full Width & Stack */
        .card-header-clean {
            padding: 15px;
            flex-direction: column;
            align-items: flex-start;
        }
        .header-actions {
            width: 100%;
            margin-top: 15px;
            display: flex;
            gap: 8px;
        }
        .btn-custom-import, .btn-custom-add {
            flex: 1; /* Tombol berbagi rata lebar */
            text-align: center;
            font-size: 0.8rem;
        }

        /* GRID FILTER: 1 BARIS (50:50) */
        /* Mengatur padding agar tidak terlalu mepet saat bersebelahan */
        .filter-grid-mobile {
            padding-right: 5px !important;
        }
        .filter-grid-mobile:last-child {
            padding-left: 5px !important;
            padding-right: 15px !important;
        }

        /* SEARCH & SHOW ENTRIES Mobile */
        div.dataTables_wrapper div.dataTables_filter {
            text-align: left !important;
            margin-top: 10px;
        }
        div.dataTables_wrapper div.dataTables_filter input {
            width: 100% !important; /* Search box full width */
            margin-left: 0 !important;
            display: block;
        }
        div.dataTables_wrapper div.dataTables_length {
            text-align: left !important;
        }
    }
</style>

<div class="content pt-4 px-3">
    
    <div class="card card-clean mb-4">
        
        <div class="card-header-clean">
            <div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-graduation-cap text-primary fa-lg mr-2"></i>
                    <h5 class="title-text">Daftar Pelatihan & Diklat</h5>
                </div>
                <span class="subtitle-text pl-1">Menampilkan seluruh data riwayat pelatihan pegawai.</span>
            </div>
            
            <div class="header-actions">
                <a href="home-admin.php" class="btn btn-custom-home shadow-sm" title="Dashboard">
                    <i class="fa fa-home"></i>
                </a>
                <a href="home-admin.php?page=form-import-data-diklat" class="btn btn-custom-import shadow-sm">
                    <i class="fas fa-file-excel mr-1"></i> Import
                </a>
                <a href="home-admin.php?page=form-diklat" class="btn btn-custom-add shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah Data
                </a>
            </div>
        </div>

        <div class="card-body">
            
            <div class="row mb-3">
                
                <div class="col-6 col-md-2 mb-2 filter-grid-mobile">
                    <span class="label-filter">Tahun</span>
                    <select id="filter_tahun" class="form-control form-control-clean select2bs4">
                        <?php while ($t = mysqli_fetch_assoc($qTahun)) { ?>
                            <option value="<?= $t['tahun'] ?>" <?= ($tahun_default == $t['tahun']) ? 'selected' : '' ?>><?= $t['tahun'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-6 col-md-3 mb-2 filter-grid-mobile">
                    <span class="label-filter">Jenis Diklat</span>
                    <select id="filter_diklat" class="form-control form-control-clean select2bs4">
                        <option value="">- Semua Jenis -</option>
                        <?php while ($d = mysqli_fetch_assoc($qDiklat)) { ?>
                            <option value="<?= $d['diklat'] ?>"><?= $d['diklat'] ?></option>
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
                <table class="table w-100" id="tabelDiklatAjax">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>Nama Pegawai</th>
                            <th>Jenis Diklat</th>
                            <th>Penyelenggara</th>
                            <th>Unit Kerja</th>
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

    var table = $('#tabelDiklatAjax').DataTable({
        "processing": true,
        "serverSide": true,
        "ordering": false,
        "ajax": {
            "url": "pages/ref-diklat/ajax-data-diklat.php",
            "type": "GET",
            "data": function (d) {
                d.tahun  = $('#filter_tahun').val();
                d.diklat = $('#filter_diklat').val();
                var kantorVal = $('#filter_kantor').val();
                if(!kantorVal && $('#hidden_kantor').length) kantorVal = $('#hidden_kantor').val();
                d.kantor = kantorVal;
            }
        },
        "columns": [
            { "data": "no", "className": "text-center font-weight-bold" },
            { "data": "nama_peg" },
            { "data": "diklat" },
            { "data": "penyelenggara" },
            { "data": "unit_kerja" },
            { "data": "aksi", "className": "text-center" }
        ],
        "language": {
            "search": "", 
            "searchPlaceholder": "Cari data...",
            "zeroRecords": "Data tidak ditemukan",
            "lengthMenu": "Tampil _MENU_", // Teks pendek agar muat di HP
            "info": "_START_ - _END_ dari _TOTAL_",
            "processing": "<div class='spinner-border text-primary' role='status'><span class='sr-only'>...</span></div>"
        },
        // Layout Custom untuk Mobile Responsive
        "dom": "<'row'<'col-6 col-md-6'l><'col-12 col-md-6'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });

    // Auto Filter
    $('#filter_diklat, #filter_kantor').change(function(){ table.ajax.reload(); });

    // Dinamis Tahun -> Diklat
    $('#filter_tahun').change(function(){
        var tahunDipilih = $(this).val();
        $('#filter_diklat').prop('disabled', true).html('<option>Loading...</option>');
        $.ajax({
            url: 'pages/ref-diklat/ajax-get-jenis.php',
            type: 'POST',
            data: { tahun: tahunDipilih },
            success: function(response){
                $('#filter_diklat').html(response).prop('disabled', false);
                table.ajax.reload();
            }
        });
    });
});
</script>