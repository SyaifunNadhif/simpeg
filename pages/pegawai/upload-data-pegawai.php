<?php
// =============================================================
// FILE: pages/pegawai/upload-data-pegawai.php
// UPDATE: Fix Year 2038 Problem (Support Pensiun > 2038 on PHP 5.6)
// =============================================================

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date; 

error_reporting(0);
ini_set('display_errors', 0);

if (session_id() == '') session_start(); 

ob_start();
header('Content-Type: application/json');

// --- 1. FUNGSI FORMAT TANGGAL ---
function formatTanggal($date) {
    $date = trim($date);
    if (empty($date) || $date == '-' || $date == '') return NULL;

    // A. Angka Excel
    if (is_numeric($date)) {
        try {
            return Date::excelToDateTimeObject($date)->format('Y-m-d');
        } catch (Exception $e) { return NULL; }
    }

    // B. Ubah format
    $date = str_replace('/', '-', $date);

    // C. Cek YYYY-MM-DD
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
        return $date;
    }

    // D. Cek DD-MM-YYYY
    $parts = explode('-', $date);
    if (count($parts) == 3) {
        // Format Indonesia (31-12-2000)
        if (strlen($parts[2]) == 4 && is_numeric($parts[2])) {
            return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
    }

    // E. Fallback
    try {
        $dt = new DateTime($date);
        return $dt->format('Y-m-d');
    } catch (Exception $e) {
        return NULL;
    }
}

