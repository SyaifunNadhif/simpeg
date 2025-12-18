<?php
ob_start();
session_start();

include "dist/koneksi.php"; // Pastikan path ini benar

// Gunakan mysqli_real_escape_string atau prepared statement jika input id_app berasal dari user. 
// Karena di sini hardcode '1', maka aman.
$App = mysqli_query($conn, "SELECT * FROM tb_config WHERE id_app='1'");
$set = mysqli_fetch_array($App);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <title><?php echo htmlspecialchars($set['desc_app']); ?></title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">

  <link rel="shortcut icon" href="dist/favicon.ico" type="image/x-icon" />
</head>
<body class="hold-transition login-page"> 
  <section class="content">
        <?php
        // LOGIC Halaman ini SUDAH AMAN dari LFI (Local File Inclusion) 
        // karena brother menggunakan whitelist (switch case), bukan include langsung $_GET['page'].
        // Good job bro! Pertahankan cara ini.
        $page = (isset($_GET['page']))? $_GET['page'] : "main";
        switch ($page) {
          case 'act-login': include "pages/login/act-login.php"; break;
          default : include 'pages/login/form-login.php'; 
        }
        ?>
      </section>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>

<script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>

</body>
</html>