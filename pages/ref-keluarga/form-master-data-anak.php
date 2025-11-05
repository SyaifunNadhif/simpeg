<?php
/*********************************************************
 * FILE    : pages/ref-keluarga/form-master-data-anak.php
 * MODULE  : SIMPEG — Data Anak (Entry)
 * VERSION : v1.3 (PHP 5.6 compatible)
 * DATE    : 2025-09-07
 * AUTHOR  : EWS/SIMPEG BKK Jateng — by ChatGPT
 *
 * CHANGELOG
 * - v1.3 (2025-09-07):
 *   • Mode EDIT: ?id=<id_anak> untuk prefill & update data.
 *   • Saat NIK duplikat → SweetAlert: "Edit data tersebut?" → Ya=masuk edit; Tidak=reset form & kembali ke entry.
 *   • Cek NIK server-side mengabaikan diri sendiri ketika EDIT.
 * - v1.2 (2025-09-07): Select2 picker pegawai; konfirmasi jika pegawai sudah punya anak; validasi NIK via AJAX.
 * - v1.1 (2025-09-07): Card layout, picker pegawai bila uid kosong, SweetAlert dasar.
 * - v1.0 (2025-09-07): Versi awal form entry, simpan ke tb_anak.
 *********************************************************/
?>
<!DOCTYPE html>
<html lang="id">
<head>
<?php
if (session_id()==='') session_start();
/* koneksi standar */
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn) || !$conn) { @include_once __DIR__ . '/../../config/koneksi.php'; }
if (!isset($conn) && isset($koneksi) && $koneksi) { $conn = $koneksi; }
if (!isset($koneksi) && isset($conn) && $conn) { $koneksi = $conn; }

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function postv($k,$d=''){ return isset($_POST[$k])?trim($_POST[$k]):$d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }
function toDate($s){ $s=trim($s); if($s==='')return ''; if(preg_match('~^\d{2}/\d{2}/\d{4}$~',$s)){ $a=explode('/',$s); return $a[2].'-'.$a[1].'-'.$a[0]; } return $s; }
$today = date('Y-m-d');

$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';

/* MODE: ADD/EDIT */
$id_anak = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id_anak>0 ? 'edit' : 'add';
$anak = null;
if ($mode==='edit'){
  $qa = mysqli_query($conn, "SELECT * FROM tb_anak WHERE id_anak=".(int)$id_anak." LIMIT 1");
  if ($qa && mysqli_num_rows($qa)>0){
    $anak = mysqli_fetch_assoc($qa);
    // paksa uid mengikuti data anak
    $uid = $anak['id_peg'];
  } else {
    $mode = 'add';
    $id_anak = 0;
  }
}

$pegawai=null; if($uid!==''){ $q=mysqli_query($conn,"SELECT id_peg,nama FROM tb_pegawai WHERE id_peg='".clean($conn,$uid)."' LIMIT 1"); if($q&&mysqli_num_rows($q)>0)$pegawai=mysqli_fetch_assoc($q); }

$status=''; $msg=''; $dup_id=0; $dup_nama='';

if($_SERVER['REQUEST_METHOD']==='POST'){
  $id_peg=clean($conn,postv('id_peg'));
  $nik=clean($conn,postv('nik'));
  $nama=clean($conn,postv('nama'));
  $tmp=clean($conn,postv('tmp_lhr'));
  $tgl=clean($conn,toDate(postv('tgl_lhr')));
  $pend=clean($conn,postv('pendidikan'));
  $id_pekerjaan=clean($conn,postv('id_pekerjaan'));
  $pekerjaan=clean($conn,postv('pekerjaan'));
  $status_hub=clean($conn,postv('status_hub'));
  $anak_ke=clean($conn,postv('anak_ke'));
  $bpjs=clean($conn,postv('bpjs_anak'));
  $id_anak_post = (int)postv('id_anak', 0);
  $mode_post = $id_anak_post>0 ? 'edit' : 'add';

  if($id_peg!=='' && $nama!==''){
    /* Cek NIK duplikat (per pegawai) jika NIK diisi; abaikan diri sendiri pada edit */
    if($nik!==''){
      $sqlNik = "SELECT id_anak,nama FROM tb_anak WHERE id_peg='{$id_peg}' AND nik='{$nik}'".
                ($mode_post==='edit' ? " AND id_anak<>".(int)$id_anak_post : "").
                " LIMIT 1";
      $qNik = mysqli_query($conn, $sqlNik);
      if($qNik && mysqli_num_rows($qNik)>0){
        $dup = mysqli_fetch_assoc($qNik);
        $status='duplikat_nik_id';
        $msg='NIK anak sudah terdaftar.';
        $dup_id=(int)$dup['id_anak'];
        $dup_nama=$dup['nama'];
      }
    }

    if($status===''){
      if($mode_post==='edit'){
        $sql="UPDATE tb_anak SET nik='{$nik}', nama='{$nama}', tmp_lhr='{$tmp}', tgl_lhr='{$tgl}', 
              pendidikan='{$pend}', id_pekerjaan='{$id_pekerjaan}', pekerjaan='{$pekerjaan}',
              status_hub='{$status_hub}', anak_ke='{$anak_ke}', bpjs_anak='{$bpjs}'
              WHERE id_anak={$id_anak_post} LIMIT 1";
      } else {
        $sql="INSERT INTO tb_anak(id_peg,id_peg_old,nik,nama,tmp_lhr,tgl_lhr,pendidikan,id_pekerjaan,pekerjaan,status_hub,anak_ke,bpjs_anak,date_reg)
              VALUES('{$id_peg}',NULL,'{$nik}','{$nama}','{$tmp}','{$tgl}','{$pend}','{$id_pekerjaan}','{$pekerjaan}','{$status_hub}','{$anak_ke}','{$bpjs}','{$today}')";
      }
      $ok=mysqli_query($conn,$sql); $status=$ok?'sukses':'gagal';
      if(!$ok){ $msg = 'Gagal menyimpan data anak.'; }
      // perbarui mode edit agar prefill setelah update
      if($ok && $mode_post==='edit') { $mode='edit'; $id_anak=$id_anak_post; }
    }
  } else { $status='gagal'; $msg='Periksa isian wajib (pegawai & nama).'; }
}
?>
  <meta charset="utf-8">
  <title><?php echo $mode==='edit' ? 'Edit Data Anak' : 'Entry Data Anak'; ?></title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>.card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}.card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}</style>
