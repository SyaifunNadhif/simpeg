<?php
/***********************
 * FILE    : pages/ref-pendidikan/form-import-data-pendidikan.php
 * VERSION : v1.2 (PHP 5.6)
 * DATE    : 2025-09-07
 * CHANGELOG
 * - v1.2: Insert tanpa ID manual (id_pendidikan auto). Kolom id_sekolah opsional ('' jika tidak diisi).
 ***********************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="id"><head>
  <meta charset="utf-8"><title>Impor Data Pendidikan</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>.card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}.card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}</style>
</head><body>
<div class="container mt-3">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">Impor Data Pendidikan</h5>
        <small>Unggah file CSV/XLSX sesuai template</small>
      </div>
    </div>
    <div class="card-body">
      <?php $flash=isset($_SESSION['flash_msg'])?$_SESSION['flash_msg']:''; unset($_SESSION['flash_msg']); if($flash!==''):?>
        <script>Swal.fire({icon:'info',title:'Informasi',html: <?php echo json_encode($flash); ?>});</script>
      <?php endif; ?>
      <div class="mb-2 d-flex flex-wrap gap-2">
        <a class="btn btn-sm btn-outline-primary"
        href="pages/ref-pendidikan/templates/pendidikan-template.xlsx" download>Unduh Template XLSX</a>
        <a class="btn btn-sm btn-outline-success"
        href="pages/ref-pendidikan/templates/pendidikan-template.csv" download>Unduh Template CSV</a>
      </div>
      <div class="mb-2"><b>Header Template:</b>
        <pre class="mb-0">id_peg, id_sekolah (opsional), jenjang, nama_sekolah, lokasi, jurusan, th_masuk, th_lulus, no_ijazah, tgl_ijazah, kepala, status</pre>
      </div>
      <form method="post" enctype="multipart/form-data" class="row g-3" action="home-admin.php?page=form-import-data-pendidikan">
        <div class="col-md-6">
          <label class="form-label">File CSV/XLSX</label>
          <input type="file" name="file_import" class="form-control" accept=".csv,.xlsx" required>
        </div>
        <div class="col-12 d-flex justify-content-between mt-2">
          <a class="btn btn-light" href="home-admin.php?page=form-view-data-pendidikan">Batal</a>
          <button class="btn btn-primary" type="submit">Proses Impor</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body></html>
<?php
// ==== Proses Import ====
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
    } else { $_SESSION['flash_msg']='Library PHPExcel tidak ditemukan.'; header('Location: home-admin.php?page=form-import-data-pendidikan'); exit; }
  }

  if(!empty($rows)){
    $ins=0;$fail=0;$log=array(); $today=date('Y-m-d');
    mysqli_begin_transaction($conn);

    foreach($rows as $i=>$r){
      $rowno=$i+2;
      $id_peg=clean($conn,isset($r['id_peg'])?$r['id_peg']:'');
      $id_sekolah=clean($conn,isset($r['id_sekolah'])?$r['id_sekolah']:''); // opsional
      $jenjang=clean($conn,isset($r['jenjang'])?$r['jenjang']:'');
      $nama_sekolah=clean($conn,isset($r['nama_sekolah'])?$r['nama_sekolah']:'');
      $lokasi=clean($conn,isset($r['lokasi'])?$r['lokasi']:'');
      $jurusan=clean($conn,isset($r['jurusan'])?$r['jurusan']:'');
      $th_masuk=clean($conn,isset($r['th_masuk'])?$r['th_masuk']:'');
      $th_lulus=clean($conn,isset($r['th_lulus'])?$r['th_lulus']:'');
      $no_ijazah=clean($conn,isset($r['no_ijazah'])?$r['no_ijazah']:'');
      $tgl_ijazah=clean($conn,toDate(isset($r['tgl_ijazah'])?$r['tgl_ijazah']:''));
      $kepala=clean($conn,isset($r['kepala'])?$r['kepala']:'');
      $status_p=clean($conn,isset($r['status'])?$r['status']:'');

      if($id_peg==='' || $jenjang==='' || $nama_sekolah===''){ $fail++; $log[]='Baris '.$rowno.': id_peg/jenjang/nama_sekolah kosong'; continue; }

      // duplikat kasar: jenjang+sekolah+th_lulus per pegawai
      $qdup=mysqli_query($conn,"SELECT 1 FROM tb_pendidikan WHERE id_peg='{$id_peg}' AND jenjang='{$jenjang}' AND nama_sekolah='{$nama_sekolah}' AND th_lulus='{$th_lulus}' LIMIT 1");
      if($qdup && mysqli_num_rows($qdup)>0){ $fail++; $log[]='Baris '.$rowno.': duplikat (jenjang+sekolah+th_lulus)'; continue; }

      $sql="INSERT INTO tb_pendidikan
           (id_peg,id_peg_old,id_sekolah,jenjang,nama_sekolah,lokasi,jurusan,no_ijazah,tgl_ijazah,kepala,status,th_masuk,th_lulus,date_reg,created_by)
           VALUES
           ('{$id_peg}',NULL,".($id_sekolah!==''?"'{$id_sekolah}'":"''").",'{$jenjang}','{$nama_sekolah}','{$lokasi}','{$jurusan}','{$no_ijazah}','{$tgl_ijazah}','{$kepala}','{$status_p}','{$th_masuk}','{$th_lulus}','{$today}','import')";
      $ok=mysqli_query($conn,$sql); if($ok)$ins++; else { $fail++; $log[]='Baris '.$rowno.' gagal simpan'; }
    }

    if($fail>0){ mysqli_rollback($conn); $_SESSION['flash_msg']='Impor gagal sebagian. Sukses: '.$ins.', Gagal: '.$fail.'<br>'.e(implode('<br>',$log)); }
    else { mysqli_commit($conn); $_SESSION['flash_msg']='Impor selesai. Sukses: '.$ins; }
  } else { $_SESSION['flash_msg']='Tidak ada data terbaca.'; }
  header('Location: home-admin.php?page=form-import-data-pendidikan'); exit;
}
?>
