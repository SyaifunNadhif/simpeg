<?php
/*********************************************************
 * FILE    : pages/user/view-data-user.php
 * MODULE  : Manajemen User (Modern UI)
 * VERSION : v2.0
 *********************************************************/

$page_title = "Data";
$page_subtitle = "User System";
$breadcrumbs = [
  ["label" => "Dashboard", "url" => "home-admin.php"],
  ["label" => "Data User"]
];

include "komponen/header.php";
include 'dist/koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM tb_user ORDER BY created_at DESC");
?>

<style>
    .content-wrapper { background-color: #f4f6f9; }
    
    /* Card Style */
    .card-modern {
        border: none;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        background: #fff;
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    /* Header Card */
    .card-header-modern {
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        padding: 20px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    /* Tombol Modern */
    .btn-modern {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 600;
        transition: all 0.3s;
        font-size: 0.9rem;
    }
    .btn-modern:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .btn-primary-modern { background-color: #007bff; border-color: #007bff; color: white; }
    .btn-outline-modern { border: 1px solid #ddd; color: #555; background: transparent; }
    .btn-outline-modern:hover { background: #f8f9fa; color: #333; }

    /* Table Style */
    table.dataTable thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #e9ecef !important;
        color: #495057;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 15px !important;
        white-space: nowrap;
    }
    table.dataTable tbody td {
        padding: 12px 15px !important;
        vertical-align: middle;
        font-size: 0.9rem;
        color: #333;
    }
    table.dataTable tbody tr:hover { background-color: #fcfcfc; }

    /* Badge Status */
    .badge-status {
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-aktif { background-color: #e6fffa; color: #047481; border: 1px solid #b2f5ea; }
    .badge-nonaktif { background-color: #fff5f5; color: #c53030; border: 1px solid #feb2b2; }
    
    /* Role Badge */
    .badge-role { font-size: 0.8rem; font-weight: 500; }
</style>

<section class="content pt-4">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                
                <div class="card card-modern">
                    
                    <div class="card-header-modern">
                        <div>
                            <h5 class="m-0 font-weight-bold text-dark" style="font-size: 1.2rem;">Daftar Pengguna</h5>
                            <p class="text-muted mb-0 small">Manajemen akun akses sistem</p>
                        </div>
                        <div>
                            <button onclick="history.back()" class="btn btn-outline-modern btn-modern mr-2">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </button>
                            <a href="home-admin.php?page=form-user&mode=create" class="btn btn-primary-modern btn-modern shadow-sm">
                                <i class="fas fa-plus mr-1"></i> Tambah User
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tabelUser" class="table table-hover w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="5%">No</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Jabatan</th>
                                        <th class="text-center">Level Akses</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1; 
                                    while($row = mysqli_fetch_assoc($query)) : 
                                        // Warna Badge Level
                                        $role = strtolower($row['hak_akses']);
                                        $badgeRole = 'badge-secondary';
                                        if ($role == 'admin') $badgeRole = 'badge-primary';
                                        elseif ($role == 'kepala') $badgeRole = 'badge-info';
                                    ?>
                                    <tr>
                                        <td class="text-center font-weight-bold"><?= $no++ ?></td>
                                        <td class="font-weight-bold text-primary"><?= htmlspecialchars($row['id_user']) ?></td>
                                        <td>
                                            <div class="font-weight-bold text-dark"><?= htmlspecialchars($row['nama_user']) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($row['jabatan']) ?></td>
                                        
                                        <td class="text-center">
                                            <span class="badge <?= $badgeRole ?> badge-role px-3 py-2 rounded-pill">
                                                <?= ucfirst($row['hak_akses']) ?>
                                            </span>
                                        </td>
                                        
                                        <td class="text-center">
                                            <?php if ($row['status_aktif'] == 'Y'): ?>
                                                <span class="badge badge-status badge-aktif">
                                                    <i class="fas fa-check-circle mr-1"></i> Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-status badge-nonaktif">
                                                    <i class="fas fa-times-circle mr-1"></i> Nonaktif
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="home-admin.php?page=form-user&mode=edit&id=<?= $row['id_user'] ?>" class="btn btn-sm btn-light text-warning border" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="pages/user/proses-user.php?act=hapus&id=<?= $row['id_user'] ?>" class="btn btn-sm btn-light text-danger border btn-hapus" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
    // Init DataTable
    var table = $('#tabelUser').DataTable({
        "responsive": false,
        "scrollX": true,
        "autoWidth": false,
        "pageLength": 10,
        "language": {
            "search": "",
            "searchPlaceholder": "Cari user...",
            "zeroRecords": "Tidak ada data user.",
            "info": "Hal _PAGE_ dari _PAGES_",
            "infoEmpty": "Kosong",
            "paginate": { "next": ">", "previous": "<" }
        },
        "columnDefs": [
            { "orderable": false, "targets": [6] } // Kolom Aksi tidak bisa disortir
        ]
    });

    // Styling Search Box DataTables
    $('.dataTables_filter input').addClass('form-control rounded-pill border-secondary').css({'width': '250px', 'padding': '5px 15px'});
    $('.dataTables_wrapper').css('padding', '20px');

    // Konfirmasi Hapus dengan SweetAlert
    $(document).on('click', '.btn-hapus', function(e) {
        e.preventDefault();
        var link = $(this).attr('href');
        
        Swal.fire({
            title: 'Hapus User?',
            text: "Data user ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
            }
        });
    });
});
</script>