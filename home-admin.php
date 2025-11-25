<?php
session_start();
include "dist/koneksi.php";
include "dist/functions.php";
// include "cek.php";

//cekAkses(['Admin']);
aturSessionTimeout(1800, "index.php");

$App = mysqli_query($conn, "SELECT * FROM tb_config WHERE id_app='1'");
$set = mysqli_fetch_array($App);

include "templates/layout-header.php";
include "templates/layout-sidebar.php";
?>

<div class="content-wrapper">
  <section class="content text-sm">
    <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    $file = getPage($page); // pastikan variabel $file didefinisikan
    include $file;
    ?>
  </section>
</div>



<?php include "templates/layout-footer.php"; ?>
