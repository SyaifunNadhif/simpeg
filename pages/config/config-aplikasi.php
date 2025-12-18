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

  // 1. Sanitasi ID (Anti SQL Injection)
  $id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 1;

  // Ambil data lama dulu (PENTING: Biar logo lama gak hilang kalau gak upload baru)
  $q_old = mysqli_query($conn, "SELECT logo FROM tb_config WHERE id_app='$id'");
  $d_old = mysqli_fetch_array($q_old);

  if (isset($_POST['save']) && $_POST['save'] == "save") {
    // 2. Sanitasi Input (Anti SQL Injection)
    $nama_app   = mysqli_real_escape_string($conn, $_POST['nama_app']);
    $desc_app   = mysqli_real_escape_string($conn, $_POST['desc_app']);
    $alias_app  = mysqli_real_escape_string($conn, $_POST['alias_app']);
    $url_app    = mysqli_real_escape_string($conn, $_POST['url_app']);
    $anchor_app = mysqli_real_escape_string($conn, $_POST['anchor_app']);
    
    // Default logo pakai yang lama
    $logo = $d_old['logo'];

    // 3. Logic Upload Aman (Anti Shell .php)
    if (!empty($_FILES['logo']['name'])) {
        $filename = $_FILES['logo']['name'];
        $tmp_name = $_FILES['logo']['tmp_name'];
        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed  = array('png', 'jpg', 'jpeg', 'gif');

        if (in_array($ext, $allowed)) {
            // Rename file biar unik & aman
            $new_name = "profile_" . time() . "." . $ext;
            $target_dir = "dist/img/profile/";

            // Buat folder kalau belum ada
            if (!file_exists($target_dir)) { mkdir($target_dir, 0755, true); }

            if (move_uploaded_file($tmp_name, $target_dir . $new_name)) {
                $logo = $new_name;
                
                // (Opsional) Hapus file lama biar hemat storage
                if (!empty($d_old['logo']) && file_exists($target_dir . $d_old['logo'])) {
                    unlink($target_dir . $d_old['logo']);
                }
            }
        } else {
             echo "<div class='alert alert-warning'>Format file tidak valid! Hanya boleh JPG/PNG.</div>";
        }
    }

    $update = mysqli_query($conn, "UPDATE tb_config SET nama_app='$nama_app', desc_app='$desc_app', alias_app='$alias_app', logo='$logo', url_app='$url_app', anchor_app='$anchor_app' WHERE id_app='$id'");

    if ($update) {
      echo "<div class='register-logo'><b>Config</b> Successful!</div>
            <div class='box box-primary'>
              <div class='register-box-body'>
                <p class='text-success text-center'>Konfigurasi Berhasil Disimpan</p>
                <div class='row'>
                  <div class='col-xs-8'></div>
                  <div class='col-xs-4'>
                    <button type='button' onclick=location.href='home-admin.php' class='btn btn-success btn-block'>OK <i class='fa fa-check'></i></button>
                  </div>
                </div>
              </div>
            </div>";
    } else {
      echo "<div class='register-logo'><b>Oops!</b> Gagal Menyimpan.</div>";
    }
  }
?>
</div>