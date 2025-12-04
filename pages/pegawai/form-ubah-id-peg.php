<?php
/*********************************************************
 * FILE    : pages/kepegawaian/form-ubah-id-peg.php
 * MODULE  : Form Pengangkatan (Ubah ID Pegawai)
 * VERSION : v1.0
 * NOTE    : Hanya meload pegawai dengan ID awalan 'K' atau 'O'
 *********************************************************/

if (session_id() == '') session_start();
include "dist/koneksi.php";

// --- QUERY DROPDOWN (FILTER K & O) ---
// Mengambil pegawai yang ID-nya diawali huruf K atau O
$sqlPegawai = "SELECT id_peg, nama FROM tb_pegawai 
               WHERE id_peg LIKE 'K%' OR id_peg LIKE 'O%' 
               ORDER BY nama ASC";
$qPegawai = mysqli_query($conn, $sqlPegawai);
?>

<style>
    .card-modern { border: none; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: #fff; }
    .form-header-modern { background: #6f42c1; color: #fff; border-bottom: 1px solid #f1f1f1; padding: 20px; border-radius: 16px 16px 0 0; }
    .input-modern { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px; height: 45px; width: 100%; }
    .form-label-modern { font-size: 0.85rem; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 8px; }
    
    /* Select2 Fix */
    .select2-container .select2-selection--single { height: 45px !important; border-radius: 10px !important; border: 1px solid #e2e8f0 !important; display: flex; align-items: center; }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { line-height: 45px; padding-left: 15px; }
</style>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark">Pengangkatan Pegawai</h1>
                <p class="text-muted mb-0">Perubahan Status & ID Pegawai (K/O menjadi Tetap)</p>
            </div>
            <div>
                <a href="home-admin.php?page=data-pegawai" class="btn btn-light rounded-pill border shadow-sm">
                    <i class="fa fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content mt-3">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="card card-modern">
                    <div class="form-header-modern">
                        <h5 class="m-0 font-weight-bold"><i class="fas fa-user-check mr-2"></i> Form Perubahan ID</h5>
                    </div>
                    
                    <div class="card-body p-4">
                        <form action="pages/pegawai/proses-ubah-id.php" method="POST" enctype="multipart/form-data">
                            
                            <div class="alert alert-info border-0 shadow-sm mb-4">
                                <i class="fas fa-info-circle mr-2"></i> 
                                <b>Catatan:</b> Hanya Pegawai dengan ID berawalan <b>"K"</b> atau <b>"O"</b> yang muncul di daftar.
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label-modern">Pilih Pegawai (ID Lama) <span class="text-danger">*</span></label>
                                <select name="id_peg_lama" class="form-control select2-search" required>
                                    <option value="">-- Cari Nama Pegawai / ID Lama --</option>
                                    <?php while($p = mysqli_fetch_assoc($qPegawai)) { ?>
                                        <option value="<?= $p['id_peg'] ?>">
                                            <?= $p['nama'] ?> (ID: <?= $p['id_peg'] ?>)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">ID Pegawai Baru <span class="text-danger">*</span></label>
                                        <input type="text" name="id_peg_baru" class="form-control input-modern" placeholder="Masukkan NIP Baru" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Jenis Mutasi/Pengangkatan</label>
                                        <select name="jns_mutasi" class="form-control input-modern">
                                            <option value="Calon Pegawai">Calon Pegawai</option>
                                            <option value="Pegawai Tetap">Pegawai Tetap</option>
                                            <option value="Perubahan NIP">Perubahan NIP</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Nomor SK</label>
                                        <input type="text" name="no_mutasi" class="form-control input-modern" placeholder="Nomor Surat Keputusan" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Tanggal SK</label>
                                        <input type="date" name="tgl_mutasi" class="form-control input-modern" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">TMT (Terhitung Mulai Tanggal)</label>
                                        <input type="date" name="tmt" class="form-control input-modern" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">File SK (PDF) <small class="text-muted">(Opsional)</small></label>
                                        <input type="file" name="sk_mutasi" class="form-control input-modern" accept=".pdf" style="padding-top: 8px;">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-right mt-4 pt-3 border-top">
                                <button type="submit" name="simpan" class="btn btn-primary btn-modern shadow-sm px-4">
                                    <i class="fa fa-save mr-2"></i> Proses Pengangkatan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-search').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: "Ketik Nama atau ID..."
        });
    });
</script>