<?php
// =============================================================
// FILE: pages/ref-sertifikasi/upload-data-sertifikasi.php
// =============================================================

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// 1. SETTING AGAR JSON BERSIH & AMAN
error_reporting(0);
ini_set('display_errors', 0);

ob_start();
header('Content-Type: application/json');

// 2. FUNGSI FORMAT TANGGAL
function formatTanggal($date) {
    if (empty($date) || $date == '-' || $date == '') return NULL;
    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
        return $date;
    }
    try {
        $timestamp = strtotime(str_replace('/', '-', $date));
        return $timestamp ? date('Y-m-d', $timestamp) : NULL;
    } catch (Exception $e) {
        return NULL;
    }
}

try {
    // 3. KONEKSI DATABASE
    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File koneksi tidak ditemukan");
    include $path_koneksi;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid Request Method");

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // =========================================================
    // BAGIAN A: PREVIEW DATA
    // =========================================================
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");

        $file = $_FILES['file_excel'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['xls', 'xlsx'])) throw new Exception("Format harus Excel (.xlsx)");

        $spreadsheet = IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) <= 1) throw new Exception("File Excel kosong");

        $header = array_shift($rows); 

        // Buat HTML Tabel
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm text-nowrap" style="font-size: 0.9em;">';
        $html .= '<thead class="bg-primary"><tr>';
        foreach ($header as $col) {
            $html .= '<th>' . htmlspecialchars($col) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        $limit = 10;
        $count = 0;
        foreach ($rows as $row) {
            if ($count >= $limit) break;
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
            $count++;
        }
        $html .= '</tbody></table></div>';
        
        if (count($rows) > $limit) {
            $html .= '<div class="alert alert-info text-center p-1"><small>... menampilkan 10 dari ' . count($rows) . ' data.</small></div>';
        }

        $html .= '<hr>';
        $html .= '<div class="text-right">';
        
        // [PENTING!!!] ID INI HARUS 'btnSimpanSertifikasi' AGAR BISA DIKLIK
        $html .= '<button type="button" class="btn btn-primary" id="btnSimpanSertifikasi"><i class="fas fa-save"></i> Simpan Sertifikasi</button>';
        
        $html .= '</div>';
        
        // [PENTING!!!] ID INI HARUS 'json_data_sertifikasi'
        $json_rows = json_encode($rows);
        $html .= '<textarea id="json_data_sertifikasi" style="display:none;">' . $json_rows . '</textarea>';

        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // =========================================================
    // BAGIAN B: SIMPAN DATA
    // =========================================================
    elseif ($action === 'save') {
        // [PENTING] Tangkap 'data_sertifikasi'
        if (!isset($_POST['data_sertifikasi'])) throw new Exception("Data tidak diterima (Key mismatch)");

        $data = json_decode($_POST['data_sertifikasi'], true);
        if (!$data) throw new Exception("Data korup/kosong");

        $berhasil = 0;
        $gagal = 0;

        foreach ($data as $row) {
            // Mapping Index
            $id_peg = isset($row[0]) ? mysqli_real_escape_string($conn, $row[0]) : '';
            if(empty($id_peg)) continue;

            $sertifikasi    = isset($row[1]) ? mysqli_real_escape_string($conn, $row[1]) : '';
            $penyelenggara  = isset($row[2]) ? mysqli_real_escape_string($conn, $row[2]) : '';
            $tgl_sertifikat = isset($row[3]) ? formatTanggal($row[3]) : NULL;
            $tgl_expired    = isset($row[4]) ? formatTanggal($row[4]) : NULL;
            $no_sertifikat  = isset($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : '';

            $query = "INSERT INTO tb_sertifikasi (
                id_peg, sertifikasi, penyelenggara, tgl_sertifikat, tgl_expired, sertifikat
            ) VALUES (
                '$id_peg', '$sertifikasi', '$penyelenggara', '$tgl_sertifikat', '$tgl_expired', '$no_sertifikat'
            )";

            if (mysqli_query($conn, $query)) {
                $berhasil++;
            } else {
                $gagal++;
            }
        }

        ob_clean();
        echo json_encode([
            'status' => 'success', 
            'message' => "Proses Selesai! Data Masuk: $berhasil, Gagal: $gagal"
        ]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}