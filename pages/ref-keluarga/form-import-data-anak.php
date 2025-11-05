<?php
/*********************************************************
 * FILE    : pages/ref-keluarga/form-import-data-anak.php
 * MODULE  : SIMPEG — Import Data Anak (Kolektif)
 * VERSION : v1.2 (PHP 5.6 compatible)
 * DATE    : 2025-09-07
 * CHANGELOG
 * - v1.2: Fix parse error (kurung tutup berlebih di SQL), standar koneksi via dist/koneksi ($conn),
 *         transaksi pakai $conn, dan validasi duplikat NIK (per pegawai) saat impor.
 * - v1.1: Card layout; SweetAlert flash; petunjuk header template.
 * - v1.0: Import CSV/XLSX, parsing tanggal.
 *********************************************************/
?>
<!DOCTYPE html>
<html lang="id">
<head>
<?php
if (session_id()==='') session_start();
/* koneksi standar */
@include_once __DIR__ . '/../../dist/koneksi.php';
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
// fallback lama jika environment tertentu masih pakai config/
if (!isset($conn) || !$conn) { @include_once __DIR__ . '/../../config/koneksi.php'; }
if (!isset($conn) && isset($koneksi) && $koneksi) { $conn = $koneksi; }
if (!isset($koneksi) && isset($conn) && $conn) { $koneksi = $conn; }
?>
  <meta charset="utf-8"><title>Impor Data Anak</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>.card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}.card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}</style>
</head>
<body>
<div class="container mt-3">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">Impor Data Anak</h5>
        <small>Unggah file CSV/XLSX sesuai template</small>
      </div>
    </div>
    <div class="card-body">
      <?php $flash = isset($_SESSION['flash_msg'])?$_SESSION['flash_msg']:''; unset($_SESSION['flash_msg']); if($flash!==''):?>
        <script>Swal.fire({icon:'info',title:'Informasi',html: <?php echo json_encode($flash); ?>});</script>
      <?php endif; ?>
      <div class="mb-2"><b>Header Template (CSV/XLSX):</b>
        <pre class="mb-0">id_peg, nik, nama, tmp_lhr, tgl_lhr, pendidikan, id_pekerjaan, pekerjaan, status_hub, anak_ke, bpjs_anak</pre>
      </div>
      <div class="mb-2">
        <a class="btn btn-sm btn-outline-primary me-2" href="home-admin.php?page=download-template-anak&type=xlsx">Unduh Template XLSX</a>
        <a class="btn btn-sm btn-outline-success" href="home-admin.php?page=download-template-anak&type=csv">Unduh Template CSV</a>
      </div>
      <form method="post" enctype="multipart/form-data" class="row g-3" action="home-admin.php?page=form-import-data-anak">
        <div class="col-md-6">
          <label class="form-label">File CSV/XLSX</label>
          <input type="file" name="file_import" class="form-control" accept=".csv,.xlsx" required>
        </div>
        <div class="mt-3 d-flex justify-content-between">
          <a href="home-admin.php?page=form-view-data-anak" class="btn btn-outline-secondary">Kembali</a>
          <button type="submit" class="btn btn-primary">Impor Data</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
