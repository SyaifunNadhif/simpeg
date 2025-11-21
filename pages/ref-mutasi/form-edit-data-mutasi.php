<?php
/*********************************************************
 * FILE    : pages/mutasi/form-edit-data-mutasi.php
 * MODULE  : Edit Data Mutasi (Modern UI & Fix Logic)
 * VERSION : v3.0
 *********************************************************/

include "dist/koneksi.php";
include "dist/library.php";

// 1. Ambil ID dari URL & Validasi
if (isset($_GET['id_mutasi'])) {
    $id_mutasi = mysqli_real_escape_string($conn, $_GET['id_mutasi']);
} else {
    echo "<script>window.history.back();</script>";
    exit;
}

// 2. Proses Update Data (Logic ditaruh di atas HTML)
$status_update = '';

if (isset($_POST['edit'])) {
    $jns_mutasi = mysqli_real_escape_string($conn, $_POST['jns_mutasi']);
    $no_mutasi  = mysqli_real_escape_string($conn, $_POST['no_mutasi']);
    
    // Format tanggal untuk database (Y-m-d)
    $tgl_mutasi = $_POST['tgl_mutasi']; 
    $tmt        = $_POST['tmt'];

    // Cek apakah ada file baru diupload
    $query_update_file = "";
    if (!empty($_FILES['sk_mutasi']['name'])) {
        $sk_name = $_FILES['sk_mutasi']['name'];
        $x = explode('.', $sk_name);
        $ext = strtolower(end($x));
        $sk_baru = "SK_MUTASI_EDIT_".rand(1,999).".".$ext;
        
        // Pastikan path folder sesuai struktur project Anda
        $target_dir = "pages/assets/sk_mutasi/"; 
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        if(move_uploaded_file($_FILES['sk_mutasi']['tmp_name'], $target_dir . $sk_baru)){
             $query_update_file = ", sk_mutasi='$sk_baru'";
        }
    }

    // Query Update Utama
    $sql_update = "UPDATE tb_mutasi SET 
                   jns_mutasi='$jns_mutasi', 
                   tgl_mutasi='$tgl_mutasi', 
                   no_mutasi='$no_mutasi', 
                   tmt='$tmt' 
                   $query_update_file
                   WHERE id_mutasi='$id_mutasi'";
                   
    $update = mysqli_query($conn, $sql_update);

    if ($update) {
        $status_update = 'sukses';
    } else {
        $status_update = 'gagal';
    }
}

// 3. Ambil Data Lama untuk Ditampilkan di Form
$tampilMut = mysqli_query($conn, "SELECT * FROM tb_mutasi WHERE id_mutasi='$id_mutasi'");
$mut       = mysqli_fetch_array($tampilMut);

