<?php
/*********************************************************
 * FILE    : pages/ref-pelanggaran/form-view-data-hukuman.php
 * MODULE  : Data Pelanggaran (Fix Modal PDF Stuck)
 * VERSION : v11.0 Fixed
 *********************************************************/

include "dist/koneksi.php";
include "dist/library.php";

// --- 1. SECURITY CHECK ---
$hak_akses_user = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$is_admin       = ($hak_akses_user == 'admin' || $hak_akses_user == 'superadmin');

// --- 2. QUERY DATA ---
$sql = "SELECT h.*, p.nama, p.nip, k.nama_kantor 
        FROM tb_hukuman h
        JOIN tb_pegawai p ON h.id_peg = p.id_peg
        LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
        LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
        WHERE 1=1 
        ORDER BY h.tgl_sk DESC";

$tampilJudge = mysqli_query($conn, $sql);
?>

<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">

<style>
    /* Content Background */
    .content-wrapper { background-color: #f8f9fa; }
    
    /* Card Modern */
    .card-modern {
        border: none; border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        background: #fff; margin-bottom: 25px; overflow: hidden;
    }
    
    /* Header Modern */
    .card-header-modern {
        padding: 25px 30px; background: #fff; border-bottom: 1px solid #f1f5f9;
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;
    }
    
    .text-title { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0; line-height: 1.2; }
    .text-subtitle { font-size: 0.9rem; color: #64748b; margin-top: 4px; display: block; }

    /* Search Input */
    .search-box { position: relative; margin-right: 10px; }
    .search-input {
        border-radius: 50px; border: 1px solid #e2e8f0;
        padding: 10px 20px 10px 40px; font-size: 0.9rem; width: 250px;
        transition: all 0.3s; background-color: #f8fafc;
    }
    .search-input:focus {
        border-color: #ef4444; background-color: #fff;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1); outline: none; width: 300px;
    }
    .search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }

    /* Button Add */
    .btn-add-modern {
        border-radius: 50px; padding: 10px 25px; font-weight: 700;
        background-color: #ef4444; border-color: #ef4444; color: white;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2); transition: all 0.3s;
    }
    .btn-add-modern:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(239, 68, 68, 0.3); background-color: #dc2626; color: white; }

    /* Table Style */
    table.dataTable thead th {
        background-color: #fff; color: #64748b; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;
        border-bottom: 2px solid #f1f5f9 !important; padding: 15px 20px !important; white-space: nowrap;
    }
    table.dataTable tbody td { padding: 15px 20px !important; vertical-align: middle; border-bottom: 1px solid #f8fafc; color: #334155; font-size: 0.9rem; }

    /* Action Buttons */
    .btn-action {
        width: 35px; height: 35px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: all 0.2s; background: #fff; border: 1px solid #e2e8f0; color: #64748b; cursor: pointer;
    }
    .btn-action:hover { transform: translateY(-2px); box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .btn-view:hover { color: #0ea5e9; border-color: #0ea5e9; background: #f0f9ff; }
    .btn-edit:hover { color: #f59e0b; border-color: #f59e0b; background: #fffbeb; }
    .btn-delete:hover { color: #ef4444; border-color: #ef4444; background: #fef2f2; }
    
    .badge-soft-danger { background-color: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
</style>

<section class="content pt-4 px-3">
    <div class="card card-modern">
        <div class="card-header-modern">
            <div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle text-danger fa-lg mr-3"></i>
                    <h1 class="text-title">Data Pelanggaran</h1>
                </div>
                <span class="text-subtitle pl-1">Rekapitulasi data hukuman disiplin pegawai.</span>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="search-box d-none d-md-block">
                    <i class="fa fa-search search-icon"></i>
                    <input type="text" id="customSearch" class="search-input" placeholder="Cari Nama / Jenis Sanksi...">
                </div>
                
                <?php if($is_admin): ?>
                <a href="home-admin.php?page=form-master-data-pelanggaran" class="btn btn-add-modern">
                    <i class="fa fa-plus mr-2"></i> Tambah
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="pelanggaran" class="table w-100">
                    <thead>
                        <tr>
                            <th width="25%">Pegawai</th>
                            <th width="15%">Jenis Sanksi</th>
                            <th width="25%">Keterangan</th>
                            <th width="10%">Tgl Surat</th>
                            <th width="15%">Kantor</th>
                            <th class="text-center" width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($tampilJudge) > 0) {
                            while($peg = mysqli_fetch_array($tampilJudge)) { 
                                // Cek File
                                $file_exist = false;
                                $dokumen = isset($peg['dokumen']) ? $peg['dokumen'] : '';
                                if (!empty($dokumen) && file_exists("pages/assets/dokumen_hukuman/".$dokumen)) {
                                    $file_exist = true;
                                    $url_doc = "pages/assets/dokumen_hukuman/" . rawurlencode($dokumen);
                                }
                        ?>  
                        <tr>
                            <td>
                                <div class="font-weight-bold text-dark"><?= htmlspecialchars($peg['nama']) ?></div>
                                <div class="small text-muted font-italic">NIP: <?= htmlspecialchars($peg['id_peg']) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-pill badge-soft-danger px-3 py-2">
                                    <?= htmlspecialchars($peg['hukuman']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($peg['keterangan']) ?></td>
                            <td><?= date('d M Y', strtotime($peg['tgl_sk'])) ?></td>
                            <td><?= htmlspecialchars($peg['nama_kantor']) ?></td>
                            
                            <td class="text-center">
                                <div class="d-flex justify-content-center" style="gap: 5px;">
                                    
                                    <?php if($file_exist): ?>
                                    <button type="button" class="btn-action btn-view view-pdf" title="Lihat Dokumen"
                                            data-url="<?= $url_doc ?>" 
                                            data-title="Hukuman: <?= htmlspecialchars($peg['hukuman']) ?>">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <?php endif; ?>

                                    <?php if($is_admin): ?>
                                        <a href="home-admin.php?page=form-master-data-pelanggaran&id_hukum=<?=$peg['id_hukum']?>" class="btn-action btn-edit" title="Edit Data">
                                            <i class="fa fa-pen"></i>
                                        </a>

                                        <button type="button" class="btn-action btn-delete" title="Hapus Data" data-id="<?= $peg['id_hukum'] ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg rounded-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-trash-alt mr-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="close text-white closeModalBtn"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted">Apakah Anda yakin ingin memindahkan data ini ke <b>Recycle Bin</b>?</p>
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
                <button type="button" class="btn btn-link text-secondary font-weight-bold closeModalBtn">Batal</button>
                <button type="button" class="btn btn-danger font-weight-bold shadow-sm px-4" id="btnConfirmDelete">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPDF" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="height: 90vh;">
        <div class="modal-content h-100">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title" id="modalPDFLabel" style="font-size: 1rem;">Preview Dokumen</h5>
                <button type="button" class="close text-white closeModalPDF"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0 d-flex flex-column" style="background: #525659;">
                <iframe id="pdfFrame" src="" style="width: 100%; flex-grow: 1; border: none;"></iframe>
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
$(document).ready(function () {
    var table = $("#pelanggaran").DataTable({
        "responsive": false, "scrollX": true, "autoWidth": false, "lengthChange": false, 
        "pageLength": 10, "order": [[ 3, "desc" ]], "dom": 'rtip', 
        "language": { "search": "", "zeroRecords": "Tidak ada data.", "info": "Hal _PAGE_ dari _PAGES_", "infoEmpty": "Kosong", "paginate": { "previous": "<", "next": ">" }}
    });

    $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });

    // --- FUNGSI RESET MODAL & BACKDROP (PENTING AGAR TIDAK MACET) ---
    function forceCloseModal(modalID) {
        $(modalID).modal('hide');
        $('body').removeClass('modal-open'); // Hapus class di body
        $('.modal-backdrop').remove();       // Paksa hapus layar hitam
    }

    // 1. MODAL PDF
    $(document).on('click', '.view-pdf', function() {
        $('#modalPDFLabel').html('<i class="fa fa-file-pdf mr-2"></i> ' + $(this).data('title'));
        $('#pdfFrame').attr('src', $(this).data('url'));
        $('#modalPDF').modal('show');
    });

    // Tombol X di PDF
    $('.closeModalPDF').on('click', function(){ 
        $('#pdfFrame').attr('src', 'about:blank'); // Stop loading PDF
        forceCloseModal('#modalPDF'); 
    });

    // 2. MODAL HAPUS
    $('.closeModalBtn').on('click', function(){ 
        forceCloseModal('#modalHapus'); 
    });

    // Saat modal tertutup otomatis (klik luar), pastikan bersih
    $('.modal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });

    // --- LOGIC HAPUS ---
    $('body').on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#deleteId').val(id);
        $('#deleteReason').val('');
        $('#dataSummary').html('<i class="fas fa-spinner fa-spin text-primary"></i> Sedang mengambil info data...').removeClass('alert-danger').addClass('alert-secondary');
        $('#modalHapus').modal('show');

        // AJAX INFO
        $.ajax({
            url: 'pages/ref-pelanggaran/process_soft_delete_pelanggaran.php',
            type: 'POST',
            data: { action: 'get_info', id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status == 'success') {
                    $('#dataSummary').html(
                        '<strong>' + res.data.hukuman + '</strong><br>' +
                        '<small class="text-muted">' + res.data.nama_peg + ' | Tgl: ' + res.data.tgl_sk + '</small>'
                    );
                } else {
                    $('#dataSummary').html('<span class="text-danger">Gagal: ' + res.message + '</span>').addClass('alert-danger');
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                $('#dataSummary').html('<span class="text-danger">Gagal koneksi (404). Cek path backend.</span>').addClass('alert-danger');
            }
        });
    });

    $('#btnConfirmDelete').click(function() {
        var id = $('#deleteId').val();
        var reason = $.trim($('#deleteReason').val());

        if (reason == '') { Swal.fire({ title: 'Wajib Diisi', text: 'Mohon isi alasan!', icon: 'warning', confirmButtonColor: '#d33' }); return; }

        var btn = $(this); btn.prop('disabled', true).text('Menghapus...');

        $.ajax({
            url: 'pages/ref-pelanggaran/process_soft_delete_pelanggaran.php',
            type: 'POST',
            data: { action: 'delete', id: id, reason: reason },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).text('Ya, Hapus');
                forceCloseModal('#modalHapus');

                if (res.status == 'success') {
                    Swal.fire({ icon: 'success', title: 'Terhapus', text: 'Data masuk Recycle Bin', timer: 1500, showConfirmButton: false }).then(() => { location.reload(); });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).text('Ya, Hapus');
                Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
            }
        });
    });

    // FLASH MESSAGE SESSION (JIKA ADA DARI BACKEND BIASA)
    <?php if(isset($_SESSION['swal_icon'])): ?>
    Swal.fire({
        icon: '<?= $_SESSION['swal_icon']; ?>',
        title: '<?= $_SESSION['swal_title']; ?>',
        text: '<?= $_SESSION['swal_text']; ?>',
        timer: 2500,
        showConfirmButton: false
    });
    <?php unset($_SESSION['swal_icon']); unset($_SESSION['swal_title']); unset($_SESSION['swal_text']); ?>
    <?php endif; ?>
});
</script>