<?php
// =============================================================
// FILE: pages/pegawai/upload-data-pegawai.php
// MODULE: Import Excel Pegawai (Linux Strict Mode Compatible)
// =============================================================

require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date; 

// [CONFIG] : Matikan batasan memory & waktu untuk data banyak
ini_set('memory_limit', '-1'); 
set_time_limit(0); 

// [CONFIG] : Matikan error display agar JSON tidak rusak
error_reporting(0);
ini_set('display_errors', 0);

if (session_id() == '') session_start(); 

ob_start();
header('Content-Type: application/json');

// --- 1. FUNGSI HELPER AMAN UNTUK LINUX ---

// Helper untuk ubah string kosong jadi NULL (PENTING BUAT LINUX)
function getSqlVal($conn, $val, $type = 'string') {
    if ($val === '' || $val === null || $val === false) {
        return "NULL";
    }
    
    $safe = mysqli_real_escape_string($conn, $val);
    
    if ($type === 'int') {
        $num = preg_replace('/[^0-9]/', '', $val);
        return ($num === '') ? "NULL" : "'$num'";
    }
    
    return "'$safe'";
}

function formatTanggal($date) {
    $date = trim($date);
    if (empty($date) || $date == '-' || $date == '') return NULL;

    if (is_numeric($date)) {
        if ($date > 1000) {
            try { return Date::excelToDateTimeObject($date)->format('Y-m-d'); } catch (Exception $e) { return NULL; }
        }
    }

    $date = str_replace(['/', '.'], '-', $date);

    if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $date, $matches)) {
        if (checkdate($matches[2], $matches[3], $matches[1])) return $date;
    }

    if (preg_match("/^(\d{2})-(\d{2})-(\d{4})$/", $date, $matches)) {
        if (checkdate($matches[2], $matches[1], $matches[3])) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
    }

    try { $dt = new DateTime($date); return $dt->format('Y-m-d'); } catch (Exception $e) { return NULL; }
}

function hitungPensiun($tgl_lahir, $tgl_pensiun_input = '') {
    $manual = formatTanggal($tgl_pensiun_input);
    if (!empty($manual) && $manual != '1970-01-01') return $manual;

    $lahir = formatTanggal($tgl_lahir);
    if (!empty($lahir) && $lahir != '1970-01-01') {
        try {
            $date = new DateTime($lahir);
            $date->modify('+56 years'); // Usia Pensiun
            return $date->format('Y-m-d');
        } catch (Exception $e) { return NULL; }
    }
    return NULL;
}

function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000, mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

