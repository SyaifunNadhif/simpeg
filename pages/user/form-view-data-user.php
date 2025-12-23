<?php
/*********************************************************
 * FILE     : pages/user/form-view-data-user.php
 * MODULE   : Manajemen User (Secure & Modern UI)
 *********************************************************/

if (session_id() == '') session_start();
include "dist/koneksi.php";

// Hak Akses Check
if (!isset($_SESSION['hak_akses']) || ($_SESSION['hak_akses'] != 'admin' && $_SESSION['hak_akses'] != 'superadmin')) {
    echo "<script>window.location='home-admin.php';</script>";
    exit;
}
?>

<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">

<style>
    .card-modern { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: #fff; margin-bottom: 25px; }
    .card-header-modern { padding: 20px 30px; background: #fff; border-bottom: 1px solid #f1f5f9; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    
    .btn-action-rounded { border-radius: 50px; padding: 8px 20px; font-weight: 600; font-size: 0.85rem; }
    table.dataTable thead th { background-color: #fff; color: #64748b; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; border-bottom: 2px solid #f1f5f9 !important; padding: 15px 20px; }
    table.dataTable tbody td { padding: 15px 20px; vertical-align: middle; border-bottom: 1px solid #f8fafc; color: #334155; font-size: 0.9rem; }
</style>

<section class="content pt-4 px-3">
    <div class="card card-modern">
        
        <div class="card-header-modern">
            <div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-users-cog text-primary fa-lg mr-2"></i>
                    <h5 class="mb-0 font-weight-bold text-dark">Manajemen User</h5>
                </div>
                <small class="text-muted ml-1">Kelola akun akses sistem pegawai.</small>
            </div>
            
            <div>
                <a href="home-admin.php?page=form-master-data-user&mode=create" class="btn btn-primary btn-action-rounded shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah User
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <select id="filter_role" class="form-control">
                        <option value="">- Semua Role -</option>
                        <option value="admin">Admin</option>
                        <option value="kepala">Kepala</option>
                        <option value="user">User</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table w-100" id="tabelUserAjax">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="25%">User Info</th>
                            <th>Jabatan</th>
                            <th class="text-center">Role</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="10%">Aksi</th>
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
                <button type="button" class="close text-white btn-close-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted">Apakah Anda yakin ingin menonaktifkan user ini?</p>
                <div id="dataSummary" class="alert alert-secondary border-0 small">
                    <i class="fas fa-spinner fa-spin text-primary"></i> Mengambil info data...
                </div>
                <div class="form-group mt-3">
                    <label class="font-weight-bold small text-uppercase text-secondary">Alasan Penghapusan <span class="text-danger">*</span></label>
                    <textarea id="deleteReason" class="form-control" rows="3" placeholder="Contoh: Resign, Mutasi..."></textarea>
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
<script src="plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#tabelUserAjax').DataTable({
        "processing": true,
        "serverSide": true,
        "ordering": false,
        "autoWidth": false,
        "ajax": {
            "url": "pages/user/ajax-data-user.php",
            "type": "GET",
            "data": function (d) {
                d.role = $('#filter_role').val();
            }
        },
        "columns": [
            { "data": "no", "className": "text-center font-weight-bold" },
            { "data": "user_info" },
            { "data": "jabatan" },
            { "data": "role", "className": "text-center" },
            { "data": "status", "className": "text-center" },
            { "data": "aksi", "className": "text-center" }
        ],
        "language": {
            "search": "", "searchPlaceholder": "Cari User / Nama...",
            "zeroRecords": "Tidak ada data ditemukan",
            "info": "Menampilkan _START_ - _END_ dari _TOTAL_",
            "processing": "<div class='spinner-border text-primary spinner-border-sm'></div> Memuat...",
            "paginate": { "next": '<i class="fas fa-chevron-right"></i>', "previous": '<i class="fas fa-chevron-left"></i>' }
        },
        "dom": '<"d-flex justify-content-between align-items-center p-3"lf>rt<"d-flex justify-content-between align-items-center p-3"ip>'
    });

    $('#filter_role').change(function(){ table.ajax.reload(); });

    // Logic Hapus
    $('body').on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#deleteId').val(id);
        $('#deleteReason').val('');
        $('#dataSummary').html('<i class="fas fa-spinner fa-spin text-primary"></i> Sedang mengambil info data...');
        $('#modalHapus').modal('show');

        $.ajax({
            url: 'pages/user/process_soft_delete_user.php',
            type: 'POST',
            data: { action: 'get_info', id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status == 'success') {
                    $('#dataSummary').html('<strong>' + res.data.nama_user + '</strong><br><small>ID: ' + res.data.id_user + '</small>');
                } else {
                    $('#dataSummary').html('<span class="text-danger">' + res.message + '</span>');
                }
            }
        });
    });

    $('body').on('click', '.btn-close-modal', function() { $('#modalHapus').modal('hide'); });

    $('#btnConfirmDelete').click(function() {
        var id = $('#deleteId').val();
        var reason = $.trim($('#deleteReason').val());
        if (reason == '') { Swal.fire({ title: 'Wajib Diisi', text: 'Mohon isi alasan!', icon: 'warning' }); return; }

        var btn = $(this); btn.prop('disabled', true).text('Menghapus...');

        $.ajax({
            url: 'pages/user/process_soft_delete_user.php',
            type: 'POST',
            data: { action: 'delete', id: id, reason: reason },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).text('Ya, Hapus');
                $('#modalHapus').modal('hide');
                $('.modal-backdrop').remove();
                if (res.status == 'success') {
                    Swal.fire({ icon: 'success', title: 'Terhapus', text: 'User dipindahkan ke Recycle Bin', timer: 1500, showConfirmButton: false });
                    table.ajax.reload(null, false);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() { btn.prop('disabled', false).text('Ya, Hapus'); Swal.fire('Error', 'Server Error', 'error'); }
        });
    });
});
</script>