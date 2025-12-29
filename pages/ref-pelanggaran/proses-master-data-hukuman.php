<?php
/*********************************************************
 * FILE    : pages/ref-pelanggaran/proses-master-data-hukuman.php
 * MODULE  : Backend Pelanggaran (AJAX JSON Response)
 *********************************************************/

session_start();
header('Content-Type: application/json'); // PENTING: Response JSON
ini_set('display_errors', 0);

function out($arr){ echo json_encode($arr); exit; }

// Koneksi
$paths = array(dirname(__FILE__) . '/../../dist/koneksi.php', dirname(__FILE__) . '/../../config/koneksi.php');
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include_once $path; if(isset($koneksi))$conn=$koneksi; if(isset($conn))break; } }
if (!$conn) out(array('status'=>'error', 'message'=>'Koneksi Database Gagal'));

// Cek Akses
$hak_akses = isset($_SESSION['hak_akses']) ? $_SESSION['hak_akses'] : '';
if (empty($hak_akses) || ($hak_akses != 'admin' && $hak_akses != 'superadmin')) {
    out(array('status'=>'error', 'message'=>'Akses Ditolak'));
}

// Ambil Action dari POST (Karena via AJAX FormData)
$act = isset($_POST['act']) ? $_POST['act'] : '';

// --- FUNGSI UPLOAD ---
function uploadDokumen($fileInput, $id_peg) {
    if (!empty($fileInput['tmp_name'])) {
        $ext = pathinfo($fileInput['name'], PATHINFO_EXTENSION);
        $nama_file = "SK_HUKUM_" . $id_peg . "_" . date('YmdHis') . "." . $ext;
        $target_dir = dirname(__FILE__) . "/../../pages/assets/dokumen_hukuman/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        if(move_uploaded_file($fileInput['tmp_name'], $target_dir . $nama_file)){
            return $nama_file;
        }
    }
    return false;
}

// --- 1. INSERT ---
if ($act == 'insert') {
    $id_peg = $_POST['id_peg'];
    $hukuman = $_POST['hukuman'];
    $keterangan = $_POST['keterangan'];
    $pejabat_sk = $_POST['pejabat_sk'];
    $jabatan_sk = $_POST['jabatan_sk'];
    $no_sk = $_POST['no_sk'];
    $tgl_sk = $_POST['tgl_sk'];
    
    $nama_file = "";
    $upload = uploadDokumen($_FILES['dokumen'], $id_peg);
    if($upload) $nama_file = $upload;

    $pejabat_pulih = isset($_POST['pejabat_pulih']) ? $_POST['pejabat_pulih'] : '';
    $jabatan_pulih = isset($_POST['jabatan_pulih']) ? $_POST['jabatan_pulih'] : '';
    $no_pulih      = isset($_POST['no_pulih']) ? $_POST['no_pulih'] : '';
    $tgl_pulih     = !empty($_POST['tgl_pulih']) ? "'".$_POST['tgl_pulih']."'" : "NULL";

    $sql = "INSERT INTO tb_hukuman (id_peg, hukuman, keterangan, pejabat_sk, jabatan_sk, no_sk, tgl_sk, dokumen, pejabat_pulih, jabatan_pulih, no_pulih, tgl_pulih, date_reg) 
            VALUES ('$id_peg', '$hukuman', '$keterangan', '$pejabat_sk', '$jabatan_sk', '$no_sk', '$tgl_sk', '$nama_file', '$pejabat_pulih', '$jabatan_pulih', '$no_pulih', $tgl_pulih, NOW())";

    if (mysqli_query($conn, $sql)) {
        out(array('status'=>'success', 'message'=>'Data pelanggaran berhasil disimpan.'));
    } else {
        out(array('status'=>'error', 'message'=>'Gagal DB: '.mysqli_error($conn)));
    }
}

// --- 2. UPDATE ---
elseif ($act == 'update') {
    $id_hukum = $_POST['id_hukum'];
    $id_peg = $_POST['id_peg']; // Harus dikirim dari form (walaupun select)
    
    $hukuman = $_POST['hukuman'];
    $keterangan = $_POST['keterangan'];
    $pejabat_sk = $_POST['pejabat_sk'];
    $jabatan_sk = $_POST['jabatan_sk'];
    $no_sk = $_POST['no_sk'];
    $tgl_sk = $_POST['tgl_sk'];
    
    $nama_file = $_POST['dokumen_lama'];
    $upload = uploadDokumen($_FILES['dokumen'], $id_peg);
    if($upload) {
        $nama_file = $upload;
        $old_file = dirname(__FILE__) . "/../../pages/assets/dokumen_hukuman/" . $_POST['dokumen_lama'];
        if($_POST['dokumen_lama'] && file_exists($old_file)) unlink($old_file);
    }

    $pejabat_pulih = $_POST['pejabat_pulih'];
    $jabatan_pulih = $_POST['jabatan_pulih'];
    $no_pulih      = $_POST['no_pulih'];
    $tgl_pulih     = !empty($_POST['tgl_pulih']) ? "'".$_POST['tgl_pulih']."'" : "NULL";

    $sql = "UPDATE tb_hukuman SET id_peg='$id_peg', hukuman='$hukuman', keterangan='$keterangan', pejabat_sk='$pejabat_sk', jabatan_sk='$jabatan_sk', no_sk='$no_sk', tgl_sk='$tgl_sk', dokumen='$nama_file', pejabat_pulih='$pejabat_pulih', jabatan_pulih='$jabatan_pulih', no_pulih='$no_pulih', tgl_pulih=$tgl_pulih WHERE id_hukum='$id_hukum'";

    if (mysqli_query($conn, $sql)) {
        out(array('status'=>'success', 'message'=>'Data pelanggaran berhasil diperbarui.'));
    } else {
        out(array('status'=>'error', 'message'=>'Gagal Update: '.mysqli_error($conn)));
    }
}
?>