<?php
/*********************************************************
 * FILE    : pages/ref-keluarga/ajax-data-anak.php
 * MODULE  : SIMPEG — Data Anak (AJAX DataTables)
 * VERSION : v1.1.1 (PHP 5.6)
 * DATE    : 2025-09-07
 * CHANGELOG
 * - v1.1.1: Hardening JSON (bersihkan buffer, matikan display_errors),
 *           set charset, exit; perbaiki fallback saat query gagal.
 *********************************************************/

if (session_id()==='') session_start();

/* ---- HENTIKAN SEMUA OUTPUT SAMPAH SEBELUM JSON ---- */
ini_set('display_errors', 0);           // jangan lempar notice ke output
ini_set('zend.assertions', -1);
while (ob_get_level()) { ob_end_clean(); }  // bersihkan buffer yang sudah terlanjur ada

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../dist/koneksi.php';   // menghasilkan $koneksi (mysqli)

function esc($s){ return mysqli_real_escape_string($GLOBALS['koneksi'], $s); }

$uid    = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$draw   = isset($_GET['draw'])  ? (int)$_GET['draw']  : 1;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$len    = isset($_GET['length'])? (int)$_GET['length']: 10;
$search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';

$where = " WHERE 1=1 ";
if ($uid !== '') {
  $where .= " AND a.id_peg='".esc($uid)."' ";
}
if ($search !== '') {
  $s = esc($search);
  $where .= " AND (a.nama LIKE '%$s%' OR a.pekerjaan LIKE '%$s%' OR a.pendidikan LIKE '%$s%') ";
}

/* Hitung total */
$total = 0;
$qCnt  = mysqli_query($conn, "SELECT COUNT(*) AS c FROM tb_anak a $where");
if ($qCnt) {
  $row = mysqli_fetch_assoc($qCnt);
  $total = (int)$row['c'];
}

/* Ambil data page */
$sql = "SELECT a.*, p.nama AS nama_peg
        FROM tb_anak a
        LEFT JOIN tb_pegawai p ON p.id_peg = a.id_peg
        $where
        ORDER BY a.id_anak DESC
        LIMIT $start,$len";
$q   = mysqli_query($conn, $sql);

$data = array();
$no   = $start + 1;

if ($q) {
  while ($r = mysqli_fetch_assoc($q)) {
    $data[] = array(
      'no'         => $no++,
      'idpeg_nama' => $r['id_peg'].' — '.($r['nama_peg']?:'-'),
      'id_peg'     => $r['id_peg'],
      'nama'       => $r['nama'],
      'tgl_lhr'    => $r['tgl_lhr'],
      'pendidikan' => $r['pendidikan'],
      'pekerjaan'  => $r['pekerjaan'],
      'status_hub' => $r['status_hub'],
      'anak_ke'    => $r['anak_ke'],
      'bpjs_anak'  => $r['bpjs_anak'],
    );
  }
}

/* Kembalikan JSON murni */
echo json_encode(array(
  'draw'            => $draw,
  'recordsTotal'    => $total,
  'recordsFiltered' => $total,
  'data'            => $data
), JSON_UNESCAPED_UNICODE);

exit;
