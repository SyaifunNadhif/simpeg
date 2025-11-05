<?php
// proses-diklat.php
include '../../config/koneksi.php';
session_start();

if (isset($_POST['simpan'])) {
  $id_peg       = $_POST['id_peg'];
  $diklat       = mysqli_real_escape_string($conn, $_POST['diklat']);
  $penyelenggara= mysqli_real_escape_string($conn, $_POST['penyelenggara']);
  $tempat       = mysqli_real_escape_string($conn, $_POST['tempat']);
  $angkatan     = mysqli_real_escape_string($conn, $_POST['angkatan']);
  $tahun        = $_POST['tahun'];
  $date_reg     = $_POST['date_reg'];
  $created_by   = $_SESSION['id_user'];

  $query = "INSERT INTO tb_diklat (id_peg, diklat, penyelenggara, tempat, angkatan, tahun, date_reg, created_by)
            VALUES ('$id_peg', '$diklat', '$penyelenggara', '$tempat', '$angkatan', '$tahun', '$date_reg', '$created_by')";
  mysqli_query($conn, $query);

  header("Location: home-admin.php?page=master-data-diklat&status=sukses_tambah");
  exit;
}

if (isset($_POST['update'])) {
  $id_diklat    = $_POST['id_diklat'];
  $id_peg       = $_POST['id_peg'];
  $diklat       = mysqli_real_escape_string($conn, $_POST['diklat']);
  $penyelenggara= mysqli_real_escape_string($conn, $_POST['penyelenggara']);
  $tempat       = mysqli_real_escape_string($conn, $_POST['tempat']);
  $angkatan     = mysqli_real_escape_string($conn, $_POST['angkatan']);
  $tahun        = $_POST['tahun'];
  $date_reg     = $_POST['date_reg'];
  $updated_by   = $_SESSION['id_user'];

  $query = "UPDATE tb_diklat SET
              id_peg = '$id_peg',
              diklat = '$diklat',
              penyelenggara = '$penyelenggara',
              tempat = '$tempat',
              angkatan = '$angkatan',
              tahun = '$tahun',
              date_reg = '$date_reg',
              updated_by = '$updated_by'
            WHERE id_diklat = '$id_diklat'";
  mysqli_query($conn, $query);

  header("Location: home-admin.php?page=master-data-diklat&status=sukses_edit");
  exit;
}

if (isset($_GET['act']) && $_GET['act'] == 'hapus' && isset($_GET['id'])) {
  $id = $_GET['id'];
  mysqli_query($conn, "DELETE FROM tb_diklat WHERE id_diklat = '$id'");
  header("Location: home-admin.php?page=master-data-diklat&status=sukses_hapus");
  exit;
}