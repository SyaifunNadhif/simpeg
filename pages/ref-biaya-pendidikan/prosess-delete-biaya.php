<?php
/*********************************************************
 * FILE     : pages/ref-biaya-pendidikan/process-delete-biaya.php
 * MODULE   : Backend Delete Biaya Pendidikan (Recycle Bin)
 *********************************************************/

if (session_id() === '') session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
while (ob_get_level()) { @ob_end_clean(); }

function out($arr){ echo json_encode($arr); exit; }

// --- 1. KONEKSI DATABASE ---
$paths = [
    __DIR__ . '/../../dist/koneksi.php',
    __DIR__ . '/../../config/koneksi.php'
];

$conn = null;
foreach ($paths as $path) {
    if (file_exists($path)) {
        include_once $path;
        if (isset($koneksi) && $koneksi) { $conn = $koneksi; break; }
        if (isset($conn) && $conn) { break; }
    }
}

if (!$conn) out(array('status'=>'error','message'=>'Koneksi Database Gagal.'));

$action = isset($_POST['action']) ? $_POST['action'] : '';
$id     = isset($_POST['id']) ? trim($_POST['id']) : '';

if ($action === '' || $id === '') out(array('status'=>'error','message'=>'Parameter ID tidak valid'));

// --- 2. GET INFO (Untuk Tampil di Modal Konfirmasi) ---
if ($action === 'get_info') {
    // Ambil data detail biar user tau apa yang mau dihapus
    $sql = "SELECT pengembangan_sdm, total_biaya, pihak_pelaksana 
            FROM tb_biaya_pendidikan 
            WHERE biaya_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) out(array('status'=>'error','message'=>'SQL Error: '.$conn->error));

    $stmt->bind_param("s", $id);
    if (!$stmt->execute()) out(array('status'=>'error','message'=>'Exec Error: '.$stmt->error));

    $kegiatan = $biaya = $pihak = null;
    $stmt->bind_result($kegiatan, $biaya, $pihak);

    if ($stmt->fetch()) {
        $stmt->close();
        
        // Format Rupiah
        $biaya_fmt = "Rp " . number_format($biaya, 0, ',', '.');
        
        out(array('status'=>'success', 'data'=>array(
            'kegiatan' => $kegiatan,
            'biaya'    => $biaya_fmt,
            'pihak'    => $pihak ?: '-'
        )));
    } else {
        $stmt->close();
        out(array('status'=>'error','message'=>'Data ID '.$id.' tidak ditemukan.'));
    }
}

// --- 3. DELETE (Backup to Recycle Bin -> Delete) ---
if ($action === 'delete') {
    // Alasan tidak wajib diisi kalau di UI tombolnya langsung 'Ya Hapus', 
    // tapi kalau mau wajib, uncomment baris validasi di bawah.
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : 'Penghapusan via Web'; 
    $user   = (isset($_SESSION['nama_user']) && $_SESSION['nama_user'] !== '') ? $_SESSION['nama_user'] : 'Admin';

    $conn->autocommit(false); // Mulai Transaksi

    try {
        // A. Ambil Data Lama (Full Row) untuk Backup
        $sqlSel = "SELECT * FROM tb_biaya_pendidikan WHERE biaya_id = '$id' LIMIT 1";
        $resSel = mysqli_query($conn, $sqlSel);
        if (!$resSel || mysqli_num_rows($resSel) == 0) throw new Exception("Data tidak ditemukan / sudah terhapus.");
        
        $dataRow = mysqli_fetch_assoc($resSel);

        // B. Insert ke Recycle Bin
        $tableName   = 'tb_biaya_pendidikan';
        $jsonContent = json_encode($dataRow);
        
        // Pastikan tabel 'tb_recycle_bin' ada kolom 'tabel_by', 'data_json', 'alasan', 'created_by', 'created'
        $sqlInsert = "INSERT INTO tb_recycle_bin (tabel_by, data_json, alasan, created_by, created) 
                      VALUES (?, ?, ?, ?, NOW())";

        $stmtIns = $conn->prepare($sqlInsert);
        if (!$stmtIns) throw new Exception("SQL Insert Error: ".$conn->error);

        $stmtIns->bind_param("ssss", $tableName, $jsonContent, $reason, $user);
        
        if (!$stmtIns->execute()) {
            $stmtIns->close();
            throw new Exception("Gagal Backup ke Recycle Bin: ".$conn->error);
        }
        $stmtIns->close();

        // C. Hapus Data Asli
        $stmtDel = $conn->prepare("DELETE FROM tb_biaya_pendidikan WHERE biaya_id = ?");
        $stmtDel->bind_param("s", $id);
        
        if (!$stmtDel->execute()) {
            $stmtDel->close();
            throw new Exception("Gagal Hapus Data: ".$conn->error);
        }
        $stmtDel->close();

        // Sukses
        $conn->commit();
        $conn->autocommit(true);
        out(array('status'=>'success', 'message'=>'Data berhasil dihapus & dibackup.'));

    } catch (Exception $e) {
        // Gagal, Rollback
        $conn->rollback();
        $conn->autocommit(true);
        out(array('status'=>'error','message'=>$e->getMessage()));
    }
}

out(array('status'=>'error','message'=>'Action tidak dikenal'));
?>