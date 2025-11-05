<?php
/*********************************************************
 * FILE    : pages/ref-sertifikasi/ajax-data-sertifikasi.php
 * MODULE  : SIMPEG — Sertifikasi (DataTables server-side)
 * VERSION : v1.2 (PHP 5.6)
 * DATE    : 2025-09-07
 * CHANGELOG
 * - v1.2: Dukung filter f_sertif & f_tahun; badge expired <6 bln.
 * - v1.1: Perapihan count.
 * - v1.0: Versi awal.
 *********************************************************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }
header('Content-Type: application/json; charset=utf-8');

function esc($s){ global $conn; return mysqli_real_escape_string($conn, trim($s)); }

$draw   = isset($_GET['draw'])   ? (int)$_GET['draw']   : 1;
$start  = isset($_GET['start'])  ? (int)$_GET['start']  : 0;
$length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
$search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

$uid      = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$f_sertif = isset($_GET['f_sertif']) ? esc($_GET['f_sertif']) : '';
$f_tahun  = isset($_GET['f_tahun'])  ? esc($_GET['f_tahun'])  : '';

$where = " WHERE 1=1 ";
if ($uid!=='') { $where .= " AND s.id_peg='".esc($uid)."' "; }
if ($search!=='') {
  $s = esc($search);
  $where .= " AND (s.sertifikasi LIKE '%$s%' OR s.penyelenggara LIKE '%$s%' OR s.sertifikat LIKE '%$s%' OR p.nama LIKE '%$s%') ";
}
if ($f_sertif!=='') { $where .= " AND s.sertifikasi='{$f_sertif}' "; }
if ($f_tahun!=='')  { $where .= " AND YEAR(s.tgl_sertifikat)='{$f_tahun}' "; }

/* counts */
$qTotal = mysqli_query($conn, "SELECT COUNT(*) c FROM tb_sertifikasi s ".$where);
$totalFiltered = 0; if($qTotal){ $r=mysqli_fetch_assoc($qTotal); $totalFiltered=(int)$r['c']; }
$qAll = mysqli_query($conn, "SELECT COUNT(*) c FROM tb_sertifikasi"); $totalAll = 0; if($qAll){ $ra=mysqli_fetch_assoc($qAll); $totalAll=(int)$ra['c']; }

/* data */
$sql = "SELECT s.*, p.nama AS nama_peg
        FROM tb_sertifikasi s
        LEFT JOIN tb_pegawai p ON p.id_peg=s.id_peg
        $where
        ORDER BY COALESCE(s.tgl_sertifikat,'1000-01-01') DESC, s.id_sertif DESC
        LIMIT ".(int)$start.",".(int)$length;
$q = mysqli_query($conn,$sql);

$today = date('Y-m-d');
$sixm  = date('Y-m-d', strtotime('+6 months'));

$data = array(); $no=$start+1;
if($q){ while($r=mysqli_fetch_assoc($q)){
  $exp = $r['tgl_expired'];
  $statusBadge = '-';
  if($exp && $exp!='0000-00-00'){
    if($exp < $today){
      $statusBadge = '<span class="badge bg-danger">Expired</span>';
    } elseif($exp <= $sixm){
      $statusBadge = '<span class="badge bg-warning text-dark">Warning</span>';
    } else {
      $statusBadge = '<span class="badge bg-success">Aktif</span>';
    }
  }

  $data[] = array(
    'no'            => $no++,
    'idpeg_nama'    => ($r['id_peg']?$r['id_peg']:'-').' — '.($r['nama_peg']?$r['nama_peg']:'-'),
    'sertifikasi'   => $r['sertifikasi'],
    'penyelenggara' => $r['penyelenggara'],
    'sertifikat'    => $r['sertifikat'],               // akan dimasukkan ke child
    'tgl_sertifikat'=> $r['tgl_sertifikat'],
    'tgl_expired'   => ($exp && $exp!='0000-00-00') ? $exp : '',
    'status_badge'  => $statusBadge                    // tampil di kolom utama
  );
} }

echo json_encode(array(
  'draw'=>$draw,
  'recordsTotal'=>$totalAll,
  'recordsFiltered'=>$totalFiltered,
  'data'=>$data
));
