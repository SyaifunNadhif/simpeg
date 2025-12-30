<?php
// =============================================================
// FILE: pages/ref-jabatan/upload-data-jabatan.php
// MODULE: Backend Import Jabatan (Check by ID & Nama Jabatan)
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

        // A. Cek Excel Serial Number
        if (is_numeric($val) && $val > 1000) {
            try {
                return Date::excelToDateTimeObject($val)->format('Y-m-d');
            } catch (Exception $e) { return NULL; }
        }

        // B. Cek Format Y-m-d
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $val;

        // C. Cek Format Indonesia (d-m-Y atau d/m/Y)
        $val = str_replace(['/', '.', ' '], '-', $val);
        $ts = strtotime($val);
        
        if ($ts !== false && $ts > 0) return date('Y-m-d', $ts);

        return NULL;
    }

    // --- FUNGSI UPDATE HISTORY OTOMATIS (LOGIC H-1) ---
    function perbaikiHistoryJabatan($conn, $id_peg) {
        // 1. Ambil semua jabatan pegawai ini, urutkan dari TMT terlama ke terbaru
        $q = mysqli_query($conn, "SELECT id_jab, tmt_jabatan FROM tb_jabatan WHERE id_peg='$id_peg' ORDER BY tmt_jabatan ASC");
        $data = [];
        while($r = mysqli_fetch_assoc($q)) {
            $data[] = $r;
        }

        $total = count($data);
        if ($total > 0) {
            // 2. Loop dari awal sampai sebelum terakhir
            for ($i = 0; $i < $total - 1; $i++) {
                $curr = $data[$i];
                $next = $data[$i+1];

                // Logic H-1: Sampai Tanggal = TMT Jabatan Berikutnya - 1 Hari
                $tgl_tutup = date('Y-m-d', strtotime('-1 day', strtotime($next['tmt_jabatan'])));
                
                // Update jabatan lama jadi Non dan set tanggal tutupnya
                mysqli_query($conn, "UPDATE tb_jabatan SET sampai_tgl='$tgl_tutup', status_jab='Non' WHERE id_jab='".$curr['id_jab']."'");
            }

            // 3. Pastikan jabatan PALING BARU (Terakhir) itu Kosong tanggal sampainya
            $last = $data[$total - 1];
            mysqli_query($conn, "UPDATE tb_jabatan SET sampai_tgl='0000-00-00' WHERE id_jab='".$last['id_jab']."'");
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
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) throw new Exception("Format file harus Excel (.xlsx/.xls)");

        $spreadsheet = IOFactory::load($file['tmp_name']);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        
        if (count($rows) <= 1) throw new Exception("File Excel kosong");
        $header = array_shift($rows); 

        // Grouping Data
        $groupedData = [];
        foreach ($rows as $row) {
            $id_peg = isset($row['A']) ? trim($row['A']) : '';
            if (empty($id_peg)) continue;

            $tgl_sk_raw = isset($row['F']) ? $row['F'] : '';
            $tgl_sk_fix = formatTanggal($tgl_sk_raw);
            $status_raw = isset($row['G']) ? trim($row['G']) : '';

            $groupedData[$id_peg][] = [
                'raw' => $row,
                'tgl_sk_fix' => $tgl_sk_fix,
                'tgl_timestamp' => $tgl_sk_fix ? strtotime($tgl_sk_fix) : 0,
                'status_raw' => $status_raw
            ];
        }

        // Generate HTML
        $html = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm text-nowrap" style="font-size:0.85em;">';
        $html .= '<thead class="bg-primary text-white"><tr><th>Status System</th><th>ID Pegawai</th><th>Kode Jabatan</th><th>Nama Jabatan</th><th>Unit Kerja</th><th>No SK</th><th>Tgl SK / TMT</th><th>Status Jab</th></tr></thead><tbody>';

        $count = 0; $limit = 15; // Limit preview
        $jsonArray = [];

        foreach ($groupedData as $id_peg => $items) {
            if ($count >= $limit) break;
            usort($items, 'compareJabatanDate'); 

            foreach ($items as $idx => $item) {
                $row = $item['raw'];
                $tgl_fix = $item['tgl_sk_fix'];
                
                // Logic Auto Status Preview
                if (empty($item['status_raw'])) {
                    $status_final = ($idx === 0) ? 'Aktif' : 'Non';
                    $status_badge = ($idx === 0) ? '<span class="badge bg-success">Auto: Aktif</span>' : '<span class="badge bg-secondary">Auto: Non</span>';
                } else {
                    $status_final = $item['status_raw'];
                    $status_badge = '<span class="badge bg-info">'.$status_final.'</span>';
                }

                $html .= '<tr>';
                $html .= '<td>' . $status_badge . '</td>';
                $html .= '<td>' . htmlspecialchars($id_peg) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['B']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['C']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['D']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['E']) . '</td>';
                $html .= '<td>' . ($tgl_fix ? $tgl_fix : '<span class="text-danger">Invalid</span>') . '</td>';
                $html .= '<td>' . htmlspecialchars($status_final) . '</td>';
                $html .= '</tr>';
                
                $jsonArray[] = [
                    $id_peg, $row['B'], $row['C'], $row['D'], $row['E'], $tgl_fix, $status_final
                ];
                $count++;
            }
        }
        $html .= '</tbody></table></div>';
        
        $total_rows = 0;
        foreach($groupedData as $g) $total_rows += count($g);

        $html .= '<div class="alert alert-info small mt-2">Menampilkan preview 15 dari '.$total_rows.' data. Data akan diproses dengan logika: <b>Update jika ID & Nama Jabatan Sama</b>.</div>';
        $html .= '<hr><div class="text-right"><button type="button" class="btn btn-success" id="btnSimpanJabatan"><i class="fas fa-save"></i> Proses Import</button></div>';
        
        // RE-LOOP untuk Full JSON
        $jsonFull = [];
        foreach ($groupedData as $id_peg => $items) {
             usort($items, 'compareJabatanDate');
             foreach ($items as $idx => $item) {
                 $row = $item['raw'];
                 $status_final = empty($item['status_raw']) ? (($idx === 0) ? 'Aktif' : 'Non') : $item['status_raw'];
                 $jsonFull[] = [
                    $id_peg, 
                    isset($row['B'])?$row['B']:'', 
                    isset($row['C'])?$row['C']:'', 
                    isset($row['D'])?$row['D']:'', 
                    isset($row['E'])?$row['E']:'', 
                    $item['tgl_sk_fix'], 
                    $status_final
                 ];
             }
        }

        $json_data = json_encode($jsonFull);
        kirimJson('success', '', $html . '<textarea id="json_data_jabatan" style="display:none;">' . $json_data . '</textarea>');
    }

    // ============================================================
    // ACTION: SAVE (SMART HISTORY & UPSERT BY ID+NAMA)
    // ============================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_jabatan'])) throw new Exception("Data JSON tidak diterima.");
        $data_raw = json_decode($_POST['data_jabatan'], true);
        if (!$data_raw) throw new Exception("Gagal decode JSON data. File mungkin terlalu besar.");

        $created_by = isset($_SESSION['nama_user']) ? mysqli_real_escape_string($conn, $_SESSION['nama_user']) : 'System';
        
        $berhasil = 0; $update = 0; $gagal = 0;
        $processed_pegawai = []; 

        foreach ($data_raw as $row) {
            $id_peg       = isset($row[0]) ? trim($row[0]) : '';
            $kode_jab_xls = isset($row[1]) ? trim($row[1]) : '';
            $nama_jab_xls = isset($row[2]) ? trim($row[2]) : '';
            
            // Variabel SQL Aman
            $unit_kerja   = getSqlVal($conn, isset($row[3]) ? $row[3] : '');
            $no_sk        = getSqlVal($conn, isset($row[4]) ? $row[4] : '');
            
            $tgl_sk       = isset($row[5]) ? $row[5] : NULL;
            $status_jab   = getSqlVal($conn, isset($row[6]) ? trim($row[6]) : 'Non');

            if (empty($id_peg) || empty($tgl_sk)) { $gagal++; continue; }

            // Simpan ID untuk post-processing
            $processed_pegawai[$id_peg] = true;

            // 1. Lookup Kode Jabatan (Logic Bisnis)
            $final_kode_raw = ""; $final_nama_raw = "";
            
            if (!empty($kode_jab_xls)) {
                $chk_kode = mysqli_real_escape_string($conn, $kode_jab_xls);
                $qCek = mysqli_query($conn, "SELECT nama_jabatan FROM tb_master_jabatan WHERE kode_jabatan = '$chk_kode'");
                if ($rCek = mysqli_fetch_assoc($qCek)) {
                    $final_kode_raw = $chk_kode;
                    $final_nama_raw = $rCek['nama_jabatan'];
                } else {
                    $final_kode_raw = $chk_kode;
                    $final_nama_raw = !empty($nama_jab_xls) ? $nama_jab_xls : "Unknown ($chk_kode)";
                }
            } elseif (!empty($nama_jab_xls)) {
                $chk_nama = mysqli_real_escape_string($conn, $nama_jab_xls);
                $qCek = mysqli_query($conn, "SELECT kode_jabatan FROM tb_master_jabatan WHERE nama_jabatan = '$chk_nama'");
                if ($rCek = mysqli_fetch_assoc($qCek)) {
                    $final_kode_raw = $rCek['kode_jabatan'];
                    $final_nama_raw = $nama_jab_xls;
                } else {
                    $gagal++; continue; // Skip jika master tidak ketemu
                }
            } else {
                $gagal++; continue;
            }

            // Bungkus hasil lookup dengan getSqlVal
            $sql_kode_jab = getSqlVal($conn, $final_kode_raw);
            $sql_nama_jab = getSqlVal($conn, $final_nama_raw);
            $sql_tgl_sk   = ($tgl_sk) ? "'$tgl_sk'" : "NULL";
            $sql_tmt      = $sql_tgl_sk; 

            // 2. LOGIC UPSERT (CHECK DUPLIKAT: ID + NAMA JABATAN)
            $v_id_peg = mysqli_real_escape_string($conn, $id_peg);
            
            // Perubahan Logic Disini: Cek jika ID Pegawai & Nama Jabatan sama persis
            // (Tidak peduli TMT atau No SK, kalau nama jabatan sama, kita update datanya)
            $qCekDuplikat = "SELECT id_jab FROM tb_jabatan WHERE id_peg='$v_id_peg' AND jabatan = $sql_nama_jab LIMIT 1";
            
            $cekAda = mysqli_query($conn, $qCekDuplikat);
            
            if (mysqli_num_rows($cekAda) > 0) {
                // --- UPDATE (Data Lama Ditimpa) ---
                $rowOld = mysqli_fetch_assoc($cekAda);
                $id_target = $rowOld['id_jab'];

                $query = "UPDATE tb_jabatan SET 
                            kode_jabatan = $sql_kode_jab,
                            jabatan      = $sql_nama_jab,
                            unit_kerja   = $unit_kerja,
                            no_sk        = $no_sk,
                            tgl_sk       = $sql_tgl_sk,
                            tmt_jabatan  = $sql_tmt,
                            status_jab   = $status_jab,
                            updated_at   = NOW(),
                            updated_by   = '$created_by'
                          WHERE id_jab   = '$id_target'";
                
                if (mysqli_query($conn, $query)) $update++; else $gagal++;

            } else {
                // --- INSERT (Data Baru) ---
                $query = "INSERT INTO tb_jabatan (
                    id_peg, kode_jabatan, jabatan, unit_kerja, no_sk, tgl_sk, tmt_jabatan, status_jab, created_by, date_reg
                ) VALUES (
                    '$v_id_peg', $sql_kode_jab, $sql_nama_jab, $unit_kerja, $no_sk, $sql_tgl_sk, $sql_tmt, $status_jab, '$created_by', NOW()
                )";

                if (mysqli_query($conn, $query)) $berhasil++; else $gagal++;
            }
        }

        // 3. STEP TERAKHIR: PERBAIKI HISTORY
        foreach (array_keys($processed_pegawai) as $id_peg_fix) {
            perbaikiHistoryJabatan($conn, $id_peg_fix);
        }

        kirimJson('success', "Proses Selesai!<br>Input Baru: <b>$berhasil</b><br>Update Data: <b>$update</b><br>Gagal: <b>$gagal</b>");
    }

} catch (Exception $e) {
    kirimJson('error', "<b>SYSTEM ERROR:</b> " . $e->getMessage());
}
?>