<?php
/*********************************************************
 * FILE    : pages/kepegawaian/proses-ubah-id.php
 * MODULE  : Backend Proses Pengangkatan
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
    
    // 1. Cek Apakah ID Baru sudah dipakai orang lain?
    $cek = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_baru'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('GAGAL! ID Baru ($id_baru) sudah digunakan oleh pegawai lain.'); window.history.back();</script>";
        exit;
    }

    // 2. Upload File SK (Optional)
    $sk_filename = "";
    if (!empty($_FILES['sk_mutasi']['name'])) {
        $uploadDir = "../../assets/dokumen/sk_angkat/"; // Pastikan folder ini ada
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileExt = pathinfo($_FILES['sk_mutasi']['name'], PATHINFO_EXTENSION);
        $sk_filename = "SK_" . $id_baru . "_" . date('YmdHis') . "." . $fileExt;
        
        move_uploaded_file($_FILES['sk_mutasi']['tmp_name'], $uploadDir . $sk_filename);
    }

    // --- MULAI TRANSAKSI DATABASE ---
    // Kita pakai Transaction agar kalau Insert gagal, Update dibatalkan (Rollback), begitu juga sebaliknya.
    mysqli_begin_transaction($conn);

    try {
        // STEP A: Simpan History ke tb_angkat
        // Kolom sesuai screenshot Anda: id_peg (lama), jns_mutasi, id_peg_baru, tgl_mutasi, no_mutasi, tmt, sk_mutasi
        $sql_insert = "INSERT INTO tb_angkat (id_peg, jns_mutasi, id_peg_baru, tgl_mutasi, no_mutasi, tmt, sk_mutasi)
                       VALUES ('$id_lama', '$jns_mutasi', '$id_baru', '$tgl_mutasi', '$no_mutasi', '$tmt', '$sk_filename')";
        
        if (!mysqli_query($conn, $sql_insert)) {
            throw new Exception("Gagal menyimpan riwayat pengangkatan: " . mysqli_error($conn));
        }

        // STEP B: Update ID di tb_pegawai
        // PERHATIAN: Ini akan mengubah ID Pegawai. Pastikan database Anda support ON UPDATE CASCADE di tabel relasi.
        $sql_update = "UPDATE tb_pegawai SET id_peg = '$id_baru' WHERE id_peg = '$id_lama'";
        
        if (!mysqli_query($conn, $sql_update)) {
            throw new Exception("Gagal mengupdate ID Pegawai: " . mysqli_error($conn));
        }

        // STEP C: Commit (Simpan Permanen)
        mysqli_commit($conn);

        echo "<script>
                alert('SUKSES! Pegawai berhasil diangkat dan ID telah berubah menjadi $id_baru.');
                window.location='../../home-admin.php?page=data-pegawai'; 
              </script>";

    } catch (Exception $e) {
        // Jika ada error, batalkan semua perubahan
        mysqli_rollback($conn);
        echo "<script>
                alert('ERROR: " . $e->getMessage() . "');
                window.history.back();
              </script>";
    }
}
?>