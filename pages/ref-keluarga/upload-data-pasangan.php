<?php
// =============================================================
// FILE: pages/ref-pasangan/upload-data-pasangan.php
// TABLE: tb_suamiistri
// LOGIC: Manual ID Generation (MAX + 1) & Upsert
// =============================================================

session_start();
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(0);
ini_set('display_errors', 0);
ob_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

// --- HELPER FUNCTIONS ---
function formatTanggal($date) {
    if (empty($date) || $date == '-' || $date == '') return NULL;
    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
        return $date;
    }
    try {
        if (is_numeric($date)) {
            $unixDate = ($date - 25569) * 86400;
            return gmdate("Y-m-d", $unixDate);
        }
        $timestamp = strtotime(str_replace('/', '-', $date));
        return $timestamp ? date('Y-m-d', $timestamp) : NULL;
    } catch (Exception $e) {
        return NULL;
    }
}

function bersihkanAngka($str) {
    return preg_replace('/[^0-9]/', '', $str);
}

// --- FUNGSI GENERATE ID MANUAL ---
function generateNewId($conn) {
    // Ambil ID tertinggi saat ini
    $q = mysqli_query($conn, "SELECT MAX(id_si) as max_id FROM tb_suamiistri");
    $row = mysqli_fetch_assoc($q);
    $max_id = $row['max_id'];

    // Jika null (belum ada data), mulai dari 1
    if (empty($max_id)) {
        $next_num = 1;
    } else {
        // Ambil angkanya saja (asumsi id_si hanya angka string '00001273')
        $next_num = intval($max_id) + 1;
    }

    // Format jadi 8 digit (sesuai screenshot Anda: 00001273)
    return str_pad($next_num, 8, "0", STR_PAD_LEFT);
}

