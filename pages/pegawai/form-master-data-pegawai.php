<?php
/*********************************************************
 * FILE    : pages/pegawai/form-master-data-pegawai.php
 * MODULE  : Form Tambah/Edit Pegawai (Fix Account Bug & UI)
 * VERSION : v2.0
 *********************************************************/

// Header & Breadcrumbs
$page_title    = "Form Pegawai";
$page_subtitle = "Kelola Data";
$breadcrumbs   = [
    ["label" => "Dashboard", "url" => "home-admin.php"],
    ["label" => "Data Pegawai", "url" => "home-admin.php?page=form-view-data-pegawai"],
    ["label" => "Form Pegawai"]
];
include "komponen/header.php";
include 'dist/koneksi.php';

$mode   = isset($_GET['mode']) ? $_GET['mode'] : 'tambah';
$id_peg = isset($_GET['id']) ? $_GET['id'] : null;

// Inisialisasi Data Kosong (Untuk Mode Tambah)
$data = array(
    'id_peg' => '', 'nip' => '', 'nama' => '', 'tempat_lhr' => '', 'tgl_lhr' => '',
    'agama' => '', 'jk' => '', 'gol_darah' => '', 'status_nikah' => '', 'status_kepeg' => '',
    'alamat' => '', 'telp' => '', 'email' => '', 'bpjstk' => '', 'bpjskes' => '', 'foto' => ''
);

// Data User Default (Untuk mencegah bug reset akun)
$dataUser = array('hak_akses' => 'User', 'status_aktif' => 'Y', 'username' => '', 'password' => '');

// --- LOGIKA EDIT & FIX BUG AKUN ---
if ($mode == 'edit' && $id_peg) {
    // 1. Ambil Data Pegawai
    $q = mysqli_query($conn, "SELECT * FROM tb_pegawai WHERE id_peg='".$id_peg."'");
    $data = mysqli_fetch_assoc($q);
    
    if (!$data) {
        echo "<script>alert('Data pegawai tidak ditemukan'); window.location='home-admin.php?page=form-view-data-pegawai';</script>";
        exit;
    }

    // 2. Ambil Data User Terkait (PENTING UNTUK FIX BUG)
    // Kita perlu tahu hak akses & status aktif saat ini agar tidak ter-reset saat save
    $qUser = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_pegawai='".$id_peg."'");
    if(mysqli_num_rows($qUser) > 0){
        $dataUser = mysqli_fetch_assoc($qUser);
    }
}

// --- LOGIKA REDIRECT (KEMBALI KE MANA SETELAH SAVE?) ---
// Jika admin mengedit orang lain -> ke Detail
// Jika user mengedit diri sendiri -> ke Profil
$redirect_back = "home-admin.php?page=form-view-data-pegawai"; // Default
if(isset($_SESSION['id_pegawai']) && $_SESSION['id_pegawai'] == $id_peg){
    $redirect_back = "home-admin.php?page=profil-pegawai";
} elseif($mode == 'edit') {
    $redirect_back = "home-admin.php?page=view-detail-data-pegawai&id_peg=" . $id_peg;
}
?>

