<?php
/*********************************************************
 * FILE    : pages/ref-sertifikasi/process_soft_delete.php
 * MODULE  : Backend Delete Sertifikasi (Optimized Columns)
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

// --- 2. GET INFO (Untuk Modal) ---
if ($action === 'get_info') {
  $sql = "SELECT s.sertifikasi, s.penyelenggara, p.nama AS nama_peg
          FROM tb_sertifikasi s
          LEFT JOIN tb_pegawai p ON s.id_peg = p.id_peg
          WHERE s.id_sertif = ?";

  $stmt = $conn->prepare($sql);
  if (!$stmt) out(array('status'=>'error','message'=>'SQL Error: '.$conn->error));

  $stmt->bind_param("s", $id);
  if (!$stmt->execute()) out(array('status'=>'error','message'=>'Exec Error: '.$stmt->error));

  $sertifikasi = $penyelenggara = $nama_peg = null;
  $stmt->bind_result($sertifikasi, $penyelenggara, $nama_peg);

  if ($stmt->fetch()) {
    $stmt->close();
    out(array('status'=>'success', 'data'=>array(
        'sertifikasi'   => $sertifikasi,
        'penyelenggara' => $penyelenggara ?: '-',
        'nama_peg'      => $nama_peg ?: 'Tanpa Nama'
    )));
  } else {
    $stmt->close();
    out(array('status'=>'error','message'=>'Data ID '.$id.' tidak ditemukan.'));
  }
}

// --- 3. DELETE (Backup -> Delete) ---
if ($action === 'delete') {
  $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
  $user   = (isset($_SESSION['nama_user']) && $_SESSION['nama_user'] !== '') ? $_SESSION['nama_user'] : 'Admin';

  if ($reason === '') out(array('status'=>'error','message'=>'Alasan hapus wajib diisi'));

  $conn->autocommit(false);

  try {
    // A. Ambil Data Lama
    $sqlSel = "SELECT * FROM tb_sertifikasi WHERE id_sertif = '$id' LIMIT 1";
    $resSel = mysqli_query($conn, $sqlSel);
    if (!$resSel || mysqli_num_rows($resSel) == 0) throw new Exception("Data tidak ditemukan / sudah terhapus.");
    
    $dataRow = mysqli_fetch_assoc($resSel);

    // B. Insert ke Recycle Bin (Versi Ringkas)
    // Kita hapus 'dihapus_oleh', cukup pakai 'created_by'
    $tableName   = 'tb_sertifikasi';
    $jsonContent = json_encode($dataRow);
    
    // Query Insert (4 Kolom Data + Created otomatis timestamp)
    $sqlInsert = "INSERT INTO tb_recycle_bin (tabel_by, data_json, alasan, created_by, created) 
                  VALUES (?, ?, ?, ?, NOW())";

    $stmtIns = $conn->prepare($sqlInsert);
    if (!$stmtIns) throw new Exception("SQL Insert Error: ".$conn->error);

    // Bind 4 Parameter (ssss) -> tabel_by, data_json, alasan, created_by
    $stmtIns->bind_param("ssss", $tableName, $jsonContent, $reason, $user);
    
    if (!$stmtIns->execute()) {
        $stmtIns->close();
        throw new Exception("Gagal Backup: ".$conn->error);
    }
    $stmtIns->close();

    // C. Hapus Data Asli
    $stmtDel = $conn->prepare("DELETE FROM tb_sertifikasi WHERE id_sertif = ?");
    $stmtDel->bind_param("s", $id);
    
    if (!$stmtDel->execute()) {
        $stmtDel->close();
        throw new Exception("Gagal Hapus Data: ".$conn->error);
    }
    $stmtDel->close();

    // Sukses
    $conn->commit();
    $conn->autocommit(true);
    out(array('status'=>'success'));

  } catch (Exception $e) {
    // Gagal
    $conn->rollback();
    $conn->autocommit(true);
    out(array('status'=>'error','message'=>$e->getMessage()));
  }
}

out(array('status'=>'error','message'=>'Action tidak dikenal'));
?>