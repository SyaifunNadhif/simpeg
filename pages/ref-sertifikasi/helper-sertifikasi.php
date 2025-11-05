<?php
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn) || !$conn) { @include_once __DIR__ . '/../../config/koneksi.php'; if(isset($koneksi)) $conn=$koneksi; }
header('Content-Type: application/json; charset=utf-8');
function esc($s){ return mysqli_real_escape_string($GLOBALS['conn'], trim($s)); }
$mode = isset($_GET['mode'])? $_GET['mode']:'';
if($mode==='dup'){
  $idp = isset($_GET['id_peg'])? esc($_GET['id_peg']):'';
  $ser = isset($_GET['sertifikasi'])? esc($_GET['sertifikasi']):'';
  $ts  = isset($_GET['tgl_sertifikat'])? esc($_GET['tgl_sertifikat']):'';
  if($idp===''||$ser===''){ echo json_encode(['exists'=>false]); exit; }
  $condTs = ($ts!=='')? "='{$ts}'" : " IS NULL";
  $q = mysqli_query($conn,"SELECT 1 FROM tb_sertifikasi WHERE id_peg='{$idp}' AND sertifikasi='{$ser}' AND tgl_sertifikat{$condTs} LIMIT 1");
  echo json_encode(['exists'=> ($q && mysqli_num_rows($q)>0)]); exit;
}
echo json_encode(['ok'=>true]); exit;
