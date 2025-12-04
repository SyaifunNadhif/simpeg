<?php
/*********************************************************
 * FILE    : pages/ref-sertifikasi/form-edit-data-sertifikasi.php
 * MODULE  : Form Edit Sertifikasi (Modern UI & Fix Redeclare Error)
 * VERSION : v1.6
 *********************************************************/

if (session_id() === '') session_start();

// PERBAIKAN DI SINI: Gunakan include_once
// Agar jika file ini sudah diload oleh home-admin.php, tidak diload ulang (penyebab error)
@include_once "dist/koneksi.php";
@include_once "dist/functions.php"; 

// --- 1. TANGKAP ID ---
$id_sertif = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : (isset($_GET['id_sertif']) ? mysqli_real_escape_string($conn, $_GET['id_sertif']) : '');

if (empty($id_sertif)) {
    echo "<script>alert('ID Data tidak ditemukan!'); window.history.back();</script>";
    exit;
}

// --- 2. AMBIL DATA LAMA ---
$query = "SELECT a.*, b.nama, b.nip 
          FROM tb_sertifikasi a 
          JOIN tb_pegawai b ON a.id_peg = b.id_peg 
          WHERE a.id_sertif = '$id_sertif'";

$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Data tidak ditemukan di database!'); window.location='home-admin.php?page=form-view-data-sertifikasi';</script>";
    exit;
}

$row = mysqli_fetch_assoc($result);
?>

<style>
    .card-modern { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: #fff; }
    .form-header-modern { background: #007bff; color: #fff; border-bottom: 1px solid #f1f1f1; padding: 20px; border-radius: 15px 15px 0 0; }
    .input-modern { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px; height: 45px; width: 100%; }
    .form-label-modern { font-size: 0.85rem; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 8px; }
    .btn-modern { border-radius: 50px; padding: 10px 30px; font-weight: 600; }
</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark">Edit Sertifikasi</h1>
                <p class="text-muted mb-0">Perbarui data sertifikasi pegawai</p>
            </div>
            <div>
                <a href="home-admin.php?page=form-view-data-sertifikasi" class="btn btn-light rounded-pill border shadow-sm">
                    <i class="fa fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content mt-3">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                
                <div class="card card-modern">
                    <div class="form-header-modern">
                        <h5 class="m-0 font-weight-bold"><i class="fas fa-edit mr-2"></i> Form Edit Data</h5>
                    </div>
                    
                    <div class="card-body p-4">
                        <form action="pages/ref-sertifikasi/proses-sertifikasi.php" method="POST" enctype="multipart/form-data">
                            
                            <input type="hidden" name="id_sertif" value="<?= $row['id_sertif'] ?>">
                            <input type="hidden" name="id_peg" value="<?= $row['id_peg'] ?>">

                            <div class="alert alert-light border mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="font-weight-bold mb-0 text-primary"><?= htmlspecialchars($row['nama']) ?></h6>
                                        <small class="text-muted">NIP/ID: <?= htmlspecialchars($row['id_peg']) ?></small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Nama Sertifikasi <span class="text-danger">*</span></label>
                                        <input type="text" name="sertifikasi" class="form-control input-modern" 
                                               value="<?= htmlspecialchars($row['sertifikasi']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Penyelenggara</label>
                                        <input type="text" name="penyelenggara" class="form-control input-modern" 
                                               value="<?= htmlspecialchars($row['penyelenggara']) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">No. Sertifikat</label>
                                        <input type="text" name="sertifikat" class="form-control input-modern" 
                                               value="<?= htmlspecialchars($row['sertifikat']) ?>" placeholder="Nomor Seri">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Tanggal Sertifikat</label>
                                        <input type="date" name="tgl_sertifikat" class="form-control input-modern" 
                                               value="<?= $row['tgl_sertifikat'] ?>">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Tanggal Expired</label>
                                        <input type="date" name="tgl_expired" class="form-control input-modern" 
                                               value="<?= ($row['tgl_expired'] == '0000-00-00') ? '' : $row['tgl_expired'] ?>">
                                        <small class="text-muted">Kosongkan jika berlaku seumur hidup</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-right mt-4 pt-3 border-top">
                                <a href="javascript:history.back()" class="btn btn-light btn-modern mr-2 border">Batal</a>
                                <button type="submit" name="update" class="btn btn-primary btn-modern shadow-sm">
                                    <i class="fa fa-save mr-2"></i> Simpan Perubahan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>