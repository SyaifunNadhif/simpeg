<?php
function cekAkses($roles = []) {
  if (!isset($_SESSION['id_user']) || !in_array($_SESSION['hak_akses'], $roles)) {
    echo "<script>alert('Akses ditolak'); window.location='index.php';</script>";
    exit;
  }
}

function aturSessionTimeout($detik, $redirect) {
  if (isset($_SESSION['start_session'])) {
    if (time() - $_SESSION['start_session'] >= $detik) {
      // Pastikan tidak ada output sebelumnya
      if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
      }

      // Bersihkan buffer jika ada
      if (ob_get_length()) ob_end_clean();

      echo "
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset='UTF-8'>
          <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
          <script>
            Swal.fire({
              title: 'Sesi Habis',
              text: 'Sesi Anda telah berakhir. Anda akan dialihkan ke halaman login.',
              icon: 'warning',
              confirmButtonText: 'OK',
              allowOutsideClick: false
            }).then((result) => {
              if (result.isConfirmed) {
                window.location = '$redirect';
              }
            });
          </script>
        </body>
        </html>
      ";
      exit;
    }
  } else {
    // Inisialisasi awal waktu sesi jika belum ada
    $_SESSION['start_session'] = time();
  }
}


function sinkron_user_dari_pegawai($id_peg) {
  include 'koneksi.php';

  // Ambil data pegawai
  $q = mysqli_query($conn, "SELECT * FROM tb_pegawai WHERE id_peg = '$id_peg'");
  if (!$q || mysqli_num_rows($q) == 0) return;

  $peg = mysqli_fetch_assoc($q);
  $nama = $peg['nama'];
  $jabatan = $peg['jabatan']; // asumsikan sudah tersedia
  $status_aktif = $peg['status_aktif'];
  $hak_akses = (strtolower($jabatan) == 'kepala cabang') ? 'Kepala' : 'User';
  $username = strtolower(preg_replace('/[^a-z0-9]/', '', explode(' ', $peg['nama'])[0])); // e.g. linda
  $password_default = password_hash('123456', PASSWORD_DEFAULT);
  $created_by = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'system';

  // Cek apakah user dengan id_pegawai ini sudah ada
  $cek = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_pegawai = '$id_peg'");

  if (mysqli_num_rows($cek) == 0 && $status_aktif == 'Y') {
    // Buat user baru
    mysqli_query($conn, "INSERT INTO tb_user 
      (id_user, nama_user, jabatan, password, hak_akses, status_aktif, created_by, id_pegawai)
      VALUES
      ('$username', '$nama', '$jabatan', '$password_default', '$hak_akses', 'Y', '$created_by', '$id_peg')");
  } else {
    // Update user yang ada
    $update_status = ($status_aktif == 'Y') ? 'Y' : 'N';
    mysqli_query($conn, "UPDATE tb_user SET
      nama_user = '$nama',
      jabatan = '$jabatan',
      hak_akses = '$hak_akses',
      status_aktif = '$update_status',
      updated_by = '$created_by'
      WHERE id_pegawai = '$id_peg'");
  }
}


function logAktivitas($id_user, $aksi, $keterangan) {
  global $conn;
  $ip = $_SERVER['REMOTE_ADDR'];
  $agent = $_SERVER['HTTP_USER_AGENT'];
  $stmt = $conn->prepare("INSERT INTO tb_log_aktivitas (id_user, aksi, keterangan, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("issss", $id_user, $aksi, $keterangan, $ip, $agent);
  $stmt->execute();
}


function hanyaAdmin() {
    if (!isset($_SESSION['hak_akses']) || $_SESSION['hak_akses'] != 'admin') {
        echo "Akses ditolak.";
        exit;
    }
}

function aksesAdminKepala() {
    return isset($_SESSION['hak_akses']) && in_array(strtolower($_SESSION['hak_akses']), ['admin', 'kepala']);
}

function isKepala() {
    return isset($_SESSION['hak_akses']) && $_SESSION['hak_akses'] === 'kepala';
}

function filterKantor($alias = 'j') {
  if ($_SESSION['hak_akses'] === 'kepala' && isset($_SESSION['kode_kantor'])) {
    return "AND $alias.unit_kerja = '{$_SESSION['kode_kantor']}'";
  }
  return ''; // untuk admin/user tanpa filter
}

function proteksiAksesPegawai($id_peg) {
    if ($_SESSION['hak_akses'] == 'user' && $_SESSION['id_pegawai'] != $id_peg) {
        echo "Akses ditolak.";
        exit;
    }
}

function getPage($page) {
  $routes = [
    // Dashboard
    'dashboard' => 'dashboard.php',

    // Dashboard Cabang
    'dashboard-cabang' => 'dashboard-cabang.php',

    // Master
    'form-view-data-pegawai'       => 'pages/pegawai/form-view-data-pegawai.php',
    'form-edit-data-pegawai'       => 'pages/pegawai/form-edit-data-pegawai.php',
    'form-upload-data-pegawai'     => 'pages/pegawai/form-upload-data-pegawai.php',
    'view-detail-data-pegawai'     => 'pages/pegawai/view-detail-data-pegawai.php',
    'form-master-data-pegawai'     => 'pages/pegawai/form-master-data-pegawai.php',
    'simpan-data-pegawai'          => 'pages/pegawai/simpan-data-pegawai.php',
    'edit-data-pegawai'            => 'pages/pegawai/edit-data-pegawai.php',
    'delete-data-pegawai'          => 'pages/pegawai/delete-data-pegawai.php',
    'upload-data-pegawai'          => 'pages/pegawai/upload-data-pegawai.php',
    'profil-pegawai'               => 'pages/pegawai/profil-pegawai.php',
    'notifikasi-user'              => 'pages/pegawai/notifikasi-user.php',
    'preview-edit'                 => 'pages/pegawai/preview-edit.php',  
    'form-ganti-foto'              => 'pages/pegawai/form-ganti-foto.php',
    'ganti-foto'                   => 'pages/pegawai/ganti-foto.php',  

    // Otorisasi    
    'otorisasi-approval'           => 'pages/otorisasi/otorisasi-approval.php',
    'otorisasi-detail'             => 'pages/otorisasi/otorisasi-detail.php',
    'proses-otorisasi'             => 'pages/otorisasi/proses-otorisasi.php',    
    
    // Config
    'form-config-aplikasi'         => 'pages/config/form-config-aplikasi.php',
    'config-aplikasi'              => 'pages/config/config-aplikasi.php',
    
    // Ref Keluarga
    'form-view-data-suami-istri'   => 'pages/ref-keluarga/form-view-data-suami-istri.php',
    'form-master-data-suami-istri' => 'pages/ref-keluarga/form-master-data-suami-istri.php',
    'form-edit-data-suami-istri'   => 'pages/ref-keluarga/form-edit-data-suami-istri.php',
    'form-edit-data-anak'          => 'pages/ref-keluarga/form-edit-data-anak.php',   
    'form-view-data-anak'          => 'pages/ref-keluarga/form-view-data-anak.php',
    'form-master-data-anak'        => 'pages/ref-keluarga/form-master-data-anak.php',  
    'form-import-data-anak'        => 'pages/ref-keluarga/form-import-data-anak.php',        
    'form-view-data-ortu'          => 'pages/ref-keluarga/form-view-data-ortu.php',
    'form-master-data-ortu'        => 'pages/ref-keluarga/form-master-data-ortu.php',
    'form-import-data-ortu'        => 'pages/ref-keluarga/form-import-data-ortu.php',
    'form-edit-data-ortu'          => 'pages/ref-keluarga/form-edit-data-ortu.php',

    // Ref Pelanggaran
    'form-view-data-pelanggaran'   => 'pages/ref-pelanggaran/form-view-data-pelanggaran.php',
    'form-edit-data-hukuman'       => 'pages/ref-pelanggaran/edit-data-hukuman.php',
    'delete-data-hukuman'          => 'pages/ref-pelanggaran/delete-data-hukuman.php',
    'form-master-data-hukuman'     => 'pages/ref-pelanggaran/form-master-data-pelanggaran.php',



    // Pendidikan
    'form-view-data-pendidikan'        => 'pages/ref-pendidikan/form-view-data-pendidikan.php',
    'form-master-data-pendidikan'      => 'pages/ref-pendidikan/form-master-data-pendidikan.php',
    'form-import-data-pendidikan'           => 'pages/ref-pendidikan/form-import-data-pendidikan.php',

    // Jabatan
    'form-view-data-jabatan'        => 'pages/ref-jabatan/form-view-data-jabatan.php',
    'form-master-data-jabatan'      => 'pages/ref-jabatan/form-master-data-jabatan.php',
    'form-import-jabatan'           => 'pages/ref-jabatan/form-import-jabatan.php',

    // Kantor
    'form-view-data-kantor'        => 'pages/kantor/form-view-data-kantor.php',

    // User
    'daftar-user'                   => 'pages/user/daftar-user.php',
    'form-user'                     => 'pages/user/form-user.php',
    'simpan-user'                   => 'pages/user/simpan-user.php',

    // Sertifikasi (contoh tambahan)
    'form-view-data-sertifikasi'   => 'pages/ref-sertifikasi/form-view-data-sertifikasi.php',
    'form-master-data-sertifikasi' => 'pages/ref-sertifikasi/form-master-data-sertifikasi.php',
    'form-import-data-sertifikasi' => 'pages/ref-sertifikasi/form-import-data-sertifikasi.php',

    // Diklat
    'form-view-data-diklat'        => 'pages/ref-diklat/form-view-data-diklat.php',
    'form-master-data-diklat'      => 'pages/ref-diklat/form-master-data-diklat.php',    
    'form-import-data-diklat'      => 'pages/ref-diklat/form-import-data-diklat.php',

    // Laporan
    'nominatif'                    => 'pages/report/nominatif-pegawai.php',
    'formasi'                      => 'pages/report/formasi.php',
    'keadaan-pegawai'              => 'pages/report/keadaan-pegawai.php',

    // Tambahkan lebih lanjut sesuai kebutuhan Anda...
    // mutasi
    'form-view-data-mutasi'        => 'pages/ref-mutasi/form-view-data-mutasi.php',


        // mutasi
    'master-data-diklat'        => 'pages/ref-diklat/master-data-diklat.php',
    'form-diklat'               => 'pages/ref-diklat/form-diklat.php',

  ];

  return isset($routes[$page]) ? $routes[$page] : '404.php';
}