<style>
    .content-wrapper { background-color: #f4f6f9; }
    .card-modern { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .form-header { background: linear-gradient(135deg, #007bff, #6610f2); color: white; border-radius: 15px 15px 0 0; padding: 20px; }
    .input-group-text { background-color: #fff; border-right: none; border-radius: 10px 0 0 10px; }
    .form-control { border-left: none; border-radius: 0 10px 10px 0; }
    .form-control:focus { box-shadow: none; border-color: #ced4da; }
    /* Fix select border */
    select.form-control { border-left: 1px solid #ced4da; border-radius: 10px; }
    
    .section-title { font-size: 0.9rem; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; border-bottom: 2px solid #e9ecef; padding-bottom: 5px; }
</style>

<div class="container-fluid mt-3 pb-5">
    <form action="pages/pegawai/simpan-data-pegawai.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        
        <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">
        <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($redirect_back); ?>">
        
        <?php if ($mode == 'edit'): ?>
            <input type="hidden" name="id_peg" value="<?php echo htmlspecialchars($data['id_peg']); ?>">
            
            <input type="hidden" name="hak_akses_lama" value="<?php echo $dataUser['hak_akses']; ?>">
            <input type="hidden" name="status_aktif_lama" value="<?php echo $dataUser['status_aktif']; ?>">
            <input type="hidden" name="hak_akses" value="<?php echo $dataUser['hak_akses']; ?>">
            <input type="hidden" name="status_aktif" value="<?php echo $dataUser['status_aktif']; ?>">
        <?php endif; ?>


        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card card-modern">
                    <div class="form-header">
                        <h4 class="m-0 font-weight-bold"><i class="fas fa-user-edit mr-2"></i> Form <?= ucfirst($mode) ?> Biodata</h4>
                        <p class="m-0 small opacity-75">Lengkapi data pegawai dengan benar.</p>
                    </div>
                    <div class="card-body p-4">

                        <div class="section-title"><i class="fas fa-id-card mr-2"></i> Identitas Utama</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>ID Pegawai <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-badge"></i></span></div>
                                    <input type="text" name="id_peg" class="form-control" value="<?php echo htmlspecialchars($data['id_peg']); ?>" 
                                           <?php echo $mode == 'edit' ? 'disabled' : 'name="id_peg" required'; ?> placeholder="Masukkan ID Pegawai">
                                </div>
                                <?php if($mode == 'edit'): ?><small class="text-muted">ID Pegawai tidak dapat diubah.</small><?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                                    <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($data['nama']); ?>" required placeholder="Nama lengkap beserta gelar">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Nomor Induk Kependudukan (NIK) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-fingerprint"></i></span></div>
                                    <input type="number" name="nip" class="form-control" value="<?php echo htmlspecialchars($data['nip']); ?>" required placeholder="16 digit NIK">
                                </div>
                            </div>
                        </div>

                        <div class="section-title mt-4"><i class="fas fa-user-tag mr-2"></i> Data Pribadi</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Tempat Lahir</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span></div>
                                    <input type="text" name="tempat_lhr" class="form-control" value="<?php echo htmlspecialchars($data['tempat_lhr']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Tanggal Lahir</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-calendar-alt"></i></span></div>
                                    <input type="date" name="tgl_lhr" class="form-control" value="<?php echo htmlspecialchars($data['tgl_lhr']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Agama</label>
                                <select name="agama" class="form-control custom-select" required>
                                    <option value="">-- Pilih Agama --</option>
                                    <?php foreach(array('Islam','Protestan','Katolik','Hindu','Budha','KongHuCu') as $a) {
                                        $sel = ($data['agama'] == $a) ? "selected" : "";
                                        echo "<option value='$a' $sel>$a</option>";
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Jenis Kelamin</label>
                                <select name="jk" class="form-control custom-select" required>
                                    <option value="">-- Pilih JK --</option>
                                    <option value="Laki-laki" <?php echo $data['jk'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="Perempuan" <?php echo $data['jk'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Gol. Darah</label>
                                <select name="gol_darah" class="form-control custom-select">
                                    <option value="-">-</option>
                                    <?php foreach (array('A','B','AB','O') as $gol) {
                                        $sel = ($data['gol_darah'] == $gol) ? "selected" : "";
                                        echo "<option value='$gol' $sel>$gol</option>";
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Status Pernikahan</label>
                                <select name="status_nikah" class="form-control custom-select">
                                    <?php foreach (array('Menikah','Belum Menikah','Janda','Duda') as $s) {
                                        $sel = ($data['status_nikah'] == $s) ? "selected" : "";
                                        echo "<option value='$s' $sel>$s</option>";
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Status Kepegawaian</label>
                                <select name="status_kepeg" class="form-control custom-select" required>
                                    <?php foreach (array('Tetap','Kontrak','Outsource') as $s) {
                                        $sel = ($data['status_kepeg'] == $s) ? "selected" : "";
                                        echo "<option value='$s' $sel>$s</option>";
                                    } ?>
                                </select>
                            </div>
                        </div>

                        <div class="section-title mt-4"><i class="fas fa-address-book mr-2"></i> Kontak & Administrasi</div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label>Alamat Domisili</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-home"></i></span></div>
                                    <textarea name="alamat" class="form-control" rows="2" required><?php echo htmlspecialchars($data['alamat']); ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>No. Telepon / WA</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                                    <input type="text" name="telp" class="form-control" value="<?php echo htmlspecialchars($data['telp']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($data['email']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>No. BPJS Ketenagakerjaan</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-briefcase-medical"></i></span></div>
                                    <input type="text" name="bpjstk" class="form-control" value="<?php echo htmlspecialchars($data['bpjstk']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>No. BPJS Kesehatan</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-heartbeat"></i></span></div>
                                    <input type="text" name="bpjskes" class="form-control" value="<?php echo htmlspecialchars($data['bpjskes']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="section-title mt-4"><i class="fas fa-camera mr-2"></i> Foto Profil</div>
                        <div class="form-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="foto" name="foto">
                                <label class="custom-file-label" for="foto">Pilih file foto...</label>
                            </div>
                            <?php if ($mode == 'edit' && $data['foto']): ?>
                                <small class="text-success mt-2 d-block"><i class="fa fa-check-circle"></i> Foto saat ini: <?php echo htmlspecialchars($data['foto']); ?></small>
                            <?php endif; ?>
                        </div>

                    </div>
                    <div class="card-footer bg-white text-right py-3 rounded-bottom">
                        <a href="<?php echo $redirect_back; ?>" class="btn btn-light rounded-pill px-4 mr-2">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="fas fa-save mr-2"></i> Simpan Data</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
  // Custom File Input Label
  $(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
  });

  // Bootstrap Validation
  (function() {
    'use strict';
    window.addEventListener('load', function() {
      var forms = document.getElementsByClassName('needs-validation');
      var validation = Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
          if (form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    }, false);
  })();
</script>