<section class="content-header">
    <h1>Konfigurasi<small>Aplikasi</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Aplikasi</li>
    </ol>
</section>
<div class="register-box">
<?php
  include "dist/koneksi.php";

  $id = isset($_GET['id']) ? $_GET['id'] : 1; // Gunakan default ID = 1 jika tidak dikirimkan

  if (isset($_POST['save']) && $_POST['save'] == "save") {
    $nama_app  = $_POST['nama_app'];
    $desc_app  = $_POST['desc_app'];
    $alias_app = $_POST['alias_app'];
    $url_app   = $_POST['url_app'];
    $anchor_app= $_POST['anchor_app'];
    $logo      = $_FILES['logo']['name'];

    if (strlen($logo) > 0) {
      if (is_uploaded_file($_FILES['logo']['tmp_name'])) {
        move_uploaded_file($_FILES['logo']['tmp_name'], "dist/img/profile/" . $logo);
      }
    }

    $update = mysqli_query($conn, "UPDATE tb_config SET nama_app='$nama_app', desc_app='$desc_app', alias_app='$alias_app', logo='$logo', url_app='$url_app', anchor_app='$anchor_app' WHERE id_app='$id'");

    if ($update) {
      echo "<div class='register-logo'><b>Config</b> Successful!</div>
            <div class='box box-primary'>
              <div class='register-box-body'>
                <p>Konfigurasi Berhasil</p>
                <div class='row'>
                  <div class='col-xs-8'></div>
                  <div class='col-xs-4'>
                    <button type='button' onclick=location.href='home-admin.php' class='btn btn-danger btn-block'>Next >></button>
                  </div>
                </div>
              </div>
            </div>";
    } else {
      echo "<div class='register-logo'><b>Oops!</b> 404 Error Server.</div>";
    }
  }
?>
</div>