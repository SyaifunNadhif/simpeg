<?php
/*********************************************************
 * FILE    : pages/pelanggaran/form-edit-data-hukuman.php
 * MODULE  : Edit Data Pelanggaran (Final Fix Modern UI)
 * VERSION : v3.0
 *********************************************************/

include "dist/koneksi.php";
include "dist/library.php";

// 1. Ambil ID dari URL & Validasi
if (isset($_GET['id_hukum'])) {
    $id_hukum = mysqli_real_escape_string($conn, $_GET['id_hukum']);
} else {
    echo "<script>window.history.back();</script>";
    exit;
}

// 2. Proses Update Data
$status_update = '';

if (isset($_POST['edit'])) {
    $hukuman        = mysqli_real_escape_string($conn, $_POST['hukuman']);
    $keterangan     = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $pejabat_sk     = mysqli_real_escape_string($conn, $_POST['pejabat_sk']);
    $jabatan_sk     = mysqli_real_escape_string($conn, $_POST['jabatan_sk']);
    $no_sk          = mysqli_real_escape_string($conn, $_POST['no_sk']);
    
    $tgl_sk = date('Y-m-d');
    if(!empty($_POST['tgl_sk'])){
        $tgl_sk = date('Y-m-d', strtotime($_POST['tgl_sk']));
    }
    
    $pejabat_pulih  = mysqli_real_escape_string($conn, $_POST['pejabat_pulih']);
    $jabatan_pulih  = mysqli_real_escape_string($conn, $_POST['jabatan_pulih']);
    $no_pulih       = mysqli_real_escape_string($conn, $_POST['no_pulih']);
    
    $tgl_pulih_sql = "NULL";
    if(!empty($_POST['tgl_pulih'])){
        $val = date('Y-m-d', strtotime($_POST['tgl_pulih']));
        $tgl_pulih_sql = "'$val'";
    }

    // Query Update
    $sql_update = "UPDATE tb_hukuman SET 
                   hukuman='$hukuman', keterangan='$keterangan',
                   pejabat_sk='$pejabat_sk', jabatan_sk='$jabatan_sk', no_sk='$no_sk', tgl_sk='$tgl_sk',
                   pejabat_pulih='$pejabat_pulih', jabatan_pulih='$jabatan_pulih', no_pulih='$no_pulih', tgl_pulih=$tgl_pulih_sql
                   WHERE id_hukum='$id_hukum'";
                   
    $update = mysqli_query($conn, $sql_update);

    if ($update) {
        $status_update = 'sukses';
    } else {
        $status_update = 'gagal';
    }
}

// 3. Ambil Data Lama (JOIN untuk ambil nama pegawai)
$ambilData = mysqli_query($conn, "SELECT a.*, b.nama, b.id_peg FROM tb_hukuman a JOIN tb_pegawai b ON a.id_peg=b.id_peg WHERE a.id_hukum='$id_hukum'");
$hasil = mysqli_fetch_array($ambilData);

if (!$hasil) {
    echo "<div class='alert alert-danger m-4'>Data tidak ditemukan atau query salah.</div>";
    exit;
}
?>

