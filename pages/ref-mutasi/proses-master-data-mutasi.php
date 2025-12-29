<?php
/*********************************************************
 * FILE    : pages/mutasi/proses-master-data-mutasi.php
 * MODULE  : Backend Pemberhentian Pegawai (SweetAlert FIX)
 *********************************************************/

session_start();
include "../../dist/koneksi.php";

// Security Check
if (empty($_SESSION['hak_akses']) || ($_SESSION['hak_akses'] != 'admin' && $_SESSION['hak_akses'] != 'superadmin')) {
    $_SESSION['swal_icon'] = "error";
    $_SESSION['swal_title'] = "Akses Ditolak";
    $_SESSION['swal_text'] = "Anda tidak memiliki izin akses.";
    header("Location: ../../home-admin.php");
    exit;
}

$act = $_GET['act'];

// --- 1. PROSES INSERT (TAMBAH DATA) ---
if ($act == 'insert') {
    $id_peg      = $_POST['id_peg'];
    $jns_mutasi  = $_POST['jns_mutasi'];
    $no_mutasi   = $_POST['no_mutasi'];
    $tgl_mutasi  = $_POST['tgl_mutasi']; 
    $tmt         = $_POST['tmt'];        
    
    // Ambil Jabatan Terakhir
    $qJab = mysqli_query($conn, "SELECT jabatan FROM tb_jabatan WHERE id_peg='$id_peg' AND status_jab='Aktif' ORDER BY tmt_jabatan DESC LIMIT 1");
    $rJab = mysqli_fetch_array($qJab);
    $jabatan_terakhir = isset($rJab['jabatan']) ? $rJab['jabatan'] : '-';

    if (empty($id_peg) || empty($no_mutasi)) {
        echo "<script>alert('Data pegawai dan No SK wajib diisi!');history.go(-1);</script>";
        exit;
    }

    mysqli_begin_transaction($conn);

    try {
        // A. Upload File
        $nama_file_sk = "";
        if (!empty($_FILES['sk_mutasi']['tmp_name'])) {
            $ext = pathinfo($_FILES['sk_mutasi']['name'], PATHINFO_EXTENSION);
            $nama_file_sk = "SK_STOP_" . $id_peg . "_" . date('YmdHis') . "." . $ext;
            move_uploaded_file($_FILES['sk_mutasi']['tmp_name'], "../assets/sk_mutasi/" . $nama_file_sk);
        }

        // B. INSERT MUTASI
        $sql_mutasi = "INSERT INTO tb_mutasi 
            (id_peg, jns_mutasi, no_mutasi, tgl_mutasi, tmt, sk_mutasi, jabatan, created_at) 
            VALUES 
            ('$id_peg', '$jns_mutasi', '$no_mutasi', '$tgl_mutasi', '$tmt', '$nama_file_sk', '$jabatan_terakhir', NOW())";
        
        if (!mysqli_query($conn, $sql_mutasi)) throw new Exception("Gagal Simpan Mutasi: " . mysqli_error($conn));

        // C. NON-AKTIFKAN JABATAN
        $sql_update_jab = "UPDATE tb_jabatan SET status_jab = 'Non', sampai_tgl = '$tmt' 
                           WHERE id_peg = '$id_peg' AND status_jab = 'Aktif'";
        
        if (!mysqli_query($conn, $sql_update_jab)) throw new Exception("Gagal Non-Aktifkan Jabatan");

        // D. NON-AKTIFKAN PEGAWAI
        $sql_peg = "UPDATE tb_pegawai SET status_aktif = '3' WHERE id_peg = '$id_peg'";
        
        if (!mysqli_query($conn, $sql_peg)) throw new Exception("Gagal Update Status Pegawai");

        mysqli_commit($conn);

        // --- SUKSES -> SWEETALERT ---
        $_SESSION['swal_icon'] = "success";
        $_SESSION['swal_title'] = "Berhasil";
        $_SESSION['swal_text'] = "Pegawai Berhasil Diberhentikan/Pensiun!";
        
        header("Location: ../../home-admin.php?page=form-view-data-mutasi");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Gagal: " . addslashes($e->getMessage()) . "');history.go(-1);</script>";
    }
}

// --- 2. PROSES UPDATE (EDIT) ---
elseif ($act == 'update') {
    $id_mutasi   = $_POST['id_mutasi'];
    $jns_mutasi  = $_POST['jns_mutasi'];
    $no_mutasi   = $_POST['no_mutasi'];
    $tgl_mutasi  = $_POST['tgl_mutasi'];
    $tmt         = $_POST['tmt'];
    $sk_lama     = $_POST['sk_lama'];

    $nama_file_sk = $sk_lama;
    if (!empty($_FILES['sk_mutasi']['tmp_name'])) {
        $ext = pathinfo($_FILES['sk_mutasi']['name'], PATHINFO_EXTENSION);
        $nama_file_sk = "SK_STOP_" . date('YmdHis') . "_" . rand(100,999) . "." . $ext;
        move_uploaded_file($_FILES['sk_mutasi']['tmp_name'], "../assets/sk_mutasi/" . $nama_file_sk);
        
        if ($sk_lama && file_exists("../assets/sk_mutasi/" . $sk_lama)) {
            unlink("../assets/sk_mutasi/" . $sk_lama);
        }
    }

    $sql_update = "UPDATE tb_mutasi SET 
                   jns_mutasi = '$jns_mutasi',
                   no_mutasi  = '$no_mutasi',
                   tgl_mutasi = '$tgl_mutasi',
                   tmt        = '$tmt',
                   sk_mutasi  = '$nama_file_sk'
                   WHERE id_mutasi = '$id_mutasi'";

    if (mysqli_query($conn, $sql_update)) {
        // --- SUKSES -> SWEETALERT ---
        $_SESSION['swal_icon'] = "success";
        $_SESSION['swal_title'] = "Tersimpan";
        $_SESSION['swal_text'] = "Data Mutasi Berhasil Diperbarui!";
        
        header("Location: ../../home-admin.php?page=form-view-data-mutasi");
        exit;
    } else {
        echo "<script>alert('Gagal Update: " . mysqli_error($conn) . "');history.go(-1);</script>";
    }
}
?>