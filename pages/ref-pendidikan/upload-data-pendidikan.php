<?php
// =============================================================
// FILE: pages/ref-pendidikan/upload-data-pendidikan.php
// =============================================================

session_start(); // Wajib start session

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(0);
ini_set('display_errors', 0);
ob_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

// Fungsi Format Tanggal
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
    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File koneksi tidak ditemukan");
    include $path_koneksi;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid Request Method");
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // --- PREVIEW ---
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");
        
        $file = $_FILES['file_excel'];
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) <= 1) throw new Exception("File Excel kosong");
        $header = array_shift($rows); 

        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm text-nowrap" style="font-size: 0.9em;">';
        $html .= '<thead class="bg-success"><tr>';
        foreach ($header as $col) $html .= '<th>' . htmlspecialchars($col) . '</th>';
        $html .= '</tr></thead><tbody>';

        $limit = 10; $count = 0;
        foreach ($rows as $row) {
            if ($count >= $limit) break;
            $html .= '<tr>';
            foreach ($row as $cell) $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            $html .= '</tr>';
            $count++;
        }
        $html .= '</tbody></table></div>';
        
        if (count($rows) > $limit) $html .= '<div class="text-center small text-muted p-1">... menampilkan 10 data awal.</div>';

        $html .= '<hr><div class="text-right">';
        $html .= '<button type="button" class="btn btn-success" id="btnSimpanPendidikan"><i class="fas fa-save"></i> Simpan Pendidikan</button>';
        $html .= '</div>';
        
        $json_rows = json_encode($rows);
        $html .= '<textarea id="json_data_pendidikan" style="display:none;">' . $json_rows . '</textarea>';

        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // --- SAVE ---
    elseif ($action === 'save') {
        if (!isset($_POST['data_pendidikan'])) throw new Exception("Data tidak diterima");
        $data = json_decode($_POST['data_pendidikan'], true);
        
        // Ambil user login (Sesuai request: nama_pengguna)
        $current_user = isset($_SESSION['nama_user']) ? $_SESSION['nama_user'] : 'System';
        $created_by   = mysqli_real_escape_string($conn, $current_user);

        
        $berhasil = 0;
        $gagal = 0;

        foreach ($data as $row) {
            // Mapping 12 Kolom (Index 0 - 11)
            $id_peg = isset($row[0]) ? mysqli_real_escape_string($conn, $row[0]) : '';
            
            if(empty($id_peg)) continue;

            // Validasi ID Pegawai
            $cek_peg = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_peg'");
            if (mysqli_num_rows($cek_peg) == 0) { $gagal++; continue; }

            $id_sekolah     = isset($row[1]) ? mysqli_real_escape_string($conn, $row[1]) : '';
            $jenjang        = isset($row[2]) ? mysqli_real_escape_string($conn, $row[2]) : '';
            $nama_sekolah   = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
            $lokasi         = isset($row[4]) ? mysqli_real_escape_string($conn, $row[4]) : '';
            $jurusan        = isset($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : '';
            $th_masuk       = isset($row[6]) ? mysqli_real_escape_string($conn, $row[6]) : '';
            $th_lulus       = isset($row[7]) ? mysqli_real_escape_string($conn, $row[7]) : '';
            $no_ijazah      = isset($row[8]) ? mysqli_real_escape_string($conn, $row[8]) : '';
            $tgl_ijazah     = isset($row[9]) ? formatTanggal($row[9]) : NULL;
            $kepala         = isset($row[10]) ? mysqli_real_escape_string($conn, $row[10]) : '';
            $status         = isset($row[11]) ? mysqli_real_escape_string($conn, $row[11]) : '';

            $date_reg       = date('Y-m-d H:i:s'); // Waktu sekarang

            // Query Insert
            $query = "INSERT INTO tb_pendidikan (
                id_peg, id_sekolah, jenjang, nama_sekolah, lokasi, jurusan, 
                th_masuk, th_lulus, no_ijazah, tgl_ijazah, kepala, status, 
                created_by, date_reg
            ) VALUES (
                '$id_peg', '$id_sekolah', '$jenjang', '$nama_sekolah', '$lokasi', '$jurusan',
                '$th_masuk', '$th_lulus', '$no_ijazah', '$tgl_ijazah', '$kepala', '$status',
                '$created_by', '$date_reg'
            )";

            if (mysqli_query($conn, $query)) {
                $berhasil++;
            } else {
                $gagal++;
            }
        }

        ob_clean();
        echo json_encode(['status' => 'success', 'message' => "Selesai! Berhasil: $berhasil, Gagal: $gagal (User: $current_user)"]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}