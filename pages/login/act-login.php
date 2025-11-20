<?php
include "dist/koneksi.php";

$id_user    = $_POST['id_user'];
$password   = md5($_POST['password']);
$op = isset($_GET['op']) ? $_GET['op'] : 'in';
?>
<html>
<head>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php
if ($op == "in") {
  $sql = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user='$id_user' AND password='$password'");

  if (mysqli_num_rows($sql) == 1) {
    $qry = mysqli_fetch_array($sql);

    if ($qry['status_aktif'] == "N") {
      echo "<script>
        Swal.fire({
          icon: 'warning',
          title: 'User Tidak Aktif',
          text: 'Silakan hubungi Admin untuk aktivasi akun.',
          confirmButtonText: 'Kembali'
        }).then(() => {
          window.location.href = 'index.php';
        });
      </script>";
    } else {
      // Set session
      $_SESSION['id_user']    = $qry['id_user'];
      $_SESSION['nama_user']  = $qry['nama_user'];
      $akses = strtolower($qry['hak_akses']);
      $_SESSION['hak_akses'] = $akses;
      $_SESSION['id_pegawai'] = $qry['id_pegawai'];

      // Tambahan untuk kepala: ambil kode kantor
      if (strtolower($qry['hak_akses']) == 'kepala') {
          $id_pegawai = $qry['id_pegawai'];
          $qKantor = mysqli_query($conn, "
              SELECT j.unit_kerja 
              FROM tb_jabatan j 
              WHERE j.id_peg = '$id_pegawai' AND j.status_jab = 'Aktif' 
              LIMIT 1
          ");
          
          if ($qKantor && mysqli_num_rows($qKantor) > 0) {
              $dKantor = mysqli_fetch_assoc($qKantor);
              $_SESSION['kode_kantor'] = $dKantor['unit_kerja'];
          } else {
              $_SESSION['kode_kantor'] = '-'; // fallback jika tidak ditemukan
          }
      }
      
      // Redirect sesuai hak akses
      switch ($_SESSION['hak_akses']) {
        case 'admin':
          $redirectPage = 'home-admin.php';
          break;
        case 'kepala':
          $redirectPage = 'home-admin.php?page=dashboard-cabang';
          break;
        case 'user':
          $redirectPage = 'home-admin.php?page=profil-pegawai';
          break;
        default:
          $redirectPage = 'index.php';
          break;
      }

      echo "<script>
        Swal.fire({
          icon: 'success',
          title: 'Login Berhasil',
          text: 'Selamat datang {$qry['nama_user']}',
          showConfirmButton: false,
          timer: 4500
        }).then(() => {
          window.location.href = '$redirectPage';
        });
      </script>";
    }
  } else {
    echo "<script>
      Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: 'ID User atau Password salah!',
        confirmButtonText: 'Coba Lagi'
      }).then(() => {
        window.location.href = 'index.php';
      });
    </script>";
  }
} elseif ($op == "out") {
  session_destroy();
  echo "<script>
    Swal.fire({
      icon: 'info',
      title: 'Logout Berhasil',
      text: 'Anda telah keluar dari sistem.',
      showConfirmButton: false,
      timer: 4500
    }).then(() => {
      window.location.href = 'index.php';
    });
  </script>";
}
?>
</body>
</html>