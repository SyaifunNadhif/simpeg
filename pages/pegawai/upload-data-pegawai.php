<?php
// =============================================================
// FILE: pages/pegawai/upload-data-pegawai.php
// UPDATE: Smart Logic (Update ID if Name Match)
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

    if (is_numeric($date)) {
        if ($date > 1000) {
            try {
                return Date::excelToDateTimeObject($date)->format('Y-m-d');
            } catch (Exception $e) { }
        }
    }

    $date = str_replace(['/', '.'], '-', $date);

    if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $date, $matches)) {
        if (checkdate($matches[2], $matches[3], $matches[1])) {
            return $date;
        }
    }

    if (preg_match("/^(\d{2})-(\d{2})-(\d{4})$/", $date, $matches)) {
        if (checkdate($matches[2], $matches[1], $matches[3])) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
    }

    try {
        $dt = new DateTime($date);
        return $dt->format('Y-m-d');
    } catch (Exception $e) {
        return NULL; 
    }
}

// --- 2. HITUNG PENSIUN ---
function hitungPensiun($tgl_lahir, $tgl_pensiun_input = '') {
    $manual = formatTanggal($tgl_pensiun_input);
    if (!empty($manual) && $manual != '1970-01-01') {
        return $manual;
    }

    $lahir = formatTanggal($tgl_lahir);
    if (!empty($lahir) && $lahir != '1970-01-01') {
        try {
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

    // --- A. MODE PREVIEW ---
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");

        $file = $_FILES['file_excel'];
        $spreadsheet = IOFactory::load($file['tmp_name']);
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
            
            $id_excel   = isset($row[0]) ? trim($row[0]) : '';
            $nama_excel = isset($row[2]) ? trim($row[2]) : '';
            
            // Logic Cek Status Visual
            $status_row = '<span class="badge badge-secondary">New</span>';
            
            if(!empty($id_excel)) {
                $id_esc = mysqli_real_escape_string($conn, $id_excel);
                // 1. Cek ID
                $cekId = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_esc' LIMIT 1");
                if(mysqli_num_rows($cekId) > 0) {
                    $status_row = '<span class="badge badge-warning">Update (ID)</span>';
                } else {
                    // 2. Cek Nama (Jika ID tidak ketemu)
                    if(!empty($nama_excel)) {
                        $nm_esc = mysqli_real_escape_string($conn, $nama_excel);
                        $cekNama = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE nama = '$nm_esc' LIMIT 1");
                        if(mysqli_num_rows($cekNama) > 0) {
                            $status_row = '<span class="badge badge-danger">Ganti ID (Nama Match)</span>';
                        }
                    }
                }
            }

            $html .= '<tr><td>' . $status_row . '</td>';
            foreach ($row as $index => $cell) {
                if ($index == 1) { // NIP
                    $val = $cell;
                    if (is_numeric($val) && stripos($val, 'E') !== false) {
                        $val = number_format($cell, 0, '', '');
                    }
                    $html .= '<td>' . htmlspecialchars($val) . '</td>';
                }
                elseif ($index == 4 || $index == 14) { 
                    $tgl = formatTanggal($cell);
                    $html .= '<td>' . ($tgl ? $tgl : '-') . '</td>';
                }
                elseif ($index == 15) { 
                    $tgl_lahir_raw = isset($row[4]) ? $row[4] : '';
                    $tgl_pensiun_raw = $cell; 
                    $pensiun_fix = hitungPensiun($tgl_lahir_raw, $tgl_pensiun_raw);
                    if ($pensiun_fix) {
                        $is_gen = empty(formatTanggal($tgl_pensiun_raw));
                        $icon = $is_gen ? ' <i class="fas fa-magic text-warning" title="Auto Hitung"></i>' : '';
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
        
        $html .= '<hr><div class="text-right"><button type="button" class="btn btn-primary" id="btnSimpanKolektif"><i class="fas fa-save"></i> Proses Import & Update</button></div>';
        
        $json_rows = json_encode($rows);
        $html .= '<textarea id="json_data_pegawai" style="display:none;">' . $json_rows . '</textarea>';

        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // --- B. MODE SAVE (PROSES IMPORT) ---
    elseif ($action === 'save') {
        if (!isset($_POST['data_pegawai'])) throw new Exception("Data tidak diterima");
        $data = json_decode($_POST['data_pegawai'], true);
        
        $created_by = isset($_SESSION['nama_user']) ? mysqli_real_escape_string($conn, $_SESSION['nama_user']) : 'System';
        $berhasil = 0; $gagal = 0; $updated = 0; $updated_id = 0;

        foreach ($data as $row) {
            $id_peg_excel = isset($row[0]) ? trim($row[0]) : '';
            
            // Sanitasi
            $nip = isset($row[1]) ? preg_replace('/[^0-9]/', '', $row[1]) : '';
            if (stripos($row[1], 'E') !== false) { $nip = number_format(floatval($row[1]), 0, '', ''); }
            $nip = mysqli_real_escape_string($conn, $nip);

            $nama_raw     = isset($row[2]) ? trim($row[2]) : '';
            $nama         = mysqli_real_escape_string($conn, $nama_raw);
            
            $tempat_lhr   = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
            $tgl_lhr      = formatTanggal(isset($row[4]) ? $row[4] : '');
            $agama        = isset($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : '';
            $jk           = isset($row[6]) ? mysqli_real_escape_string($conn, $row[6]) : '';
            $gol_darah    = isset($row[7]) ? mysqli_real_escape_string($conn, $row[7]) : '';
            $status_nikah = isset($row[8]) ? mysqli_real_escape_string($conn, $row[8]) : '';
            $status_kepeg = isset($row[9]) ? mysqli_real_escape_string($conn, $row[9]) : '';
            $alamat       = isset($row[10]) ? mysqli_real_escape_string($conn, $row[10]) : '';
            $telp         = isset($row[11]) ? mysqli_real_escape_string($conn, $row[11]) : '';
            $email        = isset($row[12]) ? mysqli_real_escape_string($conn, $row[12]) : '';
            
            // FOTO
            $foto_raw     = isset($row[13]) ? trim($row[13]) : '';
            $foto         = mysqli_real_escape_string($conn, $foto_raw);
            
            $tmt_kerja    = formatTanggal(isset($row[14]) ? $row[14] : '');
            $final_pensiun= hitungPensiun($tgl_lhr, isset($row[15]) ? $row[15] : ''); 

            $bpjstk       = isset($row[16]) ? mysqli_real_escape_string($conn, preg_replace('/[^0-9]/', '', $row[16])) : '';
            $bpjskes      = isset($row[17]) ? mysqli_real_escape_string($conn, preg_replace('/[^0-9]/', '', $row[17])) : '';

            $uid_baru    = gen_uuid();
            $val_tgl_lhr = ($tgl_lhr) ? "'$tgl_lhr'" : "NULL";
            $val_tmt     = ($tmt_kerja) ? "'$tmt_kerja'" : "NULL";
            $val_pensiun = ($final_pensiun) ? "'$final_pensiun'" : "NULL";

            // Logic Foto Update (Hanya jika ada isinya)
            $sql_foto_update = "";
            if (!empty($foto)) {
                $sql_foto_update = ", foto = '$foto'";
            }

            if (!empty($id_peg_excel)) {
                $id_peg_new = mysqli_real_escape_string($conn, $id_peg_excel);
                
                // 1. CEK ID (PRIORITAS 1)
                $cekId = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_peg_new' LIMIT 1");
                
                if (mysqli_num_rows($cekId) > 0) {
                    // --- CASE A: ID KETEMU -> UPDATE NORMAL ---
                    $query_update = "UPDATE tb_pegawai SET 
                        nip = '$nip', nama = '$nama', tempat_lhr = '$tempat_lhr', tgl_lhr = $val_tgl_lhr,
                        agama = '$agama', jk = '$jk', gol_darah = '$gol_darah', status_nikah = '$status_nikah', status_kepeg = '$status_kepeg',
                        alamat = '$alamat', telp = '$telp', email = '$email' $sql_foto_update, 
                        tmt_kerja = $val_tmt, tgl_pensiun = $val_pensiun, bpjstk = '$bpjstk', bpjskes = '$bpjskes'
                        WHERE id_peg = '$id_peg_new'";

                    if (mysqli_query($conn, $query_update)) { $updated++; } else { $gagal++; }

                } else {
                    // 2. ID TIDAK KETEMU -> CEK NAMA (PRIORITAS 2)
                    $cekNama = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE nama = '$nama' LIMIT 1");
                    
                    if (mysqli_num_rows($cekNama) > 0) {
                        // --- CASE B: NAMA KETEMU (ID BEDA) -> UPDATE ID & DATA ---
                        $rowNama = mysqli_fetch_assoc($cekNama);
                        $old_id = $rowNama['id_peg'];

                        // Update ID (Primary Key Change) + Data Lain
                        // Perhatian: Mengubah PK bisa beresiko jika ada relasi tanpa ON UPDATE CASCADE.
                        // Namun ini sesuai request.
                        
                        $query_update_id = "UPDATE tb_pegawai SET 
                            id_peg = '$id_peg_new',
                            nip = '$nip', tempat_lhr = '$tempat_lhr', tgl_lhr = $val_tgl_lhr,
                            agama = '$agama', jk = '$jk', gol_darah = '$gol_darah', status_nikah = '$status_nikah', status_kepeg = '$status_kepeg',
                            alamat = '$alamat', telp = '$telp', email = '$email' $sql_foto_update, 
                            tmt_kerja = $val_tmt, tgl_pensiun = $val_pensiun, bpjstk = '$bpjstk', bpjskes = '$bpjskes'
                            WHERE id_peg = '$old_id'";

                        if (mysqli_query($conn, $query_update_id)) { $updated_id++; } else { $gagal++; }

                    } else {
                        // --- CASE C: ID & NAMA TIDAK ADA -> INSERT BARU ---
                        $query = "INSERT INTO tb_pegawai (
                            pegawai_uid, id_peg, nip, nama, tempat_lhr, tgl_lhr, 
                            agama, jk, gol_darah, status_nikah, status_kepeg, 
                            alamat, telp, email, foto, 
                            tmt_kerja, tgl_pensiun, bpjstk, bpjskes,
                            status_aktif, created_by, date_reg
                        ) VALUES (
                            '$uid_baru', '$id_peg_new', '$nip', '$nama', '$tempat_lhr', $val_tgl_lhr, 
                            '$agama', '$jk', '$gol_darah', '$status_nikah', '$status_kepeg', 
                            '$alamat', '$telp', '$email', '$foto', 
                            $val_tmt, $val_pensiun, '$bpjstk', '$bpjskes',
                            '1', '$created_by', '$tgl_sekarang'
                        )";

                        if (mysqli_query($conn, $query)) { $berhasil++; } else { $gagal++; }
                    }
                }
            } else {
                $gagal++; // ID Kosong
            }
        }

        ob_clean();
        echo json_encode([
            'status' => 'success', 
            'message' => "Import Selesai!<br>Input Baru: $berhasil<br>Update Normal: $updated<br>Ganti ID (Nama Sama): $updated_id<br>Gagal: $gagal"
        ]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>