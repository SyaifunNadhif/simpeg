<?php
/*********************************************************
 * FILE    : pages/pegawai/ajax-data-pegawai.php
 * MODULE  : SIMPEG — DataTables Server-side (Pegawai Aktif)
 * VERSION : v1.5 (stabil lokal & server)
 *********************************************************/

/* ===== KERAS: jangan biarkan warning/echo merusak JSON ===== */
if (session_status() === PHP_SESSION_NONE) session_start();
ini_set('display_errors', 0);           // matikan echo error ke output
error_reporting(E_ALL);

while (ob_get_level()) { ob_end_clean(); } // bersihkan buffer sebelum include
ob_start();

require_once __DIR__ . "/../../dist/koneksi.php";  // harus set $conn (mysqli)

if (!isset($conn) || !($conn instanceof mysqli)) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(["draw"=>intval($_GET['draw'] ?? 0),"recordsTotal"=>0,"recordsFiltered"=>0,"data"=>[],"error"=>"Koneksi DB tidak tersedia."]);
  exit;
}

/* ===== Helper ===== */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** Resolve URL foto (uploads/foto -> pages/assets/foto -> fallback) */
function resolve_foto_url($filename, $jk){
  $baseDir    = dirname(__DIR__, 2);            // root app
  $newFs      = $baseDir . "/uploads/foto/";
  $oldFs      = $baseDir . "/pages/assets/foto/";
  $newUrlBase = "uploads/foto/";
  $oldUrlBase = "pages/assets/foto/";

  if ($filename) {
    if (file_exists($newFs . $filename)) return $newUrlBase . $filename;
    if (file_exists($oldFs . $filename)) return $oldUrlBase . $filename;
  }
  $fallback = ($jk === 'Laki-laki') ? 'no-foto-male.png' : 'no-foto-female.png';
  if (file_exists($oldFs . $fallback)) return $oldUrlBase . $fallback;
  if (file_exists($newFs . $fallback)) return $newUrlBase . $fallback;
  return $oldUrlBase . 'no-foto.png';
}

/* ===== Param DataTables ===== */
$draw   = intval($_GET['draw'] ?? 1);
$limit  = max(1, intval($_GET['length'] ?? 10));
$offset = max(0, intval($_GET['start']  ?? 0));
$search = trim($_GET['search']['value'] ?? '');
$orderColumnIndex = intval($_GET['order'][0]['column'] ?? 1);
$orderDir         = (strtolower($_GET['order'][0]['dir'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';
$filterUnit       = isset($_GET['unit_kerja']) ? $conn->real_escape_string($_GET['unit_kerja']) : '';

$isKepala   = isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) === 'kepala';
$kodeKantor = $_SESSION['kode_kantor'] ?? '';

/* ===== Mapping kolom DT -> kolom SQL VALID =====
   Foto/action tidak bisa diurutkan; fallback ke p.id_peg */
$dtColsToSql = [
  0 => 'p.id_peg',         // foto -> fallback
  1 => 'p.id_peg',
  2 => 'p.nama',
  3 => 'p.tgl_lhr',        // ttl -> sort by tanggal lahir
  4 => 'k.nama_kantor',    // unit_kerja (alias) -> nama_kantor
  5 => 'j.jabatan',
  6 => 'p.tmt_kerja',      // tgl_masuk -> tmt_kerja
  7 => 'p.telp',
  8 => 'p.id_peg',         // action -> fallback
];
$orderBy = $dtColsToSql[$orderColumnIndex] ?? 'p.id_peg';

/* ===== WHERE & params ===== */
$where = ["p.status_aktif = 1"];
if ($isKepala && $kodeKantor !== '') {
  $where[] = "j.unit_kerja = '" . $conn->real_escape_string($kodeKantor) . "'";
} elseif ($filterUnit !== '') {
  $where[] = "j.unit_kerja = '{$filterUnit}'";
}
if ($search !== '') {
  $s = $conn->real_escape_string($search);
  $where[] = "(p.id_peg LIKE '%{$s}%' OR p.nama LIKE '%{$s}%' OR j.jabatan LIKE '%{$s}%' OR k.nama_kantor LIKE '%{$s}%' OR p.telp LIKE '%{$s}%')";
}
$whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

/* ===== Query hitung total all ===== */
$resTotal = $conn->query("SELECT COUNT(*) AS c FROM tb_pegawai WHERE status_aktif = 1");
$recordsTotal = $resTotal ? intval($resTotal->fetch_assoc()['c']) : 0;

/* ===== Query hitung filtered ===== */
$sqlCount = "
  SELECT COUNT(*) AS c
  FROM tb_pegawai p
  LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
  LEFT JOIN tb_kantor  k ON j.unit_kerja = k.kode_kantor_detail
  $whereSql
";
$resCount = $conn->query($sqlCount);
if (!$resCount) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    "draw"=>$draw,"recordsTotal"=>$recordsTotal,"recordsFiltered"=>0,"data"=>[],
    "error"=>"SQL count error: ".$conn->error
  ]);
  exit;
}
$recordsFiltered = intval($resCount->fetch_assoc()['c']);

