<?php
// =============================================================
// FILE: pages/ref-diklat/upload-data-diklat.php
// MODULE: Backend Import Diklat (Smart Update & Date Parser)
// =============================================================

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

session_start();
error_reporting(0);
ini_set('display_errors', 0);

while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

// SECURITY
if (empty($_SESSION['id_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi habis. Silakan login ulang.']);
    exit;
}

// --- FUNGSI TANGGAL SAKTI (Handle Excel Serial, ID, EN) ---
function parseTanggalSakti($val) {
    $val = trim($val);
    if (empty($val) || $val == '-' || $val == '0000-00-00') return date('Y-m-d');

    // 1. Cek Excel Serial Number
    if (is_numeric($val) && $val > 1000) {
        try {
            return Date::excelToDateTimeObject($val)->format('Y-m-d');
        } catch (Exception $e) { return date('Y-m-d'); }
    }

    // 2. Cek Format Y-m-d (Database Standard)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $val;

    // 3. Cek Format d/m/Y atau d-m-Y (Indonesia)
    // Ubah semua separator jadi dash (-)
    $val = str_replace(['/', '.'], '-', $val);
    $ts = strtotime($val);
    
    if ($ts !== false && $ts > 0) return date('Y-m-d', $ts);

    return date('Y-m-d'); // Default hari ini jika gagal total
}

