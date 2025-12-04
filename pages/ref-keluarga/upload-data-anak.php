<?php
// =============================================================
// FILE: pages/ref-anak/upload-data-anak.php
// STATUS: LOGIC UPDATE (Check ID_PEG + NAMA -> Upsert)
// =============================================================

session_start();

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(0);
ini_set('display_errors', 0);
ob_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

// Fungsi Format Tanggal
function formatTanggal($date) {
    if (empty($date) || $date == '-' || $date == '') return NULL;
    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
        return $date;
    }
    try {
        // Handle Excel numeric date
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

try {
    $path_koneksi = '../../dist/koneksi.php'; 
    if (!file_exists($path_koneksi)) throw new Exception("File koneksi tidak ditemukan");
    include $path_koneksi;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid Request");
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // --- PREVIEW ---
    if ($action === 'preview') {
        if (!isset($_FILES['file_excel'])) throw new Exception("File belum dipilih");
        
        $file = $_FILES['file_excel'];
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        if (count($rows) <= 1) throw new Exception("File Excel kosong");
        $header = array_shift($rows); 

        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm text-nowrap" style="font-size: 0.9em;">';
        $html .= '<thead class="bg-success"><tr>';
        $html .= '<th>No</th>';
        foreach ($header as $col) $html .= '<th>' . htmlspecialchars($col) . '</th>';
        $html .= '</tr></thead><tbody>';

        $limit = 10; $count = 0; $no = 1;
        foreach ($rows as $row) {
            if ($count >= $limit) break;
            // Skip baris jika kolom pertama kosong
            if (empty($row[0])) continue;

            $html .= '<tr>';
            $html .= '<td>' . $no++ . '</td>';
            foreach ($row as $cell) $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            $html .= '</tr>';
            $count++;
        }
        $html .= '</tbody></table></div>';
        
        if (count($rows) > $limit) $html .= '<div class="alert alert-info py-2 mt-2"><i class="fas fa-info-circle"></i> Menampilkan 10 data awal.</div>';

        $html .= '<hr><div class="text-right">';
        $html .= '<button type="button" class="btn btn-success" id="btnSimpanAnak"><i class="fas fa-save"></i> Proses Simpan & Update</button>';
        $html .= '</div>';
        
        $json_rows = json_encode($rows);
        $html .= '<textarea id="json_data_anak" style="display:none;">' . $json_rows . '</textarea>';

        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // --- SAVE (INSERT OR UPDATE) ---
    elseif ($action === 'save') {
        if (!isset($_POST['data_anak'])) throw new Exception("Data tidak diterima");
        $data = json_decode($_POST['data_anak'], true);
        
        $current_user = isset($_SESSION['nama_user']) ? $_SESSION['nama_user'] : 'System';
        
        $total_insert = 0;
        $total_update = 0;
        $gagal = 0;
        $list_gagal = [];

        foreach ($data as $row) {
            // [0] ID Pegawai
            $id_peg = isset($row[0]) ? mysqli_real_escape_string($conn, trim($row[0])) : '';
            if(empty($id_peg)) continue;

            // 1. Cek Validitas Pegawai (Parent)
            $cek_peg = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_peg'");
            
            if (mysqli_num_rows($cek_peg) > 0) {
                // PEGAWAI VALID
                
                // Siapkan Variabel Data
                $nik            = isset($row[1]) ? mysqli_real_escape_string($conn, $row[1]) : '';
                $nama           = isset($row[2]) ? mysqli_real_escape_string($conn, trim($row[2])) : ''; // Trim nama untuk akurasi
                $tmp_lhr        = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
                $tgl_lhr        = isset($row[4]) ? formatTanggal($row[4]) : NULL;
                $pendidikan     = isset($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : '';
                $id_pekerjaan   = isset($row[6]) ? mysqli_real_escape_string($conn, $row[6]) : '';
                $pekerjaan      = isset($row[7]) ? mysqli_real_escape_string($conn, $row[7]) : '';
                $status_hub     = isset($row[8]) ? mysqli_real_escape_string($conn, $row[8]) : '';
                $anak_ke        = isset($row[9]) ? (int)$row[9] : 0;
                $bpjs_anak      = isset($row[10]) ? mysqli_real_escape_string($conn, $row[10]) : '';
                
                $date_reg       = date('Y-m-d H:i:s');
                $tgl_val        = ($tgl_lhr) ? "'$tgl_lhr'" : "NULL";

                // 2. CEK APAKAH DATA ANAK SUDAH ADA? (Based on ID_PEG + NAMA)
                // Kita gunakan nama sebagai kunci kedua karena anak belum tentu punya NIK yg unik di awal
                $cek_anak_sql = "SELECT id_anak FROM tb_anak WHERE id_peg = '$id_peg' AND nama = '$nama'";
                $cek_anak     = mysqli_query($conn, $cek_anak_sql);

                if (mysqli_num_rows($cek_anak) > 0) {
                    // --- KONDISI: DATA SUDAH ADA -> UPDATE ---
                    $row_old = mysqli_fetch_assoc($cek_anak);
                    $id_target = $row_old['id_anak'];

                    $query_update = "UPDATE tb_anak SET 
                                        nik         = '$nik',
                                        tmp_lhr     = '$tmp_lhr',
                                        tgl_lhr     = $tgl_val,
                                        pendidikan  = '$pendidikan',
                                        id_pekerjaan= '$id_pekerjaan',
                                        pekerjaan   = '$pekerjaan',
                                        status_hub  = '$status_hub',
                                        anak_ke     = '$anak_ke',
                                        bpjs_anak   = '$bpjs_anak'
                                        -- Nama tidak diupdate karena itu kunci pencarian
                                     WHERE id_anak = '$id_target'";
                    
                    if (mysqli_query($conn, $query_update)) {
                        $total_update++;
                    } else {
                        $gagal++;
                    }

                } else {
                    // --- KONDISI: DATA BELUM ADA -> INSERT ---
                    $query_insert = "INSERT INTO tb_anak (
                        id_peg, nik, nama, tmp_lhr, tgl_lhr, pendidikan, 
                        id_pekerjaan, pekerjaan, status_hub, anak_ke, bpjs_anak, date_reg
                    ) VALUES (
                        '$id_peg', '$nik', '$nama', '$tmp_lhr', $tgl_val, '$pendidikan',
                        '$id_pekerjaan', '$pekerjaan', '$status_hub', '$anak_ke', '$bpjs_anak', '$date_reg'
                    )";

                    if (mysqli_query($conn, $query_insert)) {
                        $total_insert++;
                    } else {
                        $gagal++;
                    }
                }

            } else {
                // ID PEGAWAI TIDAK DITEMUKAN
                $gagal++;
                if (!in_array($id_peg, $list_gagal)) {
                    $list_gagal[] = $id_peg;
                }
            }
        }

        ob_clean();
        
        $pesan = "<b>Proses Selesai!</b><br>";
        $pesan .= "<span class='text-success'>Data Baru (Insert): $total_insert</span><br>";
        $pesan .= "<span class='text-info'>Data Diperbarui (Update): $total_update</span><br>";
        
        if($gagal > 0) {
            $limit_msg = 5;
            $ids = array_slice($list_gagal, 0, $limit_msg);
            $sisa = count($list_gagal) - $limit_msg;
            $txt_ids = implode(", ", $ids);
            if($sisa > 0) $txt_ids .= " dan $sisa lainnya";

            $pesan .= "<br><span class='text-danger'><b>Gagal:</b> $gagal baris.</span>";
            if (!empty($list_gagal)) {
                $pesan .= "<br><small class='text-muted'>ID Pegawai tidak ditemukan: $txt_ids</small>";
            }
        }

        echo json_encode(['status' => 'success', 'message' => $pesan]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}