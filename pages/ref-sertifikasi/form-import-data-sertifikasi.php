<?php
/*********************************************************
 * FILE    : pages/ref-sertifikasi/form-import-data-sertifikasi.php
 * MODULE  : SIMPEG — Import Data Sertifikasi
 * VERSION : v1.0 (PHP 5.6)
 * DATE    : 2025-09-07
 *********************************************************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn) || !$conn) { @include_once __DIR__ . '/../../config/koneksi.php'; if(isset($koneksi)) $conn=$koneksi; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="id"><head>
  <meta charset="utf-8"><title>Impor Data Sertifikasi</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>.card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}.card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}</style>
</head><body>
<div class="container mt-3">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div><h5 class="mb-0">Impor Data Sertifikasi</h5><small>Unggah file CSV/XLSX sesuai template</small></div>
      <div><a class="btn btn-outline-secondary" href="home-admin.php"><i class="fa fa-home"></i> Dashboard</a></div>
    </div>
    <div class="card-body">
      <?php $flash=isset($_SESSION['flash_msg'])?$_SESSION['flash_msg']:''; unset($_SESSION['flash_msg']); if($flash!==''):?>
        <script>Swal.fire({icon:'info',title:'Informasi',html: <?php echo json_encode($flash); ?>});</script>
      <?php endif; ?>

      <div class="mb-2 d-flex flex-wrap gap-2">
        <a class="btn btn-sm btn-outline-primary" href="pages/ref-sertifikasi/templates/sertifikasi-template.xlsx" download>Unduh Template XLSX</a>
        <a class="btn btn-sm btn-outline-success" href="pages/ref-sertifikasi/templates/sertifikasi-template.csv" download>Unduh Template CSV</a>
      </div>
      <div class="mb-2"><b>Header Template:</b>
        <pre class="mb-0">id_peg, sertifikasi, penyelenggara, tgl_sertifikat, tgl_expired, sertifikat</pre>
      </div>

      <form method="post" enctype="multipart/form-data" class="row g-3" action="home-admin.php?page=form-import-data-sertifikasi">
        <div class="col-md-6">
          <label class="form-label">File CSV/XLSX</label>
          <input type="file" name="file_import" class="form-control" accept=".csv,.xlsx" required>
        </div>
        <div class="col-12 d-flex justify-content-between mt-2">
          <a class="btn btn-outline-secondary" href="home-admin.php?page=form-view-data-sertifikasi">Batal</a>
          <button class="btn btn-primary" type="submit">Proses Impor</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body></html>
<?php
// Proses upload
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['file_import'])){
  function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }
  function toDate($s){ $s=trim($s); if($s==='')return ''; if(preg_match('~^\n?\r?\d{2}/\d{2}/\d{4}$~',$s)){ $a=explode('/',$s); return $a[2].'-'.$a[1].'-'.$a[0]; } if(is_numeric($s)&&$s>25569){ $ts=((int)$s-25569)*86400; return gmdate('Y-m-d',$ts);} return $s; }

  $fn=$_FILES['file_import']['name']; $tmp=$_FILES['file_import']['tmp_name']; $ext=strtolower(pathinfo($fn,PATHINFO_EXTENSION));
  $rows=array();
  if($ext==='csv'){
    if(($h=fopen($tmp,'r'))!==false){ $hdr=fgetcsv($h,0,','); foreach($hdr as $k=>$v){$hdr[$k]=strtolower(trim($v));}
      while(($d=fgetcsv($h,0,','))!==false){ $r=array(); foreach($hdr as $i=>$hname){ $r[$hname]=isset($d[$i])?trim($d[$i]):''; } if(!empty(array_filter($r)))$rows[]=$r; } fclose($h);
    }
  } elseif($ext==='xlsx'){
    $p=__DIR__.'/../../plugins/phpexcel/Classes/PHPExcel.php'; if(file_exists($p)){
      require_once $p; $obj= PHPExcel_IOFactory::load($tmp); $sh=$obj->getSheet(0);
      $hr=$sh->getHighestRow(); $hc=PHPExcel_Cell::columnIndexFromString($sh->getHighestColumn()); $hdr=array();
      for($c=0;$c<$hc;$c++){ $hdr[$c]=strtolower(trim((string)$sh->getCellByColumnAndRow($c,1)->getValue())); }
      for($r=2;$r<=$hr;$r++){ $row=array(); for($c=0;$c<$hc;$c++){ $row[$hdr[$c]]=trim((string)$sh->getCellByColumnAndRow($c,$r)->getValue()); } if(!empty(array_filter($row)))$rows[]=$row; }
    } else { $_SESSION['flash_msg']='Library PHPExcel tidak ditemukan.'; header('Location: home-admin.php?page=form-import-data-sertifikasi'); exit; }
  }

  if(!empty($rows)){
    $ins=0;$fail=0;$log=array(); $today=date('Y-m-d');
    mysqli_begin_transaction($conn);
    foreach($rows as $i=>$r){
      $rowno=$i+2;
      $id_peg=clean($conn,isset($r['id_peg'])?$r['id_peg']:'');
      $sertifikasi=clean($conn,isset($r['sertifikasi'])?$r['sertifikasi']:'');
      $penyelenggara=clean($conn,isset($r['penyelenggara'])?$r['penyelenggara']:'');
      $tgl_sertifikat=clean($conn,toDate(isset($r['tgl_sertifikat'])?$r['tgl_sertifikat']:''));
      $tgl_expired=clean($conn,toDate(isset($r['tgl_expired'])?$r['tgl_expired']:''));
      $sertifikat=clean($conn,isset($r['sertifikat'])?$r['sertifikat']:'');

      if($id_peg==='' || $sertifikasi===''){ $fail++; $log[]='Baris '.$rowno.': id_peg/sertifikasi kosong'; continue; }

      // duplikat
      $condTs = ($tgl_sertifikat!=='')? "='{$tgl_sertifikat}'" : " IS NULL";
      $qdup=mysqli_query($conn,"SELECT 1 FROM tb_sertifikasi WHERE id_peg='{$id_peg}' AND sertifikasi='{$sertifikasi}' AND tgl_sertifikat{$condTs} LIMIT 1");
      if($qdup && mysqli_num_rows($qdup)>0){ $fail++; $log[]='Baris '.$rowno.': duplikat (id_peg+sertifikasi+tgl_sertifikat)'; continue; }

      $sql="INSERT INTO tb_sertifikasi(id_peg,sertifikasi,penyelenggara,tgl_sertifikat,tgl_expired,sertifikat,date_reg,created_by)
            VALUES('{$id_peg}','{$sertifikasi}','{$penyelenggara}',".($tgl_sertifikat!==''?"'{$tgl_sertifikat}'":"NULL").",".($tgl_expired!==''?"'{$tgl_expired}'":"NULL").",'{$sertifikat}',NOW(),'import')";
      $ok=mysqli_query($conn,$sql); if($ok)$ins++; else { $fail++; $log[]='Baris '.$rowno.' gagal simpan'; }
    }
    if($fail>0){ mysqli_rollback($conn); $_SESSION['flash_msg']='Impor gagal sebagian. Sukses: '.$ins.', Gagal: '.$fail.'<br>'.e(implode('<br>',$log)); }
    else { mysqli_commit($conn); $_SESSION['flash_msg']='Impor selesai. Sukses: '.$ins; }
  } else { $_SESSION['flash_msg']='Tidak ada data terbaca.'; }
  header('Location: home-admin.php?page=form-import-data-sertifikasi'); exit;
}
?>