<?php
// ================== PROSES UNGGAH ==================
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['file_import'])){
  function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }
  function toDate($s){
    $s=trim($s);
    if($s==='') return '';
    // format dd/mm/YYYY
    if(preg_match('~^\n?\r?\d{2}/\d{2}/\d{4}$~',$s)){
      $a=explode('/',$s); return $a[2].'-'.$a[1].'-'.$a[0];
    }
    // excel serial date
    if(is_numeric($s) && $s>25569){ $ts=((int)$s-25569)*86400; return gmdate('Y-m-d',$ts); }
    // jika sudah YYYY-mm-dd, kembalikan apa adanya
    return $s;
  }

  $fn = $_FILES['file_import']['name'];
  $tmp= $_FILES['file_import']['tmp_name'];
  $ext= strtolower(pathinfo($fn,PATHINFO_EXTENSION));

  $rows=array();
  if($ext==='csv'){
    if(($h=fopen($tmp,'r'))!==false){
      $hdr=fgetcsv($h,0,','); if(!$hdr){ $hdr=array(); }
      foreach($hdr as $k=>$v){ $hdr[$k]=strtolower(trim($v)); }
      while(($d=fgetcsv($h,0,','))!==false){
        $r=array(); foreach($hdr as $i=>$hname){ $r[$hname]=isset($d[$i])?trim($d[$i]):''; }
        if(!empty(array_filter($r))) $rows[]=$r;
      }
      fclose($h);
    }
  } elseif($ext==='xlsx'){
    $p=__DIR__.'/../../plugins/phpexcel/Classes/PHPExcel.php';
    if(file_exists($p)){
      require_once $p;
      $obj= PHPExcel_IOFactory::load($tmp); $sh=$obj->getSheet(0);
      $hr=$sh->getHighestRow(); $hc=PHPExcel_Cell::columnIndexFromString($sh->getHighestColumn());
      $hdr=array(); for($c=0;$c<$hc;$c++){ $hdr[$c]=strtolower(trim((string)$sh->getCellByColumnAndRow($c,1)->getValue())); }
      for($r=2;$r<=$hr;$r++){
        $row=array(); for($c=0;$c<$hc;$c++){ $row[$hdr[$c]]=trim((string)$sh->getCellByColumnAndRow($c,$r)->getValue()); }
        if(!empty(array_filter($row))) $rows[]=$row;
      }
    } else { $_SESSION['flash_msg']='Library PHPExcel tidak ditemukan.'; header('Location: home-admin.php?page=form-import-data-anak'); exit; }
  }

  if(!empty($rows)){
    $ins=0; $fail=0; $log=array(); $today=date('Y-m-d');
    mysqli_begin_transaction($conn);

    foreach($rows as $i=>$r){
      $rowno = $i+2; // asumsikan header di baris 1
      $id_peg      = clean($conn, isset($r['id_peg'])?$r['id_peg']:'');
      $nik_val     = clean($conn, isset($r['nik'])?$r['nik']:'');
      $nama        = clean($conn, isset($r['nama'])?$r['nama']:'');
      $tmp_lhr     = clean($conn, isset($r['tmp_lhr'])?$r['tmp_lhr']:'');
      $tgl_lhr     = clean($conn, toDate(isset($r['tgl_lhr'])?$r['tgl_lhr']:''));
      $pendidikan  = clean($conn, isset($r['pendidikan'])?$r['pendidikan']:'');
      $id_pekerjaan= clean($conn, isset($r['id_pekerjaan'])?$r['id_pekerjaan']:'');
      $pekerjaan   = clean($conn, isset($r['pekerjaan'])?$r['pekerjaan']:'');
      $status_hub  = clean($conn, isset($r['status_hub'])?$r['status_hub']:'');
      $anak_ke     = clean($conn, isset($r['anak_ke'])?$r['anak_ke']:'');
      $bpjs_anak   = clean($conn, isset($r['bpjs_anak'])?$r['bpjs_anak']:'');

      if($id_peg===''){ $fail++; $log[]='Baris '.$rowno.': id_peg kosong'; continue; }
      if($nama===''){   $fail++; $log[]='Baris '.$rowno.': nama kosong';   continue; }

      // tolak duplikat NIK untuk pegawai yang sama (jika NIK diisi)
      if($nik_val!==''){
        $qdup = mysqli_query($conn, "SELECT 1 FROM tb_anak WHERE id_peg='{$id_peg}' AND nik='{$nik_val}' LIMIT 1");
        if($qdup && mysqli_num_rows($qdup)>0){ $fail++; $log[]='Baris '.$rowno.': duplikat NIK untuk pegawai ini'; continue; }
      }

      $sql = "INSERT INTO tb_anak(id_peg,nik,nama,tmp_lhr,tgl_lhr,pendidikan,id_pekerjaan,pekerjaan,status_hub,anak_ke,bpjs_anak,date_reg)".
             " VALUES('{$id_peg}','{$nik_val}','{$nama}','{$tmp_lhr}','{$tgl_lhr}','{$pendidikan}','{$id_pekerjaan}','{$pekerjaan}','{$status_hub}','{$anak_ke}','{$bpjs_anak}','{$today}')";

      $ok = mysqli_query($conn,$sql);
      if($ok) $ins++; else { $fail++; $log[]='Baris '.$rowno.' gagal simpan'; }
    }

    if($fail>0){ mysqli_rollback($conn); $_SESSION['flash_msg']='Impor gagal sebagian. Sukses: '.$ins.', Gagal: '.$fail.'<br>'.e(implode('<br>',$log)); }
    else { mysqli_commit($conn); $_SESSION['flash_msg']='Impor selesai. Sukses: '.$ins; }
  } else {
    $_SESSION['flash_msg']='Tidak ada data terbaca.';
  }
  header('Location: home-admin.php?page=form-import-data-anak'); exit;
}
?>
