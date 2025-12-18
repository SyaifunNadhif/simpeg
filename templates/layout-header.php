<?php
// --- 1. LOGIC PHP: AMBIL FOTO REAL-TIME DARI DATABASE ---
// Pastikan koneksi ($conn) sudah include di file induk (home-admin.php)

$id_user    = $_SESSION['id_user'];
$hak_akses  = strtolower($_SESSION['hak_akses']);

// Query Join User ke Pegawai untuk ambil Foto Terbaru
// Asumsi: tb_user punya kolom 'id_pegawai' yang nyambung ke 'id_peg' di tb_pegawai
$qUserPeg = mysqli_query($conn, "
    SELECT u.*, p.nama as nama_lengkap, p.foto, p.jk 
    FROM tb_user u 
    LEFT JOIN tb_pegawai p ON u.id_pegawai = p.id_peg 
    WHERE u.id_user = '$id_user'
");
$dUserPeg = mysqli_fetch_assoc($qUserPeg);

// Logic Penentuan Foto
$foto_db    = isset($dUserPeg['foto']) ? $dUserPeg['foto'] : '';
$gender     = isset($dUserPeg['jk']) ? $dUserPeg['jk'] : 'L';

// Path File Fisik (Relative terhadap home-admin.php)
$path_fisik = "pages/assets/foto/" . $foto_db; 

// Default Avatar (Jika foto tidak ada/kosong)
$avatar_def = ($gender == 'Perempuan' || $gender == 'P' || $gender == 'Wanita') ? 'dist/img/avatar3.png' : 'dist/img/avatar5.png';

// Cek apakah file ada di folder
if (!empty($foto_db) && file_exists($path_fisik)) {
    $foto_profil = $path_fisik . "?t=" . time(); // Tambah time() biar gak cache browser
} else {
    $foto_profil = $avatar_def;
}

// --- 2. LOGIC NOTIFIKASI (SAMA SEPERTI SEBELUMNYA) ---
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
            ORDER BY ep.tanggal_pengajuan DESC LIMIT 5
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
  <title><?php echo isset($set['desc_app']) ? $set['desc_app'] : 'SIMPEG App'; ?></title>
  <link rel="icon" type="image/png" href="dist/img/bkk.png">

  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="plugins/bootstrap5/css/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

  <style>
    /* 1. Glassmorphism Navbar */
    .main-header {
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(15px); /* Efek Blur di belakang navbar */
        -webkit-backdrop-filter: blur(15px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.3) !important;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03); /* Shadow super halus */
        height: 65px;
    }

    /* 2. Toggle Sidebar Button */
    .nav-link.ripple {
        border-radius: 50%;
        width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.3s ease;
        color: #64748b;
    }
    .nav-link.ripple:hover {
        background-color: #f1f5f9;
        color: #0d6efd;
        transform: rotate(90deg); /* Animasi putar dikit */
    }

    /* 3. Notifikasi Pulse */
    .notif-btn {
        position: relative;
        color: #64748b !important;
        transition: 0.3s;
    }
    .notif-btn:hover { color: #0d6efd !important; transform: translateY(-2px); }
    
    .pulse-badge {
        animation: pulse-red 2s infinite;
        border: 2px solid #fff;
    }
    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }

    /* 4. Profile Pill (Modern Shape) */
    .profile-pill {
        background: transparent;
        border: 1px solid transparent;
        padding: 4px 15px 4px 4px !important;
        border-radius: 50px !important;
        transition: all 0.3s ease;
    }
    .profile-pill:hover, .profile-pill[aria-expanded="true"] {
        background: #fff;
        border-color: #e2e8f0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .profile-img {
        width: 38px; height: 38px; object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* 5. Dropdown Menu Animasi */
    .dropdown-menu {
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.15) !important;
        margin-top: 10px !important;
        padding: 8px !important;
        animation: slideDownFade 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    @keyframes slideDownFade {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Dropdown Items */
    .dropdown-item {
        border-radius: 8px; padding: 8px 15px; font-weight: 500; color: #475569;
        transition: all 0.2s;
    }
    .dropdown-item:hover {
        background-color: #f8fafc; color: #0d6efd; padding-left: 20px;
    }
    .dropdown-item.text-danger:hover {
        background-color: #fef2f2; color: #ef4444 !important;
    }

    /* 6. Date Widget */
    .date-widget {
        font-size: 0.85rem; font-weight: 600; color: #64748b;
        background: #f8fafc; padding: 6px 15px; border-radius: 20px;
        border: 1px solid #e2e8f0;
    }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed text-sm">
<div class="wrapper">

<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  
  <ul class="navbar-nav align-items-center">
    <li class="nav-item">
      <a class="nav-link ripple" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block ms-2">
      <div class="date-widget">
        <i class="far fa-calendar-alt me-2 text-primary"></i> 
        <?php echo date('l, d F Y'); ?>
      </div>
    </li>
  </ul>

  <ul class="navbar-nav ms-auto align-items-center gap-3 pe-3">
    
    <li class="nav-item dropdown">
      <a class="nav-link notif-btn" data-bs-toggle="dropdown" href="#">
        <i class="far fa-bell fa-lg"></i>
        <?php if ($jumlahNotif > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger pulse-badge" style="font-size: 0.6rem;">
            <?= $jumlahNotif > 9 ? '9+' : $jumlahNotif ?>
          </span>
        <?php endif; ?>
      </a>
      
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom mb-2 bg-light rounded-top">
            <span class="fw-bold text-dark">Notifikasi</span>
            <?php if($jumlahNotif > 0): ?><span class="badge bg-primary rounded-pill"><?= $jumlahNotif ?> Baru</span><?php endif; ?>
        </div>
        
        <div style="max-height: 300px; overflow-y: auto;">
          <?php if($jumlahNotif == 0): ?>
             <div class="text-center py-4 text-muted">
                 <i class="far fa-bell-slash fa-2x mb-2 text-light"></i><br><small>Tidak ada notifikasi</small>
             </div>
          <?php else: ?>
              <?php while ($notif = mysqli_fetch_assoc($qNotif)): ?>
                <a href="<?= $hak_akses == 'kepala' ? 'home-admin.php?page=otorisasi-detail&id_pending=' . $notif['id_edit'] : $notif['link_aksi'] ?>" 
                   class="dropdown-item border-bottom">
                  <div class="d-flex justify-content-between mb-1">
                    <small class="text-primary fw-bold">Update</small>
                    <small class="text-muted"><i class="far fa-clock me-1"></i> 
                      <?php 
                        $tgl = isset($notif['tanggal_pengajuan']) ? $notif['tanggal_pengajuan'] : (isset($notif['waktu_notif']) ? $notif['waktu_notif'] : date('Y-m-d H:i:s'));
                        echo date('H:i', strtotime($tgl)); 
                      ?>
                    </small>
                  </div>
                  <p class="mb-0 text-wrap text-sm text-dark" style="line-height: 1.2;">
                    <?= $hak_akses == 'kepala' ? 'Approval data: <b>' . $notif['nama'] . '</b>' : $notif['judul'] ?>
                  </p>
                </a>
              <?php endwhile; ?>
          <?php endif; ?>
        </div>
        <a href="<?= $targetLink ?>" class="dropdown-item text-center text-primary fw-bold mt-1 bg-light rounded-bottom small">Lihat Semua</a>
      </div>
    </li>

    <li class="nav-item dropdown">
      <a class="nav-link profile-pill d-flex align-items-center" data-bs-toggle="dropdown" href="#">
        <img src="<?php echo $foto_profil; ?>" class="profile-img rounded-circle" alt="User">
        
        <div class="d-none d-md-flex flex-column text-start ms-2 me-1" style="line-height: 1.1;">
            <span class="fw-bold text-dark text-xs"><?php echo substr($_SESSION['nama_user'], 0, 15); ?></span>
            
        </div>
        <i class="fas fa-chevron-down ms-2 text-muted" style="font-size: 10px;"></i>
      </a>

      <div class="dropdown-menu dropdown-menu-end" style="min-width: 240px;">
        <div class="text-center py-3 bg-light rounded-3 mb-2 mx-1 mt-1">
            <img src="<?php echo $foto_profil; ?>" class="rounded-circle shadow-sm mb-2" style="width:65px; height:65px; object-fit: cover; border: 3px solid #fff;">
            <h6 class="mb-0 fw-bold text-dark px-2"><?php echo $_SESSION['nama_user']; ?></h6>
            <small class="text-muted">NIP. <?php echo isset($_SESSION['id_pegawai']) ? $_SESSION['id_pegawai'] : '-'; ?></small>
        </div>

        <a href="home-admin.php?page=profil-pegawai" class="dropdown-item">
            <i class="far fa-user-circle me-2 text-primary width-20"></i> Profil Saya
        </a>
        <a href="#" class="dropdown-item">
            <i class="fas fa-cog me-2 text-secondary width-20"></i> Pengaturan
        </a>
        
        <div class="dropdown-divider my-2"></div>
        
        <a href="pages/login/act-logout.php" class="dropdown-item text-danger fw-bold">
            <i class="fas fa-sign-out-alt me-2 width-20"></i> Keluar
        </a>
      </div>
    </li>

  </ul>
</nav>