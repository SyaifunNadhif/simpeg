<?php
/*********************************************************
 * FILE    : pages/ref-jabatan/simpan-import-jabatan.php
 * MODULE  : SIMPEG — Proses Impor Jabatan Pegawai (Kolektif)
 * VERSION : v1.0 (PHP 5.6 compatible)
 * DATE    : 2025-09-07
 * AUTHOR  : EWS/SIMPEG BKK Jateng — by ChatGPT
 *
 * INPUT  : Upload .xlsx atau .csv dengan header:
 *          id_peg | kode_jabatan | jabatan | unit_kerja | status_jab | no_sk | tgl_sk
 * RULES  : tmt_jabatan=tgl_sk; unit_kerja=kode_kantor_detail; status_jab='Aktif' → close aktif lama.
 * DEP    : PHPExcel untuk .xlsx (plugins/phpexcel/Classes/PHPExcel.php)
 *********************************************************/
if (session_id()==='') session_start();
require_once __DIR__ . '/../../config/koneksi.php';

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }
function toDate($s){
  $s = trim($s);
  if ($s==='') return '';
  if (preg_match('~^\d{2}/\d{2}/\d{4}$~',$s)){
    $a = explode('/',$s); return $a[2].'-'.$a[1].'-'.$a[0];
  }
  // Excel serial? (angka)
  if (is_numeric($s) && $s>25569){
    $ts = ((int)$s - 25569) * 86400; return gmdate('Y-m-d',$ts);
  }
  return $s; // as-is (yyyy-mm-dd)
}

$maxSize = isset($_POST['MAX_FILE_SIZE'])? (int)$_POST['MAX_FILE_SIZE'] : 2097152;
$mode    = isset($_POST['mode']) ? $_POST['mode'] : 'insert';
$dryrun  = isset($_POST['dryrun']) ? $_POST['dryrun'] : '1';
$user    = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'system';
$today   = date('Y-m-d');

if (!isset($_FILES['file_import']) || !is_uploaded_file($_FILES['file_import']['tmp_name'])){
  $_SESSION['flash_msg'] = 'Tidak ada file yang diunggah.';
  header('Location: home-admin.php?page=form-import-jabatan'); exit;
}

$fn   = $_FILES['file_import']['name'];
$tmp  = $_FILES['file_import']['tmp_name'];
$size = (int)$_FILES['file_import']['size'];
$ext  = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
if ($size>$maxSize){ $_SESSION['flash_msg'] = 'Ukuran file melebihi 2MB.'; header('Location: home-admin.php?page=form-import-jabatan'); exit; }

$rows = array();
if ($ext==='csv'){
  if (($handle = fopen($tmp, 'r'))!==false){
    $hdr = fgetcsv($handle, 0, ',');
    if ($hdr){
      // normalisasi header ke huruf kecil
      foreach($hdr as $k=>$v){ $hdr[$k] = strtolower(trim($v)); }
      while(($data=fgetcsv($handle,0,','))!==false){
        $row = array();
        foreach($hdr as $i=>$h){ $row[$h] = isset($data[$i]) ? trim($data[$i]) : ''; }
        if (isset($row['id_peg']) && $row['id_peg']!==''){ $rows[] = $row; }
      }
    }
    fclose($handle);
  }
} else if ($ext==='xlsx'){
  // baca via PHPExcel
  $phpexcelPath = __DIR__.'/../../plugins/phpexcel/Classes/PHPExcel.php';
  if (!file_exists($phpexcelPath)){
    $_SESSION['flash_msg'] = 'Library PHPExcel tidak ditemukan di plugins/phpexcel.';
    header('Location: home-admin.php?page=form-import-jabatan'); exit;
  }
  require_once $phpexcelPath;
  $obj = PHPExcel_IOFactory::load($tmp);
  $sheet = $obj->getSheet(0);
  $highestRow = $sheet->getHighestRow();
  $highestCol = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
  // header
  $hdr = array();
  for($c=0;$c<$highestCol;$c++){ $hdr[$c] = strtolower(trim((string)$sheet->getCellByColumnAndRow($c,1)->getValue())); }
  for($r=2;$r<=$highestRow;$r++){
    $row = array();
    for($c=0;$c<$highestCol;$c++){
      $row[$hdr[$c]] = trim((string)$sheet->getCellByColumnAndRow($c,$r)->getValue());
    }
    if (isset($row['id_peg']) && $row['id_peg']!==''){ $rows[] = $row; }
  }
}

if (empty($rows)){
  $_SESSION['flash_msg'] = 'Tidak ada baris data yang terbaca.';
  header('Location: home-admin.php?page=form-import-jabatan'); exit;
}

// VALIDASI & SIMULASI
$total = 0; $okCount = 0; $err = array();
foreach($rows as $idx=>$r){
  $total++;
  $id_peg = isset($r['id_peg'])? $r['id_peg'] : '';
  $kode_jabatan = isset($r['kode_jabatan'])? $r['kode_jabatan'] : '';
  $jabatan = isset($r['jabatan'])? $r['jabatan'] : '';
  $unit_kerja = isset($r['unit_kerja'])? $r['unit_kerja'] : '';
  $status_jab = isset($r['status_jab'])&&$r['status_jab']!='' ? $r['status_jab'] : 'Aktif';
  $no_sk = isset($r['no_sk'])? $r['no_sk'] : '';
  $tgl_sk = toDate(isset($r['tgl_sk'])? $r['tgl_sk'] : '');

  if ($id_peg=='' || $kode_jabatan=='' || $unit_kerja=='' || $no_sk=='' || $tgl_sk==''){
    $err[] = 'Baris '.($idx+2).': kolom wajib kosong.'; continue;
  }

  // cek pegawai ada
  $qP = mysqli_query($koneksi, "SELECT 1 FROM tb_pegawai WHERE id_peg='".clean($koneksi,$id_peg)."' LIMIT 1");
  if (!$qP || mysqli_num_rows($qP)==0){ $err[]='Baris '.($idx+2).': id_peg tidak ditemukan.'; continue; }

  // lengkapi nama jabatan bila kosong
  if ($jabatan==''){
    $qJ = mysqli_query($koneksi, "SELECT jabatan FROM tb_ref_jabatan WHERE kode_jabatan='".clean($koneksi,$kode_jabatan)."' LIMIT 1");
    if ($qJ && mysqli_num_rows($qJ)>0){ $jabatan = mysqli_fetch_assoc($qJ)['jabatan']; }
  }

  $okCount++;
}

