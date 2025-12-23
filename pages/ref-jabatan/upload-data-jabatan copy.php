<?php
// =============================================================
// FILE: pages/ref-jabatan/upload-data-jabatan.php
// UPDATE: Logic Update jika (ID + No SK + TMT) sudah ada
// =============================================================

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_id() == '') session_start(); 

ob_start();
header('Content-Type: application/json');

// --- Helper Response ---
function kirimJson($status, $msg, $html = '') {
    ob_clean(); 
    echo json_encode(['status' => $status, 'message' => $msg, 'html' => $html]);
    exit;
}

try {
    $path_vendor = '../../vendor/autoload.php';
    if (!file_exists($path_vendor)) throw new Exception("Library Excel tidak ditemukan.");
    require $path_vendor;

    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File Koneksi tidak ditemukan.");
    include $path_koneksi;

    if (!$conn) throw new Exception("Koneksi database gagal.");

    // --- FUNGSI FORMAT TANGGAL ---
    function formatTanggal($date) {
        $date = trim($date);
        if (empty($date) || $date == '-' || $date == '') return NULL;
        
        // A. Angka Excel
        if (is_numeric($date)) {
            try { return Date::excelToDateTimeObject($date)->format('Y-m-d'); } catch (Exception $e) { return NULL; }
        }
        
        // B. String
        $date = str_replace('/', '-', $date);
        if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) return $date; // yyyy-mm-dd
        
        $parts = explode('-', $date);
        // Cek format dd-mm-yyyy (25-10-2025)
        if (count($parts) == 3) {
            // Jika bagian pertama (tanggal) > 12 atau bagian ke-3 (tahun) 4 digit
            if (strlen($parts[2]) == 4) {
                return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
        }
        
        try { return date('Y-m-d', strtotime($date)); } catch (Exception $e) { return NULL; }
    }

    // --- FUNGSI SORTING ---
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
        // Load RAW data
        $rows = $spreadsheet->getActiveSheet()->toArray(null, false, true, false);
        
        if (count($rows) <= 1) throw new Exception("File Excel kosong");
        $header = array_shift($rows); 

        // Grouping untuk Auto Status Preview
        $groupedData = [];
        foreach ($rows as $row) {
            $id_peg = isset($row[0]) ? trim($row[0]) : '';
            if (empty($id_peg)) continue;

            $tgl_sk_fix = formatTanggal(isset($row[5]) ? $row[5] : '');
            
            $groupedData[$id_peg][] = [
                'raw' => $row,
                'tgl_sk_fix' => $tgl_sk_fix,
                'tgl_timestamp' => $tgl_sk_fix ? strtotime($tgl_sk_fix) : 0
            ];
        }

        $html = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm text-nowrap" style="font-size:0.85em;">';
        $html .= '<thead class="bg-primary text-white"><tr><th>Status System</th>';
        foreach ($header as $col) $html .= '<th>' . htmlspecialchars($col) . '</th>';
        $html .= '</tr></thead><tbody>';

        $count = 0; $limit = 50;

        foreach ($groupedData as $id_peg => $items) {
            if ($count >= $limit) break;
            usort($items, 'compareJabatanDate');

            foreach ($items as $idx => $item) {
                $row = $item['raw'];
                $status_input = isset($row[6]) ? trim($row[6]) : '';

                if (empty($status_input)) {
                    $status_badge = ($idx === 0) ? '<span class="badge badge-success">Auto: Aktif</span>' : '<span class="badge badge-secondary">Auto: Non</span>';
                } else {
                    $status_badge = '<span class="badge badge-info">'.$status_input.'</span>';
                }

                $html .= '<tr><td>' . $status_badge . '</td>';
                foreach ($row as $index => $cell) {
                    if ($index == 5) { // Tgl SK
                        $tgl = $item['tgl_sk_fix'];
                        $display = $tgl ? date('d-m-Y', strtotime($tgl)) : '<span class="text-danger fw-bold">Invalid</span>';
                        $html .= '<td>' . $display . '</td>';
                    } else {
                        $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                    }
                }
                $html .= '</tr>';
                $count++;
            }
        }
        $html .= '</tbody></table></div>';
        $html .= '<hr><div class="text-right"><button type="button" class="btn btn-primary" id="btnSimpanJabatan"><i class="fas fa-save"></i> Proses Import</button></div>';
        
        $json_data = json_encode($rows);
        kirimJson('success', '', $html . '<textarea id="json_data_jabatan" style="display:none;">' . $json_data . '</textarea>');
    }

    // ============================================================
    // ACTION: SAVE (UPSERT LOGIC)
    // ============================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_jabatan'])) throw new Exception("Data JSON tidak diterima.");
        $data_raw = json_decode($_POST['data_jabatan'], true);
        if (!$data_raw) throw new Exception("Gagal decode JSON data.");

        $created_by = isset($_SESSION['nama_user']) ? mysqli_real_escape_string($conn, $_SESSION['nama_user']) : 'System';
        
        // 1. Grouping & Sorting
        $groupedData = [];
        foreach ($data_raw as $row) {
            $id_peg = isset($row[0]) ? trim($row[0]) : '';
            if (empty($id_peg)) continue;
            $tgl_sk = formatTanggal(isset($row[5]) ? $row[5] : '');
            
            $groupedData[$id_peg][] = [
                'raw' => $row,
                'tgl_sk_fix' => $tgl_sk,
                'tgl_timestamp' => $tgl_sk ? strtotime($tgl_sk) : 0
            ];
        }

        $finalData = []; 
        foreach ($groupedData as $id_peg => $items) {
            usort($items, 'compareJabatanDate');
            foreach ($items as $index => $item) {
                $row = $item['raw'];
                $row['tgl_sk_fix'] = $item['tgl_sk_fix'];
                $status_input = isset($row[6]) ? trim($row[6]) : '';
                if (empty($status_input)) {
                    $row[6] = ($index === 0) ? 'Aktif' : 'Non';
                }
                $finalData[] = $row;
            }
        }

        // 2. Proses Insert / Update
        $berhasil = 0; $update = 0; $gagal = 0; $err_detail = "";

        foreach ($finalData as $row) {
            $id_peg       = isset($row[0]) ? trim($row[0]) : '';
            $kode_jab_xls = isset($row[1]) ? trim($row[1]) : '';
            $nama_jab_xls = isset($row[2]) ? trim($row[2]) : '';
            $unit_kerja   = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
            $no_sk        = isset($row[4]) ? mysqli_real_escape_string($conn, $row[4]) : '';
            $tgl_sk       = $row['tgl_sk_fix']; // Pakai tanggal yg sudah difix
            $status_jab   = isset($row[6]) ? trim($row[6]) : 'Non';

            if (empty($id_peg) || empty($tgl_sk)) { $gagal++; continue; }

            // Lookup Jabatan
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
                    $gagal++; $err_detail .= "Jabatan '$final_nama' tidak ditemukan. "; continue; 
                }
            } else {
                $gagal++; continue;
            }

            $tmt_jabatan = $tgl_sk; 

            mysqli_begin_transaction($conn);
            $err_tr = false;

            // AUTO CLOSE: Matikan jabatan lain jika yang ini 'Aktif'
            // Catatan: Logic ini tetap jalan walau kita Update data yang existing
            if ($status_jab == 'Aktif') {
                $tgl_tutup = date('Y-m-d', strtotime('-1 day', strtotime($tgl_sk)));
                
                // Matikan semua jabatan Aktif milik pegawai ini, KECUALI jabatan dengan No SK yang sama (karena itu yg sedang kita proses)
                $sqlClose = "UPDATE tb_jabatan SET 
                             status_jab = 'Non', 
                             sampai_tgl = '$tgl_tutup',
                             updated_at = NOW(),
                             updated_by = '$created_by'
                             WHERE id_peg = '$id_peg' 
                             AND status_jab = 'Aktif' 
                             AND no_sk != '$no_sk'"; 
                
                if (!mysqli_query($conn, $sqlClose)) $err_tr = true;
            }

            if (!$err_tr) {
                // --- LOGIKA CEK DATA KEMBAR (UPSERT) ---
                // Cek apakah data dengan ID Peg + No SK + TMT sudah ada?
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
                    
                    if (mysqli_query($conn, $query)) {
                        $update++;
                        mysqli_commit($conn);
                    } else {
                        mysqli_rollback($conn); $gagal++;
                        $err_detail .= "Gagal Update: ".mysqli_error($conn)."|";
                    }

                } else {
                    // --- INSERT ---
                    $query = "INSERT INTO tb_jabatan (
                        id_peg, kode_jabatan, jabatan, unit_kerja, no_sk, tgl_sk, tmt_jabatan, status_jab, created_by, created_at
                    ) VALUES (
                        '$id_peg', '$final_kode', '$final_nama', '$unit_kerja', '$no_sk', '$tgl_sk', '$tmt_jabatan', '$status_jab', '$created_by', NOW()
                    )";

                    if (mysqli_query($conn, $query)) {
                        $berhasil++;
                        mysqli_commit($conn);
                    } else {
                        mysqli_rollback($conn); $gagal++;
                        $err_detail .= "Gagal Insert: ".mysqli_error($conn)."|";
                    }
                }
            } else {
                mysqli_rollback($conn); $gagal++;
            }
        }

        kirimJson('success', "Proses Selesai!<br>Input Baru: <b>$berhasil</b><br>Update Data: <b>$update</b><br>Gagal: <b>$gagal</b><br><small>$err_detail</small>");
    }

} catch (Exception $e) {
    kirimJson('error', "<b>SYSTEM ERROR:</b> " . $e->getMessage());
}
?>