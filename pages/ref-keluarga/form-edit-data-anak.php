<?php
/*********************************************************
 * FILE    : pages/keluarga/form-edit-data-anak.php
 * MODULE  : Form Edit Data Anak (Modern UI)
 * VERSION : v1.1
 *********************************************************/

if (session_id() === '') session_start();
include "dist/koneksi.php";

// --- 1. TANGKAP ID ---
// Menerima 'id' (standard) atau 'id_anak' (legacy)
$id_anak = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : (isset($_GET['id_anak']) ? mysqli_real_escape_string($conn, $_GET['id_anak']) : '');

if (empty($id_anak)) {
    echo "<script>alert('ID Data tidak ditemukan!'); window.history.back();</script>";
    exit;
}

// --- 2. AMBIL DATA ---
$query = "SELECT a.*, p.nama AS nama_peg 
          FROM tb_anak a
          JOIN tb_pegawai p ON a.id_peg = p.id_peg 
          WHERE a.id_anak = '$id_anak'";

$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.history.back();</script>";
    exit;
}

$row = mysqli_fetch_assoc($result);
$id_peg   = $row['id_peg'];
$nama_peg = $row['nama_peg'];

// --- 3. PROSES UPDATE ---
if (isset($_POST['edit'])) {
    $anak_ke    = mysqli_real_escape_string($conn, $_POST['anak_ke']);
    $nik        = mysqli_real_escape_string($conn, $_POST['nik']);
    $nama       = mysqli_real_escape_string($conn, $_POST['nama']);
    $tmp_lhr    = mysqli_real_escape_string($conn, $_POST['tmp_lhr']);
    
    // Konversi Tgl Lahir (dd-mm-yyyy -> yyyy-mm-dd)
    $tgl_lhr_raw = $_POST['tgl_lhr'];
    $tgl_lhr     = date('Y-m-d', strtotime(str_replace('/', '-', $tgl_lhr_raw))); 

    $pendidikan = mysqli_real_escape_string($conn, $_POST['pendidikan']);
    $pekerjaan  = mysqli_real_escape_string($conn, $_POST['pekerjaan']);
    $status_hub = mysqli_real_escape_string($conn, $_POST['status_hub']);
    $bpjs_anak  = mysqli_real_escape_string($conn, $_POST['bpjs_anak']);

    $update = mysqli_query($conn, "UPDATE tb_anak SET 
                anak_ke     = '$anak_ke',
                nik         = '$nik',
                nama        = '$nama',
                tmp_lhr     = '$tmp_lhr',
                tgl_lhr     = '$tgl_lhr',
                pendidikan  = '$pendidikan',
                pekerjaan   = '$pekerjaan',
                status_hub  = '$status_hub',
                bpjs_anak   = '$bpjs_anak'
                WHERE id_anak = '$id_anak'");

    if ($update) {
        // Redirect kembali ke detail pegawai (Tab Anak)
        echo "<script>
                alert('Data Berhasil Diubah!'); 
                window.location='home-admin.php?page=view-detail-data-pegawai&id_peg=$id_peg';
              </script>";
    } else {
        echo "<script>alert('Gagal Mengubah Data: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<style>
    .card-modern { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: #fff; }
    .form-header-modern { background: #007bff; color: #fff; border-bottom: 1px solid #f1f1f1; padding: 20px; border-radius: 15px 15px 0 0; }
    .input-modern { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px; height: 45px; width: 100%; }
    .form-label-modern { font-size: 0.85rem; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 8px; }
    
    /* Fix Select2 Height */
    .select2-container .select2-selection--single { height: 45px !important; border-radius: 10px !important; border: 1px solid #e2e8f0 !important; display: flex; align-items: center; }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { line-height: 45px; padding-left: 15px; color: #495057; }
</style>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

<section class="content-header pt-4 pb-2">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="m-0 font-weight-bold text-dark">Edit Data Anak</h1>
      </div>
      <div>
        <a href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?= $id_peg ?>" class="btn btn-light rounded-pill border shadow-sm">
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
            <h5 class="m-0 font-weight-bold"><i class="fas fa-child mr-2"></i> Form Edit Data Anak</h5>
          </div>
          
          <div class="card-body p-4">
            <form action="" method="POST">
              
              <div class="alert alert-light border mb-4">
                  <div class="d-flex align-items-center">
                      <div class="mr-3">
                          <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                              <i class="fas fa-user-tie"></i>
                          </div>
                      </div>
                      <div>
                          <h6 class="font-weight-bold mb-0 text-primary"><?= htmlspecialchars($nama_peg) ?></h6>
                          <small class="text-muted">NIP/ID: <?= htmlspecialchars($id_peg) ?></small>
                      </div>
                  </div>
              </div>

              <div class="row">
                <div class="col-sm-2 mb-3">
                  <div class="form-group">
                    <label class="form-label-modern">Anak Ke-</label>
                    <input type="number" name="anak_ke" required class="form-control input-modern" value="<?= $row['anak_ke'] ?>">
                  </div>
                </div>
                <div class="col-sm-4 mb-3">
                  <div class="form-group">
                    <label class="form-label-modern">NIK</label>
                    <input type="text" name="nik" class="form-control input-modern" value="<?= $row['nik'] ?>">
                  </div>
                </div>
                <div class="col-sm-6 mb-3">
                  <div class="form-group">
                    <label class="form-label-modern">Nama Anak <span class="text-danger">*</span></label>
                    <input type="text" name="nama" required class="form-control input-modern" value="<?= $row['nama'] ?>">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6 mb-3">
                  <div class="form-group">
                    <label class="form-label-modern">Tempat Lahir</label>
                    <input type="text" name="tmp_lhr" class="form-control input-modern" value="<?= $row['tmp_lhr'] ?>">
                  </div>
                </div>
                <div class="col-sm-6 mb-3">
                  <div class="form-group"> 
                    <label class="form-label-modern">Tanggal Lahir</label>
                    <input type="date" name="tgl_lhr" class="form-control input-modern" value="<?= $row['tgl_lhr'] ?>" required>
                  </div>  
                </div>  
              </div>

              <div class="row">
                <div class="col-sm-4 mb-3">
                  <div class="form-group">
                    <label class="form-label-modern">Pendidikan</label>
                    <select name="pendidikan" class="form-control select2bs4">
                      <?php 
                        $pends = ["Belum Sekolah", "TK", "SD", "SLTP", "SLTA", "D3", "S1", "S2", "S3"];
                        foreach($pends as $p) {
                            $selected = ($row['pendidikan'] == $p) ? "selected" : "";
                            echo "<option value='$p' $selected>$p</option>";
                        }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-4 mb-3">
                  <div class="form-group">
                    <label class="form-label-modern">Pekerjaan</label>
                    <input type="text" name="pekerjaan" class="form-control input-modern" value="<?= $row['pekerjaan'] ?>">
                  </div>
                </div> 
                <div class="col-sm-4 mb-3">
                  <div class="form-group">
                    <label class="form-label-modern">Status Hubungan</label>
                      <select name="status_hub" class="form-control select2bs4">
                        <option value="Anak Kandung" <?= ($row['status_hub']=='Anak Kandung')?"selected":""; ?>>Anak Kandung</option>
                        <option value="Anak Angkat" <?= ($row['status_hub']=='Anak Angkat')?"selected":""; ?>>Anak Angkat</option>
                        <option value="Anak Tiri" <?= ($row['status_hub']=='Anak Tiri')?"selected":""; ?>>Anak Tiri</option>
                      </select>
                  </div>
                </div>    
              </div>

              <div class="row">
                  <div class="col-md-12 mb-3">
                      <div class="form-group">
                          <label class="form-label-modern">No. BPJS Anak</label>
                          <input type="text" name="bpjs_anak" class="form-control input-modern" value="<?= isset($row['bpjs_anak']) ? $row['bpjs_anak'] : '' ?>">
                      </div>
                  </div>
              </div>

              <div class="form-group mt-4 pt-3 border-top text-right">
                  <a href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?= $id_peg ?>" class="btn btn-light btn-modern border mr-2">Batal</a>
                  <button type="submit" name="edit" value="edit" class="btn btn-primary btn-modern shadow-sm">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    $('.select2bs4').select2({
      theme: 'bootstrap4',
      width: '100%'
    });
  });
</script>