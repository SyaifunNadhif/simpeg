<?php
/*********************************************************
 * FILE     : pages/user/process_soft_delete_user.php
 * MODULE   : Delete User (Recycle Bin)
 *********************************************************/

if (session_id() == '') session_start();
header('Content-Type: application/json; charset=utf-8');
include "../../dist/koneksi.php";

// Cek Akses
if (!isset($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['admin', 'superadmin'])) {
    echo json_encode(['status'=>'error', 'message'=>'Akses Ditolak']); exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$id     = isset($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : '';

if ($action == 'get_info') {
    $q = mysqli_query($conn, "SELECT nama_user, id_user FROM tb_user WHERE id_user = '$id'");
    if($r = mysqli_fetch_assoc($q)){
        echo json_encode(['status'=>'success', 'data'=>$r]);
    } else {
        echo json_encode(['status'=>'error', 'message'=>'Data tidak ditemukan']);
    }
}

if ($action == 'delete') {
    $reason = isset($_POST['reason']) ? mysqli_real_escape_string($conn, $_POST['reason']) : '-';
    $admin  = $_SESSION['id_user'];

    // Ambil Data Lama
    $qSel = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user = '$id'");
    if(mysqli_num_rows($qSel) > 0) {
        $row = mysqli_fetch_assoc($qSel);
        $json = mysqli_real_escape_string($conn, json_encode($row));

        // Insert Recycle Bin
        $sqlBin = "INSERT INTO tb_recycle_bin (tabel_by, data_json, alasan, created_by, created) VALUES ('tb_user', '$json', '$reason', '$admin', NOW())";
        mysqli_query($conn, $sqlBin);

        // Delete Asli
        mysqli_query($conn, "DELETE FROM tb_user WHERE id_user = '$id'");
        
        echo json_encode(['status'=>'success']);
    } else {
        echo json_encode(['status'=>'error', 'message'=>'Gagal Hapus']);
    }
}
?>