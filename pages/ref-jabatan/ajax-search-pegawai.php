<?php
// pages/ref-jabatan/ajax_search_pegawai.php
// Return JSON untuk Select2 AJAX (search pegawai)

if (session_id() === '') session_start();
header('Content-Type: application/json; charset=utf-8');

@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn)) {
    @include_once __DIR__ . '/../../config/koneksi.php';
    $conn = isset($koneksi) ? $koneksi : null;
}

if (!isset($conn) || !$conn) {
    echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
    exit;
}

function clean($c, $s) { return mysqli_real_escape_string($c, trim($s)); }

$q    = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit  = 20;
$offset = ($page - 1) * $limit;

$where = "1=1";
if ($q !== '') {
    $qq = clean($conn, $q);
    // cari by id_peg atau nama
    $where = "(id_peg LIKE '%$qq%' OR nama LIKE '%$qq%')";
}

// ambil 1 ekstra untuk cek apakah masih ada next page
$sql = "SELECT id_peg, nama
        FROM tb_pegawai
        WHERE $where
        ORDER BY nama ASC
        LIMIT " . ($limit + 1) . " OFFSET $offset";

$rs = mysqli_query($conn, $sql);

$results = [];
$count = 0;

if ($rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        $count++;
        if ($count <= $limit) {
            $results[] = [
                'id'   => $row['id_peg'],
                'text' => $row['id_peg'] . ' - ' . $row['nama'],
            ];
        }
    }
}

$more = ($count > $limit);

echo json_encode([
    'results' => $results,
    'pagination' => ['more' => $more],
]);
