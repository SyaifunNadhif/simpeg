<?php
/*********************************************************
 * FILE    : pages/ref-keluarga/ajax-anak-check.php
 * MODULE  : SIMPEG — Data Anak (Helper AJAX)
 * VERSION : v1.1 (PHP 5.6)
 * DATE    : 2025-09-07
 * DESC    :
 *   - GET ?uid=IDPEG  → {count:<jumlah_anak>}
 *   - GET ?mode=nik&id_peg=IDPEG&nik=XXXXXXXXXXXXXX → {exists:true|false, id_anak, nama}
 * CHANGELOG
 * - v1.1: Tambah pengembalian id_anak & nama ketika NIK duplikat, untuk navigasi ke mode edit.
 * - v1.0: Cek jumlah anak (uid) & cek eksistensi NIK sederhana.
 *********************************************************/
?>
<?php
if (session_id()==='') session_start();
/* koneksi standar */
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn) || !$conn) { @include_once __DIR__ . '/../../config/koneksi.php'; }
if (!isset($conn) && isset($koneksi) && $koneksi) { $conn = $koneksi; }
header('Content-Type: application/json; charset=utf-8');

function esc($s){ return mysqli_real_escape_string($GLOBALS['conn'], trim($s)); }
$mode = isset($_GET['mode'])? $_GET['mode'] : '';

if ($mode==='nik'){
  $idp = isset($_GET['id_peg'])? esc($_GET['id_peg']) : '';
  $nik = isset($_GET['nik'])? esc($_GET['nik']) : '';
  if($idp==='' || $nik===''){ echo json_encode(['exists'=>false]); exit; }
  $q = mysqli_query($conn, "SELECT id_anak, nama FROM tb_anak WHERE id_peg='{$idp}' AND nik='{$nik}' LIMIT 1");
  if($q && mysqli_num_rows($q)>0){
    $r = mysqli_fetch_assoc($q);
    echo json_encode(['exists'=>true, 'id_anak'=>(int)$r['id_anak'], 'nama'=>$r['nama']]); exit;
  }
  echo json_encode(['exists'=>false]); exit;
}

$uid = isset($_GET['uid'])? esc($_GET['uid']) : '';
if ($uid===''){ echo json_encode(['count'=>0]); exit; }
$q = mysqli_query($conn, "SELECT COUNT(*) c FROM tb_anak WHERE id_peg='{$uid}'");
$cnt = 0; if($q){ $r=mysqli_fetch_assoc($q); $cnt=(int)$r['c']; }
print json_encode(['count'=>$cnt]);
exit;
?>