// --- 2. FUNGSI HITUNG PENSIUN (ANTI ERROR 2038) ---
function hitungPensiun($tgl_lahir, $tgl_pensiun_input = '') {
    // Cek input manual
    $manual = formatTanggal($tgl_pensiun_input);
    if (!empty($manual) && $manual != '1970-01-01') {
        return $manual;
    }

    // Hitung otomatis (+56 Tahun)
    $lahir = formatTanggal($tgl_lahir);
    if (!empty($lahir) && $lahir != '1970-01-01') {
        try {
            // Gunakan DateTime Class agar aman dari bug 2038 di PHP 5.6
            $date = new DateTime($lahir);
            $date->modify('+56 years');
            return $date->format('Y-m-d');
        } catch (Exception $e) {
            return NULL;
        }
    }
    return NULL;
}

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
    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File koneksi tidak ditemukan");
    include $path_koneksi;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid Request Method");

    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $tgl_sekarang = date('Y-m-d');

    // --- PREVIEW ---
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");

        $file = $_FILES['file_excel'];
        $spreadsheet = IOFactory::load($file['tmp_name']);
        
        // Baca RAW data
        $rows = $spreadsheet->getActiveSheet()->toArray(null, false, true, false);

        if (count($rows) <= 1) throw new Exception("File Excel kosong");
        $header = array_shift($rows); 

        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm text-nowrap" style="font-size: 0.85em;">';
        $html .= '<thead class="bg-primary text-white"><tr><th>Status</th>'; 
        foreach ($header as $col) $html .= '<th>' . htmlspecialchars($col) . '</th>';
        $html .= '</tr></thead><tbody>';

        $limit = 10; $count = 0;
        foreach ($rows as $row) {
            if ($count >= $limit) break;
            $id_excel = isset($row[0]) ? $row[0] : '';
            $status_row = !empty($id_excel) ? '<span class="badge badge-info">Ready</span>' : '<span class="badge badge-warning">Skip</span>';

            $html .= '<tr><td>' . $status_row . '</td>';
            foreach ($row as $index => $cell) {
                
                // NIP
                if ($index == 1) {
                    $val = $cell;
                    if (is_numeric($val) && stripos($val, 'E') !== false) {
                        $val = number_format($cell, 0, '', '');
                    }
                    $html .= '<td>' . htmlspecialchars($val) . '</td>';
                }
                // Tgl Lahir
                elseif ($index == 4) {
                    $tgl = formatTanggal($cell);
                    $html .= '<td>' . ($tgl ? $tgl : '<span class="text-danger">Invalid</span>') . '</td>';
                }
                // TMT
                elseif ($index == 14) {
                    $tgl = formatTanggal($cell);
                    $html .= '<td>' . ($tgl ? $tgl : '-') . '</td>';
                }
                // Tgl Pensiun (Preview)
                elseif ($index == 15) {
                    $tgl_lahir_raw = isset($row[4]) ? $row[4] : '';
                    $tgl_pensiun_raw = $cell; 

                    // Panggil fungsi DateTime
                    $pensiun_fix = hitungPensiun($tgl_lahir_raw, $tgl_pensiun_raw);
                    
                    if ($pensiun_fix) {
                        $is_gen = empty(formatTanggal($tgl_pensiun_raw));
                        $icon = $is_gen ? ' <i class="fas fa-magic text-warning" title="Auto +56 Thn"></i>' : '';
                        $html .= '<td>' . $pensiun_fix . $icon . '</td>';
                    } else {
                        $html .= '<td>-</td>';
                    }
                }
                else {
                    $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
            }
            $html .= '</tr>';
            $count++;
        }
        $html .= '</tbody></table></div>';
        
        $html .= '<hr><div class="text-right"><button type="button" class="btn btn-primary" id="btnSimpanKolektif"><i class="fas fa-save"></i> Proses Import</button></div>';
        
        $json_rows = json_encode($rows);
        $html .= '<textarea id="json_data_pegawai" style="display:none;">' . $json_rows . '</textarea>';

        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // --- SAVE ---
    elseif ($action === 'save') {
        if (!isset($_POST['data_pegawai'])) throw new Exception("Data tidak diterima");
        $data = json_decode($_POST['data_pegawai'], true);
        
        $created_by = isset($_SESSION['nama_user']) ? mysqli_real_escape_string($conn, $_SESSION['nama_user']) : 'System';
        $berhasil = 0; $gagal = 0; $duplikat = 0;

        foreach ($data as $row) {
            $id_peg_excel = isset($row[0]) ? trim($row[0]) : '';
            
            // NIP
            $nip = isset($row[1]) ? preg_replace('/[^0-9]/', '', $row[1]) : '';
            if (stripos($row[1], 'E') !== false) { 
                $nip = number_format(floatval($row[1]), 0, '', ''); 
            }
            $nip = mysqli_real_escape_string($conn, $nip);

            $nama         = isset($row[2]) ? mysqli_real_escape_string($conn, $row[2]) : '';
            $tempat_lhr   = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
            
            // Tgl Lahir
            $tgl_lhr      = formatTanggal(isset($row[4]) ? $row[4] : '');
            
            $agama        = isset($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : '';
            $jk           = isset($row[6]) ? mysqli_real_escape_string($conn, $row[6]) : '';
            $gol_darah    = isset($row[7]) ? mysqli_real_escape_string($conn, $row[7]) : '';
            $status_nikah = isset($row[8]) ? mysqli_real_escape_string($conn, $row[8]) : '';
            $status_kepeg = isset($row[9]) ? mysqli_real_escape_string($conn, $row[9]) : '';
            $alamat       = isset($row[10]) ? mysqli_real_escape_string($conn, $row[10]) : '';
            $telp         = isset($row[11]) ? mysqli_real_escape_string($conn, $row[11]) : '';
            $email        = isset($row[12]) ? mysqli_real_escape_string($conn, $row[12]) : '';
            $foto         = isset($row[13]) ? mysqli_real_escape_string($conn, $row[13]) : '';
            
            // TMT
            $tmt_kerja    = formatTanggal(isset($row[14]) ? $row[14] : '');
            
            // HITUNG PENSIUN (Save)
            $tgl_pensiun_excel = isset($row[15]) ? $row[15] : '';
            $final_pensiun     = hitungPensiun($tgl_lhr, $tgl_pensiun_excel); 

            $bpjstk       = isset($row[16]) ? mysqli_real_escape_string($conn, preg_replace('/[^0-9]/', '', $row[16])) : '';
            $bpjskes      = isset($row[17]) ? mysqli_real_escape_string($conn, preg_replace('/[^0-9]/', '', $row[17])) : '';

            $uid_baru    = gen_uuid();
            $val_tgl_lhr = ($tgl_lhr) ? "'$tgl_lhr'" : "NULL";
            $val_tmt     = ($tmt_kerja) ? "'$tmt_kerja'" : "NULL";
            $val_pensiun = ($final_pensiun) ? "'$final_pensiun'" : "NULL";

            if (!empty($id_peg_excel)) {
                $id_peg = mysqli_real_escape_string($conn, $id_peg_excel);
                
                $cek = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_peg'");
                
                if (mysqli_num_rows($cek) == 0) {
                    $query = "INSERT INTO tb_pegawai (
                        pegawai_uid, id_peg, nip, nama, tempat_lhr, tgl_lhr, 
                        agama, jk, gol_darah, status_nikah, status_kepeg, 
                        alamat, telp, email, foto, 
                        tmt_kerja, tgl_pensiun, 
                        bpjstk, bpjskes,
                        status_aktif, created_by, date_reg
                    ) VALUES (
                        '$uid_baru', '$id_peg', '$nip', '$nama', '$tempat_lhr', $val_tgl_lhr, 
                        '$agama', '$jk', '$gol_darah', '$status_nikah', '$status_kepeg', 
                        '$alamat', '$telp', '$email', '$foto', 
                        $val_tmt, $val_pensiun, 
                        '$bpjstk', '$bpjskes',
                        '1', '$created_by', '$tgl_sekarang'
                    )";

                    if (mysqli_query($conn, $query)) {
                        $berhasil++;
                    } else {
                        $gagal++; 
                    }
                } else {
                    $duplikat++; 
                }
            } else {
                $gagal++;
            }
        }

        ob_clean();
        echo json_encode([
            'status' => 'success', 
            'message' => "Import Selesai!<br>Berhasil: $berhasil<br>Duplikat (Skip): $duplikat<br>Gagal: $gagal"
        ]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>