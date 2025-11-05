<?php
include '../../dist/koneksi.php';

$id_user     = $_POST['id_user'];
$nama_user   = $_POST['nama_user'];
$jabatan     = $_POST['jabatan'];
$password    = $_POST['password'];
$hak_akses   = $_POST['hak_akses'];
$status      = $_POST['status_aktif'];
$now_user    = $_SESSION['id_user']; // asumsi sesi login sudah ada

if ($mode == 'create') {
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $sql = "INSERT INTO tb_user 
          (id_user, nama_user, jabatan, password, hak_akses, status_aktif, created_by) 
          VALUES 
          ('$id_user', '$nama_user', '$jabatan', '$hash', '$hak_akses', '$status', '$now_user')";
} else {
  $sql_pw = $password ? ", password = '".password_hash($password, PASSWORD_DEFAULT)."'" : "";
  $sql = "UPDATE tb_user SET 
            nama_user = '$nama_user',
            jabatan = '$jabatan',
            hak_akses = '$hak_akses',
            status_aktif = '$status',
            updated_by = '$now_user'
            $sql_pw
          WHERE id_user = '$id_user'";
}
