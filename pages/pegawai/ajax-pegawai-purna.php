<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include "../../dist/koneksi.php";

// Kolom yang akan digunakan untuk pengurutan
$columns = ['id_peg', 'nama', 'ttl', 'jabatan', 'status_kepeg', 'tgl_pensiun', 'telp', 'action'];

$limit = isset($_GET['length']) ? intval($_GET['length']) : 10;
$offset = isset($_GET['start']) ? intval($_GET['start']) : 0;
$search = isset($_GET['search']['value']) ? mysqli_real_escape_string($conn, $_GET['search']['value']) : '';
$orderColumnIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
$orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'nama';
$orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'asc';
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;

// WHERE dan filter user kepala
$where = "WHERE m.jns_mutasi IS NOT NULL";
if (isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) == 'kepala') {
    $kode_kantor = mysqli_real_escape_string($conn, $_SESSION['kode_kantor']);
    $where .= " AND j.unit_kerja = '$kode_kantor'";
}

// Tambahan filter pencarian
$filter = "";
if ($search !== '') {
    $filter .= " AND (
        p.nama LIKE '%$search%' OR
        p.id_peg LIKE '%$search%' OR
        j.jabatan LIKE '%$search%' OR
        m.jns_mutasi LIKE '%$search%'
    )";
}

// SQL utama
$sql = "FROM tb_pegawai p
LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
LEFT JOIN (
  SELECT id_peg, jns_mutasi, MAX(tgl_mutasi) AS tgl_mutasi
  FROM tb_mutasi
  WHERE jns_mutasi IN ('Pensiun', 'Pensiun Dini', 'Meninggal Dunia', 'Pengunduran Diri', 'PTDH')
  GROUP BY id_peg
) m ON p.id_peg = m.id_peg
$where $filter";

// Hitung total filtered
$sqlFiltered = "SELECT COUNT(*) AS total $sql";
$resultFiltered = mysqli_query($conn, $sqlFiltered);
$rowFiltered = mysqli_fetch_assoc($resultFiltered);
$totalFiltered = isset($rowFiltered['total']) ? $rowFiltered['total'] : 0;

// Data utama
$sqlData = "SELECT 
    p.id_peg, 
    p.nama, 
    p.tempat_lhr, 
    p.tgl_lhr,
    j.jabatan,
    m.jns_mutasi,
    m.tgl_mutasi,
    p.telp 
    $sql ORDER BY $orderColumn $orderDir LIMIT $offset, $limit";
$result = mysqli_query($conn, $sqlData);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ttl = $row['tempat_lhr'] . ', ' . date('d-m-Y', strtotime($row['tgl_lhr']));
    $tgl_pensiun = $row['tgl_mutasi'] ? date('d-m-Y', strtotime($row['tgl_mutasi'])) : '-';

    $data[] = [
        'id_peg' => $row['id_peg'],
        'nama' => $row['nama'],
        'ttl' => $ttl,
        'jabatan' => isset($row['jabatan']) ? $row['jabatan'] : '-',
        'status_kepeg' => $row['jns_mutasi'],
        'tgl_pensiun' => $tgl_pensiun,
        'telp' => $row['telp'],
        'action' => '<a href="home-admin.php?page=view-detail-data-pegawai&id_peg=' . $row['id_peg'] . '" class="btn btn-sm btn-outline-info" title="Detail"><i class="fa fa-folder-open"></i></a>'
    ];
}

// Total semua
$sqlTotal = "SELECT COUNT(DISTINCT p.id_peg) AS total 
FROM tb_pegawai p 
JOIN tb_mutasi m ON p.id_peg = m.id_peg 
WHERE m.jns_mutasi IN ('Pensiun', 'Pensiun Dini', 'Meninggal Dunia', 'Pengunduran Diri', 'PTDH')";
$resTotal = mysqli_query($conn, $sqlTotal);
$rowTotal = mysqli_fetch_assoc($resTotal);
$totalAll = isset($rowTotal['total']) ? $rowTotal['total'] : 0;

// Output JSON
header('Content-Type: application/json');
echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $totalAll,
    'recordsFiltered' => $totalFiltered,
    'data' => $data
]);
?>
