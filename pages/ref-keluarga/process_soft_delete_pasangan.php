<?php
/*********************************************************
 * FILE     : pages/ref-keluarga/process_soft_delete_pasangan.php
 * MODULE   : Backend Delete Pasangan (Secure Transaction)
 *********************************************************/

if (session_id() === '') session_start();
header('Content-Type: application/json; charset=utf-8');

// Matikan error display agar JSON bersih
ini_set('display_errors', 0);
while (ob_get_level()) { @ob_end_clean(); }

function out($arr){ echo json_encode($arr); exit; }

// --- 1. SECURITY CHECK (Wajib Admin) ---
if (!isset($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['admin', 'superadmin'])) {
    out(array('status'=>'error', 'message'=>'Akses ditolak.'));
}

// --- 2. KONEKSI DATABASE ---
$paths = [ __DIR__ . '/../../dist/koneksi.php', __DIR__ . '/../../config/koneksi.php' ];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include_once $path; if (isset($koneksi)) $conn = $koneksi; break; } }
if (!$conn) out(array('status'=>'error','message'=>'Koneksi Database Gagal.'));

$action = isset($_POST['action']) ? $_POST['action'] : '';
$id     = isset($_POST['id']) ? trim($_POST['id']) : '';

if ($action === '' || $id === '') out(array('status'=>'error','message'=>'Parameter tidak valid'));

// --- 3. GET INFO (Untuk Preview di Modal) ---
if ($action === 'get_info') {
  $sql = "SELECT si.nama AS nama_pasangan, si.status_hub, p.nama AS nama_peg
          FROM tb_suamiistri si
          LEFT JOIN tb_pegawai p ON si.id_peg = p.id_peg
          WHERE si.id_si = ?"; 

  $stmt = $conn->prepare($sql);
  if (!$stmt) out(array('status'=>'error','message'=>'SQL Error'));

  $stmt->bind_param("s", $id);
  
  if ($stmt->execute()) {
      $stmt->bind_result($nama_pasangan, $status_hub, $nama_peg);
      if ($stmt->fetch()) {
        $stmt->close();
        // Kirim data ke Frontend
        out(array('status'=>'success', 'data'=>array(
            'nama_pasangan' => $nama_pasangan,
            'status_hub'    => $status_hub,
            'nama_peg'      => $nama_peg ?: 'Tanpa Nama'
        )));
      } else {
        $stmt->close();
        out(array('status'=>'error','message'=>'Data tidak ditemukan.'));
      }
  } else {
      out(array('status'=>'error','message'=>'Gagal mengambil data.'));
  }
}

// --- 4. DELETE ACTION (Backup -> Delete) ---
if ($action === 'delete') {
  $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
  $user   = (isset($_SESSION['nama_user']) && $_SESSION['nama_user'] !== '') ? $_SESSION['nama_user'] : 'System';

  if ($reason === '') out(array('status'=>'error','message'=>'Alasan hapus wajib diisi'));

  // Mulai Transaksi Database
  $conn->autocommit(false);

  try {
    // A. Ambil Data Lama (Select Full Row)
    $stmtSel = $conn->prepare("SELECT * FROM tb_suamiistri WHERE id_si = ? LIMIT 1");
    $stmtSel->bind_param("s", $id);
    $stmtSel->execute();
    $resSel = $stmtSel->get_result();
    
    if ($resSel->num_rows === 0) throw new Exception("Data tidak ditemukan atau sudah terhapus.");
    
    $dataRow = $resSel->fetch_assoc();
    $stmtSel->close();

    // B. Masukkan ke Recycle Bin (Backup)
    $tableName   = 'tb_suamiistri';
    $jsonContent = json_encode($dataRow); // Simpan data lama jadi JSON
    
    $sqlInsert = "INSERT INTO tb_recycle_bin (tabel_by, data_json, alasan, created_by, created) 
                  VALUES (?, ?, ?, ?, NOW())";

    $stmtIns = $conn->prepare($sqlInsert);
    if (!$stmtIns) throw new Exception("Gagal menyiapkan backup.");

    $stmtIns->bind_param("ssss", $tableName, $jsonContent, $reason, $user);
    
    if (!$stmtIns->execute()) {
        $stmtIns->close();
        throw new Exception("Gagal melakukan backup data.");
    }
    $stmtIns->close();

    // C. Hapus Data Asli (Delete)
    $stmtDel = $conn->prepare("DELETE FROM tb_suamiistri WHERE id_si = ?");
    $stmtDel->bind_param("s", $id);
    
    if (!$stmtDel->execute()) {
        $stmtDel->close();
        throw new Exception("Gagal menghapus data asli.");
    }
    $stmtDel->close();

    // Sukses? Commit perubahan!
    $conn->commit();
    $conn->autocommit(true);
    out(array('status'=>'success'));

  } catch (Exception $e) {
    // Gagal? Batalkan semua perubahan!
    $conn->rollback();
    $conn->autocommit(true);
    out(array('status'=>'error','message'=>$e->getMessage()));
  }
}

out(array('status'=>'error','message'=>'Action tidak dikenal'));
?>