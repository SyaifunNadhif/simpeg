<div class="login-box">
  <?php
  include "dist/koneksi.php";
  $id_user		= $_POST['id_user'];
  $password		= md5($_POST['password']);
  $op 			= $_GET['op'];
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
              $_SESSION['id_user'] = $qry['id_user'];
              $_SESSION['nama_user'] = $qry['nama_user'];
              $_SESSION['hak_akses'] = $qry['hak_akses'];

              $redirectPage = ($qry['hak_akses'] == "Admin") ? 'home-admin.php' : 'home.php';

              echo "<script>
              Swal.fire({
                icon: 'success',
                title: 'Login Berhasil',
                text: 'Selamat datang {$qry['nama_user']}',
                showConfirmButton: false,
                timer: 1000
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
                } else if ($op == "out") {
                  session_destroy();
                  echo "<script>
                  Swal.fire({
                    icon: 'info',
                    title: 'Logout Berhasil',
                    text: 'Anda telah keluar dari sistem.',
                    showConfirmButton: false,
                    timer: 1500
                    }).then(() => {
                      window.location.href = 'index.php';
                      });
                      </script>";
                    }
                    ?>
                  </body>
                  </html>