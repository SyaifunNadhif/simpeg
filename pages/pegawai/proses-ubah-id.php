<?php
/*********************************************************
 * FILE    : pages/kepegawaian/proses-ubah-id.php
 * MODULE  : Backend Proses Pengangkatan & Ubah ID
 * UPDATE  : Redirect ke Detail Pegawai + SweetAlert Modal
 *********************************************************/
session_start();
include "../../dist/koneksi.php";

if (isset($_POST['simpan'])) {
    $id_lama    = mysqli_real_escape_string($conn, $_POST['id_peg_lama']);
    $id_baru    = mysqli_real_escape_string($conn, $_POST['id_peg_baru']);
    $jns_mutasi = mysqli_real_escape_string($conn, $_POST['jns_mutasi']);
    $no_mutasi  = mysqli_real_escape_string($conn, $_POST['no_mutasi']);
    $tgl_mutasi = mysqli_real_escape_string($conn, $_POST['tgl_mutasi']);
    $tmt        = mysqli_real_escape_string($conn, $_POST['tmt']);
    
    // Header HTML untuk SweetAlert (Karena ini file backend)
    echo '<!DOCTYPE html>
          <html lang="id">
          <head>
              <meta charset="UTF-8">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
              <style>body { font-family: sans-serif; background: #f4f6f9; }</style>
          </head>
          <body>';

    // 1. Cek Apakah ID Baru sudah dipakai orang lain?
    $cek = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_baru'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'ID Baru ($id_baru) sudah digunakan oleh pegawai lain.',
                confirmButtonText: 'Kembali'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit;
    }

    // 2. Upload File SK (Optional)
    $sk_filename = "";
    if (!empty($_FILES['sk_mutasi']['name'])) {
        $uploadDir = "../../assets/dokumen/sk_angkat/"; 
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileExt = pathinfo($_FILES['sk_mutasi']['name'], PATHINFO_EXTENSION);
        $sk_filename = "SK_" . $id_baru . "_" . date('YmdHis') . "." . $fileExt;
        
        move_uploaded_file($_FILES['sk_mutasi']['tmp_name'], $uploadDir . $sk_filename);
    }

    // --- MULAI TRANSAKSI DATABASE ---
    mysqli_begin_transaction($conn);

    try {
        // STEP A: Simpan History ke tb_angkat
        $sql_insert = "INSERT INTO tb_angkat (id_peg, jns_mutasi, id_peg_baru, tgl_mutasi, no_mutasi, tmt, sk_mutasi)
                       VALUES ('$id_lama', '$jns_mutasi', '$id_baru', '$tgl_mutasi', '$no_mutasi', '$tmt', '$sk_filename')";
        
        if (!mysqli_query($conn, $sql_insert)) {
            throw new Exception("Gagal menyimpan riwayat pengangkatan: " . mysqli_error($conn));
        }

        // STEP B: Update ID di tb_pegawai
        $sql_update = "UPDATE tb_pegawai SET id_peg = '$id_baru' WHERE id_peg = '$id_lama'";
        
        if (!mysqli_query($conn, $sql_update)) {
            throw new Exception("Gagal mengupdate ID Pegawai: " . mysqli_error($conn));
        }

        // STEP C: Commit (Simpan Permanen)
        mysqli_commit($conn);

        // --- SUCCESS RESPONSE ---
        // Redirect ke view-detail-data-pegawai dengan ID BARU
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: 'Pegawai berhasil diangkat.<br>ID berubah menjadi <b>$id_baru</b>',
                showConfirmButton: true,
                confirmButtonText: 'Lihat Detail Pegawai'
            }).then((result) => {
                window.location.href = '../../home-admin.php?page=view-detail-data-pegawai&id_peg=$id_baru';
            });
        </script>";

    } catch (Exception $e) {
        // Jika ada error, batalkan semua perubahan
        mysqli_rollback($conn);
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: '" . addslashes($e->getMessage()) . "',
                confirmButtonText: 'Kembali'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
    
    echo '</body></html>';
}
?>