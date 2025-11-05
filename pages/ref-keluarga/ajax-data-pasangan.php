<?php
/*********************************************************
 * FILE    : pages/ref-keluarga/ajax-data-pasangan.php
 * MODULE  : SIMPEG — Data Pasangan (DataTables Server-side)
 * VERSION : v1.0 (PHP 5.6)
 * DATE    : 2025-10-11
 * AUTHOR  : ChatGPT (SIMPEG Assistant)
 * COMPAT  : PHP 5.6, MySQL 5.5
 * PURPOSE : Endpoint server-side untuk DataTables pada
 *           form-view-data-suami-istri.php
 *
 * INPUT (GET)
 *   - draw, start, length, search[value], order[0][column], order[0][dir]
 *   - uid (opsional): filter berdasarkan id_peg
 *
 * OUTPUT (JSON)
 *   {
 *     draw: <int>,
 *     recordsTotal: <int>,
 *     recordsFiltered: <int>,
 *     data: [
 *       { no, id_peg, nama_peg, nama, nik, pendidikan, pekerjaan_desc, status_hub, aksi }
 *     ]
 *   }
 *********************************************************/
if (session_id()==='') session_start();
header('Content-Type: application/json; charset=utf-8');

/* === Koneksi standar proyek === */
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($koneksi)){
  // fallback lama jika proyek masih pakai $conn
  @include_once __DIR__ . '/../../config/koneksi.php';
  if (isset($conn) && !isset($koneksi)) $koneksi = $conn;
}
if (!isset($koneksi)){
  echo json_encode(array('draw'=>0,'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>array()));
  exit;
}

/* === Helpers === */
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function req($key,$def=''){ return isset($_GET[$key]) ? $_GET[$key] : $def; }

$draw   = (int) req('draw', 1);
$start  = (int) req('start', 0);
$length = (int) req('length', 10);
if ($length < 1) $length = 10; if ($length > 500) $length = 500; // guard
$uid    = preg_replace('~[^A-Za-z0-9_\-]~','', req('uid',''));
$search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';

/* === Mapping kolom untuk ordering (index DataTables) === */
// Kolom pada tabel: [0]No [1]id_peg [2]nama_peg [3]nama [4]nik [5]pendidikan [6]pekerjaan_desc [7]status_hub [8]aksi
$columnsMap = array(
  1 => 'si.id_peg',
  2 => 'p.nama',
  3 => 'si.nama',
  4 => 'si.nik',
  5 => 'si.pendidikan',
  6 => 'mp.desc_pekerjaan',
  7 => 'si.status_hub'
);
$orderIdx = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 2;
$orderDir = isset($_GET['order'][0]['dir']) ? strtolower($_GET['order'][0]['dir']) : 'asc';
if (!in_array($orderDir, array('asc','desc'))) $orderDir = 'asc';
$orderCol = isset($columnsMap[$orderIdx]) ? $columnsMap[$orderIdx] : 'p.nama';

/* === Base FROM & JOIN === */
$from = " FROM tb_suamiistri si
          LEFT JOIN tb_pegawai p ON p.id_peg = si.id_peg
          LEFT JOIN tb_master_pekerjaan mp ON mp.id_pekerjaan = si.pekerjaan ";

/* === WHERE conditions === */
$where = array();
$params = array();
if ($uid !== ''){
  $where[] = "si.id_peg='".mysqli_real_escape_string($koneksi,$uid)."'";
}
if ($search !== ''){
  $s = mysqli_real_escape_string($koneksi, $search);
  $like = "(p.nama LIKE '%$s%' OR si.nama LIKE '%$s%' OR si.nik LIKE '%$s%' OR si.pendidikan LIKE '%$s%' OR COALESCE(mp.desc_pekerjaan,'') LIKE '%$s%' OR si.status_hub LIKE '%$s%')";
  $where[] = $like;
}
$whereSql = count($where) ? (' WHERE '.implode(' AND ', $where)) : '';

/* === Query counts === */
// Total tanpa filter
$sqlTotal = "SELECT COUNT(*) AS jml FROM tb_suamiistri si"; // tanpa join biar cepat
$resTotal = mysqli_query($koneksi, $sqlTotal);
$rowTotal = ($resTotal? mysqli_fetch_assoc($resTotal): array('jml'=>0));
$recordsTotal = (int) $rowTotal['jml'];

// Total dengan filter
$sqlFiltered = "SELECT COUNT(*) AS jml " . $from . $whereSql;
$resFiltered = mysqli_query($koneksi, $sqlFiltered);
$rowFiltered = ($resFiltered? mysqli_fetch_assoc($resFiltered): array('jml'=>0));
$recordsFiltered = (int) $rowFiltered['jml'];

/* === Data query (paged) === */
$select = "SELECT si.id_si, si.id_peg, si.nama, si.nik, si.pendidikan, si.status_hub,
                  COALESCE(mp.desc_pekerjaan, si.pekerjaan) AS pekerjaan_desc,
                  p.nama AS nama_peg ";
$sqlData = $select . $from . $whereSql . " ORDER BY $orderCol $orderDir LIMIT ".$start.", ".$length;
$resData = mysqli_query($koneksi, $sqlData);

$data = array();
$no = $start + 1;
if ($resData){
  while ($r = mysqli_fetch_assoc($resData)){
    $id_peg = $r['id_peg'];
    $id_si  = $r['id_si'];
    $aksi = '<div class="btn-group btn-group-sm" role="group">'
          . '<a class="btn btn-primary" title="Detail Pegawai" href="home-admin.php?page=view-detail-data-pegawai&id_peg='.rawurlencode($id_peg).'"><i class="fa fa-folder-open"></i></a>'
          . '<a class="btn btn-warning" title="Edit Pasangan" href="home-admin.php?page=form-master-data-suami-istri&mode=edit&id_si='.rawurlencode($id_si).'"><i class="fa fa-edit"></i></a>'
          . '</div>';

    $data[] = array(
      'no'             => $no++,
      'id_peg'         => e($r['id_peg']),
      'nama_peg'       => e($r['nama_peg']),
      'nama'           => e($r['nama']),
      'nik'            => e($r['nik']),
      'pendidikan'     => e($r['pendidikan']),
      'pekerjaan_desc' => e($r['pekerjaan_desc']),
      'status_hub'     => e($r['status_hub']),
      'aksi'           => $aksi
    );
  }
}

$out = array(
  'draw' => $draw,
  'recordsTotal' => $recordsTotal,
  'recordsFiltered' => $recordsFiltered,
  'data' => $data
);

echo json_encode($out);
exit;
