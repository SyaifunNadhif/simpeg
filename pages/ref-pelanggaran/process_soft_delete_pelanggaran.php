<?php
/*********************************************************
 * FILE    : pages/ref-pelanggaran/process_soft_delete_pelanggaran.php
 * MODULE  : Backend Soft Delete Pelanggaran (PHP 5.6 Compatible)
 *********************************************************/

session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);

function out($arr){ echo json_encode($arr); exit; }

// --- 1. KONEKSI DATABASE (Support Multi Path) ---
$paths = array(
    dirname(__FILE__) . '/../../dist/koneksi.php',
    dirname(__FILE__) . '/../../config/koneksi.php'
);

$conn = null;
foreach ($paths as $path) {
    if (file_exists($path)) { 
        include_once $path; 
        if(isset($koneksi)) $conn = $koneksi; 
        if(isset($conn)) break; 
    }
}

if (!$conn) out(array('status'=>'error','message'=>'Koneksi DB Gagal'));

// Cek Akses
$hak_akses = isset($_SESSION['hak_akses']) ? $_SESSION['hak_akses'] : '';
if (empty($hak_akses) || ($hak_akses != 'admin' && $hak_akses != 'superadmin')) {
    out(array('status'=>'error','message'=>'Akses Ditolak'));
}

// --- PHP 5.6 COMPATIBLE INPUT HANDLING ---
$action = isset($_POST['action']) ? $_POST['action'] : '';
$id     = isset($_POST['id']) ? $_POST['id'] : '';

// --- 2. GET INFO ---
if ($action === 'get_info') {
    // Gunakan query manual agar aman di PHP 5.6 (Prepared Statement style)
    $stmt = $conn->prepare("SELECT h.hukuman, h.tgl_sk, p.nama as nama_peg 
                            FROM tb_hukuman h 
                            JOIN tb_pegawai p ON h.id_peg = p.id_peg 
                            WHERE h.id_hukum = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $tgl_fmt = date('d-m-Y', strtotime($row['tgl_sk']));
        out(array('status'=>'success', 'data'=>array(
            'hukuman'  => $row['hukuman'],
            'tgl_sk'   => $tgl_fmt,
            'nama_peg' => $row['nama_peg']
        )));
    } else {
        out(array('status'=>'error','message'=>'Data tidak ditemukan'));
    }
}

// --- 3. DELETE (Soft Delete + Backup) ---
if ($action === 'delete') {
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    $user   = isset($_SESSION['nama_user']) ? $_SESSION['nama_user'] : 'System';

    if (empty($reason)) out(array('status'=>'error','message'=>'Alasan wajib diisi'));

    $conn->autocommit(false); // Mulai Transaksi

    try {
        // A. Ambil Data Lama
        $qSel = mysqli_query($conn, "SELECT * FROM tb_hukuman WHERE id_hukum = '$id'");
        if(mysqli_num_rows($qSel) == 0) throw new Exception("Data tidak ditemukan.");
        $dataRow = mysqli_fetch_assoc($qSel);
        
        // B. Backup ke Recycle Bin
        $json = json_encode($dataRow);
        
        $stmtIns = $conn->prepare("INSERT INTO tb_recycle_bin (tabel_by, data_json, alasan, created_by, created) VALUES ('tb_hukuman', ?, ?, ?, NOW())");
        $stmtIns->bind_param("sss", $json, $reason, $user);
        
        if(!$stmtIns->execute()) throw new Exception("Gagal Backup: " . $conn->error);
        $stmtIns->close();

        // C. Delete Permanen dari tabel utama (Sesuai request Recyle Bin)
        if(!mysqli_query($conn, "DELETE FROM tb_hukuman WHERE id_hukum = '$id'")) {
            throw new Exception("Gagal Hapus Data: " . mysqli_error($conn));
        }

        $conn->commit();
        out(array('status'=>'success'));

    } catch (Exception $e) {
        $conn->rollback();
        out(array('status'=>'error','message'=>$e->getMessage()));
    }
}
?>