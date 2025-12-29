<?php
/*********************************************************
 * FILE    : pages/mutasi/process_soft_delete_mutasi.php
 * MODULE  : Backend Delete Mutasi (Backup -> Delete -> Restore Status)
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

// Cek Hak Akses
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
if ($hak_akses != 'admin' && $hak_akses != 'superadmin') {
    out(array('status'=>'error','message'=>'Akses Ditolak.'));
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$id     = isset($_POST['id']) ? trim($_POST['id']) : '';

if ($action === '' || $id === '') out(array('status'=>'error','message'=>'Parameter ID tidak valid'));

// --- 2. GET INFO (Untuk Modal) ---
if ($action === 'get_info') {
    $sql = "SELECT m.no_mutasi, m.tmt, p.nama AS nama_peg
            FROM tb_mutasi m
            LEFT JOIN tb_pegawai p ON m.id_peg = p.id_peg
            WHERE m.id_mutasi = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    
    $no_mutasi = $tmt = $nama_peg = null;
    $stmt->bind_result($no_mutasi, $tmt, $nama_peg);

    if ($stmt->fetch()) {
        $stmt->close();
        $tmt_fmt = ($tmt && $tmt != '0000-00-00') ? date('d-m-Y', strtotime($tmt)) : '-';
        out(array('status'=>'success', 'data'=>array(
            'no_mutasi' => $no_mutasi,
            'tmt'       => $tmt_fmt,
            'nama_peg'  => $nama_peg ?: 'Tanpa Nama'
        )));
    } else {
        $stmt->close();
        out(array('status'=>'error','message'=>'Data tidak ditemukan.'));
    }
}

// --- 3. DELETE (Backup -> Delete -> Restore Status) ---
if ($action === 'delete') {
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    $user   = (isset($_SESSION['nama_user']) && $_SESSION['nama_user'] !== '') ? $_SESSION['nama_user'] : 'Admin';

    if ($reason === '') out(array('status'=>'error','message'=>'Alasan hapus wajib diisi'));

    $conn->autocommit(false); // Mulai Transaksi

    try {
        // A. Ambil Data Lama (Untuk Backup & Ambil ID Pegawai)
        $sqlSel = "SELECT * FROM tb_mutasi WHERE id_mutasi = '$id' LIMIT 1";
        $resSel = mysqli_query($conn, $sqlSel);
        if (!$resSel || mysqli_num_rows($resSel) == 0) throw new Exception("Data sudah terhapus.");
        
        $dataRow = mysqli_fetch_assoc($resSel);
        $id_peg  = $dataRow['id_peg']; // Simpan ID Pegawai untuk update nanti

        // B. Insert ke Recycle Bin
        $tableName   = 'tb_mutasi';
        $jsonContent = json_encode($dataRow);
        
        $sqlInsert = "INSERT INTO tb_recycle_bin (tabel_by, data_json, alasan, created_by, created) 
                      VALUES (?, ?, ?, ?, NOW())";

        $stmtIns = $conn->prepare($sqlInsert);
        $stmtIns->bind_param("ssss", $tableName, $jsonContent, $reason, $user);
        
        if (!$stmtIns->execute()) throw new Exception("Gagal Backup: ".$stmtIns->error);
        $stmtIns->close();

        // C. Hapus Data Asli dari tb_mutasi
        $stmtDel = $conn->prepare("DELETE FROM tb_mutasi WHERE id_mutasi = ?");
        $stmtDel->bind_param("s", $id);
        
        if (!$stmtDel->execute()) throw new Exception("Gagal Hapus: ".$stmtDel->error);
        $stmtDel->close();

        // ======================================================
        // D. RESTORE STATUS PEGAWAI & JABATAN (LOGIC TAMBAHAN)
        // ======================================================

        // 1. Update tb_pegawai jadi AKTIF (1)
        if (!empty($id_peg)) {
            $sqlPeg = "UPDATE tb_pegawai SET status_aktif = '1' WHERE id_peg = '$id_peg'";
            if (!mysqli_query($conn, $sqlPeg)) {
                throw new Exception("Gagal mengaktifkan pegawai: " . mysqli_error($conn));
            }

            // 2. Update tb_jabatan TERAKHIR jadi AKTIF
            // Kita cari jabatan dengan TMT paling baru milik pegawai tersebut
            // Set status_jab = 'Aktif' dan sampai_tgl = NULL
            $sqlJab = "UPDATE tb_jabatan 
                       SET status_jab = 'Aktif', sampai_tgl = NULL 
                       WHERE id_peg = '$id_peg' 
                       ORDER BY tmt_jabatan DESC, id_jab DESC 
                       LIMIT 1";
            
            if (!mysqli_query($conn, $sqlJab)) {
                throw new Exception("Gagal mengaktifkan jabatan terakhir: " . mysqli_error($conn));
            }
        }

        // Sukses Semua
        $conn->commit();
        $conn->autocommit(true);
        out(array('status'=>'success'));

    } catch (Exception $e) {
        $conn->rollback();
        $conn->autocommit(true);
        out(array('status'=>'error','message'=>$e->getMessage()));
    }
}

out(array('status'=>'error','message'=>'Action tidak dikenal'));
?>