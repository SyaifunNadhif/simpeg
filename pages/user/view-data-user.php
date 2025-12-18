<?php
// FILE: pages/user/view-data-user.php
// Pastikan koneksi ($conn) sudah tersedia dari home-admin.php

$query = mysqli_query($conn, "SELECT * FROM tb_user ORDER BY created_at DESC");
?>

<style>
    .card-modern { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); background: #fff; overflow: hidden; }
    .header-actions { padding: 20px 25px; border-bottom: 1px solid #f0f2f5; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
    .table-modern thead th { background-color: #f8f9fa; color: #6c757d; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; border-bottom: 2px solid #e9ecef; padding: 15px; }
    .table-modern tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f0f2f5; font-size: 0.9rem; color: #343a40; }
    .badge-modern { padding: 5px 12px; border-radius: 20px; font-weight: 600; font-size: 0.7rem; }
    .filter-select { border-radius: 50px; border: 1px solid #e2e8f0; padding: 6px 15px; font-size: 0.85rem; outline: none; background: #f8f9fa; min-width: 150px; }
</style>

<section class="content pt-3">
    <div class="container-fluid">
        <div class="card card-modern">
            
            <div class="header-actions">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0 font-weight-bold text-dark mr-4">Data User</h5>
                    <select id="filter_role" class="filter-select">
                        <option value="">Semua Role</option>
                        <option value="Admin">Admin</option>
                        <option value="Kepala">Kepala</option>
                        <option value="User">User</option>
                    </select>
                </div>
                <a href="home-admin.php?page=form-master-data-user&mode=create" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                    <i class="fas fa-plus mr-2"></i> Tambah User
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tabelUser" class="table table-modern w-100 mb-0">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>User Info</th>
                                <th>Jabatan</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1; 
                            while($row = mysqli_fetch_assoc($query)) : 
                                $role = ucfirst(strtolower($row['hak_akses']));
                                $cls = ($role == 'Admin') ? 'primary' : (($role == 'Kepala') ? 'info' : 'secondary');
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <div class="font-weight-bold"><?= $row['nama_user'] ?></div>
                                    <small class="text-muted">@<?= $row['id_user'] ?></small>
                                </td>
                                <td><?= !empty($row['jabatan']) ? $row['jabatan'] : '-' ?></td>
                                <td><span class="badge badge-<?= $cls ?> badge-modern"><?= $role ?></span></td>
                                <td>
                                    <?php if($row['status_aktif']=='Y'): ?>
                                        <span class="badge badge-success badge-modern">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger badge-modern">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="home-admin.php?page=form-master-data-user&mode=edit&id=<?= $row['id_user'] ?>" class="btn btn-xs btn-outline-warning rounded-circle" title="Edit" style="width:30px;height:30px;line-height:20px;">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    
                                    <button type="button" class="btn btn-xs btn-outline-danger rounded-circle btn-hapus-aman" 
                                            data-id="<?= $row['id_user'] ?>" 
                                            data-nama="<?= $row['nama_user'] ?>"
                                            style="width:30px;height:30px;line-height:20px;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<form id="formDelete" action="home-admin.php?page=form-master-data-user" method="POST" style="display:none;">
    <input type="hidden" name="act" value="delete_aman">
    <input type="hidden" name="id_user" id="del_id">
    <input type="hidden" name="alasan" id="del_alasan">
</form>

<script>
$(document).ready(function() {
    var table = $('#tabelUser').DataTable({ "dom": 'rtip', "pageLength": 10 });

    $('#filter_role').on('change', function () {
        table.column(3).search(this.value).draw();
    });

    $('.btn-hapus-aman').on('click', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');

        Swal.fire({
            title: 'Hapus User?',
            html: `Anda akan menghapus user <b>${nama}</b>.<br>Data akan masuk Recycle Bin.`,
            input: 'text',
            inputPlaceholder: 'Wajib isi alasan penghapusan...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Hapus',
            preConfirm: (alasan) => {
                if (!alasan) { Swal.showValidationMessage('Alasan harus diisi!'); }
                return alasan;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $('#del_id').val(id);
                $('#del_alasan').val(result.value);
                $('#formDelete').submit();
            }
        });
    });
});
</script>