try {
    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File koneksi tidak ditemukan");
    include $path_koneksi;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid Request");
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // --- 1. PREVIEW DATA EXCEL ---
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");
        
        $file = $_FILES['file_excel'];
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        if (count($rows) <= 1) throw new Exception("File Excel kosong");
        $header = array_shift($rows); 

        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm text-nowrap" style="font-size: 0.9em;">';
        $html .= '<thead class="bg-primary text-white"><tr>';
        $html .= '<th>No</th>';
        foreach ($header as $col) $html .= '<th>' . htmlspecialchars($col) . '</th>';
        $html .= '</tr></thead><tbody>';

        $limit = 10; $count = 0; $no = 1;
        foreach ($rows as $row) {
            if ($count >= $limit) break;
            if (empty($row[0])) continue; 

            $html .= '<tr>';
            $html .= '<td>' . $no++ . '</td>';
            foreach ($row as $index => $cell) {
                if ($index == 1 || $index == 10) $html .= '<td>' . htmlspecialchars(bersihkanAngka($cell)) . '</td>';
                else $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
            $count++;
        }
        $html .= '</tbody></table></div>';
        
        if (count($rows) > $limit) $html .= '<div class="alert alert-info py-2 mt-2"><i class="fas fa-info-circle"></i> Menampilkan 10 data awal.</div>';

        $html .= '<hr><div class="text-right">';
        $html .= '<button type="button" class="btn btn-primary" id="btnSimpanPasangan"><i class="fas fa-save"></i> Proses Simpan & Update</button>';
        $html .= '</div>';
        
        $json_rows = json_encode($rows);
        $html .= '<textarea id="json_data_pasangan" style="display:none;">' . $json_rows . '</textarea>';

        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // --- 2. PROSES SAVE (MANUAL ID & UPSERT) ---
    elseif ($action === 'save') {
        if (!isset($_POST['data_pasangan'])) throw new Exception("Data tidak diterima");
        $data = json_decode($_POST['data_pasangan'], true);
        
        $total_insert = 0;
        $total_update = 0;
        $gagal = 0;
        $list_gagal = [];

        // Kunci tabel agar generate ID tidak bentrok jika banyak user akses bersamaan
        mysqli_query($conn, "LOCK TABLES tb_suamiistri WRITE, tb_pegawai READ");

        // Ambil counter ID terakhir sekali saja di awal
        $q_max = mysqli_query($conn, "SELECT MAX(id_si) as max_id FROM tb_suamiistri");
        $r_max = mysqli_fetch_assoc($q_max);
        $current_max_id = intval($r_max['max_id']); // Konversi ke integer

        foreach ($data as $row) {
            $id_peg = isset($row[0]) ? mysqli_real_escape_string($conn, trim($row[0])) : '';
            if(empty($id_peg)) continue;

            // Cek Pegawai Ada
            $cek_peg = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_peg'");
            
            if (mysqli_num_rows($cek_peg) > 0) {
                // DATA MAPPING
                $nik            = isset($row[1]) ? mysqli_real_escape_string($conn, bersihkanAngka($row[1])) : '';
                $nama           = isset($row[2]) ? mysqli_real_escape_string($conn, trim($row[2])) : '';
                $tmp_lhr        = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
                $tgl_lhr        = isset($row[4]) ? formatTanggal($row[4]) : NULL;
                $pendidikan     = isset($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : '';
                $id_pekerjaan   = isset($row[6]) ? mysqli_real_escape_string($conn, $row[6]) : '';
                $pekerjaan      = isset($row[7]) ? mysqli_real_escape_string($conn, $row[7]) : '';
                $status_hub     = isset($row[8]) ? mysqli_real_escape_string($conn, $row[8]) : '';
                $hp             = isset($row[9]) ? mysqli_real_escape_string($conn, bersihkanAngka($row[9])) : '';
                $bpjs           = isset($row[10]) ? mysqli_real_escape_string($conn, bersihkanAngka($row[10])) : '';
                
                $date_reg       = date('Y-m-d H:i:s');
                $tgl_val        = ($tgl_lhr) ? "'$tgl_lhr'" : "NULL";

                // LOGIKA CEK DUPLIKAT (ID_PEG + NAMA)
                $cek_ada_sql = "SELECT id_si FROM tb_suamiistri WHERE id_peg = '$id_peg' AND nama = '$nama'";
                $cek_ada     = mysqli_query($conn, $cek_ada_sql);

                if (mysqli_num_rows($cek_ada) > 0) {
                    // --- UPDATE (ID Tidak Berubah) ---
                    $row_old = mysqli_fetch_assoc($cek_ada);
                    $id_target = $row_old['id_si'];

                    $query_update = "UPDATE tb_suamiistri SET 
                                        nik         = '$nik',
                                        tmp_lhr     = '$tmp_lhr',
                                        tgl_lhr     = $tgl_val,
                                        pendidikan  = '$pendidikan',
                                        id_pekerjaan= '$id_pekerjaan',
                                        pekerjaan   = '$pekerjaan',
                                        status_hub  = '$status_hub',
                                        hp          = '$hp',
                                        bpjs_pasangan = '$bpjs'
                                     WHERE id_si = '$id_target'";
                    
                    if (mysqli_query($conn, $query_update)) {
                        $total_update++;
                    } else {
                        $gagal++;
                    }

                } else {
                    // --- INSERT (GENERATE NEW ID) ---
                    
                    // Increment ID Manual
                    $current_max_id++; 
                    $new_id_si = str_pad($current_max_id, 8, "0", STR_PAD_LEFT);

                    $query_insert = "INSERT INTO tb_suamiistri (
                        id_si, id_peg, nik, nama, tmp_lhr, tgl_lhr, pendidikan, 
                        id_pekerjaan, pekerjaan, status_hub, hp, bpjs_pasangan, date_reg
                    ) VALUES (
                        '$new_id_si', '$id_peg', '$nik', '$nama', '$tmp_lhr', $tgl_val, '$pendidikan',
                        '$id_pekerjaan', '$pekerjaan', '$status_hub', '$hp', '$bpjs', '$date_reg'
                    )";

                    if (mysqli_query($conn, $query_insert)) {
                        $total_insert++;
                    } else {
                        $gagal++;
                        // Jika gagal, kembalikan counter agar ID tidak loncat (opsional)
                        $current_max_id--; 
                    }
                }

            } else {
                $gagal++;
                if (!in_array($id_peg, $list_gagal)) { $list_gagal[] = $id_peg; }
            }
        }

        // Buka kunci tabel
        mysqli_query($conn, "UNLOCK TABLES");

        ob_clean();
        
        $pesan = "<b>Proses Selesai!</b><br>";
        $pesan .= "<span class='text-success'>Data Baru (Insert): $total_insert</span><br>";
        $pesan .= "<span class='text-info'>Data Diperbarui (Update): $total_update</span><br>";
        
        if($gagal > 0) {
            $pesan .= "<br><span class='text-danger'><b>Gagal:</b> $gagal baris.</span>";
        }

        echo json_encode(['status' => 'success', 'message' => $pesan]);
        exit;
    }

} catch (Exception $e) {
    // Pastikan unlock tables jika error
    if (isset($conn)) mysqli_query($conn, "UNLOCK TABLES");
    
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>