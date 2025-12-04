<?php
// FILE: pages/ref-sertifikasi/ajax-data-sertifikasi.php
include '../../dist/koneksi.php'; 

// 1. Ambil Parameter DataTables
$draw   = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start  = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// 2. Ambil Parameter Filter
$uid      = isset($_GET['uid']) ? mysqli_real_escape_string($conn, $_GET['uid']) : '';
$f_sertif = isset($_GET['f_sertif']) ? mysqli_real_escape_string($conn, $_GET['f_sertif']) : '';
$f_tahun  = isset($_GET['f_tahun']) ? mysqli_real_escape_string($conn, $_GET['f_tahun']) : '';

// 3. Query WHERE
$where = " WHERE 1=1 ";
if (!empty($uid)) { $where .= " AND s.id_peg = '$uid' "; }
if (!empty($f_sertif)) { $where .= " AND s.sertifikasi = '$f_sertif' "; }
if (!empty($f_tahun)) { $where .= " AND YEAR(s.tgl_sertifikat) = '$f_tahun' "; }
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $where .= " AND (p.nama LIKE '%$search%' OR s.sertifikasi LIKE '%$search%' OR s.penyelenggara LIKE '%$search%') ";
}

// 4. Hitung Total
$sqlCount = "SELECT count(*) as total FROM tb_sertifikasi s JOIN tb_pegawai p ON s.id_peg = p.id_peg $where";
$resCount = mysqli_query($conn, $sqlCount);
$rowC = mysqli_fetch_assoc($resCount);
$totalRecords = $rowC['total'];

// 5. Ambil Data
$sqlData = "SELECT s.*, p.nama as nama_pegawai 
            FROM tb_sertifikasi s 
            JOIN tb_pegawai p ON s.id_peg = p.id_peg 
            $where 
            ORDER BY s.tgl_sertifikat DESC 
            LIMIT $start, $length";

$resData = mysqli_query($conn, $sqlData);
$data = array();
$no = $start + 1;
$today = date('Y-m-d');

while ($row = mysqli_fetch_assoc($resData)) {
    // Status Badge
    if (!empty($row['tgl_expired']) && $row['tgl_expired'] != '0000-00-00') {
        if ($row['tgl_expired'] < $today) {
            $sb = '<span class="badge bg-danger">Expired</span>';
        } else {
            $sb = '<span class="badge bg-success">Aktif</span>';
        }
    } else {
        $sb = '<span class="badge bg-info text-dark">Permanen</span>';
    }

    $nestedData = array();
    $nestedData['no']             = $no++;
    $nestedData['idpeg_nama']     = '<strong>' . $row['nama_pegawai'] . '</strong><br><small class="text-muted">' . $row['id_peg'] . '</small>';
    $nestedData['sertifikasi']    = $row['sertifikasi'];
    $nestedData['penyelenggara']  = $row['penyelenggara'] ? $row['penyelenggara'] : '-';
    $nestedData['tgl_expired']    = ($row['tgl_expired'] && $row['tgl_expired']!='0000-00-00') ? date('d-m-Y', strtotime($row['tgl_expired'])) : '-';
    $nestedData['status_badge']   = $sb;
    $nestedData['sertifikat']     = $row['sertifikat'] ? $row['sertifikat'] : '-';
    $nestedData['tgl_sertifikat'] = ($row['tgl_sertifikat'] && $row['tgl_sertifikat']!='0000-00-00') ? date('d-m-Y', strtotime($row['tgl_sertifikat'])) : '-';
    
    // --- PERBAIKAN DI SINI (Gunakan id_sertif) ---
    $nestedData['id_sertif']      = $row['id_sertif']; 

    $data[] = $nestedData;
}

echo json_encode(array(
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $data
));
?>