<?php
/*********************************************************
 * FILE    : pages/laporan/ajax-data-nominatif.php
 * MODULE  : Backend JSON Laporan Nominatif
 *********************************************************/

// 1. Matikan Error & Bersihkan Buffer (Wajib!)
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

if (session_id() == '') session_start();
include "../../dist/koneksi.php";

// 2. Ambil Parameter DataTables
$draw   = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
$search = isset($_GET['search']['value']) ? mysqli_real_escape_string($conn, $_GET['search']['value']) : '';

// 3. Ambil Parameter Filter
$f_status  = isset($_GET['f_status']) ? $_GET['f_status'] : '';
$f_unit    = isset($_GET['f_unit']) ? $_GET['f_unit'] : '';
$f_jabatan = isset($_GET['f_jabatan']) ? $_GET['f_jabatan'] : '';

// Hak Akses Kepala (Lock Unit Kerja)
$hak_akses   = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$kode_kantor = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
if ($hak_akses == 'kepala') {
    $f_unit = $kode_kantor; 
}

// 4. Query Builder
// Query ini mengambil jabatan terakhir dan pendidikan terakhir
$baseQuery = " FROM tb_pegawai p
               LEFT JOIN (
                   SELECT j1.id_peg, j1.jabatan, j1.unit_kerja, j1.tmt_jabatan 
                   FROM tb_jabatan j1
                   JOIN (SELECT id_peg, MAX(tmt_jabatan) AS max_tmt FROM tb_jabatan GROUP BY id_peg) j2 
                   ON j1.id_peg = j2.id_peg AND j1.tmt_jabatan = j2.max_tmt
                   WHERE j1.status_jab = 'Aktif'
               ) j ON p.id_peg = j.id_peg
               LEFT JOIN tb_pendidikan s ON p.id_peg = s.id_peg AND s.status = 'Akhir'
               LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail ";

$where = " WHERE p.status_aktif = 1 ";

// Filter Logic
if (!empty($f_status))  { $where .= " AND p.status_kepeg = '$f_status' "; }
if (!empty($f_unit))    { $where .= " AND j.unit_kerja = '$f_unit' "; }
if (!empty($f_jabatan)) { $where .= " AND j.jabatan = '$f_jabatan' "; }

// Search Logic
if (!empty($search)) {
    $where .= " AND (p.nama LIKE '%$search%' OR p.nip LIKE '%$search%' OR j.jabatan LIKE '%$search%') ";
}

// 5. Hitung Total Data
$qCount = mysqli_query($conn, "SELECT COUNT(*) as c $baseQuery $where");
$totalFiltered = ($r = mysqli_fetch_assoc($qCount)) ? $r['c'] : 0;

$qAll = mysqli_query($conn, "SELECT COUNT(*) as c FROM tb_pegawai WHERE status_aktif=1");
$totalAll = ($rAll = mysqli_fetch_assoc($qAll)) ? $rAll['c'] : 0;

// 6. Ambil Data
$sql = "SELECT p.nama, p.nip, p.status_kepeg,
               j.jabatan, j.tmt_jabatan, k.nama_kantor,
               s.jenjang, s.nama_sekolah
        $baseQuery
        $where
        ORDER BY p.nama ASC
        LIMIT $start, $length";

$query = mysqli_query($conn, $sql);
$data = array();
$no = $start + 1;

while ($row = mysqli_fetch_assoc($query)) {
    // Styling Badge Status
    $st = strtolower($row['status_kepeg']);
    $cls = 'secondary';
    if($st=='tetap') $cls='primary';
    if($st=='kontrak') $cls='warning';
    if(strpos($st,'calon')!==false) $cls='info';
    
    $badgeStatus = "<span class='badge badge-$cls px-2 py-1 rounded-pill'>{$row['status_kepeg']}</span>";
    
    // Format Tanggal TMT
    $tmt = ($row['tmt_jabatan'] && $row['tmt_jabatan']!='0000-00-00') ? date('d-m-Y', strtotime($row['tmt_jabatan'])) : '-';

    // Format Pendidikan
    $pend = $row['jenjang'] ? "<b>{$row['jenjang']}</b> - {$row['nama_sekolah']}" : "-";

    $data[] = array(
        'no'         => $no++,
        'nama'       => "<div class='fw-bold text-dark'>{$row['nama']}</div><small class='text-muted'>{$row['nip']}</small>",
        'jabatan'    => $row['jabatan'] ?: '-',
        'unit_kerja' => $row['nama_kantor'] ?: '-',
        'status'     => "<div class='text-center'>$badgeStatus</div>",
        'tmt'        => "<div class='text-center'>$tmt</div>",
        'pendidikan' => $pend
    );
}

// 7. Output JSON Bersih
ob_end_clean();
header('Content-Type: application/json');
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $totalAll,
    "recordsFiltered" => $totalFiltered,
    "data" => $data
]);
exit;
?>