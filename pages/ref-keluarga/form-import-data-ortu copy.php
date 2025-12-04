<?php
/*********************************************************
 * DIR     : pages/ref-keluarga/form-import-data-ortu.php
 * MODULE  : SIMPEG — Impor Kolektif Orang Tua Pegawai (tb_ortu)
 * VERSION : v1.1 (PHP 5.6)
 * DATE    : 2025-09-06
 * AUTHOR  : EWS/SIMPEG BKK Jateng 
 *
 * RINGKAS :
 * - DUKUNG  Excel (XLS/XLSX). XLS/XLSX
 * - Kunci relasi: id_peg (FK ke tb_pegawai).
 * - Duplikat (id_peg+nama+status_hub): UPDATE jika dicentang.
 *********************************************************/
if (session_id()==='') session_start();
require_once __DIR__ . '/../../dist/koneksi.php';

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }

/* whitelist */
$pendOK = array('SD','SMP','SMA','D1','D2','D3','D4','S1','S2','S3');
$hubOK  = array('Ayah','Ibu','Ayah Sambung','Ibu Sambung','Mertua L','Mertua P','Wali');

/* util tgl */
function parse_date_to_mysql($s){
  $s = trim($s);
  if ($s==='') return '';
  if (preg_match('~^\d{4}-\d{2}-\d{2}$~',$s)) return $s;
  if (preg_match('~^(\d{1,2})[/-](\d{1,2})[/-](\d{4})$~',$s,$m)){
    $d = str_pad($m[1],2,'0',STR_PAD_LEFT);
    $M = str_pad($m[2],2,'0',STR_PAD_LEFT);
    return $m[3].'-'.$M.'-'.$d;
  }
  return '';
}

/* detect PHPExcel (sejumlah kemungkinan path umum) */
$PHPEXCEL_OK = false;
$__paths = array(
  __DIR__.'/../../libs/PHPExcel/Classes/PHPExcel/IOFactory.php',
  __DIR__.'/../../vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php',
  __DIR__.'/../../PHPExcel/IOFactory.php',
  'PHPExcel/IOFactory.php'
);
foreach ($__paths as $__p){
  if (file_exists($__p)){ require_once $__p; $PHPEXCEL_OK = true; break; }
}

$errors = array();
$summary = null;

$do_update = isset($_POST['do_update']) ? 1 : 0;

function handle_row($conn,$r,$pendOK,$hubOK,&$errors,&$rows,$line){
  $id_peg       = isset($r['id_peg'])? trim($r['id_peg']) : '';
  $nik          = isset($r['nik'])? trim($r['nik']) : '';
  $nama         = isset($r['nama'])? trim($r['nama']) : '';
  $tmp_lhr      = isset($r['tmp_lhr'])? trim($r['tmp_lhr']) : '';
  $tgl_lhr_raw  = isset($r['tgl_lhr'])? trim($r['tgl_lhr']) : '';
  $pendidikan   = isset($r['pendidikan'])? strtoupper(trim($r['pendidikan'])) : '';
  $id_pekerjaan = isset($r['id_pekerjaan'])? trim($r['id_pekerjaan']) : '';
  $pekerjaan    = isset($r['pekerjaan'])? trim($r['pekerjaan']) : '';
  $status_hub   = isset($r['status_hub'])? trim($r['status_hub']) : '';

  if ($id_peg===''){ $errors[] = 'Baris '.$line.': id_peg kosong'; return; }
  $cekp = mysqli_query($conn, "SELECT 1 FROM tb_pegawai WHERE id_peg='".clean($conn,$id_peg)."' LIMIT 1");
  if (!$cekp || mysqli_num_rows($cekp)==0){ $errors[] = 'Baris '.$line.': id_peg tidak ada di tb_pegawai'; return; }
  if ($nama===''){ $errors[] = 'Baris '.$line.': nama orang tua wajib'; return; }
  if ($status_hub==='' || !in_array($status_hub,$hubOK)){ $errors[] = 'Baris '.$line.': status_hub tidak valid'; return; }
  if ($nik!=='' && !preg_match('~^\d{16}$~',$nik)){ $errors[] = 'Baris '.$line.': NIK harus 16 digit'; return; }
  if ($id_pekerjaan!=='' && !preg_match('~^\d{1,3}$~',$id_pekerjaan)){ $errors[] = 'Baris '.$line.': id_pekerjaan maks 3 digit'; return; }
  if ($pendidikan!=='' && !in_array($pendidikan,$pendOK)){ $errors[] = 'Baris '.$line.': pendidikan tidak termasuk daftar'; return; }

  $tgl_lhr = parse_date_to_mysql($tgl_lhr_raw);
  if ($tgl_lhr_raw!=='' && $tgl_lhr===''){ $errors[] = 'Baris '.$line.': tgl_lhr tidak valid (YYYY-MM-DD/DMY)'; return; }

  $rows[] = array(
    'id_peg'=>$id_peg,'nik'=>$nik,'nama'=>$nama,'tmp_lhr'=>$tmp_lhr,'tgl_lhr'=>$tgl_lhr,
    'pendidikan'=>$pendidikan,'id_pekerjaan'=>$id_pekerjaan,'pekerjaan'=>$pekerjaan,'status_hub'=>$status_hub,
    'line'=>$line
  );
}

