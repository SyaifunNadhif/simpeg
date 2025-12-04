<?php
// =============================================================
// FILE: pages/ref-diklat/upload-data-diklat.php
// STATUS: MODE BEBAS (DUPLIKAT DIIZINKAN)
// =============================================================

session_start();

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(0);
ini_set('display_errors', 0);
ob_start();
header('Content-Type: application/json');

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
    // 1. Koneksi Database
    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File koneksi tidak ditemukan");
    include $path_koneksi;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid Request");
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // =========================================================
    // ACTION: PREVIEW
    // =========================================================
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");
        
        $file = $_FILES['file_excel'];
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        if (count($rows) <= 1) throw new Exception("File Excel kosong");
        $header = array_shift($rows); 

        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm text-nowrap" style="font-size: 0.9em;">';
        $html .= '<thead class="bg-primary"><tr>';
        foreach ($header as $col) $html .= '<th>' . htmlspecialchars($col) . '</th>';
        $html .= '</tr></thead><tbody>';

        $limit = 10; $count = 0;
        foreach ($rows as $row) {
            if ($count >= $limit) break;
            $html .= '<tr>';
            foreach ($row as $cell) {
                if ($count >= 0 && array_search($cell, $row) === 4 && is_numeric($cell)) {
                     $html .= '<td>Rp ' . number_format((float)$cell, 0, ',', '.') . '</td>';
                } else {
                     $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
            }
            $html .= '</tr>';
            $count++;
        }
        $html .= '</tbody></table></div>';
        
        if (count($rows) > $limit) $html .= '<div class="text-center small text-muted p-1">... menampilkan 10 data awal.</div>';

        $html .= '<hr><div class="text-right">';
        $html .= '<button type="button" class="btn btn-success" id="btnSimpanDiklat"><i class="fas fa-save"></i> Simpan Diklat</button>';
        $html .= '</div>';
        
        $json_rows = json_encode($rows);
        $html .= '<textarea id="json_data_diklat" style="display:none;">' . $json_rows . '</textarea>';

        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // =========================================================
    // ACTION: SAVE (TANPA CEK DUPLIKAT - LANGSUNG GAS)
    // =========================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_diklat'])) throw new Exception("Data tidak diterima");
        $data = json_decode($_POST['data_diklat'], true);
        
        // Ambil User Login
        $current_user = isset($_SESSION['nama_user']) ? $_SESSION['nama_user'] : 'System';
        $created_by   = mysqli_real_escape_string($conn, $current_user);

        $berhasil = 0;
        $gagal = 0;

        foreach ($data as $row) {
            // [0] ID Pegawai
            $id_peg = isset($row[0]) ? mysqli_real_escape_string($conn, trim($row[0])) : '';
            if(empty($id_peg)) continue;

            // 1. Cek Validitas ID Pegawai (Ini Tetap Wajib biar gak Error Database)
            $cek_peg = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_peg'");
            if (mysqli_num_rows($cek_peg) == 0) { 
                $gagal++; // ID tidak ada di master
                continue; 
            }

            // Mapping Data
            $diklat         = isset($row[1]) ? mysqli_real_escape_string($conn, $row[1]) : '';
            $penyelenggara  = isset($row[2]) ? mysqli_real_escape_string($conn, $row[2]) : '';
            $tempat         = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
            
            // Biaya
            $biaya_raw      = isset($row[4]) ? $row[4] : '0';
            $biaya          = preg_replace('/[^0-9]/', '', $biaya_raw);

            $angkatan       = isset($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : '';
            $tahun          = isset($row[6]) ? mysqli_real_escape_string($conn, $row[6]) : '';
            $date_reg       = isset($row[7]) ? formatTanggal($row[7]) : NULL;

            // 2. LANGSUNG INSERT (Tanpa Cek Duplikat)
            $query = "INSERT INTO tb_diklat (
                id_peg, diklat, penyelenggara, tempat, biaya, angkatan, tahun, date_reg, created_by
            ) VALUES (
                '$id_peg', '$diklat', '$penyelenggara', '$tempat', '$biaya', '$angkatan', '$tahun', '$date_reg', '$created_by'
            )";

            if (mysqli_query($conn, $query)) {
                $berhasil++;
            } else {
                $gagal++;
            }
        }

        ob_clean();
        echo json_encode(['status' => 'success', 'message' => "Selesai! Berhasil Masuk: $berhasil, Gagal: $gagal"]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}