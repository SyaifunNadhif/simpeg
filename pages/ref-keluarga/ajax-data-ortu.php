<?php
/*********************************************************
 * DIR     : pages/ref-keluarga/ajax-data-ortu.php
 * MODULE  : SIMPEG — Data Orang Tua Pegawai (tb_ortu)
 * VERSION : v1.4.4 (Force exact o.id_peg match when uid provided)
 *********************************************************/
if (session_id()==='') session_start();

/* ---- koneksi (fleksibel dist|config) ---- */
$__conn_ok = false;
$__paths = array(
  __DIR__ . '/../../dist/koneksi.php',
  __DIR__ . '/../../config/koneksi.php'
);
foreach ($__paths as $__p) {
  if (file_exists($__p)) { require_once $__p; $__conn_ok = true; break; }
}
if (!$__conn_ok) {
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array('draw'=>0,'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>array(),'error'=>'Koneksi tidak ditemukan.'));
  exit;
}

/* ---- pilih handle koneksi ---- */
$db = null;
if (isset($koneksi) && $koneksi) $db = $koneksi;
elseif (isset($conn) && $conn)   $db = $conn;

if (!$db) {
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array('draw'=>0,'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>array(),'error'=>'Variabel koneksi tidak tersedia.'));
  exit;
}

header('Content-Type: application/json; charset=UTF-8');

/* ---- helpers ---- */
function esc($c,$s){ return mysqli_real_escape_string($c, $s); }
function nv($v,$dash='-'){ return ($v!==null && $v!=='') ? $v : $dash; }
function getv($k,$d=''){ return isset($_GET[$k]) ? trim($_GET[$k]) : $d; }
function col_exists($conn,$table,$col){
  $q = mysqli_query($conn, "SHOW COLUMNS FROM {$table} LIKE '".mysqli_real_escape_string($conn,$col)."'");
  return $q && mysqli_num_rows($q)>0;
}

/* ---- DataTables params ---- */
$draw   = isset($_GET['draw'])  ? (int)$_GET['draw']  : 1;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$length = isset($_GET['length'])? (int)$_GET['length']: 10;
if ($length < 1) $length = 10; if ($length > 500) $length = 500;

$search = isset($_GET['search']['value']) ? esc($db, trim($_GET['search']['value'])) : '';
$filter_idpeg  = isset($_GET['filter_idpeg']) ? esc($db, trim($_GET['filter_idpeg'])) : '';
$filter_status = isset($_GET['filter_status']) ? esc($db, trim($_GET['filter_status'])) : '';
$uid           = getv('uid','');          // use exactly as provided, e.g. "K-102-065"
$ctx_idpeg     = getv('id_peg','');       // context id_peg (priority)
$dbg           = getv('dbg','');

$has_namalengkap = col_exists($db,'tb_pegawai','nama_lengkap');
$has_idpegcode   = col_exists($db,'tb_pegawai','id_peg_code');

$COL_NAMA = $has_namalengkap ? 'p.nama_lengkap' : 'p.nama';
$COL_IDPG = $has_idpegcode   ? 'p.id_peg_code'  : 'o.id_peg';

/* ---- mapping kolom sort ---- */
$columns = array(
  0 => 'o.id_ortu',
  1 => $COL_IDPG,
  2 => $COL_NAMA,
  3 => 'o.status_hub',
  4 => 'o.nama',
  5 => 'o.nik',
  6 => 'o.tgl_lhr',
  7 => 'o.pendidikan',
  8 => 'o.pekerjaan'
);
$order_col_index = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 1;
$order_col = isset($columns[$order_col_index]) ? $columns[$order_col_index] : $COL_IDPG;
$order_dir = (isset($_GET['order'][0]['dir']) && strtolower($_GET['order'][0]['dir'])==='desc') ? 'DESC' : 'ASC';

/* ---- base FROM/WHERE ---- */
$from  = ' FROM tb_ortu o LEFT JOIN tb_pegawai p ON p.id_peg = o.id_peg ';
$where = ' WHERE 1=1 ';

/* -- konteks: id_peg (prioritas) atau uid (fallback) --
   IMPORTANT: use exact match on o.id_peg to avoid accidental matches */
if ($ctx_idpeg !== '') {
  $where .= " AND o.id_peg = '".esc($db,$ctx_idpeg)."' ";
} elseif ($uid !== '') {
  // Use exactly o.id_peg = :uid (no broad OR/LIKE)
  $where .= " AND o.id_peg = '".esc($db,$uid)."' ";
}

/* -- filter kolom -- */
if ($filter_idpeg !== '')   { $where .= " AND (p.id_peg_code LIKE '%".esc($db,$filter_idpeg)."%' OR o.id_peg LIKE '%".esc($db,$filter_idpeg)."%') "; }
if ($filter_status !== '')  { $where .= " AND o.status_hub = '".esc($db,$filter_status)."' "; }

/* -- where tanpa search untuk total -- */
$where_no_search = $where;

/* -- search global -- */
if ($search !== '') {
  $like = esc($db, $search);
  $where .= " AND (
      {$COL_IDPG}  LIKE '%{$like}%' OR
      o.id_peg     LIKE '%{$like}%' OR
      {$COL_NAMA}  LIKE '%{$like}%' OR
      o.nama       LIKE '%{$like}%' OR
      o.nik        LIKE '%{$like}%' OR
      o.pendidikan LIKE '%{$like}%' OR
      o.pekerjaan  LIKE '%{$like}%'
  ) ";
}

/* ---- total & filtered ---- */
$q_total = mysqli_query($db, "SELECT COUNT(*) AS c {$from} {$where_no_search}");
$total = ($q_total && ($r=mysqli_fetch_assoc($q_total))) ? (int)$r['c'] : 0;

$q_filtered = mysqli_query($db, "SELECT COUNT(*) AS c {$from} {$where}");
$filtered = ($q_filtered && ($r=mysqli_fetch_assoc($q_filtered))) ? (int)$r['c'] : 0;

/* ---- data ---- */
$sql = "SELECT
          o.id_ortu,
          o.id_peg,
          {$COL_NAMA} AS nama_peg,
          {$COL_IDPG} AS id_peg_code,
          o.status_hub,
          o.nama AS nama_ortu,
          o.nik, o.tmp_lhr, o.tgl_lhr, o.pendidikan, o.pekerjaan
        {$from} {$where}
        ORDER BY {$order_col} {$order_dir}
        LIMIT {$start}, {$length}";
$res = mysqli_query($db, $sql);

$data = array();
if ($res) {
  while($row = mysqli_fetch_assoc($res)){
    $ttl = nv($row['tmp_lhr']).', '.( $row['tgl_lhr'] ? date('d-m-Y', strtotime($row['tgl_lhr'])) : '-' );

    // Prioritaskan nilai id_peg dari baris DB (o.id_peg) untuk header/links.
    // Jika kosong, fallback ke ctx_idpeg, lalu ke uid param.
    $raw_idpeg = isset($row['id_peg']) ? $row['id_peg'] : '';
    if ($raw_idpeg !== '') {
      $uid_formatted = $raw_idpeg;
    } elseif ($ctx_idpeg !== '') {
      $uid_formatted = $ctx_idpeg;
    } elseif ($uid !== '') {
      $uid_formatted = $uid;
    } else {
      $uid_formatted = '';
    }

    // buat link aksi: gunakan id_ortu dan uid_formatted (verbatis)
    $href_profile = 'home-admin.php?page=view-detail-data-pegawai&id_peg=' . rawurlencode($row['id_peg']);
    $href_edit = 'home-admin.php?page=form-master-data-ortu&mode=edit&id_ortu=' . rawurlencode($row['id_ortu'])
                 . ($uid_formatted !== '' ? '&uid=' . rawurlencode($uid_formatted) : '&id_peg=' . rawurlencode($row['id_peg']));

    $aksi  = '<a class="btn btn-xs btn-outline-info" title="Profil Pegawai" href="'.htmlspecialchars($href_profile,ENT_QUOTES,'UTF-8').'"><i class="fa fa-user"></i></a> ';
    $aksi .= '<a class="btn btn-xs btn-outline-primary" title="Edit" href="'.htmlspecialchars($href_edit,ENT_QUOTES,'UTF-8').'"><i class="fa fa-edit"></i></a>';

    $data[] = array(
      'id_ortu'     => (string)$row['id_ortu'],
      'uid'         => $uid_formatted,
      'aksi'        => $aksi,
      'id_peg'      => nv($row['id_peg_code']) ? nv($row['id_peg_code']) : nv($row['id_peg']),
      'nama_peg'    => nv($row['nama_peg']),
      'status_hub'  => nv($row['status_hub']),
      'nama_ortu'   => nv($row['nama_ortu']),
      'nik'         => nv($row['nik']),
      'ttl'         => $ttl,
      'pendidikan'  => nv($row['pendidikan']),
      'pekerjaan'   => nv($row['pekerjaan'])
    );
  }
} else if ($dbg==='1') {
  $data[] = array('id_peg'=>'','nama_peg'=>'','status_hub'=>'','nama_ortu'=>'','nik'=>'','ttl'=>'','pendidikan'=>'','pekerjaan'=>'','aksi'=>mysqli_error($db));
}

/* ---- bersihkan output liar lalu kirim JSON ---- */
if (function_exists('ob_get_length') && ob_get_length()) { ob_clean(); }

echo json_encode(array(
  'draw' => $draw,
  'recordsTotal' => $total,
  'recordsFiltered' => $filtered,
  'data' => $data
));
