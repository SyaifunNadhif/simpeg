<?php
/*********************************************************
 * FILE     : pages/ref-pendidikan/form-view-data-pendidikan.php
 * MODULE   : View Pendidikan (Secure, Offline, Matching Ajax)
 *********************************************************/

if (session_id() == '') session_start();
include "dist/koneksi.php";

// --- 1. SECURITY & SESSION ---
$hak_akses   = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$kode_kantor = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';

$is_admin    = ($hak_akses == 'admin' || $hak_akses == 'superadmin');
$is_kepala   = ($hak_akses == 'kepala');

// --- 2. DROPDOWN DATA (Filter) ---
// Filter Tahun Lulus (th_lulus)
$qTahun   = mysqli_query($conn, "SELECT DISTINCT th_lulus FROM tb_pendidikan WHERE th_lulus IS NOT NULL AND th_lulus != '0000' ORDER BY th_lulus DESC");
// Filter Jenjang (jenjang)
$qJenjang = mysqli_query($conn, "SELECT DISTINCT jenjang FROM tb_pendidikan WHERE jenjang != '' ORDER BY jenjang ASC");
// Filter Kantor
$qKantor  = mysqli_query($conn, "SELECT * FROM tb_kantor WHERE level IN ('KC','KP') ORDER BY nama_kantor ASC");
?>

<link rel="stylesheet" href="plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">

