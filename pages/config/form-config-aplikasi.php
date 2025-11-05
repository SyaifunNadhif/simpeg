<?php
include "dist/koneksi.php";
$id = isset($_GET['id']) ? $_GET['id'] : 1;
$result = mysqli_query($conn, "SELECT * FROM tb_config WHERE id_app='$id'");
$data = mysqli_fetch_array($result);

if (isset($_POST['save']) && $_POST['save'] == "save") {
  $nama_app = $_POST['nama_app'];
  $desc_app = $_POST['desc_app'];
  $alias_app = $_POST['alias_app'];
  $url_app = $_POST['url_app'];
  $anchor_app = $_POST['anchor_app'];
  $logo = $_FILES['logo']['name'];

  if (!empty($logo) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
    move_uploaded_file($_FILES['logo']['tmp_name'], "dist/img/profile/" . $logo);
  } else {
    $logo = $data['logo'];
  }

  $update = mysqli_query($conn, "UPDATE tb_config SET 
    nama_app='$nama_app', desc_app='$desc_app', alias_app='$alias_app', 
    logo='$logo', url_app='$url_app', anchor_app='$anchor_app' 
    WHERE id_app='$id'");

  if ($update) {
    echo "<script>Swal.fire('Berhasil', 'Konfigurasi telah disimpan', 'success')</script>";
    $data = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM tb_config WHERE id_app='$id'"));
  } else {
    echo "<script>Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan', 'error')</script>";
  }
}
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h1 class="h4 font-weight-bold text-dark mb-0">Konfigurasi <small class="text-muted">Aplikasi</small></h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="home-admin.php" class="text-primary"><i class="fas fa-home"></i>&nbsp;Dashboard</a></li>
        <li class="breadcrumb-item active text-muted">Konfigurasi Aplikasi</li>
      </ol>
    </div>
  </div>
</section>

<section class="content text-sm d-flex justify-content-center align-items-start">
  <form method="POST" enctype="multipart/form-data" class="text-sm w-100" style="max-width: 800px;">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header">
        <h3 class="card-title text-sm"><b>Form Konfigurasi</b></h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label>Nama Aplikasi</label>
          <input type="text" name="nama_app" class="form-control form-control-sm" value="<?= $data['nama_app'] ?>" required>
        </div>
        <div class="form-group">
          <label>Deskripsi</label>
          <input type="text" name="desc_app" class="form-control form-control-sm" value="<?= $data['desc_app'] ?>" required>
        </div>
        <div class="form-group">
          <label>Alias</label>
          <input type="text" name="alias_app" class="form-control form-control-sm" value="<?= $data['alias_app'] ?>">
        </div>
        <div class="form-group">
          <label>Logo</label>
          <input type="file" name="logo" class="form-control form-control-sm">
          <?php if ($data['logo']): ?>
            <div class="mt-2">
              <small>Logo Saat Ini:</small><br>
              <img src="dist/img/profile/<?= $data['logo']; ?>" width="100" class="img-thumbnail">
            </div>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label>URL / Link</label>
          <input type="url" name="url_app" class="form-control form-control-sm" value="<?= $data['url_app'] ?>">
        </div>
        <div class="form-group">
          <label>Text Anchor URL / Link</label>
          <input type="text" name="anchor_app" class="form-control form-control-sm" value="<?= $data['anchor_app'] ?>">
        </div>
      </div>
      <div class="card-footer bg-light text-right">
        <button type="submit" name="save" value="save" class="btn btn-sm btn-primary">
          Simpan
        </button>
        <a href="home-admin.php" class="btn btn-sm btn-secondary">Batal</a>
      </div>
    </div>
  </form>
</section>
