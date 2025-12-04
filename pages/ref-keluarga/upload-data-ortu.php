<?php
// =============================================================
// FILE: pages/ref-ortu/upload-data-ortu.php
// LOGIC: Check (ID_PEG + STATUS_HUB) -> IF EXIST UPDATE, ELSE INSERT
// =============================================================

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(0);
ini_set('display_errors', 0);
ob_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

// --- FUNGSI BANTUAN ---
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

function bersihkanNik($nik) {
    return preg_replace('/[^0-9]/', '', $nik);
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
            if (empty($row[0])) continue; // Skip baris kosong

            $html .= '<tr>';
            $html .= '<td>' . $no++ . '</td>';
            foreach ($row as $index => $cell) {
                // Bersihkan tampilan NIK di preview
                if ($index == 1) $html .= '<td>' . htmlspecialchars(bersihkanNik($cell)) . '</td>';
                else $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
            $count++;
        }
        $html .= '</tbody></table></div>';
        
        $html .= '<div class="alert alert-info py-2 mt-2"><i class="fas fa-info-circle"></i> Menampilkan 10 data pertama. Klik Simpan untuk memproses semua data.</div>';
        $html .= '<hr><div class="text-right">';
        $html .= '<button type="button" class="btn btn-primary" id="btnSimpanOrtu"><i class="fas fa-save"></i> Proses Simpan & Update</button>';
        $html .= '</div>';
        
        $json_rows = json_encode($rows);
        $html .= '<textarea id="json_data_ortu" style="display:none;">' . $json_rows . '</textarea>';

        ob_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;
    }

    // --- 2. PROSES SAVE (INSERT OR UPDATE) ---
    elseif ($action === 'save') {
        if (!isset($_POST['data_ortu'])) throw new Exception("Data tidak diterima");
        $data = json_decode($_POST['data_ortu'], true);
        
        $total_insert = 0;
        $total_update = 0;
        $gagal = 0;
        $list_gagal = [];

        foreach ($data as $row) {
            // [0] ID Pegawai
            $id_peg = isset($row[0]) ? mysqli_real_escape_string($conn, trim($row[0])) : '';
            if(empty($id_peg)) continue;

            // Cek Validitas ID Pegawai
            $cek_peg = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_peg'");
            
            if (mysqli_num_rows($cek_peg) > 0) {
                // DATA PEGAWAI VALID
                
                // Siapkan Variabel Data
                $nik            = isset($row[1]) ? mysqli_real_escape_string($conn, bersihkanNik($row[1])) : '';
                $nama           = isset($row[2]) ? mysqli_real_escape_string($conn, $row[2]) : '';
                $tmp_lhr        = isset($row[3]) ? mysqli_real_escape_string($conn, $row[3]) : '';
                $tgl_lhr        = isset($row[4]) ? formatTanggal($row[4]) : NULL;
                $pendidikan     = isset($row[5]) ? mysqli_real_escape_string($conn, $row[5]) : '';
                $id_pekerjaan   = isset($row[6]) ? mysqli_real_escape_string($conn, $row[6]) : '';
                $pekerjaan      = isset($row[7]) ? mysqli_real_escape_string($conn, $row[7]) : '';
                $status_hub     = isset($row[8]) ? mysqli_real_escape_string($conn, $row[8]) : '';
                $date_reg       = date('Y-m-d H:i:s');

                // Siapkan Nilai Tanggal untuk SQL
                $tgl_val = ($tgl_lhr) ? "'$tgl_lhr'" : "NULL";

                // LOGIKA UTAMA: CEK BERDASARKAN ID_PEG DAN STATUS_HUB
                // Contoh: Apakah pegawai ini sudah punya "Ayah Kandung"?
                $cek_ada_sql = "SELECT id_ortu FROM tb_ortu WHERE id_peg = '$id_peg' AND status_hub = '$status_hub'";
                $cek_ada     = mysqli_query($conn, $cek_ada_sql);

                if (mysqli_num_rows($cek_ada) > 0) {
                    // --- KONDISI: DATA SUDAH ADA -> UPDATE ---
                    $row_old = mysqli_fetch_assoc($cek_ada);
                    $id_target = $row_old['id_ortu'];

                    $query_update = "UPDATE tb_ortu SET 
                                        nik = '$nik',
                                        nama = '$nama',
                                        tmp_lhr = '$tmp_lhr',
                                        tgl_lhr = $tgl_val,
                                        pendidikan = '$pendidikan',
                                        id_pekerjaan = '$id_pekerjaan',
                                        pekerjaan = '$pekerjaan'
                                        -- status_hub tidak diupdate karena itu kunci pengecekan
                                     WHERE id_ortu = '$id_target'";
                    
                    if (mysqli_query($conn, $query_update)) {
                        $total_update++;
                    } else {
                        $gagal++;
                    }

                } else {
                    // --- KONDISI: DATA BELUM ADA -> INSERT ---
                    $query_insert = "INSERT INTO tb_ortu (
                        id_peg, nik, nama, tmp_lhr, tgl_lhr, pendidikan, id_pekerjaan, pekerjaan, status_hub, date_reg
                    ) VALUES (
                        '$id_peg', '$nik', '$nama', '$tmp_lhr', $tgl_val, '$pendidikan', '$id_pekerjaan', '$pekerjaan', '$status_hub', '$date_reg'
                    )";

                    if (mysqli_query($conn, $query_insert)) {
                        $total_insert++;
                    } else {
                        $gagal++;
                    }
                }

            } else {
                // ID PEGAWAI TIDAK DITEMUKAN DI DATABASE
                $gagal++;
                if (!in_array($id_peg, $list_gagal)) {
                    $list_gagal[] = $id_peg; // Catat ID yang error
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
                $pesan .= "<br><small class='text-muted'>Kemungkinan ID Pegawai tidak ditemukan: $txt_ids</small>";
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
?>