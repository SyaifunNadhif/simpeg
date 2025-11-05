<?php
session_start();
if(!isset($_SESSION['id_user'])){
  die("<b>Oops!</b> Access Failed.
    <p>Sistem Logout. Anda harus melakukan Login kembali.</p>
    <button type='button' onclick=location.href='index.php'>Back</button>");
}
if($_SESSION['hak_akses']!="Admin"){
  die("<b>Oops!</b> Access Failed.
    <p>Anda Bukan Admin.</p>
    <button type='button' onclick=location.href='index.php'>Back</button>");
}

$timeout = 1; // setting timeout dalam menit

$timeout = $timeout * 900; // menit ke detik
if(isset($_SESSION['start_session'])){
  $elapsed_time = time()-$_SESSION['start_session'];
  if($elapsed_time >= $timeout){
    session_destroy();
    echo "<script type='text/javascript'>alert('Sesi telah berakhir');window.location='$logout'</script>";
  }
}  $logout = "index.php"; // redirect halaman logout


$_SESSION['start_session']=time();

include "dist/koneksi.php";
$App=mysqli_query($conn, "SELECT * FROM tb_config WHERE id_app='1'");
$set=mysqli_fetch_array($App);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $set['desc_app']?></title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- pace-progress -->
  <link rel="stylesheet" href="plugins/pace-progress/themes/black/pace-theme-flash.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Toastr -->
  <link rel="stylesheet" href="plugins/toastr/toastr.min.css">
  <!-- SweetAlert -->
  <link rel="stylesheet" href="dist/css/sweetalert.css">
  <link rel="stylesheet" href="plugins/sweetalert/dist/sweetalert2.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <link rel="shortcut icon" href="dist/favicon.ico" type="image/x-icon" />

  <!-- Bootstrap Color Picker -->
  <link rel="stylesheet" href="plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
  <!-- Bootstrap4 Duallistbox -->
  <link rel="stylesheet" href="plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
  <!-- BS Stepper -->
  <link rel="stylesheet" href="plugins/bs-stepper/css/bs-stepper.min.css">
  <!-- dropzonejs -->
  <link rel="stylesheet" href="plugins/dropzone/min/dropzone.min.css"> 
</head>
<body class="hold-transition sidebar-mini layout-fixed text-sm pace-info">
  <div class="wrapper">

    

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="home-admin.php" class="nav-link">Home</a>
        </li>      
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown user-menu">
          <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
            <img src="assets/img/<?php echo $_SESSION['foto']; ?>" class="user-image img-circle elevation-2" alt="User Image">
            <span class="d-none d-md-inline"><?php echo $_SESSION['nama_user']; ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <!-- User image -->
            <li class="user-header bg-primary">
              <img src="dist/img/avatar.png" class="img-circle elevation-2" alt="User Image">
              <p>
                <?php echo $_SESSION['nama_user']; ?>
                <small>Online</small>
              </p>
            </li>
            <!-- Menu Footer-->
            <li class="user-footer">
              <a href="profil-user.php" class="btn btn-default btn-flat">Profil</a>
              <a href="logout.php" class="btn btn-default btn-flat float-right">Sign out</a>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
    <!-- /.navbar -->


    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="home-admin.php" class="brand-link">
        <img src="dist/img/bkk.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"><?php echo $set['nama_app']?></span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-2 pb-2 mb-2 d-flex">
          <div class="image">
            <img src="dist/img/avatar.png" class="img-circle elevation-2" alt="User Image">
          </div>
          <div class="info">
            <a href="#" class="d-block"><b><h5><?php echo $_SESSION['nama_user'] ?></h5></b></a>
            <p><a href="#" class="d-block">Online</a>
            </div>        
          </div>

          <div class="user-panel mt-2 pb-2 mb-2 d-flex">
            <div class="info">
              <a class="btn btn-info" href="" title="profil"><i class="far fa-user"></i>USER PROFIL</a>
              <a class="btn btn-danger" href="pages/login/act-logout.php" title="signout"><i class="fas fa-sign-out-alt"></i>SIGN OUT</a>
            </div>
          </div>

          <!-- SidebarSearch Form -->
          <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
              <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-sidebar">
                  <i class="fas fa-search fa-fw"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Sidebar Menu -->
          <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
           with font-awesome or any other icon font library -->
           <li class="nav-header">MENU APLIKASI</li>
           <li class="nav-item">
            <a href="home-admin.php" class="nav-link">
              <i class="fas fa-tachometer-alt nav-icon"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <!--
          <li class="nav-item">
            <a href="home-admin.php?page=dashboard-cabang" class="nav-link">
              <i class="fas fa-tachometer-alt nav-icon"></i>
              <p>Dashboard Cabang</p>
            </a>
          </li>
          -->
          <li class="nav-item">
            <a href="home-admin.php?page=form-config-aplikasi" class="nav-link">
              <i class="fas fa-circle nav-icon"></i>
              <p>Konfigurasi Aplikasi</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="far fas fa-circle nav-icon"></i>
              <p>
                Data Pegawai
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="home-admin.php?page=form-view-data-pegawai" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Tampil Data Pegawai</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="home-admin.php?page=form-master-data-pegawai" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Entry Data Pegawai</p>
                </a>
              </li> 
              <li class="nav-item">
                <a href="home-admin.php?page=form-upload-data-pegawai" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Entry Data Pegawai Kolektif</p>
                </a>
              </li>                                 
            </ul>
          </li>                              
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-circle"></i>
              <p>
                Data Referensi
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Keluarga
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-suami-istri" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Suami Istri</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-anak" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Anak</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-ortu" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Orang Tua</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Pendidikan
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-sekolah" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Pendidikan Formal</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-diklat" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Pendidikan Informal</p>
                    </a>
                  </li>
                </ul>
              </li> 
              <li class="nav-item">
                <a href="home-admin.php?page=form-view-data-sertifikasi" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Sertifikasi
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
              </li>  
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Mutasi Pegawai
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-angkat" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Pegawai Masuk</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-mutasi" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Pegawai Keluar</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-jabatan" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Mutasi</p>
                    </a>
                  </li>                  
                </ul>
              </li>                            
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Riwayat Kepegawaian
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-angkat" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Pengangkatan</p>
                    </a>
                  </li>                    
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-cuti" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Cuti</p>
                    </a>
                  </li>                                  
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-penghargaan" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Penghargaan</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-hukuman" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Pelanggaran</p>
                    </a>
                  </li>                  
                </ul>
              </li>
            </ul>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="far fas fa-circle nav-icon"></i>
                <p>
                  Laporan-laporan
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="home-admin.php?page=daftar-urut-kepangkatan" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Daftar Kepegawaian</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="home-admin.php?page=formasi" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Laporan Formasi Pegawai</p>
                  </a>
                </li>                  
                <li class="nav-item">
                  <a href="home-admin.php?page=keadaan-pegawai" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Keadaan Pegawai</p>
                  </a>
                </li>                 
              </ul>
            </li>  
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
      <?php
      $page = (isset($_GET['page']))? $_GET['page'] : "main";
      switch ($page) {
        case 'form-view-data-pegawai': include "pages/master/form-view-data-pegawai.php"; break;
        case 'form-view-data-purna': include "pages/master/form-view-data-purna.php"; break;
        case 'form-master-data-pegawai': include "pages/master/form-master-data-pegawai.php"; break;
        case 'form-upload-data-pegawai': include "pages/master/form-upload-data-pegawai.php"; break;
        case 'upload-data-pegawai': include "pages/master/upload-data-pegawai.php"; break;
        case 'master-data-pegawai': include "pages/master/master-data-pegawai.php"; break;
        case 'form-edit-data-pegawai': include "pages/master/form-edit-data-pegawai.php"; break;
        case 'edit-data-pegawai': include "pages/master/edit-data-pegawai.php"; break;
        case 'delete-data-pegawai': include "pages/master/delete-data-pegawai.php"; break;
        case 'view-detail-data-pegawai': include "pages/master/view-detail-data-pegawai.php"; break;

        case 'form-view-data-suami-istri': include "pages/ref-keluarga/form-view-data-suami-istri.php"; break;
        case 'form-master-data-suami-istri': include "pages/ref-keluarga/form-master-data-suami-istri.php"; break;
        case 'master-data-suami-istri': include "pages/ref-keluarga/master-data-suami-istri.php"; break;
        case 'form-edit-data-suami-istri': include "pages/ref-keluarga/form-edit-data-suami-istri.php"; break;
        case 'edit-data-suami-istri': include "pages/ref-keluarga/edit-data-suami-istri.php"; break;
        case 'delete-data-suami-istri': include "pages/ref-keluarga/delete-data-suami-istri.php"; break;

        case 'form-view-data-kantor': include "pages/kantor/form-view-data-kantor.php"; break;
        case 'form-kantor-data-kantor': include "pages/kantor/form-kantor-data-kantor.php"; break;
        case 'kantor-data-kantor': include "pages/kantor/kantor-data-kantor.php"; break;
        case 'form-edit-data-kantor': include "pages/kantor/form-edit-data-kantor.php"; break;
        case 'edit-data-kantor': include "pages/kantor/edit-data-kantor.php"; break;
        case 'delete-data-kantor': include "pages/kantor/delete-data-kantor.php"; break;
        case 'view-detail-data-kantor': include "pages/kantor/view-detail-data-kantor.php"; break;

        case 'form-view-data-anak': include "pages/ref-keluarga/form-view-data-anak.php"; break;
        case 'form-master-data-anak': include "pages/ref-keluarga/form-master-data-anak.php"; break;
        case 'master-data-anak': include "pages/ref-keluarga/master-data-anak.php"; break;
        case 'form-edit-data-anak': include "pages/ref-keluarga/form-edit-data-anak.php"; break;
        case 'edit-data-anak': include "pages/ref-keluarga/edit-data-anak.php"; break;
        case 'delete-data-anak': include "pages/ref-keluarga/delete-data-anak.php"; break;

        case 'form-view-data-ortu': include "pages/ref-keluarga/form-view-data-ortu.php"; break;
        case 'form-master-data-ortu': include "pages/ref-keluarga/form-master-data-ortu.php"; break;
        case 'master-data-ortu': include "pages/ref-keluarga/master-data-ortu.php"; break;
        case 'form-edit-data-ortu': include "pages/ref-keluarga/form-edit-data-ortu.php"; break;
        case 'edit-data-ortu': include "pages/ref-keluarga/edit-data-ortu.php"; break;
        case 'delete-data-ortu': include "pages/ref-keluarga/delete-data-ortu.php"; break;

        case 'form-ganti-foto': include "pages/master/form-ganti-foto.php"; break;
        case 'ganti-foto': include "pages/master/ganti-foto.php"; break;

        case 'form-master-data-bahasa': include "pages/ref-pendidikan/form-master-data-bahasa.php"; break;
        case 'master-data-bahasa': include "pages/ref-pendidikan/master-data-bahasa.php"; break;
        case 'form-edit-data-bahasa': include "pages/ref-pendidikan/form-edit-data-bahasa.php"; break;
        case 'edit-data-bahasa': include "pages/ref-pendidikan/edit-data-bahasa.php"; break;
        case 'delete-data-bahasa': include "pages/ref-pendidikan/delete-data-bahasa.php"; break;

        case 'form-view-data-sekolah': include "pages/ref-pendidikan/form-view-data-sekolah.php"; break;
        case 'form-master-data-sekolah': include "pages/ref-pendidikan/form-master-data-sekolah.php"; break;
        case 'master-data-sekolah': include "pages/ref-pendidikan/master-data-sekolah.php"; break;
        case 'form-edit-data-sekolah': include "pages/ref-pendidikan/form-edit-data-sekolah.php"; break;
        case 'edit-data-sekolah': include "pages/ref-pendidikan/edit-data-sekolah.php"; break;
        case 'delete-data-sekolah': include "pages/ref-pendidikan/delete-data-sekolah.php"; break;
        case 'set-pendidikan-akhir': include "pages/ref-pendidikan/set-pendidikan-akhir.php"; break;

        case 'form-view-data-sertifikasi': include "pages/ref-sertifikasi/form-view-data-sertifikasi.php"; break;    
       case 'view-detail-data-sertifikasi': include "pages/ref-sertifikasi/view-detail-data-sertifikasi.php"; break;
        case 'form-master-data-sertifikasi': include "pages/ref-sertifikasi/form-master-data-sertifikasi.php"; break;
        case 'master-data-sertifikasi': include "pages/ref-sertifikasi/master-data-sertifikasi.php"; break;
        case 'form-edit-data-sertifikasi': include "pages/ref-sertifikasi/form-edit-data-sertifikasi.php"; break;
        case 'edit-data-sertifikasi': include "pages/ref-sertifikasi/edit-data-sertifikasi.php"; break;
        case 'delete-data-sertifikasi': include "pages/ref-sertifikasi/delete-data-sertifikasi.php"; break;
        case 'upload-data-sertifikasi': include "pages/ref-sertifikasi/upload-data-sertifikasi.php"; break;
        case 'download-sertif': include "pages/ref-sertifikasi/download-sertif.php"; break;        
            

        case 'form-view-data-jabatan': include "pages/ref-jabatan/form-view-data-jabatan.php"; break;
        case 'form-master-data-jabatan': include "pages/ref-jabatan/form-master-data-jabatan.php"; break;
        case 'master-data-jabatan': include "pages/ref-jabatan/master-data-jabatan.php"; break;
        case 'form-edit-data-jabatan': include "pages/ref-jabatan/form-edit-data-jabatan.php"; break;
        case 'edit-data-jabatan': include "pages/ref-jabatan/edit-data-jabatan.php"; break;
        case 'delete-data-jabatan': include "pages/ref-jabatan/delete-data-jabatan.php"; break;
        case 'set-jabatan-sekarang': include "pages/ref-jabatan/set-jabatan-sekarang.php"; break;
        case 'upload-data-jabatan': include "pages/ref-jabatan/upload-data-jabatan.php"; break;        

        case 'form-master-data-pangkat': include "pages/ref-riwayat/form-master-data-pangkat.php"; break;
        case 'master-data-pangkat': include "pages/ref-riwayat/master-data-pangkat.php"; break;
        case 'form-edit-data-pangkat': include "pages/ref-riwayat/form-edit-data-pangkat.php"; break;
        case 'edit-data-pangkat': include "pages/ref-riwayat/edit-data-pangkat.php"; break;
        case 'delete-data-pangkat': include "pages/ref-riwayat/delete-data-pangkat.php"; break;
        case 'set-pangkat-sekarang': include "pages/ref-riwayat/set-pangkat-sekarang.php"; break;

        case 'form-view-data-hukuman': include "pages/ref-pelanggaran/form-view-data-hukuman.php"; break;
        case 'form-master-data-hukuman': include "pages/ref-pelanggaran/form-master-data-hukuman.php"; break;
        case 'master-data-hukuman': include "pages/ref-pelanggaran/master-data-hukuman.php"; break;
        case 'form-edit-data-hukuman': include "pages/ref-pelanggaran/form-edit-data-hukuman.php"; break;
        case 'edit-data-hukuman': include "pages/ref-pelanggaran/edit-data-hukuman.php"; break;
        case 'delete-data-hukuman': include "pages/ref-pelanggaran/delete-data-hukuman.php"; break;

        case 'form-view-data-diklat': include "pages/ref-diklat/form-view-data-diklat.php"; break;
        case 'view-detail-data-diklat': include "pages/ref-diklat/view-detail-data-diklat.php"; break;
        case 'form-master-data-diklat': include "pages/ref-diklat/form-master-data-diklat.php"; break;
        case 'master-data-diklat': include "pages/ref-diklat/master-data-diklat.php"; break;
        case 'form-edit-data-diklat': include "pages/ref-diklat/form-edit-data-diklat.php"; break;
        case 'edit-data-diklat': include "pages/ref-diklat/edit-data-diklat.php"; break;
        case 'delete-data-diklat': include "pages/ref-diklat/delete-data-diklat.php"; break;
        case 'upload-data-diklat': include "pages/ref-diklat/upload-data-diklat.php"; break;

        case 'form-master-data-penghargaan': include "pages/ref-riwayat/form-master-data-penghargaan.php"; break;
        case 'master-data-penghargaan': include "pages/ref-riwayat/master-data-penghargaan.php"; break;
        case 'form-edit-data-penghargaan': include "pages/ref-riwayat/form-edit-data-penghargaan.php"; break;
        case 'edit-data-penghargaan': include "pages/ref-riwayat/edit-data-penghargaan.php"; break;
        case 'delete-data-penghargaan': include "pages/ref-riwayat/delete-data-penghargaan.php"; break;

        case 'form-master-data-penugasan': include "pages/ref-riwayat/form-master-data-penugasan.php"; break;
        case 'master-data-penugasan': include "pages/ref-riwayat/master-data-penugasan.php"; break;
        case 'form-edit-data-penugasan': include "pages/ref-riwayat/form-edit-data-penugasan.php"; break;
        case 'edit-data-penugasan': include "pages/ref-riwayat/edit-data-penugasan.php"; break;
        case 'delete-data-penugasan': include "pages/ref-riwayat/delete-data-penugasan.php"; break;

        case 'form-master-data-seminar': include "pages/ref-riwayat/form-master-data-seminar.php"; break;
        case 'master-data-seminar': include "pages/ref-riwayat/master-data-seminar.php"; break;
        case 'form-edit-data-seminar': include "pages/ref-riwayat/form-edit-data-seminar.php"; break;
        case 'edit-data-seminar': include "pages/ref-riwayat/edit-data-seminar.php"; break;
        case 'delete-data-seminar': include "pages/ref-riwayat/delete-data-seminar.php"; break;

        case 'form-view-data-cuti': include "pages/ref-riwayat/form-view-data-cuti.php"; break;
        case 'form-master-data-cuti': include "pages/ref-riwayat/form-master-data-cuti.php"; break;
        case 'master-data-cuti': include "pages/ref-riwayat/master-data-cuti.php"; break;
        case 'form-edit-data-cuti': include "pages/ref-riwayat/form-edit-data-cuti.php"; break;
        case 'edit-data-cuti': include "pages/ref-riwayat/edit-data-cuti.php"; break;
        case 'delete-data-cuti': include "pages/ref-riwayat/delete-data-cuti.php"; break;

        case 'form-master-data-dp3': include "pages/ref-riwayat/form-master-data-dp3.php"; break;
        case 'master-data-dp3': include "pages/ref-riwayat/master-data-dp3.php"; break;
        case 'form-edit-data-dp3': include "pages/ref-riwayat/form-edit-data-dp3.php"; break;
        case 'edit-data-dp3': include "pages/ref-riwayat/edit-data-dp3.php"; break;
        case 'delete-data-dp3': include "pages/ref-riwayat/delete-data-dp3.php"; break;
        case 'view-detail-data-dp3': include "pages/ref-riwayat/view-detail-data-dp3.php"; break;

        case 'form-master-data-lat-jabatan': include "pages/ref-riwayat/form-master-data-lat-jabatan.php"; break;
        case 'master-data-lat-jabatan': include "pages/ref-riwayat/master-data-lat-jabatan.php"; break;
        case 'form-edit-data-lat-jabatan': include "pages/ref-riwayat/form-edit-data-lat-jabatan.php"; break;
        case 'edit-data-lat-jabatan': include "pages/ref-riwayat/edit-data-lat-jabatan.php"; break;
        case 'delete-data-lat-jabatan': include "pages/ref-riwayat/delete-data-lat-jabatan.php"; break;

        case 'form-view-data-mutasi': include "pages/ref-mutasi/form-view-data-mutasi.php"; break;
        case 'form-master-data-mutasi': include "pages/ref-mutasi/form-master-data-mutasi.php"; break;
        case 'master-data-mutasi': include "pages/ref-mutasi/master-data-mutasi.php"; break;
        case 'form-edit-data-mutasi': include "pages/ref-mutasi/form-edit-data-mutasi.php"; break;
        case 'edit-data-mutasi': include "pages/ref-mutasi/edit-data-mutasi.php"; break;
        case 'delete-data-mutasi': include "pages/ref-mutasi/delete-data-mutasi.php"; break;

        case 'form-view-data-angkat': include "pages/ref-pengangkatan/form-view-data-angkat.php"; break;
        case 'form-master-data-angkat': include "pages/ref-pengangkatan/form-master-data-angkat.php"; break;
        case 'master-data-angkat': include "pages/ref-pengangkatan/master-data-angkat.php"; break;
        case 'form-edit-data-angkat': include "pages/ref-pengangkatan/form-edit-data-angkat.php"; break;
        case 'edit-data-angkat': include "pages/ref-pengangkatan/edit-data-angkat.php"; break;
        case 'delete-data-angkat': include "pages/ref-pengangkatan/delete-data-angkat.php"; break;
        case 'view-pengangkatan': include "pages/ref-pengangkatan/view-pengangkatan.php"; break;          

        case 'master-data-golongan': include "pages/ref-master/master-data-golongan.php"; break;
        case 'form-edit-data-golongan': include "pages/ref-master/form-edit-data-golongan.php"; break;
        case 'edit-data-golongan': include "pages/ref-master/edit-data-golongan.php"; break;
        case 'delete-data-golongan': include "pages/ref-master/delete-data-golongan.php"; break;

        case 'master-data-eselon': include "pages/ref-master/master-data-eselon.php"; break;
        case 'form-edit-data-eselon': include "pages/ref-master/form-edit-data-eselon.php"; break;
        case 'edit-data-eselon': include "pages/ref-master/edit-data-eselon.php"; break;
        case 'delete-data-eselon': include "pages/ref-master/delete-data-eselon.php"; break;

        case 'chart-by-jk': include "pages/chart/chart-by-jk.php"; break;         
        case 'chart-by-gol-darah': include "pages/chart/chart-by-gol-darah.php"; break;         
        case 'chart-by-agama': include "pages/chart/chart-by-agama.php"; break;         
        case 'chart-by-status-nikah': include "pages/chart/chart-by-status-nikah.php"; break;

        case 'daftar-urut-kepangkatan': include "pages/report/daftar-urut-kepangkatan.php"; break;
        case 'formasi': include "pages/report/formasi.php"; break;
        case 'keadaan-pegawai': include "pages/report/keadaan-pegawai.php"; break;

        case 'form-config-aplikasi': include "pages/config/form-config-aplikasi.php"; break;          
        case 'config-aplikasi': include "pages/config/config-aplikasi.php"; break;  
        case 'dashboard-cabang': include "dashboard-cabang.php"; break;   

        default: include "dashboard.php";

      }
      ?>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <strong>Copyright &copy; 2023 <a href="<?php echo $set['url_app']?>" target="_blank"><?php echo $set['nama_app']?></a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 2.0
    </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="plugins/jszip/jszip.min.js"></script>
<script src="plugins/pdfmake/pdfmake.min.js"></script>
<script src="plugins/pdfmake/vfs_fonts.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Select2 -->
<script src="plugins/select2/js/select2.full.min.js"></script>
<!-- Bootstrap Switch -->
<script src="plugins/bootstrap-switbootstrap-switch.min.js"></script>
<!-- BS-Stepper -->
<script src="plugins/bs-steppbs-stepper.min.js"></script>
<!-- dropzonejs -->
<script src="plugins/dropzone/min/dropzone.min.js"></script>
<!-- bootstrap color picker -->
<script src="plugins/bootstrap-colorpickbootstrap-colorpicker.min.js"></script>
<!-- Bootstrap4 Duallistbox -->
<script src="plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
<!-- InputMask -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/inputmask/jquery.inputmask.min.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- Toastr -->
<script src="plugins/toastr/toastr.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js"></script>
<!-- jquery-validation -->
<script src="plugins/jquery-validation/jquery.validate.min.js"></script>
<script src="plugins/jquery-validation/additional-methods.min.js"></script>
  <!-- pace-progress -->
<script src="plugins/pace-progress/pace.min.js"></script>
<script>

$(function () {
    
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
    });
    
    $('.toastrDefaultSuccess').click(function() {
      toastr.success('Lorem ipsum dolor sit amet, consetetur sadipscing elitr.')
    });
    $('.toastrDefaultInfo').click(function() {
      toastr.info('Lorem ipsum dolor sit amet, consetetur sadipscing elitr.')
    });
    $('.toastrDefaultError').click(function() {
      toastr.error('Lorem ipsum dolor sit amet, consetetur sadipscing elitr.')
    });
    $('.toastrDefaultWarning').click(function() {
      toastr.warning('Data Berhasil ditemukan, yakin akan Edit Data?')
    });


    //Initialize Select2 Elements
    $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })

    //Datemask dd/mm/yyyy
    $('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' })
    //Datemask2 mm/dd/yyyy
    $('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' })
    //Money Euro
    $('[data-mask]').inputmask()

    //Date picker
    $('#tgl_lhr').datetimepicker({
      format: 'L'
    });

    //Date and time picker
    $('#reservationdatetime').datetimepicker({ icons: { time: 'far fa-clock' } });

    //Date range picker
    $('#reservation').daterangepicker()
    //Date range picker with time picker
    $('#reservationtime').daterangepicker({
      timePicker: true,
      timePickerIncrement: 30,
      locale: {
        format: 'MM/DD/YYYY hh:mm A'
      }
    })
    //Date range as a button
    $('#daterange-btn').daterangepicker(
    {
      ranges   : {
        'Today'       : [moment(), moment()],
        'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month'  : [moment().startOf('month'), moment().endOf('month')],
        'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      },
      startDate: moment().subtract(29, 'days'),
      endDate  : moment()
    },
    function (start, end) {
      $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
    }
    )

    //Timepicker
    $('#timepicker').datetimepicker({
      format: 'LT'
    })
    
    //Bootstrap Duallistbox
    $('.duallistbox').bootstrapDualListbox()

    //Colorpicker
    $('.my-colorpicker1').colorpicker()
    //color picker with addon
    $('.my-colorpicker2').colorpicker()

    $('.my-colorpicker2').on('colorpickerChange', function(event) {
      $('.my-colorpicker2 .fa-square').css('color', event.color.toString());
    });

    $("input[data-bootstrap-switch]").each(function(){
      $(this).bootstrapSwitch('state', $(this).prop('checked'));
    });

  }) 
</script>
</body>
</html>
