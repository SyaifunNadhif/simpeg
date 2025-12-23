<?php
// 1. Matikan error display agar path file tidak bocor ke user
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include "../../dist/koneksi.php";

// 2. KEAMANAN: Cek apakah user sudah login?
if (empty($_SESSION['id_user'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Akses ditolak']));
}

// Helper untuk mencegah XSS (Output Encoding)
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Kolom yang diizinkan untuk sorting (Whitelist)
$columns = ['p.id_peg', 'p.nama', 'p.tempat_lhr', 'j.jabatan', 'm.jns_mutasi', 'm.tgl_mutasi', 'p.telp', 'action'];

// Input Validation & Sanitization
$limit  = isset($_GET['length']) ? intval($_GET['length']) : 10;
$offset = isset($_GET['start']) ? intval($_GET['start']) : 0;
$search = isset($_GET['search']['value']) ? mysqli_real_escape_string($conn, $_GET['search']['value']) : '';
$draw   = isset($_GET['draw']) ? intval($_GET['draw']) : 1;

// Sorting Logic (Lebih Aman)
$orderColumnIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 1; // Default ke nama
$orderColumn      = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'p.nama';
// Validasi arah sort (ASC/DESC saja)
$orderDirRaw      = isset($_GET['order'][0]['dir']) ? strtolower($_GET['order'][0]['dir']) : 'asc';
$orderDir         = ($orderDirRaw === 'desc') ? 'DESC' : 'ASC';

// 3. LOGIKA QUERY DASAR
// Kita gunakan subquery mutasi agar data tidak duplikat
$sqlBase = "
    FROM tb_pegawai p
    LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
    JOIN (
        SELECT id_peg, jns_mutasi, MAX(tgl_mutasi) AS tgl_mutasi
        FROM tb_mutasi
        WHERE jns_mutasi IN ('Pensiun', 'Pensiun Dini', 'Meninggal Dunia', 'Pengunduran Diri', 'PTDH')
        GROUP BY id_peg
    ) m ON p.id_peg = m.id_peg
    WHERE 1=1 
";
// Catatan: Saya ganti LEFT JOIN ke JOIN pada tabel 'm' karena WHERE clause Anda (m.jns_mutasi IS NOT NULL)
// secara efektif membuatnya jadi INNER JOIN. Ini lebih cepat secara performa.

// Filter Kepala Cabang
if (isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) == 'kepala') {
    $kode_kantor = mysqli_real_escape_string($conn, $_SESSION['kode_kantor']);
    $sqlBase .= " AND j.unit_kerja = '$kode_kantor'";
}

// Simpan query dasar untuk menghitung Total Records (Tanpa filter search)
$sqlTotal = "SELECT COUNT(*) AS total " . $sqlBase;
$resTotal = mysqli_query($conn, $sqlTotal);
$rowTotal = mysqli_fetch_assoc($resTotal);
$totalAll = isset($rowTotal['total']) ? intval($rowTotal['total']) : 0;

// 4. FILTER PENCARIAN
if ($search !== '') {
    $sqlBase .= " AND (
        p.nama LIKE '%$search%' OR
        p.id_peg LIKE '%$search%' OR
        j.jabatan LIKE '%$search%' OR
        m.jns_mutasi LIKE '%$search%'
    )";
}

// Hitung Total Filtered (Setelah kena search)
$sqlFiltered = "SELECT COUNT(*) AS total " . $sqlBase;
$resultFiltered = mysqli_query($conn, $sqlFiltered);
$rowFiltered = mysqli_fetch_assoc($resultFiltered);
$totalFiltered = isset($rowFiltered['total']) ? intval($rowFiltered['total']) : 0;

// 5. AMBIL DATA FINAL
$sqlData = "SELECT 
    p.id_peg, 
    p.nama, 
    p.tempat_lhr, 
    p.tgl_lhr,
    j.jabatan,
    m.jns_mutasi,
    m.tgl_mutasi,
    p.telp 
    " . $sqlBase . " 
    ORDER BY $orderColumn $orderDir 
    LIMIT $offset, $limit";

$result = mysqli_query($conn, $sqlData);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Format Tanggal
    $ttl = h($row['tempat_lhr']) . ', ' . ($row['tgl_lhr'] ? date('d-m-Y', strtotime($row['tgl_lhr'])) : '-');
    $tgl_pensiun = $row['tgl_mutasi'] ? date('d-m-Y', strtotime($row['tgl_mutasi'])) : '-';
    
    // Tombol Action
    $btnAction = '<a href="home-admin.php?page=view-detail-data-pegawai&id_peg=' . h($row['id_peg']) . '" class="btn btn-sm btn-outline-info" title="Detail"><i class="fa fa-folder-open"></i></a>';

    $data[] = [
        'id_peg'       => h($row['id_peg']),
        'nama'         => h($row['nama']),
        'ttl'          => $ttl,
        'jabatan'      => h($row['jabatan']),
        'status_kepeg' => h($row['jns_mutasi']),
        'tgl_pensiun'  => $tgl_pensiun,
        'telp'         => h($row['telp']),
        'action'       => $btnAction
    ];
}

// Output JSON
header('Content-Type: application/json');
echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $totalAll,
    'recordsFiltered' => $totalFiltered,
    'data'            => $data
]);
?>