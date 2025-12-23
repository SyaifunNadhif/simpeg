<?php
// =============================================================
// FILE: pages/ref-sertifikasi/upload-data-sertifikasi.php
// MODULE: Backend Import (Smart Preview with Status Check)
// =============================================================

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

error_reporting(0);
ini_set('display_errors', 0);

ob_start();
header('Content-Type: application/json');

// --- HELPER FORMAT TANGGAL ---
function formatTanggal($val) {
    if (empty($val) || $val == '-' || $val == '') return NULL;
    if (is_numeric($val)) return Date::excelToDateTimeObject($val)->format('Y-m-d');
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $val)) return $val;
    $val = str_replace('/', '-', $val);
    $ts = strtotime($val);
    return $ts ? date('Y-m-d', $ts) : NULL;
}

try {
    // --- KONEKSI DATABASE ---
    $paths = [__DIR__ . '/../../dist/koneksi.php', __DIR__ . '/../../config/koneksi.php'];
    $conn = null;
    foreach ($paths as $path) { if (file_exists($path)) { include $path; if(isset($koneksi))$conn=$koneksi; if($conn)break; } }
    if (!$conn) throw new Exception("Koneksi Database Gagal.");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid Request");
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // =========================================================
    // BAGIAN A: PREVIEW DATA (CEK STATUS DATABASE)
    // =========================================================
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");

        $file = $_FILES['file_excel'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['xls', 'xlsx'])) throw new Exception("Format harus Excel (.xlsx)");

        $spreadsheet = IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true); 
        $header = array_shift($rows); 

        // HTML Header Tabel
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm text-nowrap align-middle" style="font-size: 0.9em;">';
        $html .= '<thead class="bg-primary text-white"><tr>';
        // Tambahkan Kolom Status
        $html .= '<th width="10%">Status Import</th><th>ID Pegawai</th><th>Nama Sertifikasi</th><th>Penyelenggara</th><th>Tgl Sertifikat</th><th>Tgl Expired</th><th>No Sertifikat</th>';
        $html .= '</tr></thead><tbody>';

        $limit = 10;
        $count = 0;
        $previewData = [];

        foreach ($rows as $row) {
            $id_peg      = trim($row['A']);
            if(empty($id_peg)) continue;

            $nama_sertif = trim($row['B']);
            $penyelenggara = trim($row['C']);
            
            // Format Tanggal
            $tgl_sertif_db = formatTanggal($row['D']);
            $tgl_exp_db    = formatTanggal($row['E']);
            $no_sertif     = trim($row['F']);

            // --- SMART LOGIC: CEK APAKAH DATA SUDAH ADA? ---
            $id_peg_esc = mysqli_real_escape_string($conn, $id_peg);
            $sertif_esc = mysqli_real_escape_string($conn, $nama_sertif);
            
            $sqlCek = "SELECT id_sertif FROM tb_sertifikasi 
                       WHERE id_peg = '$id_peg_esc' AND sertifikasi = '$sertif_esc'";
            
            if ($tgl_exp_db) {
                $sqlCek .= " AND tgl_expired = '$tgl_exp_db'";
            } else {
                $sqlCek .= " AND (tgl_expired IS NULL OR tgl_expired = '0000-00-00')";
            }

            $resCek = mysqli_query($conn, $sqlCek);
            $is_exist = ($resCek && mysqli_num_rows($resCek) > 0);

            // Tentukan Badge Status
            if ($is_exist) {
                $status_badge = '<span class="badge bg-warning text-dark"><i class="fas fa-edit"></i> Update Data</span>';
            } else {
                $status_badge = '<span class="badge bg-success"><i class="fas fa-plus"></i> Data Baru</span>';
            }

            // Simpan Data Bersih untuk JSON (Simpan logic exist juga biar cepat saat save)
            $previewData[] = [
                $id_peg, $nama_sertif, $penyelenggara, $tgl_sertif_db, $tgl_exp_db, $no_sertif
            ];

            if ($count < $limit) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . $status_badge . '</td>'; // Kolom Status
                $html .= '<td>' . htmlspecialchars($id_peg) . '</td>';
                $html .= '<td>' . htmlspecialchars($nama_sertif) . '</td>';
                $html .= '<td>' . htmlspecialchars($penyelenggara) . '</td>';
                $html .= '<td>' . ($tgl_sertif_db ? $tgl_sertif_db : '-') . '</td>';
                $html .= '<td>' . ($tgl_exp_db ? $tgl_exp_db : '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($no_sertif) . '</td>';
                $html .= '</tr>';
                $count++;
            }
        }
        $html .= '</tbody></table></div>';
        
        if (count($previewData) > $limit) {
            $html .= '<div class="alert alert-info text-center p-1 mt-2"><small>... menampilkan 10 dari ' . count($previewData) . ' data.</small></div>';
        }

        $html .= '<hr>';
        $html .= '<div class="d-flex justify-content-between align-items-center">';
        $html .= '<span class="text-muted small">Pastikan status di atas sesuai harapan Anda.</span>';
        $html .= '<button type="button" class="btn btn-primary px-4 rounded-pill shadow-sm" id="btnSimpanSertifikasi"><i class="fas fa-save mr-2"></i> Proses Simpan</button>';
        $html .= '</div>';
        
        $json_rows = json_encode($previewData);
        $html .= '<textarea id="json_data_sertifikasi" style="display:none;">' . $json_rows . '</textarea>';

        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // =========================================================
    // BAGIAN B: SIMPAN DATA (EKSEKUSI)
    // =========================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_sertifikasi'])) throw new Exception("Data tidak diterima");

        $data = json_decode($_POST['data_sertifikasi'], true);
        if (!$data) throw new Exception("Data korup");

        $jml_insert = 0;
        $jml_update = 0;
        $gagal = 0;
        $user_login = isset($_SESSION['nama_user']) ? $_SESSION['nama_user'] : 'System Import';

        foreach ($data as $row) {
            $id_peg         = mysqli_real_escape_string($conn, $row[0]);
            $sertifikasi    = mysqli_real_escape_string($conn, $row[1]);
            $penyelenggara  = mysqli_real_escape_string($conn, $row[2]);
            $tgl_sertifikat = $row[3]; // Sudah diformat saat preview
            $tgl_expired    = $row[4]; // Sudah diformat saat preview
            $no_sertifikat  = mysqli_real_escape_string($conn, $row[5]);

            if(empty($id_peg)) continue;

            // CEK LAGI UNTUK MENENTUKAN QUERY (Insert/Update)
            $sqlCek = "SELECT id_sertif FROM tb_sertifikasi WHERE id_peg = '$id_peg' AND sertifikasi = '$sertifikasi'";
            if ($tgl_expired) $sqlCek .= " AND tgl_expired = '$tgl_expired'";
            else $sqlCek .= " AND (tgl_expired IS NULL OR tgl_expired = '0000-00-00')";

            $resCek = mysqli_query($conn, $sqlCek);

            if ($resCek && mysqli_num_rows($resCek) > 0) {
                // UPDATE
                $dt = mysqli_fetch_assoc($resCek);
                $id_exist = $dt['id_sertif'];
                $upd = "UPDATE tb_sertifikasi SET 
                        penyelenggara = '$penyelenggara',
                        tgl_sertifikat = " . ($tgl_sertifikat ? "'$tgl_sertifikat'" : "NULL") . ",
                        sertifikat = '$no_sertifikat',
                        date_reg = NOW() 
                        WHERE id_sertif = '$id_exist'";
                if(mysqli_query($conn, $upd)) $jml_update++; else $gagal++;

            } else {
                // INSERT
                $ins = "INSERT INTO tb_sertifikasi (id_peg, sertifikasi, penyelenggara, tgl_sertifikat, tgl_expired, sertifikat, date_reg, created_by) 
                        VALUES ('$id_peg', '$sertifikasi', '$penyelenggara', " . ($tgl_sertifikat ? "'$tgl_sertifikat'" : "NULL") . ", " . ($tgl_expired ? "'$tgl_expired'" : "NULL") . ", '$no_sertifikat', NOW(), '$user_login')";
                if(mysqli_query($conn, $ins)) $jml_insert++; else $gagal++;
            }
        }

        ob_clean();
        echo json_encode([
            'status' => 'success', 
            'message' => "Selesai! Data Baru: $jml_insert, Diupdate: $jml_update, Gagal: $gagal"
        ]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>