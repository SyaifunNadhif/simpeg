<?php
// FILE: pages/ref-keluarga/ajax-data-anak.php
include '../../dist/koneksi.php'; // Sesuaikan path koneksi

// 1. Ambil Parameter DataTables
$draw   = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start  = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// 2. Ambil Parameter Filter (UID / ID Pegawai)
$uid = isset($_GET['uid']) ? mysqli_real_escape_string($conn, $_GET['uid']) : '';

// 3. Bangun Query WHERE
$where = " WHERE 1=1 ";

// Filter berdasarkan ID Pegawai (Wajib jika ada)
if (!empty($uid)) {
    $where .= " AND a.id_peg = '$uid' ";
}

// Filter Pencarian Global
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $where .= " AND (a.nama LIKE '%$search%' OR p.nama LIKE '%$search%') ";
}

// 4. Hitung Total Data
$sqlCount = "SELECT count(*) as total 
             FROM tb_anak a 
             LEFT JOIN tb_pegawai p ON a.id_peg = p.id_peg 
             $where";
$resCount = mysqli_query($conn, $sqlCount);
$rowC = mysqli_fetch_assoc($resCount);
$totalRecords = $rowC['total'];

// 5. Ambil Data Utama
// PENTING: Pastikan kolom 'id_anak' (Primary Key) terambil!
$sqlData = "SELECT a.*, p.nama as nama_pegawai 
            FROM tb_anak a 
            LEFT JOIN tb_pegawai p ON a.id_peg = p.id_peg 
            $where 
            ORDER BY a.tgl_lhr ASC 
            LIMIT $start, $length";

$resData = mysqli_query($conn, $sqlData);
$data = array();
$no = $start + 1;

while ($row = mysqli_fetch_assoc($resData)) {
    
    $nestedData = array();
    $nestedData['no']         = $no++;
    // Tampilkan Nama Pegawai + NIP
    $nestedData['idpeg_nama'] = '<strong>'.htmlspecialchars($row['nama_pegawai']).'</strong><br><small class="text-muted">'.htmlspecialchars($row['id_peg']).'</small>';
    
    $nestedData['nama']       = htmlspecialchars($row['nama']);
    $nestedData['tgl_lhr']    = ($row['tgl_lhr'] && $row['tgl_lhr']!='0000-00-00') ? date('d-m-Y', strtotime($row['tgl_lhr'])) : '-';
    $nestedData['pendidikan'] = $row['pendidikan'];
    $nestedData['pekerjaan']  = $row['pekerjaan'];
    $nestedData['status_hub'] = $row['status_hub'];
    $nestedData['anak_ke']    = $row['anak_ke'];
    $nestedData['bpjs_anak']  = $row['bpjs_anak'];

    // --- KUNCI AGAR TOMBOL EDIT MUNCUL ---
    // Kirim ID Primary Key (Sesuaikan nama kolom di database, misal: id_anak atau id)
    $nestedData['id_anak']    = isset($row['id_anak']) ? $row['id_anak'] : (isset($row['id']) ? $row['id'] : '');
    
    // Kirim ID Pegawai juga untuk tombol profil
    $nestedData['id_peg']     = $row['id_peg'];

    $data[] = $nestedData;
}

// 6. Return JSON
echo json_encode(array(
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $data
));
?>