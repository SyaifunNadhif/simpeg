<?php
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn) || !$conn) { @include_once __DIR__ . '/../../config/koneksi.php'; if(isset($koneksi)) $conn=$koneksi; }
header('Content-Type: application/json; charset=utf-8');
function esc($s){ return mysqli_real_escape_string($GLOBALS['conn'], trim($s)); }
$mode = isset($_GET['mode'])? $_GET['mode']:'';
if($mode==='dup'){
  $idp = isset($_GET['id_peg'])? esc($_GET['id_peg']):'';
  $dik = isset($_GET['diklat'])? esc($_GET['diklat']):'';
  $th  = isset($_GET['tahun'])? esc($_GET['tahun']):'';
  if($idp===''||$dik===''){ echo json_encode(['exists'=>false]); exit; }
  $q = mysqli_query($conn,"SELECT 1 FROM tb_diklat WHERE id_peg='{$idp}' AND diklat='{$dik}' AND tahun='{$th}' LIMIT 1");
  echo json_encode(['exists'=> ($q && mysqli_num_rows($q)>0)]); exit;
}
echo json_encode(['ok'=>true]); exit;