// --- CORE LOGIC ---
try {
    // 1. Koneksi Database
    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File koneksi database tidak ditemukan.");
    include $path_koneksi;

    // [BARIS SAKTI] : SOLUSI AGAR LINUX TIDAK ERROR KARENA DATA KOSONG
    // Ini memaksa MySQL mode menjadi "santuy" seperti di XAMPP Windows
    mysqli_query($conn, "SET SESSION sql_mode = ''"); 

    // 2. Cek Request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Metode request tidak valid.");
    if (empty($_SESSION['id_user'])) throw new Exception("Sesi kadaluarsa. Silakan login ulang.");

    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $tgl_sekarang = date('Y-m-d');

    // ==========================================================
    // A. MODE PREVIEW (BACA FILE EXCEL)
    // ==========================================================
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih.");

        $file = $_FILES['file_excel'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) throw new Exception("Format file tidak didukung.");

        try {
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, false, true, false);
        } catch (Exception $e) {
            throw new Exception("Gagal membaca file Excel. Pastikan file tidak corrupt.");
        }

        if (count($rows) <= 1) throw new Exception("File Excel kosong atau hanya header.");
        
        $header = array_shift($rows); 
        $limit_preview = 15; 
        $preview_rows = array_slice($rows, 0, $limit_preview);

        // Generate HTML Table
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm text-nowrap" style="font-size: 0.85em;">';
        $html .= '<thead class="bg-primary text-white"><tr><th>Status System</th>'; 
        foreach ($header as $col) $html .= '<th>' . htmlspecialchars($col) . '</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($preview_rows as $row) {
            $id_excel = isset($row[0]) ? trim($row[0]) : '';
            $nama_excel = isset($row[2]) ? trim($row[2]) : '';
            
            $status_row = '<span class="badge badge-success">New Input</span>';
            
            if(!empty($id_excel)) {
                $id_esc = mysqli_real_escape_string($conn, $id_excel);
                $cekId = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_esc' LIMIT 1");
                if(mysqli_num_rows($cekId) > 0) {
                    $status_row = '<span class="badge badge-warning">Update Data</span>';
                } else {
                    if(!empty($nama_excel)) {
                        $nm_esc = mysqli_real_escape_string($conn, $nama_excel);
                        $cekNama = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE nama = '$nm_esc' LIMIT 1");
                        if(mysqli_num_rows($cekNama) > 0) {
                            $status_row = '<span class="badge badge-danger">Ganti ID (Nama Cocok)</span>';
                        }
                    }
                }
            }

            $html .= '<tr><td>' . $status_row . '</td>';
            foreach ($row as $index => $cell) {
                if ($index == 1) { // NIP
                     $val = $cell;
                     if (is_numeric($val) && stripos($val, 'E') !== false) $val = number_format($cell, 0, '', '');
                     $html .= '<td>' . htmlspecialchars($val) . '</td>';
                }
                elseif ($index == 4 || $index == 14) { // Tgl Lahir & TMT
                    $tgl = formatTanggal($cell);
                    $html .= '<td>' . ($tgl ? $tgl : '-') . '</td>';
                }
                elseif ($index == 15) { // Pensiun
                    $pensiun_fix = hitungPensiun(isset($row[4])?$row[4]:'', $cell);
                    $html .= '<td>' . ($pensiun_fix ? $pensiun_fix : '-') . '</td>';
                }
                else {
                    $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        
        if (count($rows) > $limit_preview) {
            $html .= '<div class="alert alert-info py-2 my-2"><i class="fas fa-info-circle"></i> Menampilkan 15 dari '.count($rows).' baris data. Semua data akan diproses saat disimpan.</div>';
        }

        $html .= '<hr><div class="text-right"><button type="button" class="btn btn-primary" id="btnSimpanKolektif"><i class="fas fa-save"></i> Proses Import & Update</button></div>';
        
        $json_rows = json_encode($rows);
        $html .= '<textarea id="json_data_pegawai" style="display:none;">' . htmlspecialchars($json_rows) . '</textarea>';

        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // ==========================================================
    // B. MODE SAVE (EKSEKUSI DATABASE - LINUX SAFE)
    // ==========================================================
    elseif ($action === 'save') {
        if (!isset($_POST['data_pegawai'])) throw new Exception("Data import tidak ditemukan.");
        
        $data = json_decode($_POST['data_pegawai'], true);
        if (!$data) throw new Exception("Format data corrupt.");

        $created_by = isset($_SESSION['nama_user']) ? mysqli_real_escape_string($conn, $_SESSION['nama_user']) : 'System';
        
        $berhasil = 0; $gagal = 0; $updated = 0; $updated_id = 0;
        $pesan_error_db = ""; // Untuk menampung contoh error jika ada

        foreach ($data as $row) {
            $id_peg_raw = isset($row[0]) ? trim($row[0]) : '';
            
            // --- DATA PREPARATION ---
            $v_id_peg   = mysqli_real_escape_string($conn, $id_peg_raw);
            $v_nip      = isset($row[1]) ? trim($row[1]) : '';
            $v_nama     = isset($row[2]) ? strip_tags(trim($row[2])) : '';
            
            $tgl_lhr        = formatTanggal(isset($row[4]) ? $row[4] : '');
            $tmt_kerja      = formatTanggal(isset($row[14]) ? $row[14] : '');
            $final_pensiun  = hitungPensiun(isset($row[4]) ? $row[4] : '', isset($row[15]) ? $row[15] : ''); 

            // KONVERSI KE FORMAT SQL AMAN (NULL-SAFE)
            $sql_nama         = getSqlVal($conn, $v_nama);
            $sql_tempat_lhr   = getSqlVal($conn, isset($row[3]) ? $row[3] : '');
            $sql_tgl_lhr      = ($tgl_lhr) ? "'$tgl_lhr'" : "NULL";
            
            $sql_agama        = getSqlVal($conn, isset($row[5]) ? $row[5] : '');
            $sql_jk           = getSqlVal($conn, isset($row[6]) ? $row[6] : '');
            $sql_gol_darah    = getSqlVal($conn, isset($row[7]) ? $row[7] : '');
            $sql_status_nikah = getSqlVal($conn, isset($row[8]) ? $row[8] : '');
            $sql_status_kepeg = getSqlVal($conn, isset($row[9]) ? $row[9] : '');
            $sql_alamat       = getSqlVal($conn, isset($row[10]) ? $row[10] : '');
            $sql_telp         = getSqlVal($conn, isset($row[11]) ? $row[11] : '');
            $sql_email        = getSqlVal($conn, isset($row[12]) ? $row[12] : '');
            $sql_foto         = getSqlVal($conn, isset($row[13]) ? $row[13] : '');
            
            $sql_tmt_kerja    = ($tmt_kerja) ? "'$tmt_kerja'" : "NULL";
            $sql_tgl_pensiun  = ($final_pensiun) ? "'$final_pensiun'" : "NULL";
            
            $sql_bpjstk       = getSqlVal($conn, isset($row[16]) ? $row[16] : '', 'int');
            $sql_bpjskes      = getSqlVal($conn, isset($row[17]) ? $row[17] : '', 'int');

            $safe_nip = mysqli_real_escape_string($conn, preg_replace('/[^0-9]/', '', $v_nip));
            $sql_nip = ($safe_nip === '') ? "NULL" : "'$safe_nip'";

            // Logic Foto
            $sql_foto_update = "";
            $foto_raw = isset($row[13]) ? trim($row[13]) : '';
            if (!empty($foto_raw)) {
                $sql_foto_update = ", foto = " . getSqlVal($conn, $foto_raw);
            }

            if (!empty($id_peg_raw)) {
                // CEK DB
                $cekId = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$v_id_peg' LIMIT 1");
                
                if (mysqli_num_rows($cekId) > 0) {
                    // UPDATE
                    $query_update = "UPDATE tb_pegawai SET 
                        nip = $sql_nip, nama = $sql_nama, tempat_lhr = $sql_tempat_lhr, tgl_lhr = $sql_tgl_lhr,
                        agama = $sql_agama, jk = $sql_jk, gol_darah = $sql_gol_darah, status_nikah = $sql_status_nikah, 
                        status_kepeg = $sql_status_kepeg, alamat = $sql_alamat, telp = $sql_telp, email = $sql_email 
                        $sql_foto_update, 
                        tmt_kerja = $sql_tmt_kerja, tgl_pensiun = $sql_tgl_pensiun, bpjstk = $sql_bpjstk, bpjskes = $sql_bpjskes
                        WHERE id_peg = '$v_id_peg'";

                    if (mysqli_query($conn, $query_update)) { 
                        $updated++; 
                    } else { 
                        $gagal++; 
                        if(empty($pesan_error_db)) $pesan_error_db = mysqli_error($conn);
                    }

                } else {
                    $cekNama = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE nama = $sql_nama LIMIT 1");
                    
                    if (mysqli_num_rows($cekNama) > 0) {
                        // UPDATE ID & DATA
                        $rowNama = mysqli_fetch_assoc($cekNama);
                        $old_id = $rowNama['id_peg'];

                        $query_update_id = "UPDATE tb_pegawai SET 
                            id_peg = '$v_id_peg',
                            nip = $sql_nip, tempat_lhr = $sql_tempat_lhr, tgl_lhr = $sql_tgl_lhr,
                            agama = $sql_agama, jk = $sql_jk, gol_darah = $sql_gol_darah, status_nikah = $sql_status_nikah, 
                            status_kepeg = $sql_status_kepeg, alamat = $sql_alamat, telp = $sql_telp, email = $sql_email 
                            $sql_foto_update, 
                            tmt_kerja = $sql_tmt_kerja, tgl_pensiun = $sql_tgl_pensiun, bpjstk = $sql_bpjstk, bpjskes = $sql_bpjskes
                            WHERE id_peg = '$old_id'";

                        if (mysqli_query($conn, $query_update_id)) { 
                            $updated_id++; 
                        } else { 
                            $gagal++; 
                            if(empty($pesan_error_db)) $pesan_error_db = mysqli_error($conn);
                        }
                    } else {
                        // INSERT BARU
                        $uid_baru = gen_uuid();
                        $query = "INSERT INTO tb_pegawai (
                            pegawai_uid, id_peg, nip, nama, tempat_lhr, tgl_lhr, 
                            agama, jk, gol_darah, status_nikah, status_kepeg, 
                            alamat, telp, email, foto, 
                            tmt_kerja, tgl_pensiun, bpjstk, bpjskes,
                            status_aktif, created_by, date_reg
                        ) VALUES (
                            '$uid_baru', '$v_id_peg', $sql_nip, $sql_nama, $sql_tempat_lhr, $sql_tgl_lhr, 
                            $sql_agama, $sql_jk, $sql_gol_darah, $sql_status_nikah, $sql_status_kepeg, 
                            $sql_alamat, $sql_telp, $sql_email, $sql_foto, 
                            $sql_tmt_kerja, $sql_tgl_pensiun, $sql_bpjstk, $sql_bpjskes,
                            '1', '$created_by', '$tgl_sekarang'
                        )";

                        if (mysqli_query($conn, $query)) { 
                            // Auto Create User
                            $pass_def = md5("123456");
                            $qUser = "INSERT INTO tb_user (id_user, password, nama_user, id_pegawai, hak_akses, status_aktif) 
                                      VALUES ('$v_id_peg', '$pass_def', $sql_nama, '$v_id_peg', 'User', 'Y')";
                            mysqli_query($conn, $qUser);
                            $berhasil++; 
                        } else { 
                            $gagal++; 
                            if(empty($pesan_error_db)) $pesan_error_db = mysqli_error($conn);
                        }
                    }
                }
            } else {
                $gagal++; 
            }
        }

        $debug_info = "";
        if(!empty($pesan_error_db)) {
            $debug_info = "<br><span class='text-danger' style='font-size:10px'>Contoh Error DB: $pesan_error_db</span>";
        }

        ob_clean();
        echo json_encode([
            'status' => 'success', 
            'message' => "<b>Import Selesai!</b><br>
                          <span class='text-success'>Input Baru: $berhasil</span><br>
                          <span class='text-primary'>Update Data: $updated</span><br>
                          <span class='text-warning'>Ganti ID: $updated_id</span><br>
                          <span class='text-danger'>Gagal: $gagal</span>$debug_info"
        ]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>