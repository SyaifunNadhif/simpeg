<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $set['desc_app']; ?></title>
  <link rel="icon" type="image/png" href="dist/img/bkk.png">

<style>
  .dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.25rem 0.25rem !important;
    margin-left: 0.25px !important;
    margin-right: 0.25px !important;
    font-size: 0.85rem;
  }

  .dataTables_wrapper .dataTables_paginate {
    margin-top: 1rem !important;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.25rem;
  }
</style>

<!-- Font Awesome -->
<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
<!-- AdminLTE -->
<link rel="stylesheet" href="dist/css/adminlte.min.css">
<!-- Bootstrap5 -->
<link rel="stylesheet" href="plugins/bootstrap5/css/bootstrap.min.css">
<!-- Overlay Scrollbars -->
<link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Select2 -->
<link rel="stylesheet" href="plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed text-sm pace-info">
<div class="wrapper">

<!-- Bagian navbar di layout-header.php -->
<nav class="main-header navbar navbar-expand navbar-white py-2 navbar-light text-sm">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="#" class="nav-link">Home</a>
    </li>
  </ul>

  <?php
  if (!empty($_SESSION['foto']) && file_exists('dist/img/' . $_SESSION['foto'])) {
      $foto = $_SESSION['foto'];
  } else {
      $foto = 'no-image.jpg';
  }

  $id_user = $_SESSION['id_user'];
  $hak_akses = strtolower($_SESSION['hak_akses']);
  $jumlahNotif = 0;
  $targetLink = '#';

  if ($hak_akses == 'kepala') {
    if (isset($_SESSION['kode_kantor'])) {
      $kode_kantor = $_SESSION['kode_kantor'];

      $qNotif = mysqli_query($conn, "
        SELECT ep.id_edit, p.nama, ep.tanggal_pengajuan 
        FROM tb_edit_pending ep
        JOIN tb_pegawai p ON p.id_peg = ep.id_peg
        JOIN tb_jabatan j ON j.id_peg = p.id_peg AND j.status_jab = 'Aktif'
        WHERE ep.status_otorisasi = 'pending' AND j.unit_kerja = '$kode_kantor'
        ORDER BY ep.tanggal_pengajuan DESC
        LIMIT 5
      ");

      $jumlahNotif = $qNotif ? mysqli_num_rows($qNotif) : 0;
      $targetLink = 'home-admin.php?page=otorisasi-approval';
    }
  } else {
    $qNotif = mysqli_query($conn, "
      SELECT id_notif, pesan AS judul, link_aksi, waktu_notif 
      FROM tb_notifikasi 
      WHERE id_user = '$id_user' AND status_baca = 'unread' 
      ORDER BY waktu_notif DESC LIMIT 5
    ");

    $jumlahNotif = $qNotif ? mysqli_num_rows($qNotif) : 0;
    $targetLink = 'home-admin.php?page=notifikasi-user';
  }
  ?>

  <ul class="navbar-nav ms-auto">
    <!-- Notifikasi -->
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="far fa-bell"></i>
        <?php if ($jumlahNotif > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
            <?= $jumlahNotif ?>
          </span>
        <?php endif; ?>
      </a>
      <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end" aria-labelledby="notifDropdown">
        <li class="dropdown-header">
          <i class="fas fa-bell"></i> <?= $jumlahNotif ?> Notifikasi
        </li>
        <?php while ($notif = mysqli_fetch_assoc($qNotif)): ?>
          <li>
            <a href="<?=
              $hak_akses == 'kepala'
                ? 'home-admin.php?page=otorisasi-detail&id_pending=' . $notif['id_edit']
                : $notif['link_aksi'] ?>"
              class="dropdown-item text-wrap"
              style="white-space: normal; word-break: break-word; max-width: 300px;">
              <i class="fas fa-info-circle me-2"></i>
              <?=
                $hak_akses == 'kepala'
                  ? 'Permintaan dari ' . $notif['nama']
                  : $notif['judul'] ?>
              <span class="float-end text-muted text-sm">
                <?php
                  $tanggal_waktu = isset($notif['tanggal_pengajuan']) ? $notif['tanggal_pengajuan'] : (isset($notif['waktu_notif']) ? $notif['waktu_notif'] : date('Y-m-d H:i:s'));
                  echo date('H:i', strtotime($tanggal_waktu));
                ?>
              </span>
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
        <?php endwhile; ?>
        <li><a href="<?= $targetLink ?>" class="dropdown-item dropdown-footer">Lihat Semua Notifikasi</a></li>
      </ul>
    </li>

    <!-- User Info -->
    <li class="nav-item dropdown user-menu">
      <a href="#" class="nav-link dropdown-toggle px-5 py-1" data-bs-toggle="dropdown">
        <img src="dist/img/<?php echo $foto; ?>" class="rounded-circle" style="height:32px; width:32px; object-fit: cover;">
        <span class="d-none d-md-inline"><b><?php echo $_SESSION['nama_user']; ?></b></span>
      </a>
      <div class="dropdown-menu dropdown-menu-end shadow" style="width:220px; background-color:#2f3336; border:none;">
        <div class="text-center p-3">
          <img src="dist/img/<?php echo $foto; ?>" class="rounded-circle mb-2" style="width:70px; height:70px; object-fit: cover;">
          <div class="text-white font-weight-bold"><?php echo $_SESSION['nama_user']; ?></div>
        </div>
        <div class="dropdown-divider m-0 p-0" style="border-top: 1px solid #444;"></div>
        <div class="p-2">
          <a href="pages/login/act-logout.php" class="btn btn-block btn-sm btn-primary">Sign out</a>
        </div>
      </div>
    </li>
  </ul>
</nav>
