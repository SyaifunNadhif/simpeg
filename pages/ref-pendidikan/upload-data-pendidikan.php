<?php
// =============================================================
// FILE: pages/ref-pendidikan/upload-data-pendidikan.php
// LOGIC: Smart Upsert (ID_PEG + JENJANG + NAMA_SEKOLAH)
// =============================================================

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// 1. SETUP ENVIRONMENT
session_start();
error_reporting(0); // Matikan error warning PHP agar JSON bersih
ini_set('display_errors', 0);

// Buffer & Header JSON
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

// --- HELPER: FORMAT TANGGAL CERDAS (Sama seperti Sertifikasi) ---
function formatTanggal($val) {
    $val = trim($val);
    if (empty($val) || $val == '-' || $val == '' || $val == '0000-00-00') return NULL;

    // A. Cek Excel Serial Number (Angka)
    if (is_numeric($val) && $val > 1000) {
        try {
            return Date::excelToDateTimeObject($val)->format('Y-m-d');
        } catch (Exception $e) { return NULL; }
    }

    // B. Cek Format Y-m-d (Database Standard)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
        return $val;
    }

    // C. Cek Format d/m/Y atau d-m-Y (Indonesia)
    // Ubah separator / atau . menjadi -
    $val = str_replace(['/', '.', ' '], '-', $val);
    $ts = strtotime($val);
    
    if ($ts !== false && $ts > 0) {
        return date('Y-m-d', $ts);
    }

    return NULL; // Gagal parsing
}

// --- HELPER: KIRIM JSON ---
function kirimJson($status, $msg, $html = '') {
    ob_clean(); 
    echo json_encode(['status' => $status, 'message' => $msg, 'html' => $html]);
    exit;
}

