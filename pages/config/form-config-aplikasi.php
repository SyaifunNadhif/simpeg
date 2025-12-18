<?php
include "dist/koneksi.php";

// --- 1. LOGIC PHP & SECURITY FIX ---

// FIX: Sanitasi input GET ID untuk mencegah SQL Injection
$id_raw = isset($_GET['id']) ? $_GET['id'] : 1;
$id = mysqli_real_escape_string($conn, $id_raw);

$result = mysqli_query($conn, "SELECT * FROM tb_config WHERE id_app='$id'");
$data = mysqli_fetch_array($result);

// Variabel Notifikasi
$notif_pesan = "";
$notif_type = "";

if (isset($_POST['save']) && $_POST['save'] == "save") {
    $nama_app   = mysqli_real_escape_string($conn, $_POST['nama_app']);
    $desc_app   = mysqli_real_escape_string($conn, $_POST['desc_app']);
    $alias_app  = mysqli_real_escape_string($conn, $_POST['alias_app']);
    $url_app    = mysqli_real_escape_string($conn, $_POST['url_app']);
    $anchor_app = mysqli_real_escape_string($conn, $_POST['anchor_app']);
    
    // --- LOGIC UPLOAD LOGO (FIX PATH ke dist/img/) ---
    $logo = $data['logo']; 
    
    if (!empty($_FILES['logo']['name'])) {
        $filename = $_FILES['logo']['name'];
        $tmp_name = $_FILES['logo']['tmp_name'];
        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // SECURITY: Whitelist Ekstensi File (Anti Shell)
        $allowed_ext = array('png', 'jpg', 'jpeg', 'gif');
        
        if(in_array($ext, $allowed_ext)){
            // Rename file biar aman & unik
            $new_name = "app_logo_" . time() . "." . $ext; 
            
            // FOLDER TUJUAN
            $target_dir = "dist/img/";
            
            // Cek folder, buat jika belum ada
            // FIX: Ubah 0777 jadi 0755 biar lebih aman
            if (!file_exists($target_dir)) { mkdir($target_dir, 0755, true); }

            if (move_uploaded_file($tmp_name, $target_dir . $new_name)) {
                $logo = $new_name;
                
                // Hapus logo lama jika ada dan bukan default
                if (!empty($data['logo']) && file_exists($target_dir . $data['logo']) && $data['logo'] != 'default.png' && $data['logo'] != 'no-image.jpg') {
                    unlink($target_dir . $data['logo']);
                }
            }
        } else {
             // Jika ekstensi file salah
             $notif_type = "warning";
             $notif_pesan = "<b>Format Salah!</b> Hanya diperbolehkan upload gambar (JPG, PNG).";
        }
    }

    // Jalankan update hanya jika tidak ada error validasi sebelumnya
    if (empty($notif_pesan)) {
        $update = mysqli_query($conn, "UPDATE tb_config SET 
            nama_app='$nama_app', desc_app='$desc_app', alias_app='$alias_app', 
            logo='$logo', url_app='$url_app', anchor_app='$anchor_app' 
            WHERE id_app='$id'");

        if ($update) {
            $notif_type = "success";
            $notif_pesan = "<b>Berhasil!</b> Konfigurasi aplikasi telah diperbarui.";
            // Refresh data
            $data = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM tb_config WHERE id_app='$id'"));
        } else {
            $notif_type = "danger";
            $notif_pesan = "<b>Gagal!</b> Terjadi kesalahan saat menyimpan data.";
        }
    }
}
?>

<style>
    /* Card Style */
    .card-modern {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    /* Input Style */
    .form-control-modern {
        border-radius: 0 8px 8px 0 !important;
        border: 1px solid #ced4da;
        padding: 9px 12px;
        height: auto;
        font-size: 0.9rem;
    }
    .form-control-modern:focus {
        border-color: #007bff;
        box-shadow: none;
    }
    
    /* Icon Input Group */
    .input-group-text-modern {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-right: none;
        border-radius: 8px 0 0 8px !important;
        color: #6c757d;
        width: 40px;
        justify-content: center;
    }

    /* Logo Preview Container */
    .logo-preview-wrapper {
        position: relative;
        width: 130px;
        height: 130px;
        margin: 0 auto 15px;
        border-radius: 15px; 
        border: 3px solid #e9ecef;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        overflow: hidden;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .logo-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain; 
        padding: 5px;
    }

    /* Custom File Button */
    .btn-upload-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }
    .btn-upload-wrapper input[type=file] {
        font-size: 100px;
        position: absolute;
        left: 0; top: 0;
        opacity: 0;
        cursor: pointer;
    }
    
    /* Label Form */
    .form-label-modern {
        font-weight: 600;
        font-size: 0.8rem;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
</style>

<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 font-weight-bold text-dark mb-0">Konfigurasi Aplikasi</h1>
    </div>
  </div>
</section>

<section class="content text-sm">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-lg-9 col-md-11">

        <?php if (!empty($notif_pesan)): ?>
        <div class="alert alert-<?= $notif_type ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fas fa-<?= ($notif_type == 'success') ? 'check-circle' : 'exclamation-triangle'; ?> mr-2"></i>
            <?= $notif_pesan ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
          <div class="card card-modern card-outline card-primary">
            
            <div class="card-body p-4">
                
                <div class="row">
                    <div class="col-md-4 text-center border-right">
                        <label class="form-label-modern mb-3">Logo Aplikasi</label>
                        
                        <div class="logo-preview-wrapper">
                            <?php 
                                $path_logo = "dist/img/" . $data['logo'];
                                // Fallback image jika file tidak ditemukan
                                $src_logo = (!empty($data['logo']) && file_exists($path_logo)) ? $path_logo : "dist/img/no-image.jpg";
                            ?>
                            <img src="<?= $src_logo ?>?t=<?= time() ?>" class="logo-img" id="previewLogo">
                        </div>
                        
                        <div class="btn-upload-wrapper mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4 font-weight-bold">
                                <i class="fas fa-upload mr-1"></i> Pilih Gambar
                            </button>
                            <input type="file" name="logo" accept="image/*" onchange="previewImage(this);">
                        </div>
                        <p class="text-muted small mt-2">Format: JPG/PNG (Max. 2MB)</p>
                    </div>

                    <div class="col-md-8 pl-md-4">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="form-label-modern">Nama Aplikasi</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text input-group-text-modern"><i class="fas fa-desktop"></i></span>
                                    </div>
                                    <input type="text" name="nama_app" class="form-control form-control-modern" value="<?= htmlspecialchars($data['nama_app']) ?>" required placeholder="Contoh: SIMPEG PRO">
                                </div>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="form-label-modern">Singkatan / Alias</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text input-group-text-modern"><i class="fas fa-tag"></i></span>
                                    </div>
                                    <input type="text" name="alias_app" class="form-control form-control-modern" value="<?= htmlspecialchars($data['alias_app']) ?>" placeholder="Contoh: SIMPEG">
                                </div>
                            </div>

                            <div class="col-12 form-group">
                                <label class="form-label-modern">Deskripsi Singkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text input-group-text-modern"><i class="fas fa-align-left"></i></span>
                                    </div>
                                    <input type="text" name="desc_app" class="form-control form-control-modern" value="<?= htmlspecialchars($data['desc_app']) ?>" placeholder="Sistem Informasi Kepegawaian...">
                                </div>
                            </div>

                            <div class="col-12"><hr class="my-3"></div>

                            <div class="col-md-6 form-group">
                                <label class="form-label-modern">URL Website</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text input-group-text-modern"><i class="fas fa-link"></i></span>
                                    </div>
                                    <input type="url" name="url_app" class="form-control form-control-modern" value="<?= htmlspecialchars($data['url_app']) ?>" placeholder="https://...">
                                </div>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="form-label-modern">Teks Footer (Anchor)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text input-group-text-modern"><i class="fas fa-copyright"></i></span>
                                    </div>
                                    <input type="text" name="anchor_app" class="form-control form-control-modern" value="<?= htmlspecialchars($data['anchor_app']) ?>" placeholder="Nama Instansi / Perusahaan">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card-footer bg-white text-right border-top-0 pb-4 pr-4">
                <a href="home-admin.php" class="btn btn-light border btn-sm rounded-pill px-4 font-weight-bold mr-2 text-muted">
                    <i class="fas fa-times mr-1"></i> Batal
                </a>
                <button type="submit" name="save" value="save" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold shadow-sm">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                </button>
            </div>

          </div>
        </form>

      </div>
    </div>
  </div>
</section>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewLogo').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>