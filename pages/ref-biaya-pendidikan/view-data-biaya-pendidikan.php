<?php
/*********************************************************
 * FILE     : pages/ref-biaya-pendidikan/view-data-biaya-pendidikan.php
 * MODULE   : Biaya Pendidikan (Solid Buttons & Clean Modal)
 *********************************************************/

if (session_id() == '') session_start();
include "dist/koneksi.php"; 

// --- SECURITY ---
$hak_akses   = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$is_admin    = ($hak_akses == 'admin' || $hak_akses == 'superadmin');

// --- PREPARED DATA (Filter Tahun) ---
$qTahun  = mysqli_query($conn, "SELECT DISTINCT YEAR(tgl_pengembangan_sdm) as th FROM tb_biaya_pendidikan WHERE tgl_pengembangan_sdm IS NOT NULL AND tgl_pengembangan_sdm != '0000-00-00' ORDER BY th DESC");
?>

<link rel="stylesheet" href="plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">

<style>
    /* --- MODERN CARD STYLE --- */
    .content-wrapper { background-color: #f8f9fa; }
    
    .card-modern {
        border: none; border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        background: #fff; margin-bottom: 25px; overflow: hidden;
    }
    
    /* HEADER SECTION */
    .card-header-modern {
        padding: 25px 30px 20px 30px;
        background: #fff;
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;
    }
    .text-title { font-size: 1.3rem; font-weight: 800; color: #1f2937; margin: 0; line-height: 1.2; }
    .text-subtitle { font-size: 0.85rem; color: #6b7280; margin-top: 4px; display: block; font-weight: 500; }

    /* --- BUTTONS STYLE (CLEAN NO UNDERLINE) --- */
    a:hover { text-decoration: none !important; }

    /* Tombol Home Bulat Flat */
    .btn-circle-home { 
        width: 42px; height: 42px; border-radius: 50%; 
        display: inline-flex; align-items: center; justify-content: center; 
        color: #6b7280; background: #f3f4f6; 
        border: none; transition: .2s; font-size: 1.1rem;
        text-decoration: none !important;
    }
    .btn-circle-home:hover { background: #e5e7eb; color: #374151; }

    /* Tombol Import Solid Hijau */
    .btn-modern-solid-green {
        background: #10b981; color: white; border: none; 
        font-weight: 600; padding: 9px 24px; border-radius: 50px; font-size: 0.9rem;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); 
        display: inline-flex; align-items: center; transition: .2s;
        text-decoration: none !important;
    }
    .btn-modern-solid-green:hover { background: #059669; color: white; box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3); }

    /* Tombol Tambah Solid Ungu */
    .btn-modern-solid-purple {
        background: #6366f1; color: white; border: none; 
        font-weight: 600; padding: 9px 24px; border-radius: 50px; font-size: 0.9rem;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        display: inline-flex; align-items: center; transition: .2s;
        text-decoration: none !important;
    }
    .btn-modern-solid-purple:hover { background: #4f46e5; color: white; box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3); }

    /* --- TOOLBAR 1 BARIS (COMPACT) --- */
    .toolbar-compact {
        padding: 15px 30px;
        background: #f9fafb; 
        border-top: 1px solid #f3f4f6;
        border-bottom: 1px solid #f3f4f6;
        display: flex; align-items: center; gap: 15px; flex-wrap: wrap;
    }

    .filter-group { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px; }
    
    .select2-container .select2-selection--single {
        height: 40px !important; border-radius: 10px !important;
        border: 1px solid #e5e7eb !important; background: #fff;
        display: flex; align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px !important; }
    
    .search-pill { position: relative; width: 300px; }
    .search-pill input {
        width: 100%; height: 40px; padding-left: 40px; border-radius: 10px;
        border: 1px solid #e5e7eb; font-size: 0.9rem; transition: .2s;
    }
    .search-pill input:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
    .search-pill i {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: #9ca3af; font-size: 1rem;
    }

    /* TABLE */
    table.dataTable { width: 100% !important; margin: 0 !important; border-collapse: separate; border-spacing: 0; }
    table.dataTable thead th {
        background-color: #fff; color: #9ca3af; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;
        border-bottom: 2px solid #f3f4f6 !important; padding: 15px 20px;
    }
    table.dataTable tbody td {
        padding: 16px 20px; vertical-align: middle; border-bottom: 1px solid #f9fafb;
        color: #374151; font-size: 0.95rem;
    }
    table.dataTable.no-footer { border-bottom: none !important; }

    @media (max-width: 992px) {
        .toolbar-compact { flex-direction: column; align-items: stretch; }
        .search-pill, .filter-group { width: 100%; }
    }
</style>

<section class="content pt-4 px-3">
    <div class="card card-modern">
        
        <div class="card-header-modern">
            <div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-graduation-cap text-primary mr-2" style="font-size: 1.5rem;"></i>
                    <h5 class="text-title">Daftar Biaya Pendidikan</h5>
                </div>
                <span class="text-subtitle">Monitoring realisasi anggaran diklat & sertifikasi.</span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="home-admin.php" class="btn-circle-home shadow-sm mr-2" title="Dashboard">
                    <i class="fas fa-home"></i>
                </a>

                <?php if($is_admin): ?>
                    <a href="home-admin.php?page=form-upload-data-biaya-pendidikan" class="btn-modern-solid-green mr-2">
                        <i class="fas fa-file-excel mr-2"></i> Import
                    </a>
                    <a href="home-admin.php?page=form-biaya-pendidikan" class="btn-modern-solid-purple">
                        <i class="fas fa-plus mr-2"></i> Tambah
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="toolbar-compact">
            <div class="filter-group">
                <div style="flex: 1;">
                    <select id="filter_tahun" class="form-control select2">
                        <option value="">- Semua Tahun -</option>
                        <?php while ($t = mysqli_fetch_assoc($qTahun)) { $th = htmlspecialchars($t['th']); ?>
                            <option value="<?= $th ?>"><?= $th ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div style="flex: 1;">
                    <select id="filter_kuartal" class="form-control select2">
                        <option value="">- Semua Kuartal -</option>
                        <option value="1">Q1 (Jan-Mar)</option>
                        <option value="2">Q2 (Apr-Jun)</option>
                        <option value="3">Q3 (Jul-Sep)</option>
                        <option value="4">Q4 (Okt-Des)</option>
                    </select>
                </div>
            </div>

            <div class="d-none d-lg-block" style="flex: 1;"></div>

            <div class="search-pill">
                <i class="fas fa-search"></i>
                <input type="text" id="customSearch" placeholder="Cari Kegiatan, Pihak...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table w-100" id="tabelBiayaAjax">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="35%">Detail Kegiatan</th>
                        <th width="20%">Pihak Pelaksana</th>
                        <th width="15%">Waktu</th>
                        <th width="15%" class="text-right">Biaya & SDM</th>
                        <th class="text-center" width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</section>

<div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-trash-alt mr-2"></i> Konfirmasi Hapus</h5>
                <button type="button" class="close text-white btn-close-modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4">
                <p class="text-dark">Apakah Anda yakin ingin menghapus data pendidikan ini?</p>
                
                <div id="dataSummary" class="alert alert-secondary border-0 text-dark small p-3 rounded mb-4" style="background-color: #e9ecef;">
                    <i class="fas fa-spinner fa-spin text-primary"></i> Mengambil info data...
                </div>

                <div class="form-group">
                    <label class="font-weight-bold small text-uppercase text-secondary">Alasan Penghapusan <span class="text-danger">*</span></label>
                    <textarea id="deleteReason" class="form-control" rows="3" placeholder="Contoh: Duplikat, Salah Input..."></textarea>
                </div>
                
                <input type="hidden" id="deleteId">
            </div>

            <div class="modal-footer bg-white border-top-0 pt-0 pr-4 pb-4">
                <button type="button" class="btn btn-link text-secondary font-weight-bold btn-close-modal">Batal</button>
                <button type="button" class="btn btn-danger font-weight-bold shadow-sm px-4" id="btnConfirmDelete">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/select2/js/select2.full.min.js"></script>
<script src="plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    var table = $('#tabelBiayaAjax').DataTable({
        "processing": true, "serverSide": true, "ordering": false, "autoWidth": false,
        "ajax": {
            "url": "pages/ref-biaya-pendidikan/ajax-data-biaya-pendidikan.php", 
            "type": "GET",
            "data": function (d) {
                d.tahun   = $('#filter_tahun').val();
                d.kuartal = $('#filter_kuartal').val();
            }
        },
        "columns": [
            { "data": "no", "className": "text-center font-weight-bold" },
            { "data": "kegiatan" },
            { "data": "pelaksana" },
            { "data": "waktu" },
            { "data": "biaya", "className": "text-right" },
            { "data": "aksi", "className": "text-center" }
        ],
        "language": {
            "zeroRecords": "Tidak ada data ditemukan",
            "info": "Menampilkan _START_ sd _END_ dari _TOTAL_",
            "infoEmpty": "0 data",
            "processing": "<span class='spinner-border spinner-border-sm text-primary'></span> Loading...",
            "paginate": { "next": ">", "previous": "<" }
        },
        "dom": 't<"d-flex align-items-center justify-content-between flex-wrap p-3 bg-white border-top"lip>',
        "drawCallback": function(settings) {
            var isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
            if (!isAdmin) { $('.btn-delete').remove(); }
        }
    });

    $('#filter_tahun, #filter_kuartal').change(function(){ table.ajax.reload(); });

    var timer;
    $('#customSearch').on('keyup', function(){
        clearTimeout(timer);
        var val = this.value;
        timer = setTimeout(function() { table.search(val).draw(); }, 500);
    });

    // --- LOGIC MODAL HAPUS ---
    $('body').on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        $('#deleteId').val(id);
        $('#deleteReason').val(''); // Reset alasan
        $('#dataSummary').html('<i class="fas fa-spinner fa-spin text-primary"></i> Mengambil info data...');
        
        $('#modalHapus').modal('show');

        // Ambil info data via AJAX (Penting buat UX)
        $.ajax({
            url: 'pages/ref-biaya-pendidikan/prosess-delete-biaya.php',
            type: 'POST',
            data: { action: 'get_info', id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status == 'success') {
                    // Tampilkan Info di Box Abu-abu
                    $('#dataSummary').html(
                        '<strong>' + res.data.kegiatan + '</strong><br>' +
                        '<small class="text-muted">Biaya: ' + res.data.biaya + ' | Pihak: ' + res.data.pihak + '</small>'
                    );
                } else {
                    $('#dataSummary').html('<span class="text-danger">' + res.message + '</span>');
                }
            },
            error: function() { $('#dataSummary').html('<span class="text-danger">Gagal load data.</span>'); }
        });
    });

    $('.btn-close-modal').click(function(){ $('#modalHapus').modal('hide'); });

    $('#btnConfirmDelete').click(function() {
        var id = $('#deleteId').val();
        var reason = $.trim($('#deleteReason').val());

        if(reason == '') {
            Swal.fire({icon: 'warning', title: 'Wajib Diisi', text: 'Mohon isi alasan penghapusan!'});
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).text('Menghapus...');

        $.ajax({
            url: 'pages/ref-biaya-pendidikan/prosess-delete-biaya.php',
            type: 'POST', 
            data: { action: 'delete', id: id, reason: reason }, 
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).text('Ya, Hapus');
                $('#modalHapus').modal('hide');
                
                if (res.status == 'success') {
                    Swal.fire({icon: 'success', title: 'Terhapus', text: 'Data berhasil dihapus & dibackup.', timer: 1500, showConfirmButton: false});
                    table.ajax.reload(null, false);
                } else { Swal.fire('Gagal', res.message, 'error'); }
            }, error: function() { 
                btn.prop('disabled', false).text('Ya, Hapus');
                Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
            }
        });
    });
});
</script>