// Jika data tidak ditemukan
if (!$mut) {
    echo "<div class='alert alert-danger m-3'>Data mutasi tidak ditemukan.</div>";
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
        background: #ffc107; /* Warna Kuning Warning untuk Edit */
        color: #333;
        border-bottom: 1px solid #f1f1f1;
        padding: 20px; border-radius: 15px 15px 0 0;
    }
    .input-modern {
        border-radius: 10px; border: 1px solid #e2e8f0;
        padding: 10px 15px; height: 45px; font-size: 0.95rem;
        width: 100%;
    }
    .input-modern:focus { border-color: #ffc107; box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2); outline: none; }
    
    /* Fix Date Input agar ikon kalender muncul */
    .date-wrapper { position: relative; }
    input[type="date"]::-webkit-calendar-picker-indicator {
        background: transparent; bottom: 0; color: transparent; cursor: pointer;
        height: auto; left: 0; position: absolute; right: 0; top: 0; width: auto;
    }
    .date-icon { position: absolute; right: 15px; top: 12px; pointer-events: none; color: #6c757d; }

    .btn-modern {
        border-radius: 50px; padding: 10px 30px; font-weight: 600; transition: 0.3s;
    }
    .btn-modern:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.8rem;">Edit Mutasi</h1>
                <p class="text-muted mb-0">Perbarui data mutasi pegawai</p>
            </div>
            <div>
                <ol class="breadcrumb bg-transparent p-0 m-0 small">
                    <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="home-admin.php?page=form-view-data-mutasi">Data Mutasi</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
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
                        <h5 class="m-0 font-weight-bold"><i class="fas fa-edit mr-2"></i> Form Edit Data Mutasi</h5>
                    </div>
                    
                    <div class="card-body p-4">
                        <form role="form" action="" method="POST" enctype="multipart/form-data">
                            
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-muted small text-uppercase mb-2">Pegawai</label>
                                <?php
                                    $qPeg = mysqli_query($conn, "SELECT nama FROM tb_pegawai WHERE id_peg='".$mut['id_peg']."'");
                                    $dPeg = mysqli_fetch_array($qPeg);
                                ?>
                                <input type="text" class="form-control input-modern bg-light" value="<?= isset($dPeg['nama']) ? $dPeg['nama'] : '' ?> (ID: <?= $mut['id_peg'] ?>)" readonly>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-2">Jenis Mutasi <span class="text-danger">*</span></label>
                                        <select name="jns_mutasi" class="form-control input-modern" required>
                                            <option value="">-- Pilih Jenis --</option>
                                            <option value="Pensiun" <?= ($mut['jns_mutasi']=='Pensiun')?'selected':''; ?>>Pensiun</option>
                                            <option value="Pensiun Dini" <?= ($mut['jns_mutasi']=='Pensiun Dini')?'selected':''; ?>>Pensiun Dini</option>
                                            <option value="Meninggal Dunia" <?= ($mut['jns_mutasi']=='Meninggal Dunia')?'selected':''; ?>>Meninggal Dunia</option>
                                            <option value="Pengunduran Diri" <?= ($mut['jns_mutasi']=='Pengunduran Diri')?'selected':''; ?>>Pengunduran Diri</option>
                                            <option value="PTDH" <?= ($mut['jns_mutasi']=='PTDH')?'selected':''; ?>>PTDH</option>
                                            <option value="Mutasi Jabatan" <?= ($mut['jns_mutasi']=='Mutasi Jabatan')?'selected':''; ?>>Mutasi Jabatan</option>
                                            <option value="Mutasi Unit Kerja" <?= ($mut['jns_mutasi']=='Mutasi Unit Kerja')?'selected':''; ?>>Mutasi Unit Kerja</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-2">Nomor SK <span class="text-danger">*</span></label>
                                        <input type="text" name="no_mutasi" class="form-control input-modern" value="<?= $mut['no_mutasi'] ?>" required style="text-transform:uppercase">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-2">Tanggal SK <span class="text-danger">*</span></label>
                                        <div class="date-wrapper">
                                            <input type="date" name="tgl_mutasi" class="form-control input-modern" value="<?= $mut['tgl_mutasi'] ?>" required>
                                            <i class="fa fa-calendar date-icon"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-2">TMT (Terhitung Mulai Tanggal) <span class="text-danger">*</span></label>
                                        <div class="date-wrapper">
                                            <input type="date" name="tmt" class="form-control input-modern" value="<?= $mut['tmt'] ?>" required>
                                            <i class="fa fa-calendar-check date-icon text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-muted small text-uppercase mb-2">Update SK Mutasi (Opsional)</label>
                                <div class="custom-file">
                                    <input type="file" name="sk_mutasi" class="custom-file-input" id="sk_mutasi">
                                    <label class="custom-file-label input-modern pt-2" for="sk_mutasi">Pilih file baru jika ingin mengubah...</label>
                                </div>
                                <?php if(!empty($mut['sk_mutasi'])): ?>
                                    <small class="text-success mt-2 d-block"><i class="fa fa-check-circle"></i> File saat ini: <?= $mut['sk_mutasi'] ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group text-right mt-4 border-top pt-4">
                                <a href="home-admin.php?page=form-view-data-mutasi" class="btn btn-light btn-modern mr-2 text-muted border">Batal</a>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  $(function () {
    // Input File Label Change
    $(".custom-file-input").on("change", function() {
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

    // SweetAlert Notification Logic
    var status = "<?= $status_update ?>";
    if (status == 'sukses') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data mutasi berhasil diperbarui.',
            showConfirmButton: false,
            timer: 1500
        }).then(function() {
            window.location.href = 'home-admin.php?page=form-view-data-mutasi';
        });
    } else if (status == 'gagal') {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat mengupdate data.'
        });
    }
  });
</script>