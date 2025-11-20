
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



<!-- Select2 -->
<link rel="stylesheet" href="plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
</head><nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0 shadow-sm text-sm" style="transition: all 0.3s;">
  <ul class="navbar-nav align-items-center">
    <li class="nav-item">
      <a class="nav-link ripple-effect" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block ms-2">
      <span class="text-muted font-weight-bold" style="font-size: 14px; letter-spacing: 0.5px;">
        <?php echo date('l, d F Y'); ?> </span>
    </li>
  </ul>

  <ul class="navbar-nav ms-auto align-items-center gap-2">
    
    <li class="nav-item dropdown me-2">
      <a class="nav-link position-relative icon-btn" data-bs-toggle="dropdown" href="#">
        <i class="far fa-bell fa-lg"></i>
        <?php if ($jumlahNotif > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow-sm" style="font-size: 0.6rem; padding: 0.3em 0.5em;">
            <?= $jumlahNotif > 9 ? '9+' : $jumlahNotif ?>
          </span>
        <?php endif; ?>
      </a>
      
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end border-0 shadow-lg rounded-3 mt-2 p-0 overflow-hidden animate__animated animate__fadeInDown">
        <div class="p-3 bg-light border-bottom">
          <span class="font-weight-bold text-dark"><i class="fas fa-bell text-primary me-1"></i> Notifikasi</span>
          <span class="float-end badge bg-primary"><?= $jumlahNotif ?> Baru</span>
        </div>
        
        <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
          <?php while ($notif = mysqli_fetch_assoc($qNotif)): ?>
            <a href="<?= $hak_akses == 'kepala' ? 'home-admin.php?page=otorisasi-detail&id_pending=' . $notif['id_edit'] : $notif['link_aksi'] ?>" 
               class="list-group-item list-group-item-action border-bottom-0 py-3">
              <div class="d-flex w-100 justify-content-between">
                <small class="text-muted mb-1">
                  <i class="far fa-clock me-1"></i> 
                  <?php 
                    $tanggal_waktu = isset($notif['tanggal_pengajuan']) ? $notif['tanggal_pengajuan'] : (isset($notif['waktu_notif']) ? $notif['waktu_notif'] : date('Y-m-d H:i:s'));
                    echo date('H:i', strtotime($tanggal_waktu)); 
                  ?>
                </small>
              </div>
              <p class="mb-1 text-sm text-dark font-weight-bold" style="line-height: 1.4;">
                <?= $hak_akses == 'kepala' ? 'Permintaan dari <span class="text-primary">' . $notif['nama'] . '</span>' : $notif['judul'] ?>
              </p>
            </a>
          <?php endwhile; ?>
        </div>
        <a href="<?= $targetLink ?>" class="dropdown-item dropdown-footer text-center text-primary font-weight-bold py-3 bg-white">Lihat Semua Notifikasi</a>
      </div>
    </li>

    <li class="nav-item dropdown">
      <a class="nav-link d-flex align-items-center gap-2 profile-link ps-3 pe-2 py-1 rounded-pill bg-light border" data-bs-toggle="dropdown" href="#">
        <img src="dist/img/<?php echo $foto; ?>" class="rounded-circle shadow-sm" alt="User Image" style="height:32px; width:32px; object-fit: cover;">
        <div class="d-none d-md-flex flex-column text-start ms-2" style="line-height: 1.1;">
            <span class="font-weight-bold text-dark text-xs"><?php echo $_SESSION['nama_user']; ?></span>
            <small class="text-muted" style="font-size: 10px;"><?php echo ucfirst($_SESSION['hak_akses']); ?></small>
        </div>
        <i class="fas fa-chevron-down ms-2 text-muted" style="font-size: 10px;"></i>
      </a>

      <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-2 p-3 animate__animated animate__fadeInDown" style="min-width: 240px;">
        
        <div class="d-flex align-items-center mb-3">
            <img src="dist/img/<?php echo $foto; ?>" class="rounded-circle shadow-sm" style="width:50px; height:50px; object-fit: cover;">
            <div class="ms-3 overflow-hidden">
                <h6 class="mb-0 font-weight-bold text-truncate"><?php echo $_SESSION['nama_user']; ?></h6>
                <small class="text-muted">NIP. <?php echo isset($_SESSION['nip']) ? $_SESSION['nip'] : '-'; ?></small>
            </div>
        </div>

        <div class="dropdown-divider mb-2"></div>

        <a href="home-admin.php?page=profil-pegawai" class="dropdown-item rounded-2 py-2 mb-1 text-muted hover-primary">
            <i class="far fa-user-circle me-2 width-20 text-center"></i> Profil Saya
        </a>
        <a href="#" class="dropdown-item rounded-2 py-2 mb-1 text-muted hover-primary">
            <i class="fas fa-cog me-2 width-20 text-center"></i> Pengaturan Akun
        </a>
        
        <div class="dropdown-divider my-2"></div>
        
        <a href="pages/login/act-logout.php" class="dropdown-item rounded-2 py-2 text-danger fw-bold hover-danger bg-light-danger">
            <i class="fas fa-sign-out-alt me-2 width-20 text-center"></i> Sign Out
        </a>
      </div>
    </li>
  </ul>
</nav>
<body class="hold-transition sidebar-mini layout-fixed text-sm pace-info">
<div class="wrapper">


<style>
  /* ------------------------------------------------------------- */
/* NAVBAR MODERN STYLING */
/* ------------------------------------------------------------- */

/* 1. Navbar Base */
.main-header {
    /* Efek Glassmorphism (Semi Transparan) agar batik di belakang (jika ada) terlihat samar */
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(0,0,0,0.05) !important;
}

/* 2. Tombol & Ikon */
.icon-btn {
    transition: transform 0.2s;
    color: #6c757d !important;
}
.icon-btn:hover {
    transform: translateY(-2px);
    color: #2c3e50 !important;
}

/* 3. Profile Section (Pill Shape) */
.profile-link {
    transition: all 0.3s ease;
    border: 1px solid transparent !important;
}
.profile-link:hover, .profile-link[aria-expanded="true"] {
    background-color: #fff !important;
    border-color: #e9ecef !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* 4. Modern Dropdown Menu */
.dropdown-menu {
    border: none !important;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1) !important;
    border-radius: 16px !important;
}

/* Header Dropdown (Notifikasi) */
.dropdown-header {
    font-weight: 600;
    color: #2c3e50;
}

/* Item Dropdown Hover Effect */
.dropdown-item {
    font-size: 0.9rem;
    transition: all 0.2s;
}
.dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(3px);
}
.hover-primary:hover {
    color: #007bff !important;
    background-color: #f0f7ff !important;
}
.hover-danger:hover {
    background-color: #fff5f5 !important;
    color: #dc3545 !important;
}

/* 5. Scrollbar Cantik untuk Notifikasi */
.list-group::-webkit-scrollbar {
    width: 5px;
}
.list-group::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 10px;
}

/* Animasi halus saat dropdown muncul */
@keyframes fadeInDownSmall {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate__fadeInDown {
    animation: fadeInDownSmall 0.3s ease-out forwards;
}

/* Utilities */
.width-20 { width: 20px; display: inline-block; }
</style>