</head>
<body>
<div class="container mt-3">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0"><?php echo $mode==='edit' ? 'Edit Data Anak' : 'Entry Data Anak'; ?></h5>
        <small><?php echo $mode==='edit' ? 'Perbarui data anak pegawai' : 'Lengkapi data anak pegawai'; ?></small>
      </div>
    </div>
    <div class="card-body">
      <?php if($status==='sukses'):?>
      <script>
        Swal.fire({icon:'success',title:'<?php echo $mode==='edit' ? 'Diperbarui' : 'Tersimpan'; ?>'})
        .then(function(){ location.href='home-admin.php?page=form-view-data-anak&uid=<?php echo e($uid); ?>'; });
      </script>
      <?php elseif($status==='gagal'):?>
      <script>Swal.fire({icon:'error',title:'Gagal',text:<?php echo json_encode($msg?:'Periksa isian.'); ?>});</script>
      <?php elseif($status==='duplikat_nik_id'):?>
      <script>
        Swal.fire({
          icon:'warning',
          title:'NIK sudah ada',
          text:'NIK telah terdaftar milik <?php echo e($dup_nama); ?>. Apakah Anda ingin mengedit data tersebut?',
          showCancelButton:true,
          confirmButtonText:'Ya',
          cancelButtonText:'Tidak'
        }).then(function(ans){
          if(ans.isConfirmed){
            location.href='home-admin.php?page=form-master-data-anak&id=<?php echo (int)$dup_id; ?>&uid=<?php echo e($uid); ?>';
          } else {
            location.href='home-admin.php?page=form-master-data-anak&uid=<?php echo e($uid); ?>';
          }
        });
      </script>
      <?php endif; ?>

      <?php if($pegawai): ?>
        <div class="alert alert-info">Pegawai: <b><?php echo e($pegawai['nama']); ?></b> — ID: <?php echo e($pegawai['id_peg']); ?></div>
      <?php else: ?>
        <div class="mb-3">
          <label class="form-label">Pilih Pegawai</label>
          <select id="uid_picker" class="form-select" style="width:100%">
            <option value="">- Pilih Pegawai -</option>
            <?php $qp=mysqli_query($conn,"SELECT id_peg,nama FROM tb_pegawai WHERE status_aktif='1' ORDER BY nama ASC LIMIT 2000"); if($qp){while($p=mysqli_fetch_assoc($qp)){echo '<option value="'.e($p['id_peg']).'">'.e($p['nama'].' — '.$p['id_peg'])."</option>";}} ?>
          </select>
          <div class="form-text">Cari nama/ID menggunakan kolom di atas.</div>
        </div>
        <script>
          $(function(){
            $('#uid_picker').select2({ theme:'bootstrap-5', width:'100%', placeholder:'- Pilih Pegawai -' });
            $('#uid_picker').on('select2:select', function(e){
              var v = $(this).val(); if(!v) return;
              // Cek jumlah anak dulu
              $.getJSON('pages/ref-keluarga/ajax-anak-check.php', {uid:v}, function(res){
                var n = (res && res.count)? res.count : 0;
                if(n>0){
                  Swal.fire({
                    icon:'question', title:'Pegawai sudah punya '+n+' anak',
                    text:'Tambah data anak lagi?', showCancelButton:true,
                    confirmButtonText:'Ya, lanjut', cancelButtonText:'Batal'
                  }).then(function(x){ if(x.isConfirmed){
                      location.href='home-admin.php?page=form-master-data-anak&uid='+encodeURIComponent(v);
                  }});
                } else {
                  location.href='home-admin.php?page=form-master-data-anak&uid='+encodeURIComponent(v);
                }
              }).fail(function(){
                // jika cek gagal, tetap lanjut
                location.href='home-admin.php?page=form-master-data-anak&uid='+encodeURIComponent(v);
              });
            });
          });
        </script>
      <?php endif; ?>

      <form method="post" action="" autocomplete="off" id="frmAnak">
        <input type="hidden" name="id_peg" value="<?php echo e($uid); ?>">
        <?php if($mode==='edit'): ?>
          <input type="hidden" name="id_anak" value="<?php echo (int)$id_anak; ?>">
        <?php endif; ?>
        <div class="row">
          <div class="col-md-4"><label class="form-label">NIK</label><input name="nik" id="nik" class="form-control" value="<?php echo e($anak ? $anak['nik'] : ''); ?>"></div>
          <div class="col-md-8"><label class="form-label">Nama Anak <span class="text-danger">*</span></label><input name="nama" class="form-control" required value="<?php echo e($anak ? $anak['nama'] : ''); ?>"></div>
        </div>
        <div class="row mt-2">
          <div class="col-md-4"><label class="form-label">Tempat Lahir</label><input name="tmp_lhr" class="form-control" value="<?php echo e($anak ? $anak['tmp_lhr'] : ''); ?>"></div>
          <div class="col-md-4"><label class="form-label">Tanggal Lahir</label><input type="date" name="tgl_lhr" class="form-control" value="<?php echo e($anak ? $anak['tgl_lhr'] : ''); ?>"></div>
          <div class="col-md-4"><label class="form-label">Pendidikan</label><input name="pendidikan" class="form-control" placeholder="SMA/S1/dll" value="<?php echo e($anak ? $anak['pendidikan'] : ''); ?>"></div>
        </div>
        <div class="row mt-2">
          <div class="col-md-3"><label class="form-label">ID Pekerjaan</label><input name="id_pekerjaan" class="form-control" maxlength="3" value="<?php echo e($anak ? $anak['id_pekerjaan'] : ''); ?>"></div>
          <div class="col-md-5"><label class="form-label">Pekerjaan</label><input name="pekerjaan" class="form-control" value="<?php echo e($anak ? $anak['pekerjaan'] : ''); ?>"></div>
          <div class="col-md-2"><label class="form-label">Status Hub</label><input name="status_hub" class="form-control" placeholder="Kandung/Tiri" value="<?php echo e($anak ? $anak['status_hub'] : ''); ?>"></div>
          <div class="col-md-2"><label class="form-label">Anak ke</label><input name="anak_ke" class="form-control" maxlength="3" value="<?php echo e($anak ? $anak['anak_ke'] : ''); ?>"></div>
        </div>
        <div class="row mt-2">
          <div class="col-md-4"><label class="form-label">BPJS Anak</label><input name="bpjs_anak" class="form-control" value="<?php echo e($anak ? $anak['bpjs_anak'] : ''); ?>"></div>
        </div>
        <div class="d-flex justify-content-between mt-3">
          <a class="btn btn-outline-secondary" href="home-admin.php?page=form-view-data-anak">Kembali</a>
          <button class="btn btn-primary" type="submit" id="btnSimpan"><?php echo $mode==='edit' ? 'Update' : 'Simpan'; ?></button>
        </div>
      </form>

      <script>
        $(function(){
          // Cek duplikat NIK saat blur
          $('#nik').on('blur', function(){
            var nik = $.trim($(this).val());
            var idp = <?php echo json_encode($uid); ?>;
            if(!nik || !idp) return;
            $.getJSON('pages/ref-keluarga/ajax-anak-check.php', {mode:'nik', id_peg:idp, nik:nik}, function(res){
              if(res && res.exists){
                Swal.fire({
                  icon:'warning',
                  title:'NIK sudah ada',
                  text:'NIK sudah terdaftar milik '+(res.nama?res.nama:'anak lain')+'. Edit data tersebut?',
                  showCancelButton:true,
                  confirmButtonText:'Ya',
                  cancelButtonText:'Tidak'
                }).then(function(ans){
                  if(ans.isConfirmed){
                    window.location = 'home-admin.php?page=form-master-data-anak&id='+res.id_anak+'&uid='+idp;
                  } else {
                    $('#frmAnak')[0].reset();
                    $('#btnSimpan').prop('disabled', true);
                    setTimeout(function(){ $('#btnSimpan').prop('disabled', false); }, 300);
                  }
                });
              } else {
                $('#btnSimpan').prop('disabled', false);
              }
            });
          });
        });
      </script>

    </div>
  </div>
</div>
</body>
</html>
