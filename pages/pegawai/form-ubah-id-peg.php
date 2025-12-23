<?php
/*********************************************************
 * FILE    : pages/kepegawaian/form-ubah-id-peg.php
 * MODULE  : Form Pengangkatan (Secure & Offline Mode)
 * VERSION : v2.1
 *********************************************************/

if (session_id() == '') session_start();
include "dist/koneksi.php";

// 1. SECURITY: Cek Login
if (empty($_SESSION['id_user'])) {
    die("<div class='alert alert-danger'>Akses Ditolak. Silakan login terlebih dahulu.</div>");
}

// 2. QUERY DATA (Aman karena hardcoded query, tidak ada input user)
$sqlPegawai = "SELECT id_peg, nama FROM tb_pegawai 
               WHERE id_peg LIKE 'K%' OR id_peg LIKE 'O%' 
               ORDER BY nama ASC";
$qPegawai = mysqli_query($conn, $sqlPegawai);
?>

<style>
    .card-ref {
        border: 1px solid #e3e6f0; border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.05); overflow: hidden; background: #fff;
    }
    .card-ref-header {
        background-color: #007bff; color: #fff; padding: 15px 20px;
        border-bottom: 1px solid #0069d9;
    }
    .card-ref-header h5 { font-weight: 700; font-size: 1.1rem; margin: 0; }
    .card-ref-header small { color: rgba(255,255,255,0.8); font-size: 0.85rem; }
    .form-label-ref { font-weight: 700; font-size: 0.85rem; color: #212529; margin-bottom: 6px; }
    .form-control-ref {
        border-radius: 6px; border: 1px solid #ced4da; height: 42px;
        font-size: 0.95rem; padding: 8px 12px;
    }
    .form-control-ref:focus { border-color: #80bdff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); }
    .card-ref-footer {
        padding: 20px; background-color: #fff; border-top: 1px solid #f1f1f1;
        display: flex; justify-content: space-between; align-items: center;
    }
    .btn-ref-back { background: #fff; border: 1px solid #ced4da; color: #5a5c69; font-weight: 600; padding: 8px 20px; border-radius: 6px; }
    .btn-ref-save { background: #007bff; border: none; color: #fff; font-weight: 600; padding: 8px 30px; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,123,255,0.3); }
    .btn-ref-save:hover { background: #0069d9; color:#fff; }
    .btn-ref-back:hover { background: #f8f9fa; color:#333; }
    
    /* Override Select2 agar sesuai tema Bootstrap 4 */
    .select2-container .select2-selection--single { height: 42px !important; border: 1px solid #ced4da !important; border-radius: 6px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; padding-left: 12px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
</style>

<link rel="stylesheet" href="plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

<section class="content pt-3">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                
                <form action="pages/pegawai/proses-ubah-id.php" method="POST" enctype="multipart/form-data">
                    <div class="card-ref">
                        
                        <div class="card-ref-header">
                            <h5>Pengangkatan Pegawai</h5>
                            <small>Form perubahan status dan ID pegawai (K/O menjadi Tetap)</small>
                        </div>

                        <div class="card-body p-4">
                            
                            <div class="form-group mb-4">
                                <label class="form-label-ref">Pilih Pegawai (ID Lama)</label>
                                <select name="id_peg_lama" class="form-control-ref select2-search" style="width: 100%;" required>
                                    <option value="">- Cari Nama / ID -</option>
                                    <?php while($p = mysqli_fetch_assoc($qPegawai)) { ?>
                                        <option value="<?= htmlspecialchars($p['id_peg']) ?>">
                                            <?= htmlspecialchars($p['nama']) ?> (ID: <?= htmlspecialchars($p['id_peg']) ?>)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label-ref">Jenis Pengangkatan</label>
                                    <select name="jns_mutasi" class="form-control-ref" required>
                                        <option value="">- Pilih -</option>
                                        <option value="Calon Pegawai">Calon Pegawai</option>
                                        <option value="Pegawai Tetap">Pegawai Tetap</option>
                                        <option value="Perubahan NIP">Perubahan NIP</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label-ref">ID Pegawai Baru</label>
                                    <input type="text" name="id_peg_baru" class="form-control-ref" placeholder="Masukkan ID Baru" required autocomplete="off">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label-ref">Nomor SK</label>
                                    <input type="text" name="no_mutasi" class="form-control-ref" placeholder="No. Surat Keputusan" required autocomplete="off">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label-ref">Tanggal SK</label>
                                    <input type="date" name="tgl_mutasi" class="form-control-ref" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label-ref">TMT (Terhitung Mulai Tgl)</label>
                                    <input type="date" name="tmt" class="form-control-ref" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label-ref">File SK <small class="text-muted">(opsional, pdf)</small></label>
                                    <div class="custom-file" style="height: 42px;">
                                        <input type="file" name="sk_mutasi" class="custom-file-input" id="customFile" accept=".pdf" style="height: 42px;">
                                        <label class="custom-file-label" for="customFile" style="height: 42px; line-height: 30px; border-radius:6px; border-color:#ced4da;">Pilih File...</label>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="card-ref-footer">
                            <a href="home-admin.php?page=form-view-data-pegawai" class="btn btn-ref-back">
                                <i class="fa fa-arrow-left mr-1"></i> Kembali
                            </a>
                            <button type="submit" name="simpan" class="btn btn-ref-save">
                                <i class="fa fa-save mr-1"></i> Simpan
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</section>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/select2/js/select2.full.min.js"></script>

<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('.select2-search').select2({
            theme: 'default', // Menggunakan style default yg kita override di CSS
            placeholder: "- Pilih Pegawai -",
            allowClear: true
        });

        // Script Custom File Input Label (Biar nama file muncul saat dipilih)
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    });
</script>     mmmm