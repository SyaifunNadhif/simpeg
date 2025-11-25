<?php
// =============================================================
// FILE: pages/pegawai/upload-data-pegawai.php
// =============================================================

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// 1. SETTING AGAR JSON BERSIH
error_reporting(0);
ini_set('display_errors', 0);

// Mulai buffer output
ob_start();
header('Content-Type: application/json');

// --- FUNGSI BANTUAN ---

// A. Format Tanggal (Excel -> MySQL)
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

// B. Generator UUID (Khusus PHP 5.6 compatible)
function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

try {
    // 2. KONEKSI DATABASE
    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File koneksi tidak ditemukan");
    include $path_koneksi;

    // 3. CEK REQUEST
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

        // Tampilan Tabel HTML
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm text-nowrap" style="font-size: 0.9em;">';
        $html .= '<thead class="bg-info"><tr>';
        
        // Tambahan Header Info (Optional)
        $html .= '<th>Status ID</th>'; // Kolom tambahan buat info
        foreach ($header as $col) {
            $html .= '<th>' . htmlspecialchars($col) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        $limit = 10;
        $count = 0;
        foreach ($rows as $row) {
            if ($count >= $limit) break;
            
            // Cek ID di kolom pertama
            $id_preview = isset($row[0]) ? $row[0] : '';
            $status_id = empty($id_preview) ? '<span class="badge badge-warning">Auto Generate</span>' : '<span class="badge badge-success">Excel</span>';

            $html .= '<tr>';
            $html .= '<td>' . $status_id . '</td>'; // Info status ID
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
            $count++;
        }
        $html .= '</tbody></table></div>';
        
        if (count($rows) > $limit) {
            $html .= '<div class="alert alert-warning text-center p-1"><small>... menampilkan 10 dari ' . count($rows) . ' data.</small></div>';
        }

        $html .= '<hr>';
        $html .= '<div class="text-right">';
        $html .= '<button type="button" class="btn btn-success" id="btnSimpanKolektif"><i class="fas fa-save"></i> Simpan Semua ke Database</button>';
        $html .= '</div>';
        
        $json_rows = json_encode($rows);
        $html .= '<textarea id="json_data_pegawai" style="display:none;">' . $json_rows . '</textarea>';

        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // =========================================================
    // BAGIAN B: SIMPAN DATA (Support UUID)
    // =========================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_pegawai'])) throw new Exception("Data tidak diterima");

        $data = json_decode($_POST['data_pegawai'], true);
        if (!$data) throw new Exception("Data korup");

        $berhasil = 0;
        $gagal = 0;

        foreach ($data as $row) {
            // --- LOGIKA ID PEGAWAI (UUID) ---
            
            // Ambil dari Excel Kolom A
            $id_peg_excel = isset($row[0]) ? trim($row[0]) : '';

            if (!empty($id_peg_excel)) {
                // Skenario A: Excel sudah bawa ID (misal: dbb6c0b7-...)
                $id_peg = mysqli_real_escape_string($conn, $id_peg_excel);
            } else {
                // Skenario B: Excel kosong, kita buatkan UUID baru
                $id_peg = gen_uuid();
            }

            // --- MAPPING DATA LAIN ---
            $nip            = isset($row[1]) ? mysqli_real_escape_string($conn, $row[1]) : '';
            $nama           = isset($row[2]) ? mysqli_real_escape_string($conn, $row[2]) : '';
            $tempat_lhr     = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
            $tgl_lhr        = isset($row[4]) ? formatTanggal($row[4]) : NULL;
            $agama          = isset($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : '';
            $jk             = isset($row[6]) ? mysqli_real_escape_string($conn, $row[6]) : '';
            $gol_darah      = isset($row[7]) ? mysqli_real_escape_string($conn, $row[7]) : '';
            $status_nikah   = isset($row[8]) ? mysqli_real_escape_string($conn, $row[8]) : '';
            $status_kepeg   = isset($row[9]) ? mysqli_real_escape_string($conn, $row[9]) : '';
            $tgl_mulaikerja = isset($row[10]) ? formatTanggal($row[10]) : NULL;
            $alamat         = isset($row[11]) ? mysqli_real_escape_string($conn, $row[11]) : '';
            $telp           = isset($row[12]) ? mysqli_real_escape_string($conn, $row[12]) : '';
            $email          = isset($row[13]) ? mysqli_real_escape_string($conn, $row[13]) : '';
            $pangkat        = isset($row[14]) ? mysqli_real_escape_string($conn, $row[14]) : '';

            // Cek Duplikat ID (Penting!)
            $cek = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai_fix WHERE id_peg = '$id_peg'");
            
            if (mysqli_num_rows($cek) == 0) {
                // QUERY INSERT
                $query = "INSERT INTO tb_pegawai_fix (
                    id_peg, nip, nama, tempat_lhr, tgl_lhr, agama, jk, gol_darah,
                    status_nikah, status_kepeg, tgl_mulaikerja, alamat, telp, email, pangkat
                ) VALUES (
                    '$id_peg', '$nip', '$nama', '$tempat_lhr', '$tgl_lhr', '$agama', '$jk', '$gol_darah',
                    '$status_nikah', '$status_kepeg', '$tgl_mulaikerja', '$alamat', '$telp', '$email', '$pangkat'
                )";

                if (mysqli_query($conn, $query)) {
                    $berhasil++;
                } else {
                    $gagal++;
                }
            } else {
                $gagal++; // Gagal Duplikat
            }
        }

        ob_clean();
        echo json_encode([
            'status' => 'success', 
            'message' => "Proses Selesai! Data Masuk: $berhasil, Gagal/Duplikat: $gagal"
        ]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}