<style>
    .card-modern { 
        border: none; border-radius: 15px; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.05); 
        background: #fff; 
    }
    .form-header-modern { 
        background: #ffc107; color: #333; 
        border-bottom: 1px solid #f1f1f1; 
        padding: 20px; border-radius: 15px 15px 0 0; 
    }
    .input-modern { 
        border-radius: 10px; border: 1px solid #e2e8f0; 
        padding: 10px 15px; height: 45px; width: 100%; 
    }
    .input-modern:focus { border-color: #ffc107; box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2); outline: none; }
    
    .form-label-modern { 
        font-size: 0.8rem; font-weight: 700; color: #6c757d; 
        text-transform: uppercase; margin-bottom: 8px; 
    }
    .btn-modern { 
        border-radius: 50px; padding: 10px 30px; 
        font-weight: 600; transition: 0.3s; 
    }
    
    /* Date Wrapper */
    .date-wrapper { position: relative; }
    input[type="date"]::-webkit-calendar-picker-indicator {
        background: transparent; bottom: 0; color: transparent; cursor: pointer;
        height: auto; left: 0; position: absolute; right: 0; top: 0; width: auto;
    }
    .date-icon { position: absolute; right: 15px; top: 12px; pointer-events: none; color: #6c757d; }
</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark">Edit Pelanggaran</h1>
            </div>
            <div>
                <a href="home-admin.php?page=form-view-data-hukuman" class="btn btn-light rounded-pill border">Kembali</a>
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
                        <form action="" method="POST" enctype="multipart/form-data">
                            
                            <div class="form-group mb-4">
                                <label class="form-label-modern">Nama Pegawai</label>
                                <input type="text" class="form-control input-modern bg-light" value="<?= $hasil['nama'] ?> (ID: <?= $hasil['id_peg'] ?>)" readonly>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label class="form-label-modern">Jenis Sanksi <span class="text-danger">*</span></label>
                                        <select name="hukuman" class="form-control input-modern select2bs4" required style="width: 100%;">
                                            <option value="">-- Pilih Jenis --</option>
                                            <?php
                                            $opsi = ["Surat Peringatan I", "Surat Peringatan II", "Surat Peringatan III", "Skorsing", "PTDH"];
                                            foreach($opsi as $op){
                                                $selected = ($hasil['hukuman'] == $op) ? "selected" : "";
                                                echo "<option value='$op' $selected>$op</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label class="form-label-modern">Keterangan</label>
                                        <textarea name="keterangan" class="form-control input-modern" rows="3" style="height: auto;"><?= $hasil['keterangan'] ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <h6 class="text-muted font-weight-bold border-bottom pb-2 mb-3 mt-4 text-uppercase" style="font-size: 0.85rem;">Detail SK Hukuman</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Pejabat Pengesah</label>
                                        <input type="text" name="pejabat_sk" class="form-control input-modern" value="<?= $hasil['pejabat_sk'] ?>">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Jabatan Pengesah</label>
                                        <input type="text" name="jabatan_sk" class="form-control input-modern" value="<?= $hasil['jabatan_sk'] ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Nomor SK</label>
                                        <input type="text" name="no_sk" class="form-control input-modern" value="<?= $hasil['no_sk'] ?>" style="text-transform:uppercase">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Tanggal SK</label>
                                        <div class="date-wrapper">
                                            <input type="date" name="tgl_sk" class="form-control input-modern" value="<?= $hasil['tgl_sk'] ?>">
                                            <i class="fa fa-calendar date-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="text-muted font-weight-bold border-bottom pb-2 mb-3 mt-4 text-uppercase" style="font-size: 0.85rem;">Detail Pemulihan</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Pejabat Pemulih</label>
                                        <input type="text" name="pejabat_pulih" class="form-control input-modern" value="<?= $hasil['pejabat_pulih'] ?>">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Jabatan Pemulih</label>
                                        <input type="text" name="jabatan_pulih" class="form-control input-modern" value="<?= $hasil['jabatan_pulih'] ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">No. SK Pemulihan</label>
                                        <input type="text" name="no_pulih" class="form-control input-modern" value="<?= $hasil['no_pulih'] ?>">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Tgl Pemulihan</label>
                                        <div class="date-wrapper">
                                            <input type="date" name="tgl_pulih" class="form-control input-modern" value="<?= $hasil['tgl_pulih'] ?>">
                                            <i class="fa fa-calendar date-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-right mt-5 border-top pt-4">
                                <a href="home-admin.php?page=form-view-data-hukuman" class="btn btn-light btn-modern mr-2 border">Batal</a>
                                <button type="submit" name="edit" value="edit" class="btn btn-warning btn-modern shadow-sm">
                                    <i class="fa fa-save mr-2"></i> Update Data
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  $(function () {
    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    var status = "<?= $status_update ?>";
    if (status == 'sukses') {
        Swal.fire({
            icon: 'success', title: 'Berhasil!', text: 'Data pelanggaran diperbarui.',
            showConfirmButton: false, timer: 1500
        }).then(function() {
            window.location.href = 'home-admin.php?page=form-view-data-pelanggaran';
        });
    } else if (status == 'gagal') {
        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat update.' });
    }
  });
</script>