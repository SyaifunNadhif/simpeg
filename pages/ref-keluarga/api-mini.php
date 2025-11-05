<?php
if (session_id()==='') session_start();
header('Content-Type: application/json; charset=UTF-8');

@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; }

function esc($c,$s){ return mysqli_real_escape_string($c, trim($s)); }

$act = isset($_GET['act']) ? $_GET['act'] : '';
if ($act === 'check_si') {
  $id_peg = isset($_GET['id_peg']) ? esc($conn, $_GET['id_peg']) : '';
  $exists = false; $id_si = null;
  if ($id_peg !== '') {
    $q = mysqli_query($conn, "SELECT id_si FROM tb_suamiistri WHERE id_peg='{$id_peg}' LIMIT 1");
    if ($q && mysqli_num_rows($q)>0) {
      $r = mysqli_fetch_assoc($q);
      $exists = true; $id_si = $r['id_si'];
    }
  }
  echo json_encode(array('exists'=>$exists, 'id_si'=>$id_si));
  exit;
}

echo json_encode(array('ok'=>true));