function upsert_rows($conn,$rows,$do_update,&$errors){
  $ok_new=0; $ok_upd=0; $skipped=0;
  foreach($rows as $r){
    $idp  = clean($conn,$r['id_peg']);
    $nam  = clean($conn,$r['nama']);
    $stat = clean($conn,$r['status_hub']);
    $qdup = mysqli_query($conn, "SELECT id_ortu FROM tb_ortu WHERE id_peg='{$idp}' AND nama='{$nam}' AND status_hub='{$stat}' LIMIT 1");
    if ($qdup && mysqli_num_rows($qdup)>0){
      if ($do_update){
        $dupRow = mysqli_fetch_assoc($qdup);
        $id_ortu = (int)$dupRow['id_ortu'];
        $sql = "UPDATE tb_ortu SET ".
               "nik=".($r['nik']!==''? "'".clean($conn,$r['nik'])."'":"NULL").",".
               "tmp_lhr=".($r['tmp_lhr']!==''? "'".clean($conn,$r['tmp_lhr'])."'":"NULL").",".
               "tgl_lhr=".($r['tgl_lhr']!==''? "'".clean($conn,$r['tgl_lhr'])."'":"NULL").",".
               "pendidikan=".($r['pendidikan']!==''? "'".clean($conn,$r['pendidikan'])."'":"NULL").",".
               "id_pekerjaan=".($r['id_pekerjaan']!==''? "'".clean($conn,$r['id_pekerjaan'])."'":"NULL").",".
               "pekerjaan=".($r['pekerjaan']!==''? "'".clean($conn,$r['pekerjaan'])."'":"NULL")." ".
               "WHERE id_ortu=".$id_ortu." LIMIT 1";
        $ok = mysqli_query($conn,$sql);
        if ($ok) $ok_upd++; else $errors[] = 'Baris '.$r['line'].': gagal UPDATE ('.mysqli_error($conn).')';
      } else {
        $skipped++;
      }
    } else {
      $sql = "INSERT INTO tb_ortu(id_peg,nik,nama,tmp_lhr,tgl_lhr,pendidikan,id_pekerjaan,pekerjaan,status_hub,date_reg) VALUES (".
             "'".$idp."',".
             ($r['nik']!==''? "'".clean($conn,$r['nik'])."'":"NULL").",".
             "'".$nam."',".
             ($r['tmp_lhr']!==''? "'".clean($conn,$r['tmp_lhr'])."'":"NULL").",".
             ($r['tgl_lhr']!==''? "'".clean($conn,$r['tgl_lhr'])."'":"NULL").",".
             ($r['pendidikan']!==''? "'".clean($conn,$r['pendidikan'])."'":"NULL").",".
             ($r['id_pekerjaan']!==''? "'".clean($conn,$r['id_pekerjaan'])."'":"NULL").",".
             ($r['pekerjaan']!==''? "'".clean($conn,$r['pekerjaan'])."'":"NULL").",".
             "'".$stat."',CURDATE())";
      $ok = mysqli_query($conn,$sql);
      if ($ok) $ok_new++; else $errors[] = 'Baris '.$r['line'].': gagal INSERT ('.mysqli_error($conn).')';
    }
  }
  return array('new'=>$ok_new,'upd'=>$ok_upd,'skip'=>$skipped,'total'=>count($rows));
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['file'])){
  if (!is_uploaded_file($_FILES['file']['tmp_name'])){
    $errors[] = 'Berkas tidak valid.';
  } else {
    $name = $_FILES['file']['name'];
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $tmp  = $_FILES['file']['tmp_name'];

    $rows = array();

    if ($ext==='csv'){
      $fh = fopen($tmp,'r');
      if ($fh===false){ $errors[]='Tidak dapat membuka berkas.'; }
      else {
        $header = fgetcsv($fh);
        if (!$header){ $errors[]='Header tidak terbaca.'; }
        else {
          $map = array();
          for($i=0;$i<count($header);$i++){ $map[strtolower(trim($header[$i]))] = $i; }
          $req = array('id_peg','nama','status_hub');
          for($i=0;$i<count($req);$i++){ if (!isset($map[$req[$i]])) $errors[]='Kolom wajib hilang: '.$req[$i]; }

          if (count($errors)===0){
            $line=1;
            while(($row=fgetcsv($fh))!==false){
              $line++;
              $nonEmpty=false;
              for($i=0;$i<count($row);$i++){ if (trim($row[$i])!==''){ $nonEmpty=true; break; } }
              if (!$nonEmpty) continue;

              $r = array(
                'id_peg'      => isset($row[$map['id_peg']])? $row[$map['id_peg']] : '',
                'nik'         => isset($map['nik'])? $row[$map['nik']] : '',
                'nama'        => isset($row[$map['nama']])? $row[$map['nama']] : '',
                'tmp_lhr'     => isset($map['tmp_lhr'])? $row[$map['tmp_lhr']] : '',
                'tgl_lhr'     => isset($map['tgl_lhr'])? $row[$map['tgl_lhr']] : '',
                'pendidikan'  => isset($map['pendidikan'])? $row[$map['pendidikan']] : '',
                'id_pekerjaan'=> isset($map['id_pekerjaan'])? $row[$map['id_pekerjaan']] : '',
                'pekerjaan'   => isset($map['pekerjaan'])? $row[$map['pekerjaan']] : '',
                'status_hub'  => isset($row[$map['status_hub']])? $row[$map['status_hub']] : ''
              );
              handle_row($conn,$r,$pendOK,$hubOK,$errors,$rows,$line);
            }
            fclose($fh);
          }
        }
      }
    } elseif ($ext==='xls' || $ext==='xlsx'){
      if (!$PHPEXCEL_OK){
        $errors[] = 'Impor Excel memerlukan PHPExcel 1.8. Letakkan library di folder "libs/PHPExcel" atau "vendor/phpoffice/phpexcel". Atau gunakan CSV.';
      } else {
        try {
          $obj = PHPExcel_IOFactory::load($tmp);
          $sheet = $obj->getSheet(0);
          $highestRow = $sheet->getHighestRow();
          $highestCol = $sheet->getHighestColumn();
          // header
          $map = array();
          $colCount = PHPExcel_Cell::columnIndexFromString($highestCol);
          for ($c=0; $c<$colCount; $c++){
            $val = $sheet->getCellByColumnAndRow($c,1)->getValue();
            $map[strtolower(trim($val))] = $c;
          }
          $req = array('id_peg','nama','status_hub');
          for($i=0;$i<count($req);$i++){ if (!isset($map[$req[$i]])) $errors[]='Kolom wajib hilang: '.$req[$i]; }

          if (count($errors)===0){
            for ($rIdx=2; $rIdx <= $highestRow; $rIdx++){
              // deteksi baris kosong
              $nonEmpty=false;
              for ($c=0; $c<$colCount; $c++){
                $v = $sheet->getCellByColumnAndRow($c,$rIdx)->getValue();
                if (trim($v)!==''){ $nonEmpty=true; break; }
              }
              if (!$nonEmpty) continue;

              // ambil nilai
              $get = function($key) use ($sheet,$map,$rIdx){
                if (!isset($map[$key])) return '';
                $c = $map[$key];
                $cell = $sheet->getCellByColumnAndRow($c,$rIdx);
                $val  = $cell->getValue();
                // khusus tgl: kalau numeric excel date
                if ($key==='tgl_lhr'){
                  if (is_numeric($val)){
                    if (class_exists('PHPExcel_Shared_Date')){
                      $ts = PHPExcel_Shared_Date::ExcelToPHP($val);
                      if ($ts>0){ return date('Y-m-d',$ts); }
                    }
                  }
                }
                return trim($val);
              };

              $rec = array(
                'id_peg'       => $get('id_peg'),
                'nik'          => isset($map['nik'])? $get('nik') : '',
                'nama'         => $get('nama'),
                'tmp_lhr'      => isset($map['tmp_lhr'])? $get('tmp_lhr') : '',
                'tgl_lhr'      => isset($map['tgl_lhr'])? $get('tgl_lhr') : '',
                'pendidikan'   => isset($map['pendidikan'])? $get('pendidikan') : '',
                'id_pekerjaan' => isset($map['id_pekerjaan'])? $get('id_pekerjaan') : '',
                'pekerjaan'    => isset($map['pekerjaan'])? $get('pekerjaan') : '',
                'status_hub'   => $get('status_hub')
              );
              handle_row($conn,$rec,$pendOK,$hubOK,$errors,$rows,$rIdx);
            }
          }
        } catch (Exception $e){
          $errors[] = 'Gagal membaca Excel: '.$e->getMessage();
        }
      }
    } else {
      $errors[] = 'Ekstensi tidak didukung. Gunakan CSV, XLS, atau XLSX.';
    }

    if (count($errors)===0 && count($rows)>0){
      $summary = upsert_rows($conn,$rows,$do_update,$errors);
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Impor Data Orang Tua (Kolektif)</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <style>
    .page{max-width:900px;margin:20px auto}
    .card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}
    .card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}
    .hint{font-size:12px;color:#64748b}
    .badge{display:inline-block;padding:2px 8px;border-radius:999px;background:#e2e8f0}
  </style>
</head>
<body>
<div class="page">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">Impor Data Orang Tua (Kolektif)</h5>
        <small>Unggah file Excel (XLS/XLSX) atau CSV. Kunci relasi: <b>id_peg</b>.</small>
      </div>
      <div>
        <a class="btn btn-light btn-sm" href="home-admin.php?page=form-view-data-ortu">Kembali</a>
      </div>
    </div>
    <div class="card-body">
      <div class="mb-3">
        <a class="btn btn-success btn-sm" href="pages/ref-keluarga/template_import_ortu.xlsx">Unduh Template (Excel)</a>
        <a class="btn btn-outline-secondary btn-sm ml-2" href="pages/ref-keluarga/template_import_ortu.csv">Unduh Template (CSV)</a>
        <span class="hint ml-2">Kolom: id_peg, nik, nama, tmp_lhr, tgl_lhr, pendidikan (SD/SMP/..), id_pekerjaan, pekerjaan, status_hub (Ayah/Ibu/...)</span>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <b>Terjadi kesalahan:</b>
          <ul style="margin:0 0 0 18px">
            <?php foreach($errors as $e): ?><li><?php echo e($e); ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($summary): ?>
        <div class="alert alert-info">
          <b>Ringkasan:</b>
          <span class="badge">Total baris: <?php echo (int)$summary['total']; ?></span>
          <span class="badge">Insert: <?php echo (int)$summary['new']; ?></span>
          <span class="badge">Update: <?php echo (int)$summary['upd']; ?></span>
          <span class="badge">Lewat: <?php echo (int)$summary['skip']; ?></span>
        </div>
      <?php endif; ?>

      <form method="post" action="" enctype="multipart/form-data" class="mt-2">
        <div class="form-group">
          <label>Pilih File (Excel/CSV)</label>
          <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
        </div>
        <div class="form-check mt-2">
          <input type="checkbox" class="form-check-input" id="do_update" name="do_update" checked>
          <label for="do_update" class="form-check-label">Update jika duplikat (id_peg + nama + status_hub)</label>
        </div>
        <div class="mt-3 d-flex justify-content-between">
          <a href="home-admin.php?page=form-view-data-ortu" class="btn btn-outline-secondary">Kembali</a>
          <button type="submit" class="btn btn-primary">Impor</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>