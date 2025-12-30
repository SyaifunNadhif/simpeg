<?php
// =============================================================
// FILE: pages/ref-pelanggaran/upload-data-hukuman.php
// LOGIC: Smart Upsert (ID_PEG + HUKUMAN + NO_SK)
// LINUX READY: Strict Mode OFF, NULL Handling, Resource Limit OFF
// =============================================================

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

session_start();

// [CONFIG] : Matikan batasan memory & waktu untuk data banyak
ini_set('memory_limit', '-1'); 
set_time_limit(0); 

// [CONFIG] : Matikan error display agar JSON tidak rusak
error_reporting(0);
ini_set('display_errors', 0);

while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

// --- HELPER SQL VALUE (PENTING BUAT LINUX) ---
function getSqlVal($conn, $val, $type = 'string') {
    if ($val === '' || $val === null || $val === false || $val === 'NULL') {
        return "NULL";
    }
    
    $safe = mysqli_real_escape_string($conn, $val);
    
    if ($type === 'int') {
        $num = preg_replace('/[^0-9]/', '', $val);
        return ($num === '') ? "NULL" : "'$num'";
    }
    
    return "'$safe'";
}

// --- HELPER FORMAT TANGGAL ---
function formatTanggal($val) {
    $val = trim($val);
    if (empty($val) || $val == '-' || $val == '' || $val == '0000-00-00') return NULL;
    
    if (is_numeric($val) && $val > 1000) {
        try { return Date::excelToDateTimeObject($val)->format('Y-m-d'); } catch (Exception $e) { return NULL; }
    }
    
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $val;
    
    $val = str_replace(['/', '.', ' '], '-', $val);
    $ts = strtotime($val);
    if ($ts !== false && $ts > 0) return date('Y-m-d', $ts);
    
    return NULL;
}

function kirimJson($status, $msg, $html = '') {
    ob_clean(); 
    echo json_encode(['status' => $status, 'message' => $msg, 'html' => $html]);
    exit;
}