/* ===== Query data utama ===== */
$sql = "
  SELECT
    p.id_peg,
    p.nama,
    p.tempat_lhr,
    p.tgl_lhr,
    p.tmt_kerja,
    p.telp,
    p.foto,
    p.jk,
    j.jabatan,
    k.nama_kantor AS unit_kerja
  FROM tb_pegawai p
  LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
  LEFT JOIN tb_kantor  k ON j.unit_kerja = k.kode_kantor_detail
  $whereSql
  ORDER BY $orderBy $orderDir
  LIMIT $offset, $limit
";
$res = $conn->query($sql);
if (!$res) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    "draw"=>$draw,"recordsTotal"=>$recordsTotal,"recordsFiltered"=>$recordsFiltered,"data"=>[],
    "error"=>"SQL data error: ".$conn->error
  ]);
  exit;
}

/* ===== Build rows ===== */
$data = [];
while ($row = $res->fetch_assoc()) {
  $ttl     = h($row['tempat_lhr']).', '.date('d-m-Y', strtotime($row['tgl_lhr']));
  $fotoUrl = resolve_foto_url($row['foto'], $row['jk']);
  $foto    = '<img src="'.h($fotoUrl).'?cb='.time().'" class="rounded-circle" width="40" height="40" loading="lazy" style="object-fit:cover">';

  $btn = '<a href="home-admin.php?page=view-detail-data-pegawai&id_peg='.h($row['id_peg']).'" class="btn btn-sm btn-outline-info" title="Detail"><i class="fa fa-folder-open"></i></a>';
  if (!$isKepala) {
    $btn .= ' <a href="home-admin.php?page=form-master-data-pegawai&mode=edit&id='.h($row['id_peg']).'" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fa fa-edit"></i></a>';
  }

  $data[] = [
    "foto"       => $foto,
    "id_peg"     => h($row['id_peg']),
    "nama"       => h($row['nama']),
    "ttl"        => $ttl,
    "unit_kerja" => h($row['unit_kerja']),
    "jabatan"    => h($row['jabatan']),
    "tgl_masuk"  => date('d-m-Y', strtotime($row['tmt_kerja'])),
    "no_telp"    => h($row['telp']),
    "action"     => $btn
  ];
}

/* ===== Output JSON bersih ===== */
header('Content-Type: application/json; charset=utf-8');
ob_clean(); // buang sisa output dari koneksi.php (BOM/echo)
echo json_encode([
  "draw"            => $draw,
  "recordsTotal"    => $recordsTotal,
  "recordsFiltered" => $recordsFiltered,
  "data"            => $data
], JSON_UNESCAPED_UNICODE);
exit;
