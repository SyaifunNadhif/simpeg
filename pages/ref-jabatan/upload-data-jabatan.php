<?php
// =============================================================
// FILE: pages/ref-jabatan/upload-data-jabatan.php
// MODULE: Backend Import Jabatan (Fix Date Reg & Smart History)
// =============================================================

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

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

try {
    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File Koneksi tidak ditemukan.");
    include $path_koneksi;

    if (!$conn) throw new Exception("Koneksi database gagal.");

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
            
            // Opsional: Set status Aktif untuk yang paling baru (jika diinginkan)
            // mysqli_query($conn, "UPDATE tb_jabatan SET status_jab='Aktif' WHERE id_jab='".$last['id_jab']."'");
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

        $html = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm text-nowrap" style="font-size:0.85em;">';
        $html .= '<thead class="bg-primary text-white"><tr><th>Status System</th><th>ID Pegawai</th><th>Kode Jabatan</th><th>Nama Jabatan</th><th>Unit Kerja</th><th>No SK</th><th>Tgl SK / TMT</th><th>Status Jab</th></tr></thead><tbody>';

        $count = 0; $limit = 10;
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

                // Cek DB untuk Badge Update/Baru
                $id_peg_esc = mysqli_real_escape_string($conn, $id_peg);
                $no_sk_esc  = mysqli_real_escape_string($conn, isset($row['E']) ? $row['E'] : '');
                $tmt_esc    = $tgl_fix; 

                $qCek = mysqli_query($conn, "SELECT id_jab FROM tb_jabatan WHERE id_peg='$id_peg_esc' AND no_sk='$no_sk_esc' AND tmt_jabatan='$tmt_esc'");
                $upsert_badge = (mysqli_num_rows($qCek) > 0) ? '<span class="badge bg-warning text-dark">Update</span>' : '<span class="badge bg-primary">Baru</span>';

                $html .= '<tr>';
                $html .= '<td>' . $upsert_badge . ' ' . $status_badge . '</td>';
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
        $html .= '<div class="alert alert-info small mt-2">Menampilkan max 50 data. Data akan diproses dengan logika: <b>Update jika ada, Insert jika baru</b> + <b>Auto Set Tanggal Akhir (H-1)</b>.</div>';
        $html .= '<hr><div class="text-right"><button type="button" class="btn btn-success" id="btnSimpanJabatan"><i class="fas fa-save"></i> Proses Import</button></div>';
        
        $json_data = json_encode($jsonArray);
        kirimJson('success', '', $html . '<textarea id="json_data_jabatan" style="display:none;">' . $json_data . '</textarea>');
    }

    // ============================================================
    // ACTION: SAVE (SMART HISTORY & UPSERT)
    // ============================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_jabatan'])) throw new Exception("Data JSON tidak diterima.");
        $data_raw = json_decode($_POST['data_jabatan'], true);
        if (!$data_raw) throw new Exception("Gagal decode JSON data.");

        $created_by = isset($_SESSION['nama_user']) ? mysqli_real_escape_string($conn, $_SESSION['nama_user']) : 'System';
        
        $berhasil = 0; $update = 0; $gagal = 0;
        $processed_pegawai = []; // Untuk list pegawai yang akan dirapikan history-nya

        foreach ($data_raw as $row) {
            $id_peg       = isset($row[0]) ? trim($row[0]) : '';
            $kode_jab_xls = isset($row[1]) ? trim($row[1]) : '';
            $nama_jab_xls = isset($row[2]) ? trim($row[2]) : '';
            $unit_kerja   = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
            $no_sk        = isset($row[4]) ? mysqli_real_escape_string($conn, $row[4]) : '';
            $tgl_sk       = isset($row[5]) ? $row[5] : NULL;
            $status_jab   = isset($row[6]) ? trim($row[6]) : 'Non';

            if (empty($id_peg) || empty($tgl_sk)) { $gagal++; continue; }

            // Simpan ID Pegawai untuk proses perapian history nanti
            $processed_pegawai[$id_peg] = true;

            // 1. Lookup Kode Jabatan
            $final_kode = ""; $final_nama = "";
            if (!empty($kode_jab_xls)) {
                $final_kode = mysqli_real_escape_string($conn, $kode_jab_xls);
                $qCek = mysqli_query($conn, "SELECT nama_jabatan FROM tb_master_jabatan WHERE kode_jabatan = '$final_kode'");
                if ($rCek = mysqli_fetch_assoc($qCek)) {
                    $final_nama = mysqli_real_escape_string($conn, $rCek['nama_jabatan']);
                } else {
                    $final_nama = !empty($nama_jab_xls) ? mysqli_real_escape_string($conn, $nama_jab_xls) : "Unknown ($final_kode)";
                }
            } elseif (!empty($nama_jab_xls)) {
                $final_nama = mysqli_real_escape_string($conn, $nama_jab_xls);
                $qCek = mysqli_query($conn, "SELECT kode_jabatan FROM tb_master_jabatan WHERE nama_jabatan = '$final_nama'");
                if ($rCek = mysqli_fetch_assoc($qCek)) {
                    $final_kode = $rCek['kode_jabatan'];
                } else {
                    $gagal++; continue; 
                }
            } else {
                $gagal++; continue;
            }

            $tmt_jabatan = $tgl_sk; 

            // 2. LOGIC UPSERT (Check Data Kembar)
            // Cek apakah data persis sama sudah ada
            $cekAda = mysqli_query($conn, "SELECT id_jab FROM tb_jabatan WHERE id_peg='$id_peg' AND no_sk='$no_sk' AND tmt_jabatan='$tmt_jabatan'");
            
            if (mysqli_num_rows($cekAda) > 0) {
                // --- UPDATE ---
                $rowOld = mysqli_fetch_assoc($cekAda);
                $id_target = $rowOld['id_jab'];

                $query = "UPDATE tb_jabatan SET 
                            kode_jabatan = '$final_kode',
                            jabatan      = '$final_nama',
                            unit_kerja   = '$unit_kerja',
                            tgl_sk       = '$tgl_sk',
                            status_jab   = '$status_jab',
                            updated_at   = NOW(),
                            updated_by   = '$created_by'
                          WHERE id_jab   = '$id_target'";
                
                if (mysqli_query($conn, $query)) $update++; else $gagal++;

            } else {
                // --- INSERT ---
                // FIX: Menggunakan kolom 'date_reg' (sesuai database) dan diisi NOW()
                $query = "INSERT INTO tb_jabatan (
                    id_peg, kode_jabatan, jabatan, unit_kerja, no_sk, tgl_sk, tmt_jabatan, status_jab, created_by, date_reg
                ) VALUES (
                    '$id_peg', '$final_kode', '$final_nama', '$unit_kerja', '$no_sk', '$tgl_sk', '$tmt_jabatan', '$status_jab', '$created_by', NOW()
                )";

                if (mysqli_query($conn, $query)) $berhasil++; else $gagal++;
            }
        }

        // 3. STEP TERAKHIR: PERBAIKI HISTORY (SAMPAI_TGL H-1)
        // Kita hanya jalankan untuk pegawai yang datanya baru saja disentuh
        foreach (array_keys($processed_pegawai) as $id_peg_fix) {
            perbaikiHistoryJabatan($conn, $id_peg_fix);
        }

        kirimJson('success', "Proses Selesai!<br>Input Baru: <b>$berhasil</b><br>Update Data: <b>$update</b><br>Gagal: <b>$gagal</b>");
    }

} catch (Exception $e) {
    kirimJson('error', "<b>SYSTEM ERROR:</b> " . $e->getMessage());
}
?>