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

// 2. FUNGSI FORMAT TANGGAL (WAJIB ADA DISINI!)
// Tanpa ini, proses Simpan akan ERROR FATAL.
function formatTanggal($date) {
    if (empty($date) || $date == '-' || $date == '') return NULL;
    
    // Cek jika formatnya sudah yyyy-mm-dd
    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
        return $date;
    }
    
    // Coba ubah dari d-m-Y atau d/m/Y
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

    // 4. CEK REQUEST
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

        // Pisahkan Header
        $header = array_shift($rows); 

        // Buat HTML Tabel
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm text-nowrap" style="font-size: 0.9em;">';
        $html .= '<thead class="bg-info"><tr>';
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
    // BAGIAN B: SIMPAN DATA
    // =========================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_pegawai'])) throw new Exception("Data tidak diterima");

        $data = json_decode($_POST['data_pegawai'], true);
        if (!$data) throw new Exception("Data korup");

        $berhasil = 0;
        $gagal = 0;
        $pesan_error = "";

        foreach ($data as $row) {
            // MAPPING DATA
            $id_peg = isset($row[0]) ? mysqli_real_escape_string($conn, $row[0]) : '';
            
            // Skip jika ID kosong
            if(empty($id_peg)) continue;

            $nip            = isset($row[1]) ? mysqli_real_escape_string($conn, $row[1]) : '';
            $nama           = isset($row[2]) ? mysqli_real_escape_string($conn, $row[2]) : '';
            $tempat_lhr     = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
            $tgl_lhr        = isset($row[4]) ? formatTanggal($row[4]) : NULL; // Panggil Fungsi
            $agama          = isset($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : '';
            $jk             = isset($row[6]) ? mysqli_real_escape_string($conn, $row[6]) : '';
            $gol_darah      = isset($row[7]) ? mysqli_real_escape_string($conn, $row[7]) : '';
            $status_nikah   = isset($row[8]) ? mysqli_real_escape_string($conn, $row[8]) : '';
            $status_kepeg   = isset($row[9]) ? mysqli_real_escape_string($conn, $row[9]) : '';
            $tgl_mulaikerja = isset($row[10]) ? formatTanggal($row[10]) : NULL; // Panggil Fungsi
            $alamat         = isset($row[11]) ? mysqli_real_escape_string($conn, $row[11]) : '';
            $telp           = isset($row[12]) ? mysqli_real_escape_string($conn, $row[12]) : '';
            $email          = isset($row[13]) ? mysqli_real_escape_string($conn, $row[13]) : '';
            $pangkat        = isset($row[14]) ? mysqli_real_escape_string($conn, $row[14]) : '';

            // Cek Duplikat ID Pegawai
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
                    // Simpan pesan error MySQL untuk debugging (opsional)
                    // $pesan_error = mysqli_error($conn);
                }
            } else {
                $gagal++; // Gagal karena duplikat
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