<?php
/*********************************************************
 * FILE    : pages/ref-sertifikasi/form-master-data-sertifikasi.php
 * MODULE  : SIMPEG — Data Sertifikasi (Entry)
 * VERSION : v1.0 (PHP 5.6)
 * DATE    : 2025-09-07
 * Fitur   : Select2 picker, duplikat (id_peg+sertifikasi+tgl_sertifikat).
 *********************************************************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn) || !$conn) { @include_once __DIR__ . '/../../config/koneksi.php'; if(isset($koneksi)) $conn=$koneksi; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function postv($k,$d=''){ return isset($_POST[$k])?trim($_POST[$k]):$d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }

$today=date('Y-m-d'); $status=''; $msg='';
$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$pegawai=null; if($uid!==''){ $q=mysqli_query($conn,"SELECT id_peg,nama FROM tb_pegawai WHERE id_peg='".clean($conn,$uid)."' LIMIT 1"); if($q&&mysqli_num_rows($q)>0)$pegawai=mysqli_fetch_assoc($q); }

if($_SERVER['REQUEST_METHOD']==='POST'){
  $id_peg=clean($conn,postv('id_peg'));
  $sertifikasi=clean($conn,postv('sertifikasi'));
  $penyelenggara=clean($conn,postv('penyelenggara'));
  $tgl_sertifikat=clean($conn,postv('tgl_sertifikat'));
  $tgl_expired=clean($conn,postv('tgl_expired'));
  $sertifikat=clean($conn,postv('sertifikat'));

  if($id_peg!=='' && $sertifikasi!==''){
    $qDup=mysqli_query($conn,"SELECT 1 FROM tb_sertifikasi WHERE id_peg='{$id_peg}' AND sertifikasi='{$sertifikasi}' AND tgl_sertifikat".($tgl_sertifikat!==''?"='{$tgl_sertifikat}'":" IS NULL")." LIMIT 1");
    if($qDup && mysqli_num_rows($qDup)>0){ $status='duplikat'; $msg='Sertifikasi sudah ada pada tanggal tersebut.'; }
    if($status===''){
      $sql="INSERT INTO tb_sertifikasi(id_peg,sertifikasi,penyelenggara,tgl_sertifikat,tgl_expired,sertifikat,date_reg,created_by)
            VALUES('{$id_peg}','{$sertifikasi}','{$penyelenggara}',".($tgl_sertifikat!==''?"'{$tgl_sertifikat}'":"NULL").",".($tgl_expired!==''?"'{$tgl_expired}'":"NULL").",'{$sertifikat}',NOW(),'admin')";
      $ok=mysqli_query($conn,$sql); $status=$ok?'sukses':'gagal'; if(!$ok)$msg='Gagal menyimpan data.';
    }
  } else { $status='gagal'; $msg='Pegawai & sertifikasi wajib diisi.'; }
}
?>
<!DOCTYPE html><html lang="id"><head>
  <meta charset="utf-8"><title>Entry Sertifikasi</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>.card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}.card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}</style>
</head><body>
<div class="container mt-3">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div><h5 class="mb-0">Entry Sertifikasi</h5><small>Lengkapi data sertifikasi pegawai</small></div>
    </div>
    <div class="card-body">
      <?php if($status==='sukses'):?><script>Swal.fire({icon:'success',title:'Tersimpan'}).then(function(){location.href='home-admin.php?page=form-view-data-sertifikasi&uid=<?php echo e($uid); ?>';});</script>
      <?php elseif($status==='gagal'):?><script>Swal.fire({icon:'error',title:'Gagal',text:<?php echo json_encode($msg?:'Periksa isian.'); ?>});</script>
      <?php elseif($status==='duplikat'):?><script>Swal.fire({icon:'warning',title:'Duplikat',text:<?php echo json_encode($msg); ?>});</script><?php endif; ?>

      <?php if($pegawai): ?>
        <div class="alert alert-info">Pegawai: <b><?php echo e($pegawai['nama']); ?></b> — ID: <?php echo e($pegawai['id_peg']); ?></div>
      <?php else: ?>
        <div class="mb-3">
          <label class="form-label">Pilih Pegawai</label>
          <select id="uid_picker" class="form-select" style="width:100%">
            <option value="">- Pilih Pegawai -</option>
            <?php $qp=mysqli_query($conn,"SELECT id_peg,nama FROM tb_pegawai WHERE status_aktif='1' ORDER BY nama ASC LIMIT 2000"); if($qp){ while($p=mysqli_fetch_assoc($qp)){ echo '<option value="'.e($p['id_peg']).'">'.e($p['nama'].' — '.$p['id_peg']).'</option>'; } } ?>
          </select>
          <script>$(function(){ $('#uid_picker').select2({theme:'bootstrap-5',width:'100%'}).on('select2:select',function(){var v=$(this).val(); if(v){ location.href='home-admin.php?page=form-master-data-sertifikasi&uid='+encodeURIComponent(v); }}); });</script>
        </div>
      <?php endif; ?>

      <form method="post" action="" autocomplete="off" id="frmSertif">
        <input type="hidden" name="id_peg" value="<?php echo e($uid); ?>">
        <div class="row">
          <div class="col-md-6"><label class="form-label">Nama Sertifikasi <span class="text-danger">*</span></label><input name="sertifikasi" id="sertifikasi" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label">Penyelenggara</label><input name="penyelenggara" class="form-control"></div>
        </div>
        <div class="row mt-2">
          <div class="col-md-4"><label class="form-label">No/Kode Sertifikat</label><input name="sertifikat" class="form-control" placeholder="opsional"></div>
          <div class="col-md-4"><label class="form-label">Tanggal Sertifikat</label><input type="date" name="tgl_sertifikat" id="tgl_sertifikat" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Tanggal Expired</label><input type="date" name="tgl_expired" class="form-control" placeholder="opsional"></div>
        </div>
        <div class="d-flex justify-content-between mt-3">
          <a class="btn btn-outline-secondary" href="home-admin.php?page=form-view-data-sertifikasi<?php echo $uid?'&uid='.urlencode($uid):''; ?>">Kembali</a>
          <button class="btn btn-primary" type="submit" id="btnSimpan">Simpan</button>
        </div>
      </form>
      <script>
        $(function(){
          function cekDup(){
            var idp=<?php echo json_encode($uid); ?>, s=$.trim($('#sertifikasi').val()), ts=$.trim($('#tgl_sertifikat').val());
            if(!idp || !s) return;
            $.getJSON('pages/ref-sertifikasi/helper-sertifikasi.php', {mode:'dup', id_peg:idp, sertifikasi:s, tgl_sertifikat:ts}, function(r){
              if(r && r.exists){ Swal.fire({icon:'warning',title:'Duplikat',text:'Sertifikasi dengan tanggal tersebut sudah ada.'}); $('#btnSimpan').prop('disabled',true); }
              else { $('#btnSimpan').prop('disabled',false); }
            });
          }
          $('#sertifikasi,#tgl_sertifikat').on('blur', cekDup);
        });
      </script>
    </div>
  </div>
</div>
</body></html>