if ($dryrun==='1'){
  $msg = 'Simulasi: total '.(int)$total.' baris, valid '.(int)$okCount.' baris.';
  if (!empty($err)) $msg .= '<br>Catatan:<br>'.e(implode('<br>', $err));
  $_SESSION['flash_msg'] = $msg;
  header('Location: home-admin.php?page=form-import-jabatan'); exit;
}

// EKSEKUSI
$ins = 0; $upd = 0; $fail = 0; $failMsg = array();
mysqli_begin_transaction($koneksi);
foreach($rows as $idx=>$r){
  $id_peg = clean($koneksi, $r['id_peg']);
  $kode_jabatan = clean($koneksi, $r['kode_jabatan']);
  $jabatan = clean($koneksi, isset($r['jabatan'])?$r['jabatan']:'');
  $unit_kerja = clean($koneksi, $r['unit_kerja']);
  $status_jab = clean($koneksi, (isset($r['status_jab'])&&$r['status_jab']!='')?$r['status_jab']:'Aktif');
  $no_sk = clean($koneksi, $r['no_sk']);
  $tgl_sk = clean($koneksi, toDate(isset($r['tgl_sk'])?$r['tgl_sk']:''));
  if ($jabatan==''){
    $qJ = mysqli_query($koneksi, "SELECT jabatan FROM tb_ref_jabatan WHERE kode_jabatan='{$kode_jabatan}' LIMIT 1");
    if ($qJ && mysqli_num_rows($qJ)>0){ $jabatan = clean($koneksi, mysqli_fetch_assoc($qJ)['jabatan']); }
  }
  if ($id_peg=='' || $kode_jabatan=='' || $unit_kerja=='' || $no_sk=='' || $tgl_sk=='') { $fail++; $failMsg[]='Baris '.($idx+2).' tidak lengkap.'; continue; }

  // Tutup jabatan aktif lama jika akan aktifkan baru
  $ok = true;
  if (strtolower($status_jab)=='aktif'){
    $sqlClose = "UPDATE tb_jabatan SET status_jab='Non', sampai_tgl=DATE_SUB('{$tgl_sk}',INTERVAL 1 DAY), updated_at=NOW(), updated_by='{$user}' WHERE id_peg='{$id_peg}' AND status_jab='Aktif'";
    $ok = mysqli_query($koneksi,$sqlClose);
  }

  // Cek duplikat (definisi: id_peg + kode_jabatan + no_sk + tgl_sk)
  $qC = mysqli_query($koneksi, "SELECT id_jab FROM tb_jabatan WHERE id_peg='{$id_peg}' AND kode_jabatan='{$kode_jabatan}' AND no_sk='{$no_sk}' AND tgl_sk='{$tgl_sk}' LIMIT 1");
  if ($qC && mysqli_num_rows($qC)>0){
    if ($mode==='upsert'){
      $rowC = mysqli_fetch_assoc($qC);
      $sqlU = "UPDATE tb_jabatan SET jabatan='{$jabatan}', unit_kerja='{$unit_kerja}', status_jab='{$status_jab}', tmt_jabatan='{$tgl_sk}', updated_at=NOW(), updated_by='{$user}' WHERE id_jab=".(int)$rowC['id_jab'];
      $ok = $ok && mysqli_query($koneksi,$sqlU); if ($ok) $upd++; else $fail++;
    } else {
      // skip
    }
  } else {
    $sqlI = "INSERT INTO tb_jabatan (id_peg,id_peg_old,kode_jabatan,jabatan,unit_kerja,tmt_jabatan,sampai_tgl,status_jab,no_sk,tgl_sk,date_reg,created_by)
             VALUES ('{$id_peg}',NULL,'{$kode_jabatan}','{$jabatan}','{$unit_kerja}','{$tgl_sk}',NULL,'{$status_jab}','{$no_sk}','{$tgl_sk}',CURDATE(),'{$user}')";
    $ok = $ok && mysqli_query($koneksi,$sqlI); if ($ok) $ins++; else $fail++;
  }
  if (!$ok){ $failMsg[] = 'Baris '.($idx+2).' gagal simpan.'; }
}

if ($fail>0){ mysqli_rollback($koneksi); $_SESSION['flash_msg'] = 'Impor GAGAL. Sukses: '.(int)$ins.' upsert: '.(int)$upd.' gagal: '.(int)$fail.'<br>'+e(implode('<br>',$failMsg)); }
else { mysqli_commit($koneksi); $_SESSION['flash_msg'] = 'Impor selesai. Sukses: '.(int)$ins.'; Upsert: '.(int)$upd.'.'; }

header('Location: home-admin.php?page=form-import-jabatan');
exit;
