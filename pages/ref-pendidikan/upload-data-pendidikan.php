<?php
// =============================================================
// FILE: pages/ref-pendidikan/upload-data-pendidikan.php
// LOGIC: Upsert based on (ID_PEG + JENJANG + NAMA_SEKOLAH)
// =============================================================

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_id() == '') session_start(); 

// Buffer output
ob_start();
header('Content-Type: application/json');

// --- Helper Response ---
function kirimJson($status, $msg, $html = '') {
    ob_clean(); 
    echo json_encode(['status' => $status, 'message' => $msg, 'html' => $html]);
    exit;
}

try {
    // 2. Load Library & Koneksi
    $path_vendor = '../../vendor/autoload.php';
    if (!file_exists($path_vendor)) throw new Exception("Library Excel tidak ditemukan.");
    require $path_vendor;

    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File Koneksi tidak ditemukan.");
    include $path_koneksi;

    if (!$conn) throw new Exception("Koneksi database gagal.");

    // --- FUNGSI FORMAT TANGGAL ROBUST ---
    function formatTanggal($date) {
        $date = trim($date);
        if (empty($date) || $date == '-' || $date == '') return NULL;
        
        // A. Cek Angka Excel
        if (is_numeric($date)) {
            try { return Date::excelToDateTimeObject($date)->format('Y-m-d'); } catch (Exception $e) { return NULL; }
        }
        
        // B. String
        $date = str_replace('/', '-', $date);
        if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) return $date;
        
        $parts = explode('-', $date);
        if (count($parts) == 3 && strlen($parts[2]) == 4) return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        
        try { return date('Y-m-d', strtotime($date)); } catch (Exception $e) { return NULL; }
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid Request Method");
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // ============================================================
    // ACTION: PREVIEW
    // ============================================================
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");
        
        $file = $_FILES['file_excel'];
        $spreadsheet = IOFactory::load($file['tmp_name']);
        
        // Load RAW Data (False)
        $rows = $spreadsheet->getActiveSheet()->toArray(null, false, true, false);

        if (count($rows) <= 1) throw new Exception("File Excel kosong");
        $header = array_shift($rows); 

        $html = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm text-nowrap" style="font-size:0.85em;">';
        $html .= '<thead class="bg-success text-white"><tr><th>Status System</th>';
        foreach ($header as $col) $html .= '<th>' . htmlspecialchars($col) . '</th>';
        $html .= '</tr></thead><tbody>';

        $limit = 10; $count = 0;
        foreach ($rows as $row) {
            if ($count >= $limit) break;
            
            $id_peg = isset($row[0]) ? $row[0] : '';
            if(empty($id_peg)) continue; 

            // Cek Kelengkapan Kunci (ID + Jenjang + Sekolah)
            $jenjang = isset($row[2]) ? $row[2] : '';
            $sekolah = isset($row[3]) ? $row[3] : '';

            if(empty($jenjang) || empty($sekolah)) {
                $status_row = '<span class="badge badge-danger">Data Tidak Lengkap</span>';
            } else {
                $status_row = '<span class="badge badge-info">Ready</span>';
            }

            $html .= '<tr><td>' . $status_row . '</td>';
            foreach ($row as $index => $cell) {
                // Kolom Tgl Ijazah (Index 7)
                if ($index == 7) {
                    $tgl = formatTanggal($cell);
                    $display = $tgl ? date('d-m-Y', strtotime($tgl)) : '<span class="text-danger fw-bold">Invalid</span>';
                    $html .= '<td>' . $display . '</td>';
                } else {
                    $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
            }
            $html .= '</tr>';
            $count++;
        }
        $html .= '</tbody></table></div>';
        $html .= '<div class="alert alert-info mt-2 small"><i class="fas fa-info-circle"></i> Jika data (ID Pegawai + Jenjang + Nama Sekolah) sama, data lama akan di-<b>UPDATE</b>.</div>';
        $html .= '<hr><div class="text-right"><button type="button" class="btn btn-success" id="btnSimpanPendidikan"><i class="fas fa-save"></i> Simpan Data</button></div>';
        
        $json_data = json_encode($rows);
        kirimJson('success', '', $html . '<textarea id="json_data_pendidikan" style="display:none;">' . $json_data . '</textarea>');
    }

    // ============================================================
    // ACTION: SAVE (UPSERT LOGIC)
    // ============================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_pendidikan'])) throw new Exception("Data tidak diterima");
        $data = json_decode($_POST['data_pendidikan'], true);
        
        $created_by = isset($_SESSION['nama_user']) ? mysqli_real_escape_string($conn, $_SESSION['nama_user']) : 'System';
        $berhasil = 0; $update = 0; $gagal = 0;

        foreach ($data as $row) {
            // MAPPING (Sesuaikan dengan Template Excel)
            // A[0]: ID Peg, B[1]: ID Sekolah, C[2]: Jenjang, D[3]: Nama Sekolah, E[4]: Lokasi, F[5]: Jurusan
            // G[6]: No Ijazah, H[7]: Tgl Ijazah, I[8]: Kepala, J[9]: Status, K[10]: Th Masuk, L[11]: Th Lulus

            $id_peg = isset($row[0]) ? trim($row[0]) : '';
            if(empty($id_peg)) continue;

            // Cek apakah pegawai ada di database?
            $cek_peg = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_peg'");
            if (mysqli_num_rows($cek_peg) > 0) {

                $id_sekolah   = isset($row[1]) ? mysqli_real_escape_string($conn, $row[1]) : '';
                $jenjang      = isset($row[2]) ? mysqli_real_escape_string($conn, $row[2]) : '';
                $nama_sekolah = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
                $lokasi       = isset($row[4]) ? mysqli_real_escape_string($conn, $row[4]) : '';
                $jurusan      = isset($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : '';
                $no_ijazah    = isset($row[6]) ? mysqli_real_escape_string($conn, $row[6]) : '';
                
                // Format Tanggal
                $tgl_ijazah   = formatTanggal(isset($row[7]) ? $row[7] : '');
                $val_tgl      = ($tgl_ijazah) ? "'$tgl_ijazah'" : "NULL";

                $kepala       = isset($row[8]) ? mysqli_real_escape_string($conn, $row[8]) : '';
                $status       = isset($row[9]) ? mysqli_real_escape_string($conn, $row[9]) : '';
                $th_masuk     = isset($row[10]) ? mysqli_real_escape_string($conn, $row[10]) : '';
                $th_lulus     = isset($row[11]) ? mysqli_real_escape_string($conn, $row[11]) : '';

                $date_reg     = date('Y-m-d H:i:s');

                // LOGIKA UPSERT: Cek ID Peg + Jenjang + Nama Sekolah
                $cekAda = mysqli_query($conn, "SELECT id_pendidikan FROM tb_pendidikan WHERE id_peg='$id_peg' AND jenjang='$jenjang' AND nama_sekolah='$nama_sekolah'");

                if (mysqli_num_rows($cekAda) > 0) {
                    // --- UPDATE ---
                    $rowOld = mysqli_fetch_assoc($cekAda);
                    $id_target = $rowOld['id_pendidikan'];

                    $qry = "UPDATE tb_pendidikan SET 
                            id_sekolah='$id_sekolah', lokasi='$lokasi', jurusan='$jurusan',
                            no_ijazah='$no_ijazah', tgl_ijazah=$val_tgl, kepala='$kepala', status='$status',
                            th_masuk='$th_masuk', th_lulus='$th_lulus', updated_at=NOW(), updated_by='$created_by'
                            WHERE id_pendidikan='$id_target'";
                    
                    if (mysqli_query($conn, $qry)) $update++; else $gagal++;

                } else {
                    // --- INSERT ---
                    $qry = "INSERT INTO tb_pendidikan (
                            id_peg, id_sekolah, jenjang, nama_sekolah, lokasi, jurusan, 
                            no_ijazah, tgl_ijazah, kepala, status, th_masuk, th_lulus, 
                            created_by, date_reg
                        ) VALUES (
                            '$id_peg', '$id_sekolah', '$jenjang', '$nama_sekolah', '$lokasi', '$jurusan',
                            '$no_ijazah', $val_tgl, '$kepala', '$status', '$th_masuk', '$th_lulus',
                            '$created_by', '$date_reg'
                        )";
                    
                    if (mysqli_query($conn, $qry)) $berhasil++; else $gagal++;
                }

            } else {
                $gagal++; // Pegawai tidak ditemukan
            }
        }

        kirimJson('success', "Import Selesai!<br>Data Baru: <b>$berhasil</b><br>Data Diupdate: <b>$update</b><br>Gagal/Skip: <b>$gagal</b>");
    }

} catch (Exception $e) {
    kirimJson('error', "<b>SYSTEM ERROR:</b> " . $e->getMessage());
}
?>