try {
    // 1. KONEKSI
    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File Koneksi tidak ditemukan.");
    include $path_koneksi;

    // [BARIS SAKTI] : SOLUSI AGAR LINUX TIDAK ERROR KARENA DATA KOSONG
    mysqli_query($conn, "SET SESSION sql_mode = ''"); 

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid Request");
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // --- ACTION PREVIEW ---
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");
        
        $file = $_FILES['file_excel'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xls', 'xlsx'])) throw new Exception("Format harus Excel (.xlsx)");

        $spreadsheet = IOFactory::load($file['tmp_name']);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        if (count($rows) <= 1) throw new Exception("File Excel kosong");
        
        $header = array_shift($rows); // Hapus Header

        // HEADER TABLE PREVIEW
        $html = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm text-nowrap" style="font-size:0.85em;">';
        $html .= '<thead class="bg-danger text-white"><tr><th width="5%">Status</th>';
        $html .= '<th>ID Pegawai</th><th>Hukuman</th><th>Pejabat SK</th><th>Jabatan SK</th><th>No SK</th><th>Tgl SK</th><th>Dokumen</th><th>Ket</th>';
        $html .= '</tr></thead><tbody>';

        $limit = 10; $count = 0; $previewData = [];

        foreach ($rows as $row) {
            $id_peg = isset($row['A']) ? trim($row['A']) : '';
            if(empty($id_peg)) continue; 

            // Mapping Kolom Excel (A-H)
            $hukuman    = trim($row['B']);
            $pejabat_sk = trim($row['C']);
            $jabatan_sk = trim($row['D']); 
            $no_sk      = trim($row['E']);
            
            $tgl_sk_raw = $row['F'];
            $tgl_sk_db  = formatTanggal($tgl_sk_raw);

            $dokumen    = trim($row['G']); 
            $keterangan = trim($row['H']);

            // Cek Database (Kunci Unik: ID + Hukuman + No SK)
            $id_peg_esc = mysqli_real_escape_string($conn, $id_peg);
            $huk_esc    = mysqli_real_escape_string($conn, $hukuman);
            $no_sk_esc  = mysqli_real_escape_string($conn, $no_sk);

            $qCek = mysqli_query($conn, "SELECT id_hukum FROM tb_hukuman WHERE id_peg='$id_peg_esc' AND hukuman='$huk_esc' AND no_sk='$no_sk_esc'");
            
            $status_row = (mysqli_num_rows($qCek) > 0) 
                ? '<span class="badge bg-warning text-dark">Update</span>' 
                : '<span class="badge bg-success">Baru</span>';

            // Simpan ke Array Preview
            $previewData[] = [
                $id_peg, $hukuman, $pejabat_sk, $jabatan_sk, $no_sk, $tgl_sk_db, $dokumen, $keterangan
            ];

            if ($count < $limit) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . $status_row . '</td>';
                $html .= '<td>' . htmlspecialchars($id_peg) . '</td>';
                $html .= '<td>' . htmlspecialchars($hukuman) . '</td>';
                $html .= '<td>' . htmlspecialchars($pejabat_sk) . '</td>';
                $html .= '<td>' . htmlspecialchars($jabatan_sk) . '</td>';
                $html .= '<td>' . htmlspecialchars($no_sk) . '</td>';
                $html .= '<td>' . ($tgl_sk_db ? $tgl_sk_db : '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($dokumen) . '</td>';
                $html .= '<td>' . htmlspecialchars($keterangan) . '</td>';
                $html .= '</tr>';
                $count++;
            }
        }
        $html .= '</tbody></table></div>';
        
        if (count($previewData) > $limit) {
            $html .= '<div class="alert alert-info mt-2 small text-center">... menampilkan 10 dari ' . count($previewData) . ' data.</div>';
        }

        $html .= '<hr><div class="d-flex justify-content-between align-items-center">';
        $html .= '<small class="text-muted">Pastikan data sudah benar sebelum disimpan.</small>';
        $html .= '<button type="button" class="btn btn-danger" id="btnSimpanHukuman"><i class="fas fa-save"></i> Simpan Data</button>';
        $html .= '</div>';
        
        $json_data = json_encode($previewData);
        kirimJson('success', '', $html . '<textarea id="json_data_hukuman" style="display:none;">' . $json_data . '</textarea>');
    }

    // --- ACTION SAVE ---
    elseif ($action === 'save') {
        if (!isset($_POST['data_hukuman'])) throw new Exception("Data tidak diterima");
        
        $data = json_decode($_POST['data_hukuman'], true);
        if (!$data) throw new Exception("Format data invalid.");
        
        $berhasil = 0; $update = 0; $gagal = 0;

        foreach ($data as $row) {
            // Ambil Data dari JSON Array
            $id_peg_raw = isset($row[0]) ? trim($row[0]) : '';
            if(empty($id_peg_raw)) { $gagal++; continue; }
            
            $v_id_peg = mysqli_real_escape_string($conn, $id_peg_raw);

            // Cek Pegawai Exist
            $cek_peg = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$v_id_peg'");
            
            if (mysqli_num_rows($cek_peg) > 0) {
                // --- PREPARE DATA DENGAN getSqlVal (AMAN LINUX) ---
                $raw_hukuman    = isset($row[1]) ? trim($row[1]) : '';
                $raw_no_sk      = isset($row[4]) ? trim($row[4]) : '';

                $sql_hukuman    = getSqlVal($conn, $raw_hukuman);
                $sql_pejabat_sk = getSqlVal($conn, isset($row[2]) ? $row[2] : '');
                $sql_jabatan_sk = getSqlVal($conn, isset($row[3]) ? $row[3] : '');
                $sql_no_sk      = getSqlVal($conn, $raw_no_sk);
                
                $tgl_sk         = isset($row[5]) ? $row[5] : NULL;
                $sql_tgl_sk     = ($tgl_sk) ? "'$tgl_sk'" : "NULL";
                
                $sql_dokumen    = getSqlVal($conn, isset($row[6]) ? $row[6] : '');
                $sql_keterangan = getSqlVal($conn, isset($row[7]) ? $row[7] : '');

                // Cek Existing Data (Kunci Unik: ID + Hukuman + No SK)
                // Kita gunakan raw value yg di-escape manual untuk pencarian
                $safe_hukuman = mysqli_real_escape_string($conn, $raw_hukuman);
                $safe_no_sk   = mysqli_real_escape_string($conn, $raw_no_sk);

                $sqlCek = "SELECT id_hukum FROM tb_hukuman WHERE id_peg='$v_id_peg' AND hukuman='$safe_hukuman' AND no_sk='$safe_no_sk'";
                $resCek = mysqli_query($conn, $sqlCek);

                if (mysqli_num_rows($resCek) > 0) {
                    // UPDATE
                    $rOld = mysqli_fetch_assoc($resCek);
                    $id_target = $rOld['id_hukum'];
                    
                    $qry = "UPDATE tb_hukuman SET 
                            pejabat_sk = $sql_pejabat_sk, 
                            jabatan_sk = $sql_jabatan_sk, 
                            tgl_sk     = $sql_tgl_sk, 
                            dokumen    = $sql_dokumen,
                            keterangan = $sql_keterangan, 
                            date_reg   = NOW() 
                            WHERE id_hukum = '$id_target'";
                            
                    if (mysqli_query($conn, $qry)) $update++; else $gagal++;

                } else {
                    // INSERT
                    $qry = "INSERT INTO tb_hukuman (
                                id_peg, hukuman, pejabat_sk, jabatan_sk, no_sk, tgl_sk, dokumen, keterangan, date_reg
                            ) VALUES (
                                '$v_id_peg', $sql_hukuman, $sql_pejabat_sk, $sql_jabatan_sk, $sql_no_sk, $sql_tgl_sk, $sql_dokumen, $sql_keterangan, NOW()
                            )";
                            
                    if (mysqli_query($conn, $qry)) $berhasil++; else $gagal++;
                }
            } else {
                $gagal++; // Pegawai tidak ada
            }
        }
        kirimJson('success', "Import Selesai!<br>Input Baru: <b>$berhasil</b><br>Update Data: <b>$update</b><br>Gagal/Skip: <b>$gagal</b>");
    }

} catch (Exception $e) {
    kirimJson('error', "<b>SYSTEM ERROR:</b> " . $e->getMessage());
}
?>