try {
    // 3. KONEKSI DATABASE
    $paths = [
        __DIR__ . '/../../dist/koneksi.php',
        __DIR__ . '/../../config/koneksi.php'
    ];
    $conn = null;
    foreach ($paths as $path) {
        if (file_exists($path)) {
            include $path;
            if (isset($koneksi) && $koneksi) { $conn = $koneksi; break; }
            if (isset($conn) && $conn) { break; }
        }
    }
    
    if (!$conn) throw new Exception("Koneksi Database Gagal.");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid Request Method");
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // =========================================================
    // BAGIAN A: PREVIEW DATA (CEK STATUS DATABASE)
    // =========================================================
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");

        $file = $_FILES['file_excel'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xls', 'xlsx'])) throw new Exception("Format harus Excel (.xlsx)");

        $spreadsheet = IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        // Ambil data (raw=true) agar format tanggal tidak otomatis dikonversi jadi string aneh
        $rows = $sheet->toArray(null, true, true, true); 

        $header = array_shift($rows); // Buang Header

        // HTML Table Header
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm text-nowrap align-middle" style="font-size: 0.9em;">';
        $html .= '<thead class="bg-primary text-white"><tr>';
        $html .= '<th width="10%">Status Import</th><th>ID Pegawai</th><th>Nama Diklat</th><th>Penyelenggara</th><th>Tempat</th><th>Biaya</th><th>Angkatan</th><th>Tahun</th><th>Tgl Mulai</th>';
        $html .= '</tr></thead><tbody>';

        $limit = 15; 
        $count = 0;
        $previewData = [];

        foreach ($rows as $row) {
            // Mapping Kolom Excel (A, B, C...)
            $id_peg        = trim($row['A']);
            if(empty($id_peg)) continue;

            $diklat        = trim($row['B']);
            $penyelenggara = trim($row['C']);
            $tempat        = trim($row['D']);
            $biaya         = preg_replace('/[^0-9]/', '', $row['E']); // Hapus 'Rp', '.', ','
            $angkatan      = trim($row['F']);
            $tahun         = trim($row['G']);
            
            // Parsing Tanggal Cerdas
            $tgl_mulai_db  = parseTanggalSakti($row['H']);

            // --- SMART LOGIC: CEK APAKAH DATA SUDAH ADA? ---
            // Kunci Cek: ID Pegawai + Nama Diklat + Tahun
            $id_peg_esc = mysqli_real_escape_string($conn, $id_peg);
            $diklat_esc = mysqli_real_escape_string($conn, $diklat);
            $tahun_esc  = mysqli_real_escape_string($conn, $tahun);

            $sqlCek = "SELECT id_diklat FROM tb_diklat 
                       WHERE id_peg = '$id_peg_esc' 
                       AND TRIM(LOWER(diklat)) = TRIM(LOWER('$diklat_esc'))
                       AND tahun = '$tahun_esc'";
            
            $resCek = mysqli_query($conn, $sqlCek);
            $is_exist = ($resCek && mysqli_num_rows($resCek) > 0);

            // Badge Status
            if ($is_exist) {
                $status_badge = '<span class="badge bg-warning text-dark"><i class="fas fa-edit"></i> Update Data</span>';
            } else {
                $status_badge = '<span class="badge bg-success"><i class="fas fa-plus"></i> Data Baru</span>';
            }

            // Simpan Array Bersih
            $previewData[] = [
                $id_peg, $diklat, $penyelenggara, $tempat, $biaya, $angkatan, $tahun, $tgl_mulai_db
            ];

            if ($count < $limit) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . $status_badge . '</td>';
                $html .= '<td>' . htmlspecialchars($id_peg) . '</td>';
                $html .= '<td>' . htmlspecialchars($diklat) . '</td>';
                $html .= '<td>' . htmlspecialchars($penyelenggara) . '</td>';
                $html .= '<td>' . htmlspecialchars($tempat) . '</td>';
                $html .= '<td>' . number_format((float)$biaya, 0, ',', '.') . '</td>';
                $html .= '<td>' . htmlspecialchars($angkatan) . '</td>';
                $html .= '<td>' . htmlspecialchars($tahun) . '</td>';
                $html .= '<td>' . htmlspecialchars($tgl_mulai_db) . '</td>';
                $html .= '</tr>';
                $count++;
            }
        }
        $html .= '</tbody></table></div>';
        
        if (count($previewData) > $limit) {
            $html .= '<div class="alert alert-info text-center p-1 mt-2"><small>... menampilkan 15 dari ' . count($previewData) . ' data.</small></div>';
        }

        $html .= '<hr><div class="d-flex justify-content-between align-items-center">';
        $html .= '<span class="text-muted small">Cek kembali status (Update/Baru) sebelum menyimpan.</span>';
        $html .= '<button type="button" class="btn btn-primary px-4 rounded-pill shadow-sm" id="btnSimpanDiklat"><i class="fas fa-save mr-2"></i> Proses Simpan</button>';
        $html .= '</div>';
        
        // Simpan JSON Raw Data
        $json_rows = json_encode($previewData);
        $html .= '<textarea id="json_data_diklat" style="display:none;">' . htmlspecialchars($json_rows) . '</textarea>';

        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // =========================================================
    // BAGIAN B: SIMPAN DATA (INSERT OR UPDATE)
    // =========================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_diklat'])) throw new Exception("Data tidak diterima");
        
        $data = json_decode($_POST['data_diklat'], true);
        if (!$data) throw new Exception("Format data corrupt");

        $user_log = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'admin';
        
        $insert = 0;
        $update = 0;
        $gagal  = 0;

        foreach ($data as $row) {
            // Index Array sesuai urutan di PreviewData[]
            $id_peg        = mysqli_real_escape_string($conn, $row[0]);
            $diklat        = mysqli_real_escape_string($conn, $row[1]);
            $penyelenggara = mysqli_real_escape_string($conn, $row[2]);
            $tempat        = mysqli_real_escape_string($conn, $row[3]);
            $biaya         = $row[4]; // Sudah bersih angka
            $angkatan      = mysqli_real_escape_string($conn, $row[5]);
            $tahun         = mysqli_real_escape_string($conn, $row[6]);
            $date_reg      = $row[7]; // Sudah format Y-m-d

            if(empty($id_peg) || empty($diklat)) { $gagal++; continue; }

            // LOGIC CEK DUPLIKAT (SAMA DENGAN PREVIEW)
            $sqlCek = "SELECT id_diklat FROM tb_diklat 
                       WHERE id_peg = '$id_peg' 
                       AND TRIM(LOWER(diklat)) = TRIM(LOWER('$diklat'))
                       AND tahun = '$tahun'"; // Cek Tahun juga biar aman

            $resCek = mysqli_query($conn, $sqlCek);

            if ($resCek && mysqli_num_rows($resCek) > 0) {
                // --- UPDATE ---
                $rDup = mysqli_fetch_assoc($resCek);
                $id_diklat_exist = $rDup['id_diklat'];

                $sqlUpdate = "UPDATE tb_diklat SET 
                              penyelenggara = '$penyelenggara',
                              tempat        = '$tempat',
                              biaya         = '$biaya',
                              angkatan      = '$angkatan',
                              date_reg      = '$date_reg', 
                              updated_by    = '$user_log',
                              updated_at    = NOW()
                              WHERE id_diklat = '$id_diklat_exist'";
                
                if(mysqli_query($conn, $sqlUpdate)) { $update++; } else { $gagal++; }

            } else {
                // --- INSERT ---
                $sqlInsert = "INSERT INTO tb_diklat (
                    id_peg, diklat, penyelenggara, tempat, biaya, angkatan, tahun, date_reg, created_by
                ) VALUES (
                    '$id_peg', '$diklat', '$penyelenggara', '$tempat', '$biaya', '$angkatan', '$tahun', '$date_reg', '$user_log'
                )";
                
                if(mysqli_query($conn, $sqlInsert)) { $insert++; } else { $gagal++; }
            }
        }

        ob_clean();
        echo json_encode([
            'status' => 'success', 
            'message' => "Proses Selesai!<br>Input Baru: <b>$insert</b><br>Update Data: <b>$update</b><br>Gagal/Skip: <b>$gagal</b>"
        ]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>