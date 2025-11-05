<?php
/*********************************************************
 * FILE    : pages/ref-diklat/ajax-data-diklat.php
 * MODULE  : SIMPEG — Diklat (DataTables server-side)
 * VERSION : v1.1 (PHP 5.6)
 * DATE    : 2025-09-07
 * CHANGELOG
 * - v1.1: Dukung filter f_diklat & f_tahun; perbaikan count.
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

$uid       = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$f_diklat  = isset($_GET['f_diklat']) ? esc($_GET['f_diklat']) : '';
$f_tahun   = isset($_GET['f_tahun'])  ? esc($_GET['f_tahun'])  : '';

$where = " WHERE 1=1 ";
if ($uid!=='') { $where .= " AND d.id_peg='".esc($uid)."' "; }
if ($search!=='') {
  $s = esc($search);
  $where .= " AND (d.diklat LIKE '%$s%' OR d.penyelenggara LIKE '%$s%' OR d.tempat LIKE '%$s%' OR d.tahun LIKE '%$s%' OR p.nama LIKE '%$s%') ";
}
if ($f_diklat!=='') { $where .= " AND d.diklat='{$f_diklat}' "; }
if ($f_tahun!=='')  { $where .= " AND d.tahun='{$f_tahun}' "; }

/* total records */
$qTotal = mysqli_query($conn, "SELECT COUNT(*) c FROM tb_diklat d ".$where);
$totalFiltered = 0; if($qTotal){ $r=mysqli_fetch_assoc($qTotal); $totalFiltered=(int)$r['c']; }
$qAll = mysqli_query($conn, "SELECT COUNT(*) c FROM tb_diklat"); $totalAll = 0; if($qAll){ $ra=mysqli_fetch_assoc($qAll); $totalAll=(int)$ra['c']; }

/* data */
$sql = "SELECT d.*, p.nama AS nama_peg
        FROM tb_diklat d
        LEFT JOIN tb_pegawai p ON p.id_peg=d.id_peg
        $where
        ORDER BY COALESCE(d.tahun,'0000') DESC, d.id_diklat DESC
        LIMIT ".(int)$start.",".(int)$length;
$q = mysqli_query($conn, $sql);

$data = array(); $no=$start+1;
if($q){ while($r=mysqli_fetch_assoc($q)){
  $data[] = array(
    'no'          => $no++,
    'idpeg_nama'  => ($r['id_peg']?$r['id_peg']:'-').' — '.($r['nama_peg']?$r['nama_peg']:'-'),
    'diklat'      => $r['diklat'],
    'penyelenggara'=> $r['penyelenggara'],
    'tempat'      => $r['tempat'],
    'angkatan'    => $r['angkatan'],
    'tahun'       => $r['tahun']
  );
} }

echo json_encode(array(
  'draw'=>$draw,
  'recordsTotal'=>$totalAll,
  'recordsFiltered'=>$totalFiltered,
  'data'=>$data
));
