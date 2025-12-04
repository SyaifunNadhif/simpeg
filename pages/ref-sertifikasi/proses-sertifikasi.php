<?php
/*********************************************************
 * FILE    : pages/ref-sertifikasi/proses-sertifikasi.php
 * MODULE  : Backend Update Data Sertifikasi
 *********************************************************/
session_start();
include "../../dist/koneksi.php";

if (isset($_POST['update'])) {
    $id_sertif      = mysqli_real_escape_string($conn, $_POST['id_sertif']);
    $sertifikasi    = mysqli_real_escape_string($conn, $_POST['sertifikasi']);
    $penyelenggara  = mysqli_real_escape_string($conn, $_POST['penyelenggara']);
    $sertifikat     = mysqli_real_escape_string($conn, $_POST['sertifikat']);
    
    // Handle Tanggal (Jika kosong set NULL/0000-00-00)
    $tgl_sertifikat = !empty($_POST['tgl_sertifikat']) ? $_POST['tgl_sertifikat'] : '0000-00-00';
    $tgl_expired    = !empty($_POST['tgl_expired']) ? $_POST['tgl_expired'] : '0000-00-00';
    
    $updated_by     = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'admin';

    // Query Update
    // Pastikan nama kolom 'id_sertif' sesuai dengan Primary Key tabel Anda
    $query = "UPDATE tb_sertifikasi SET 
                sertifikasi     = '$sertifikasi',
                penyelenggara   = '$penyelenggara',
                sertifikat      = '$sertifikat',
                tgl_sertifikat  = '$tgl_sertifikat',
                tgl_expired     = '$tgl_expired',
                updated_at      = NOW(),
                updated_by      = '$updated_by'
              WHERE id_sertif   = '$id_sertif'";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Data Berhasil Diperbarui!');
                window.location='../../home-admin.php?page=form-view-data-sertifikasi';
              </script>";
    } else {
        echo "<script>
                alert('Gagal Update: " . mysqli_error($conn) . "');
                window.history.back();
              </script>";
    }
}
?>