<?php
// =============================================================
// FILE: pages/ref-jabatan/upload-data-jabatan.php
// MODULE: Backend Import Jabatan (Upsert by ID Pegawai & No SK)
// =============================================================

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// [CONFIG] : Matikan batasan memory & waktu untuk data banyak
ini_set('memory_limit', '-1'); 
set_time_limit(0); 

// [CONFIG] : Matikan error display agar JSON tidak rusak
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_id() == '') session_start(); 

ob_start();
header('Content-Type: application/json; charset=utf-8');

// --- Helper Response ---
function kirimJson($status, $msg, $html = '') {
    ob_clean(); 
    echo json_encode(['status' => $status, 'message' => $msg, 'html' => $html]);
    exit;
}

// --- Helper SQL Value (PENTING BUAT LINUX) ---
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

try {
    // 1. KONEKSI DATABASE
    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File Koneksi tidak ditemukan.");
    include $path_koneksi;

    if (!$conn) throw new Exception("Koneksi database gagal.");

    // [BARIS SAKTI] : SOLUSI AGAR LINUX TIDAK ERROR KARENA DATA KOSONG
    mysqli_query($conn, "SET SESSION sql_mode = ''"); 

    // --- FUNGSI FORMAT TANGGAL CERDAS ---
    function formatTanggal($val) {
        $val = trim($val);
        if (empty($val) || $val == '-' || $val == '' || $val == '0000-00-00') return NULL;

        if (is_numeric($val) && $val > 1000) {
            try {
                return Date::excelToDateTimeObject($val)->format('Y-m-d');
            } catch (Exception $e) { return NULL; }
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $val;

        $val = str_replace(['/', '.', ' '], '-', $val);
        $ts = strtotime($val);
        if ($ts !== false && $ts > 0) return date('Y-m-d', $ts);

        return NULL;
    }

    // --- FUNGSI UPDATE HISTORY OTOMATIS (LOGIC H-1) ---
    function perbaikiHistoryJabatan($conn, $id_peg) {
        $q = mysqli_query($conn, "SELECT id_jab, tmt_jabatan FROM tb_jabatan WHERE id_peg='$id_peg' ORDER BY tmt_jabatan ASC");
        $data = [];
        while($r = mysqli_fetch_assoc($q)) {
            $data[] = $r;
        }

        $total = count($data);
        if ($total > 0) {
            for ($i = 0; $i < $total - 1; $i++) {
                $curr = $data[$i];
                $next = $data[$i+1];
                $tgl_tutup = date('Y-m-d', strtotime('-1 day', strtotime($next['tmt_jabatan'])));
                mysqli_query($conn, "UPDATE tb_jabatan SET sampai_tgl='$tgl_tutup', status_jab='Non' WHERE id_jab='".$curr['id_jab']."'");
            }

            $last = $data[$total - 1];
            mysqli_query($conn, "UPDATE tb_jabatan SET sampai_tgl='0000-00-00', status_jab='Aktif' WHERE id_jab='".$last['id_jab']."'");
        }
    }

    // --- FUNGSI SORTING PREVIEW ---
    function compareJabatanDate($a, $b) {
        $t1 = $a['tgl_timestamp'];
        $t2 = $b['tgl_timestamp'];
        if ($t1 == $t2) return 0;
        return ($t1 > $t2) ? -1 : 1; 
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid Request Method");
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // ============================================================
    // ACTION: PREVIEW
    // ============================================================
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");
        
        $file = $_FILES['file_excel'];
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        
        if (count($rows) <= 1) throw new Exception("File Excel kosong");
        array_shift($rows); 

        $groupedData = [];
        foreach ($rows as $row) {
            $id_peg = isset($row['A']) ? trim($row['A']) : '';
            if (empty($id_peg)) continue;

            $tgl_sk_fix = formatTanggal(isset($row['F']) ? $row['F'] : '');
            $groupedData[$id_peg][] = [
                'raw' => $row,
                'tgl_sk_fix' => $tgl_sk_fix,
                'tgl_timestamp' => $tgl_sk_fix ? strtotime($tgl_sk_fix) : 0
            ];
        }

        $html = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm text-nowrap" style="font-size:0.85em;">';
        $html .= '<thead class="bg-primary text-white"><tr><th>Status System</th><th>ID Pegawai</th><th>Kode Jab</th><th>Jabatan</th><th>Unit Kerja</th><th>No SK</th><th>TMT</th></tr></thead><tbody>';

        $jsonFull = [];
        foreach ($groupedData as $id_peg => $items) {
            usort($items, 'compareJabatanDate'); 
            foreach ($items as $idx => $item) {
                $row = $item['raw'];
                $status_final = ($idx === 0) ? 'Aktif' : 'Non';
                $badge = ($idx === 0) ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Non</span>';

                $html .= "<tr><td>$badge</td><td>$id_peg</td><td>{$row['B']}</td><td>{$row['C']}</td><td>{$row['D']}</td><td>{$row['E']}</td><td>{$item['tgl_sk_fix']}</td></tr>";
                
                $jsonFull[] = [
                    $id_peg, $row['B'], $row['C'], $row['D'], $row['E'], $item['tgl_sk_fix'], $status_final
                ];
            }
        }
        $html .= '</tbody></table></div>';
        $html .= '<hr><div class="text-right"><button type="button" class="btn btn-success" id="btnSimpanJabatan"><i class="fas fa-save"></i> Proses Import</button></div>';
        
        $json_data = json_encode($jsonFull);
        kirimJson('success', '', $html . '<textarea id="json_data_jabatan" style="display:none;">' . $json_data . '</textarea>');
    }

    // ============================================================
    // ACTION: SAVE (UPSERT BY ID PEGAWAI + NO SK)
    // ============================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_jabatan'])) throw new Exception("Data JSON tidak diterima.");
        $data_raw = json_decode($_POST['data_jabatan'], true);
        if (!$data_raw) throw new Exception("Gagal decode JSON.");

        $created_by = isset($_SESSION['nama_user']) ? mysqli_real_escape_string($conn, $_SESSION['nama_user']) : 'System';
        $berhasil = 0; $update = 0; $gagal = 0;
        $processed_pegawai = []; 

        foreach ($data_raw as $row) {
            $id_peg       = trim($row[0]);
            $kode_jab_xls = trim($row[1]);
            $nama_jab_xls = trim($row[2]);
            $unit_kerja   = getSqlVal($conn, $row[3]);
            $no_sk_raw    = trim($row[4]); // Kunci Update
            $tgl_sk       = $row[5];
            $status_jab   = getSqlVal($conn, $row[6]);

            if (empty($id_peg) || empty($tgl_sk)) { $gagal++; continue; }
            $processed_pegawai[$id_peg] = true;

            // Lookup Master Jabatan
            $f_kode = $kode_jab_xls; $f_nama = $nama_jab_xls;
            $chk_nama = mysqli_real_escape_string($conn, $nama_jab_xls);
            $qM = mysqli_query($conn, "SELECT kode_jabatan FROM tb_master_jabatan WHERE nama_jabatan = '$chk_nama' LIMIT 1");
            if ($rM = mysqli_fetch_assoc($qM)) $f_kode = $rM['kode_jabatan'];

            $sql_kode = getSqlVal($conn, $f_kode);
            $sql_nama = getSqlVal($conn, $f_nama);
            $sql_tgl  = "'$tgl_sk'";

            // LOGIC UPSERT: ID Pegawai & No SK
            $v_id_peg = mysqli_real_escape_string($conn, $id_peg);
            $v_no_sk  = mysqli_real_escape_string($conn, $no_sk_raw);
            
            $cek = mysqli_query($conn, "SELECT id_jab FROM tb_jabatan WHERE id_peg='$v_id_peg' AND no_sk='$v_no_sk' LIMIT 1");
            
            if (mysqli_num_rows($cek) > 0) {
                $rowOld = mysqli_fetch_assoc($cek);
                $query = "UPDATE tb_jabatan SET 
                            kode_jabatan=$sql_kode, jabatan=$sql_nama, unit_kerja=$unit_kerja, 
                            tgl_sk=$sql_tgl, tmt_jabatan=$sql_tgl, updated_at=NOW(), updated_by='$created_by'
                          WHERE id_jab='{$rowOld['id_jab']}'";
                if (mysqli_query($conn, $query)) $update++; else $gagal++;
            } else {
                $query = "INSERT INTO tb_jabatan (id_peg, kode_jabatan, jabatan, unit_kerja, no_sk, tgl_sk, tmt_jabatan, status_jab, created_by, date_reg) 
                          VALUES ('$v_id_peg', $sql_kode, $sql_nama, $unit_kerja, '$v_no_sk', $sql_tgl, $sql_tgl, $status_jab, '$created_by', NOW())";
                if (mysqli_query($conn, $query)) $berhasil++; else $gagal++;
            }
        }

        foreach (array_keys($processed_pegawai) as $id_p) perbaikiHistoryJabatan($conn, $id_p);

        kirimJson('success', "Selesai!<br>Baru: $berhasil<br>Update (SK Sama): $update<br>Gagal: $gagal");
    }

} catch (Exception $e) {
    kirimJson('error', $e->getMessage());
}
?>