<style>
    /* CSS MODERN (Standardized) */
    .content-wrapper { background-color: #f8f9fa; font-family: sans-serif; }
    .card-modern { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: #fff; margin-bottom: 25px; }
    .card-header-modern { padding: 20px 30px; background: #fff; border-bottom: 1px solid #f1f5f9; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    
    .filter-label { font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px; display: block; }
    .select2-container .select2-selection--single { height: 40px !important; border-radius: 8px !important; border: 1px solid #e2e8f0 !important; display: flex; align-items: center; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px !important; }
    
    table.dataTable thead th { background-color: #fff; color: #64748b; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; border-bottom: 2px solid #f1f5f9 !important; padding: 15px 20px; }
    table.dataTable tbody td { padding: 15px 20px; vertical-align: middle; border-bottom: 1px solid #f8fafc; color: #334155; font-size: 0.9rem; }
    
    .text-title { font-size: 1.25rem; font-weight: 800; color: #1e293b; margin: 0; }
    .text-subtitle { font-size: 0.85rem; color: #64748b; margin-top: 2px; display: block; }
    
    .btn-action-rounded { border-radius: 50px; padding: 8px 20px; font-weight: 600; font-size: 0.85rem; }
    .btn-circle-home { width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #64748b; background: #fff; border: 1px solid #e2e8f0; transition: all 0.2s; }
    .btn-circle-home:hover { background: #f1f5f9; color: #0ea5e9; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    
    @media (max-width: 768px) {
        .card-header-modern { flex-direction: column; align-items: flex-start; }
        .header-actions { width: 100%; display: flex; gap: 10px; margin-top: 10px; justify-content: space-between; }
        .btn-action-rounded { flex: 1; }
        .btn-circle-home { width: 100%; border-radius: 8px; }
    }
</style>

<section class="content pt-4 px-3">
    <div class="card card-modern">
        
        <div class="card-header-modern">
            <div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-graduation-cap text-success fa-lg mr-2"></i>
                    <h5 class="text-title">Daftar Pendidikan</h5>
                </div>
                <span class="text-subtitle pl-1">Monitoring riwayat pendidikan formal pegawai.</span>
            </div>
            
            <div class="header-actions">
                <a href="home-admin.php" class="btn btn-circle-home shadow-sm mr-2" title="Dashboard">
                    <i class="fa fa-home"></i>
                </a>
                
                <?php if($is_admin): ?>
                <a href="home-admin.php?page=form-import-data-pendidikan" class="btn btn-outline-success btn-action-rounded shadow-sm">
                    <i class="fas fa-file-excel mr-1"></i> Import
                </a>
                <a href="home-admin.php?page=form-master-data-pendidikan" class="btn btn-primary btn-action-rounded shadow-sm" style="background-color: #5D5FEF; border-color: #5D5FEF;">
                    <i class="fas fa-plus mr-1"></i> Tambah
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-4 bg-light rounded p-3 mx-1 border border-light">
                <div class="col-12 col-md-2 mb-2">
                    <span class="filter-label">Tahun Lulus</span>
                    <select id="filter_tahun" class="form-control select2">
                        <option value="">- Semua -</option>
                        <?php while ($t = mysqli_fetch_assoc($qTahun)) { 
                            $th = htmlspecialchars($t['th_lulus']); ?>
                            <option value="<?= $th ?>"><?= $th ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <span class="filter-label">Jenjang</span>
                    <select id="filter_jenjang" class="form-control select2">
                        <option value="">- Semua Jenjang -</option>
                        <?php while ($j = mysqli_fetch_assoc($qJenjang)) { 
                            $jen = htmlspecialchars($j['jenjang']); ?>
                            <option value="<?= $jen ?>"><?= $jen ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-2">
                    <span class="filter-label">Unit Kerja</span>
                    <select id="filter_kantor" class="form-control select2" <?= $is_kepala ? 'disabled' : '' ?>>
                        <option value="">- Semua Kantor -</option>
                        <?php while ($k = mysqli_fetch_assoc($qKantor)) { 
                            $kd = htmlspecialchars($k['kode_kantor_detail']);
                            $nm = htmlspecialchars($k['nama_kantor']); ?>
                            <option value="<?= $kd ?>" <?= ($kode_kantor == $kd) ? 'selected' : '' ?>><?= $nm ?></option>
                        <?php } ?>
                    </select>
                    <?php if($is_kepala): ?><input type="hidden" id="hidden_kantor" value="<?= htmlspecialchars($kode_kantor) ?>"><?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table w-100" id="tabelPendidikanAjax">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>Pegawai</th>
                            <th>Jenjang</th>
                            <th>Nama Sekolah / Univ</th>
                            <th>Jurusan</th>
                            <th class="text-center">Thn Lulus</th>
                            <th class="text-center" width="8%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg rounded-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-trash-alt mr-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="close text-white btn-close-modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted">Apakah Anda yakin ingin menghapus data pendidikan ini?</p>
                <div id="dataSummary" class="alert alert-secondary border-0 small">
                    <i class="fas fa-spinner fa-spin text-primary"></i> Mengambil info data...
                </div>
                <div class="form-group mt-3">
                    <label class="font-weight-bold small text-uppercase text-secondary">Alasan Penghapusan <span class="text-danger">*</span></label>
                    <textarea id="deleteReason" class="form-control" rows="3" placeholder="Contoh: Duplikat, Salah Input..."></textarea>
                </div>
                <input type="hidden" id="deleteId">
            </div>
            <div class="modal-footer bg-light">
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
    
    // 1. Init Select2
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    // 2. Init DataTable (SESUAI JSON KEY DI AJAX TERAKHIR)
    var table = $('#tabelPendidikanAjax').DataTable({
        "processing": true,
        "serverSide": true,
        "ordering": false,
        "autoWidth": false,
        "ajax": {
            "url": "pages/ref-pendidikan/ajax-data-pendidikan.php",
            "type": "GET",
            "data": function (d) {
                // Mengirim Parameter Filter ke Backend
                d.tahun   = $('#filter_tahun').val();
                d.jenjang = $('#filter_jenjang').val();
                var kantorVal = $('#filter_kantor').val();
                if(!kantorVal && $('#hidden_kantor').length) kantorVal = $('#hidden_kantor').val();
                d.kantor = kantorVal;
            }
        },
        "columns": [
            { "data": "no", "className": "text-center font-weight-bold" },
            { "data": "idpeg_nama" },    // HTML (Nama + NIP)
            { "data": "jenjang", "className": "font-weight-bold text-primary" }, 
            { "data": "nama_sekolah" },
            { "data": "jurusan" },
            { "data": "th_lulus", "className": "text-center" },
            { "data": "aksi", "className": "text-center" } // Tombol Edit/Hapus
        ],
        "language": {
            "search": "", 
            "searchPlaceholder": "Cari Nama / Sekolah...",
            "zeroRecords": "Tidak ada data ditemukan",
            "info": "Menampilkan _START_ - _END_ dari _TOTAL_",
            "processing": "<div class='spinner-border text-primary spinner-border-sm'></div> Memuat...",
            "paginate": { "next": '<i class="fas fa-chevron-right"></i>', "previous": '<i class="fas fa-chevron-left"></i>' }
        },
        "dom": '<"d-flex justify-content-between align-items-center p-3"lf>rt<"d-flex justify-content-between align-items-center p-3"ip>',
        
        "drawCallback": function(settings) {
            // Sembunyikan tombol hapus jika bukan admin
            var isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
            if (!isAdmin) { $('.btn-delete').remove(); }
        }
    });

    // 3. Trigger Reload saat Filter Berubah
    $('#filter_tahun, #filter_jenjang, #filter_kantor').change(function(){
        table.ajax.reload();
    });

    // 4. Logic Hapus (Soft Delete Modal)
    $('body').on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        $('#deleteId').val(id);
        $('#deleteReason').val('');
        $('#dataSummary').html('<i class="fas fa-spinner fa-spin text-primary"></i> Sedang mengambil info data...');
        $('#modalHapus').modal('show');

        // Ambil info data untuk ditampilkan di modal (Preview)
        $.ajax({
            url: 'pages/ref-pendidikan/process_soft_delete.php', 
            type: 'POST',
            data: { action: 'get_info', id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status == 'success') {
                    $('#dataSummary').html(
                        '<strong>' + res.data.jenjang + ' ' + res.data.jurusan + '</strong><br>' +
                        '<small class="text-muted">' + res.data.nama_peg + ' | ' + res.data.nama_sekolah + '</small>'
                    ).removeClass('alert-danger').addClass('alert-secondary');
                } else {
                    $('#dataSummary').html('<span class="text-danger">Gagal ambil data: ' + res.message + '</span>').addClass('alert-danger');
                }
            },
            error: function() { $('#dataSummary').html('<span class="text-danger">Koneksi Gagal.</span>'); }
        });
    });

    // 5. Tombol Close Modal & Confirm
    $('body').on('click', '.btn-close-modal', function() { $('#modalHapus').modal('hide'); });

    $('#btnConfirmDelete').click(function() {
        var id = $('#deleteId').val();
        var reason = $.trim($('#deleteReason').val());

        if (reason == '') {
            Swal.fire({ title: 'Wajib Diisi', text: 'Mohon isi alasan penghapusan!', icon: 'warning', confirmButtonColor: '#d33' });
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).text('Menghapus...');

        $.ajax({
            url: 'pages/ref-pendidikan/process_soft_delete.php',
            type: 'POST',
            data: { action: 'delete', id: id, reason: reason },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).text('Ya, Hapus');
                $('#modalHapus').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                if (res.status == 'success') {
                    Swal.fire({ icon: 'success', title: 'Terhapus', text: 'Data dipindahkan ke Recycle Bin', timer: 1500, showConfirmButton: false });
                    table.ajax.reload(null, false);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false).text('Ya, Hapus');
                Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
            }
        });
    });
});
</script>