try {
    // 2. KONEKSI DATABASE
    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File Koneksi tidak ditemukan.");
    include $path_koneksi;

    if (!$conn) throw new Exception("Koneksi database gagal.");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid Request Method");
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // ============================================================
    // ACTION: PREVIEW (TAMPILKAN DATA EXCEL)
    // ============================================================
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");
        
        $file = $_FILES['file_excel'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xls', 'xlsx'])) throw new Exception("Format harus Excel (.xlsx)");

        $spreadsheet = IOFactory::load($file['tmp_name']);
        
        // Load RAW Data (true, true, true, true) agar index A,B,C terbaca
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        if (count($rows) <= 1) throw new Exception("File Excel kosong");
        
        // Ambil Header & Hapus dari array data
        $header = array_shift($rows); 

        // Buat HTML Table
        $html = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm text-nowrap" style="font-size:0.85em;">';
        $html .= '<thead class="bg-primary text-white"><tr><th width="10%">Status</th>';
        
        // Mapping Header Manual biar rapi (Opsional, atau pakai loop header excel)
        $html .= '<th>ID Pegawai</th><th>Jenjang</th><th>Nama Sekolah</th><th>Lokasi</th><th>Jurusan</th><th>No Ijazah</th><th>Tgl Ijazah</th>';
        $html .= '</tr></thead><tbody>';

        $limit = 10; $count = 0;
        $previewData = [];

        foreach ($rows as $row) {
            $id_peg = isset($row['A']) ? trim($row['A']) : '';
            if(empty($id_peg)) continue; 

            // Ambil Data Kolom
            $id_sekolah   = trim($row['B']); // Jika ada ID referensi
            $jenjang      = trim($row['C']);
            $nama_sekolah = trim($row['D']);
            $lokasi       = trim($row['E']);
            $jurusan      = trim($row['F']);
            $no_ijazah    = trim($row['G']);
            
            // Format Tanggal di Preview
            $tgl_ijazah_raw = $row['H'];
            $tgl_ijazah_db  = formatTanggal($tgl_ijazah_raw);

            $kepala       = trim($row['I']);
            $status       = trim($row['J']);
            $th_masuk     = trim($row['K']);
            $th_lulus     = trim($row['L']);

            // --- CEK STATUS DATA DI DB ---
            $id_peg_esc = mysqli_real_escape_string($conn, $id_peg);
            $jenjang_esc = mysqli_real_escape_string($conn, $jenjang);
            $sekolah_esc = mysqli_real_escape_string($conn, $nama_sekolah);

            $qCek = mysqli_query($conn, "SELECT id_pendidikan FROM tb_pendidikan WHERE id_peg='$id_peg_esc' AND jenjang='$jenjang_esc' AND nama_sekolah='$sekolah_esc'");
            
            if (mysqli_num_rows($qCek) > 0) {
                $status_row = '<span class="badge bg-warning text-dark">Update</span>';
            } else {
                $status_row = '<span class="badge bg-success">Baru</span>';
            }

            // Simpan Array Bersih
            $previewData[] = [
                $id_peg, $id_sekolah, $jenjang, $nama_sekolah, $lokasi, $jurusan, 
                $no_ijazah, $tgl_ijazah_db, $kepala, $status, $th_masuk, $th_lulus
            ];

            // Tampilkan ke Tabel HTML (Limit 10)
            if ($count < $limit) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . $status_row . '</td>';
                $html .= '<td>' . htmlspecialchars($id_peg) . '</td>';
                $html .= '<td>' . htmlspecialchars($jenjang) . '</td>';
                $html .= '<td>' . htmlspecialchars($nama_sekolah) . '</td>';
                $html .= '<td>' . htmlspecialchars($lokasi) . '</td>';
                $html .= '<td>' . htmlspecialchars($jurusan) . '</td>';
                $html .= '<td>' . htmlspecialchars($no_ijazah) . '</td>';
                $html .= '<td>' . ($tgl_ijazah_db ? $tgl_ijazah_db : '-') . '</td>';
                $html .= '</tr>';
                $count++;
            }
        }
        $html .= '</tbody></table></div>';
        
        if (count($previewData) > $limit) {
            $html .= '<div class="alert alert-info mt-2 small text-center">... menampilkan 10 dari ' . count($previewData) . ' data.</div>';
        }

        $html .= '<hr><div class="d-flex justify-content-between align-items-center">';
        $html .= '<small class="text-muted">Pastikan kolom status sesuai harapan.</small>';
        $html .= '<button type="button" class="btn btn-success" id="btnSimpanPendidikan"><i class="fas fa-save"></i> Simpan Data</button>';
        $html .= '</div>';
        
        $json_data = json_encode($previewData);
        kirimJson('success', '', $html . '<textarea id="json_data_pendidikan" style="display:none;">' . $json_data . '</textarea>');
    }

    // ============================================================
    // ACTION: SAVE (UPSERT LOGIC)
    // ============================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_pendidikan'])) throw new Exception("Data tidak diterima");
        
        $data = json_decode($_POST['data_pendidikan'], true);
        if (!$data) throw new Exception("Format data tidak valid.");
        
        $created_by = isset($_SESSION['nama_user']) ? mysqli_real_escape_string($conn, $_SESSION['nama_user']) : 'System';
        $berhasil = 0; $update = 0; $gagal = 0;

        foreach ($data as $row) {
            // Mapping Data (Sesuai urutan di PreviewData[])
            $id_peg       = isset($row[0]) ? mysqli_real_escape_string($conn, trim($row[0])) : '';
            if(empty($id_peg)) { $gagal++; continue; }

            // Cek apakah pegawai ada di database?
            $cek_peg = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_peg'");
            if (mysqli_num_rows($cek_peg) > 0) {

                $id_sekolah   = isset($row[1]) ? mysqli_real_escape_string($conn, $row[1]) : '';
                $jenjang      = isset($row[2]) ? mysqli_real_escape_string($conn, $row[2]) : '';
                $nama_sekolah = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
                $lokasi       = isset($row[4]) ? mysqli_real_escape_string($conn, $row[4]) : '';
                $jurusan      = isset($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : '';
                $no_ijazah    = isset($row[6]) ? mysqli_real_escape_string($conn, $row[6]) : '';
                
                // Tanggal sudah diformat di tahap Preview, jadi tinggal pakai
                $tgl_ijazah   = isset($row[7]) ? $row[7] : NULL;
                $val_tgl      = ($tgl_ijazah) ? "'$tgl_ijazah'" : "NULL";

                $kepala       = isset($row[8]) ? mysqli_real_escape_string($conn, $row[8]) : '';
                $status       = isset($row[9]) ? mysqli_real_escape_string($conn, $row[9]) : '';
                $th_masuk     = isset($row[10]) ? mysqli_real_escape_string($conn, $row[10]) : '';
                $th_lulus     = isset($row[11]) ? mysqli_real_escape_string($conn, $row[11]) : '';

                // LOGIKA UTAMA: Cek ID Peg + Jenjang + Nama Sekolah
                $sqlCek = "SELECT id_pendidikan FROM tb_pendidikan 
                           WHERE id_peg='$id_peg' AND jenjang='$jenjang' AND nama_sekolah='$nama_sekolah'";
                
                $resCek = mysqli_query($conn, $sqlCek);

                if (mysqli_num_rows($resCek) > 0) {
                    // --- UPDATE (Jika Data Sudah Ada) ---
                    $rowOld = mysqli_fetch_assoc($resCek);
                    $id_target = $rowOld['id_pendidikan'];

                    $qry = "UPDATE tb_pendidikan SET 
                            id_sekolah='$id_sekolah', 
                            lokasi='$lokasi', 
                            jurusan='$jurusan',
                            no_ijazah='$no_ijazah', 
                            tgl_ijazah=$val_tgl, 
                            kepala='$kepala', 
                            status='$status',
                            th_masuk='$th_masuk', 
                            th_lulus='$th_lulus', 
                            updated_at=NOW(), 
                            updated_by='$created_by'
                            WHERE id_pendidikan='$id_target'";
                    
                    if (mysqli_query($conn, $qry)) $update++; else $gagal++;

                } else {
                    // --- INSERT (Jika Data Baru) ---
                    $qry = "INSERT INTO tb_pendidikan (
                            id_peg, id_sekolah, jenjang, nama_sekolah, lokasi, jurusan, 
                            no_ijazah, tgl_ijazah, kepala, status, th_masuk, th_lulus, 
                            created_by, date_reg
                        ) VALUES (
                            '$id_peg', '$id_sekolah', '$jenjang', '$nama_sekolah', '$lokasi', '$jurusan',
                            '$no_ijazah', $val_tgl, '$kepala', '$status', '$th_masuk', '$th_lulus',
                            '$created_by', NOW()
                        )";
                    
                    if (mysqli_query($conn, $qry)) $berhasil++; else $gagal++;
                }

            } else {
                $gagal++; // Pegawai tidak ditemukan
            }
        }

        kirimJson('success', "Import Selesai!<br>Input Baru: <b>$berhasil</b><br>Update Data: <b>$update</b><br>Gagal/Skip: <b>$gagal</b>");
    }

} catch (Exception $e) {
    kirimJson('error', "<b>SYSTEM ERROR:</b> " . $e->getMessage());
}
?>