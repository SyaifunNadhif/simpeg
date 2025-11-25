<?php
// =============================================================
// 1. NAMESPACE WAJIB DITARUH PALING ATAS (Global Scope)
// =============================================================
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// 2. SETTING AGAR JSON TIDAK RUSAK
// Matikan error reporting agar warning PHP tidak muncul di output
error_reporting(0);
ini_set('display_errors', 0);

// Mulai menahan output
ob_start();

// Set Header JSON
header('Content-Type: application/json');

try {
    // 3. LOAD KONEKSI DATABASE
    // Pastikan path ini benar sesuai struktur folder kamu
    $path_koneksi = '../../dist/koneksi.php';
    if (!file_exists($path_koneksi)) {
        throw new Exception("File koneksi tidak ditemukan di: " . $path_koneksi);
    }
    include $path_koneksi;

    // 4. CEK REQUEST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid Request Method");
    }

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // =========================================================
    // BAGIAN A: PREVIEW DATA (Baca Excel)
    // =========================================================
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");

        $file = $_FILES['file_excel'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Validasi Ekstensi
        if (!in_array(strtolower($ext), ['xls', 'xlsx'])) {
            throw new Exception("Format file harus Excel (.xlsx / .xls)");
        }

        // Proses Baca Excel
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) <= 1) throw new Exception("File Excel kosong atau hanya berisi header");

        // Ambil Baris Pertama sebagai Header Tabel
        $header = array_shift($rows);

        // Buat Tampilan Tabel HTML
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm">';
        $html .= '<thead class="bg-info"><tr>';
        foreach ($header as $col) {
            $html .= '<th>' . htmlspecialchars($col) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        // Batasi Preview (Misal cuma tampilkan 10 baris agar ringan)
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
            $html .= '<div class="alert alert-warning text-center p-1"><small>... dan ' . (count($rows) - $limit) . ' data lainnya.</small></div>';
        }

        // Tombol Simpan & Data Hidden
        $html .= '<hr>';
        $html .= '<div class="text-right">';
        $html .= '<button type="button" class="btn btn-success" id="btnSimpanKolektif"><i class="fas fa-save"></i> Simpan Semua ke Database</button>';
        $html .= '</div>';
        
        // PENTING: Simpan data Excel mentah ke dalam Textarea tersembunyi (JSON String)
        $json_rows = json_encode($rows);
        $html .= '<textarea id="json_data_pegawai" style="display:none;">' . $json_rows . '</textarea>';

        // Bersihkan output sebelum kirim JSON
        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
    }

    // =========================================================
    // BAGIAN B: SIMPAN KE DATABASE (Insert)
    // =========================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_pegawai'])) throw new Exception("Data pegawai tidak diterima");

        // Decode JSON kembali ke Array PHP
        $data = json_decode($_POST['data_pegawai'], true);
        if (!$data) throw new Exception("Format data korup/kosong");

        $berhasil = 0;
        $gagal = 0;

        // Looping Insert
        foreach ($data as $row) {
            // -----------------------------------------------------
            // SESUAIKAN INDEX ARRAY DENGAN KOLOM EXCEL KAMU
            // $row[0] = Kolom A (Misal NIP)
            // $row[1] = Kolom B (Misal Nama)
            // $row[2] = Kolom C (Misal Jabatan)
            // -----------------------------------------------------
            
            $nip     = isset($row[0]) ? mysqli_real_escape_string($conn, $row[0]) : '';
            $nama    = isset($row[1]) ? mysqli_real_escape_string($conn, $row[1]) : '';
            $jabatan = isset($row[2]) ? mysqli_real_escape_string($conn, $row[2]) : '';

            // Pastikan NIP tidak kosong sebelum insert
            if (!empty($nip)) {
                // SESUAIKAN NAMA TABEL DAN KOLOM DATABASE DISINI
                $query = "INSERT INTO tb_pegawai (nip, nama, jabatan) VALUES ('$nip', '$nama', '$jabatan')";
                
                if (mysqli_query($conn, $query)) {
                    $berhasil++;
                } else {
                    $gagal++;
                }
            }
        }

        ob_clean();
        echo json_encode([
            'status' => 'success', 
            'message' => "Proses Selesai! Data Berhasil: $berhasil, Gagal: $gagal"
        ]);
    }

} catch (Exception $e) {
    // Tangkap error apapun dan kirim sebagai JSON
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>