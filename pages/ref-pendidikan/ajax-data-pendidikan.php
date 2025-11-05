<?php
/***********************
 * FILE    : pages/ref-pendidikan/ajax-data-pendidikan.php
 * VERSION : v1.2 (PHP 5.6)
 * DATE    : 2025-09-07
 * CHANGELOG
 * - v1.2: Urutan pakai id_pendidikan DESC, kolom sesuai skema.
 ***********************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }
ini_set('display_errors',0); while(ob_get_level()){ob_end_clean();}
header('Content-Type: application/json; charset=utf-8');

function esc($s){ return mysqli_real_escape_string($GLOBALS['conn'], trim($s)); }
$uid    = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$draw   = isset($_GET['draw'])? (int)$_GET['draw']:1;
$start  = isset($_GET['start'])?(int)$_GET['start']:0;
$len    = isset($_GET['length'])?(int)$_GET['length']:10;
$search = isset($_GET['search']['value'])? trim($_GET['search']['value']):'';

$where=" WHERE 1=1 ";
if($uid!==''){ $where.=" AND p.id_peg='".esc($uid)."' "; }
if($search!==''){
  $s=esc($search);
  $where .= " AND (p.nama_sekolah LIKE '%$s%' OR p.jurusan LIKE '%$s%' OR p.jenjang LIKE '%$s%' OR p.lokasi LIKE '%$s%') ";
}

$total=0; $qC=mysqli_query($conn,"SELECT COUNT(*) c FROM tb_pendidikan p $where"); if($qC){ $r=mysqli_fetch_assoc($qC); $total=(int)$r['c']; }
$sql="SELECT p.*, pgw.nama AS nama_peg
      FROM tb_pendidikan p
      LEFT JOIN tb_pegawai pgw ON pgw.id_peg = p.id_peg
      $where
      ORDER BY COALESCE(p.th_lulus,'0000') DESC, p.id_pendidikan DESC
      LIMIT $start,$len";
$q=mysqli_query($conn,$sql);

$data=array(); $no=$start+1;
if($q){
  while($r=mysqli_fetch_assoc($q)){
  $data[]=array(
    'no'=>$no++,
    'idpeg_nama'=>$r['id_peg'].' — '.($r['nama_peg']?:'-'),
    'id_peg'=>$r['id_peg'],
    'jenjang'=>$r['jenjang'],
    'nama_sekolah'=>$r['nama_sekolah'],
    'lokasi'=>$r['lokasi'],
    'jurusan'=>$r['jurusan'],
    'th_masuk'=>$r['th_masuk'],
    'th_lulus'=>$r['th_lulus'],
    'no_ijazah'=>$r['no_ijazah'],
    'tgl_ijazah'=>$r['tgl_ijazah'],
    'kepala'=>$r['kepala'],
    'status'=>$r['status']
  );
  }
}
echo json_encode(array('draw'=>$draw,'recordsTotal'=>$total,'recordsFiltered'=>$total,'data'=>$data), JSON_UNESCAPED_UNICODE); exit;
