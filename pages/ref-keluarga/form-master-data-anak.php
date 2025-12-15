<?php
/*********************************************************
 * FILE    : pages/ref-keluarga/form-master-data-anak.php
 * MODULE  : SIMPEG — Data Anak (Entry)
 * VERSION : v2.2 (Force Filtered View unless Detail)
 * DATE    : 2025-12-10
 * AUTHOR  : EWS/SIMPEG BKK Jateng — Modified by Gemini
 *********************************************************/
?>
<!DOCTYPE html>
<html lang="id">
<head>
<?php
if (session_id()==='') session_start();
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

// 1. Tangkap UID dari URL (jika Add)
$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';

// 2. Cek Mode & Ambil Data Anak (Penting: Dilakukan di awal untuk memastikan UID valid)
$id_anak = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id_anak>0 ? 'edit' : 'add';
$anak = null;

if ($mode==='edit'){
  $qa = mysqli_query($conn, "SELECT * FROM tb_anak WHERE id_anak=".(int)$id_anak." LIMIT 1");
  if ($qa && mysqli_num_rows($qa)>0){
    $anak = mysqli_fetch_assoc($qa);
    // Overwrite UID dari database agar akurat saat redirect
    $uid = $anak['id_peg'];
  } else {
    $mode = 'add';
    $id_anak = 0;
  }
}

/* --- LOGIC REDIRECT "CERDAS" --- */
// A. Default Emas: Selalu ke LIST TER-FILTER jika UID ada
$url_redirect = 'home-admin.php?page=form-view-data-anak';
if($uid !== '') {
    $url_redirect = 'home-admin.php?page=form-view-data-anak&uid=' . urlencode($uid);
}

// B. Cek History: Hanya ubah jika user datang dari DETAIL PEGAWAI
// Kita abaikan jika user datang dari list unfiltered ('form-view-data-anak' tanpa uid)
$referer = '';
if (isset($_POST['url_asal']) && !empty($_POST['url_asal'])) {
    $referer = $_POST['url_asal'];
} elseif (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
}

// Cek apakah referer mengandung 'view-detail-data-pegawai'
if ($referer !== '' && strpos($referer, 'view-detail-data-pegawai') !== false) {
    // Jika ya, kembalikan ke Detail Pegawai
    $url_redirect = 'home-admin.php?page=view-detail-data-pegawai&id_peg=' . urlencode($uid);
}
/* -------------------------------- */


// Ambil Data Pegawai untuk Info UI
$pegawai=null; if($uid!==''){ $q=mysqli_query($conn,"SELECT id_peg,nama FROM tb_pegawai WHERE id_peg='".clean($conn,$uid)."' LIMIT 1"); if($q&&mysqli_num_rows($q)>0)$pegawai=mysqli_fetch_assoc($q); }

/* LIST PEKERJAAN */
$list_pekerjaan = [];
$q_job = mysqli_query($conn, "SELECT id_pekerjaan, desc_pekerjaan FROM tb_master_pekerjaan ORDER BY id_pekerjaan ASC");
if($q_job){ while($row_job = mysqli_fetch_assoc($q_job)){ $list_pekerjaan[] = $row_job; } }

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
    if($nik!==''){
      $sqlNik = "SELECT id_anak,nama FROM tb_anak WHERE id_peg='{$id_peg}' AND nik='{$nik}'".
                ($mode_post==='edit' ? " AND id_anak<>".(int)$id_anak_post : ""). " LIMIT 1";
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
      if($ok && $mode_post==='edit') { $mode='edit'; $id_anak=$id_anak_post; }
    }
  } else { $status='gagal'; $msg='Periksa isian wajib (pegawai & nama).'; }
}
?>
  <meta charset="utf-8">
  <title><?php echo $mode==='edit' ? 'Edit Data Anak' : 'Entry Data Anak'; ?></title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}
    .card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}
    
    .input-group-valid { position: relative; }
    .validation-icon { position: absolute; right: 15px; top: 38px; z-index: 10; font-weight: bold; pointer-events: none; display: none; }
    
    .is-valid-custom { border-color: #198754 !important; background-color: #f8fff9 !important; transition: all 0.3s; }
    .icon-valid::after { content: '✅'; font-size: 1.1em; }
    
    .is-invalid-custom { border-color: #dc3545 !important; background-color: #fff8f8 !important; transition: all 0.3s; }
    .icon-invalid::after { content: '❌'; font-size: 1.1em; }

    .select2-container--bootstrap-5 .select2-selection { border-color: #ced4da; }
  </style>
</head>
<body>
<div class="container mt-3">
  <div class="card">
    <div class="card-header">
       <h5 class="mb-0"><?php echo $mode==='edit' ? 'Edit Data Anak' : 'Entry Data Anak'; ?></h5>
       <small><?php echo $mode==='edit' ? 'Perbarui data anak pegawai' : 'Lengkapi data anak pegawai'; ?></small>
    </div>
    <div class="card-body">
      <?php if($status==='sukses'):?>
      <script>
        Swal.fire({icon:'success',title:'<?php echo $mode==='edit' ? 'Diperbarui' : 'Tersimpan'; ?>', timer: 1500, showConfirmButton: false})
        .then(function(){ 
            // Redirect sesuai logic PHP "Cerdas" di atas
            location.href='<?php echo $url_redirect; ?>'; 
        });
      </script>
      <?php elseif($status==='gagal'):?>
      <script>Swal.fire({icon:'error',title:'Gagal',text:<?php echo json_encode($msg?:'Periksa isian.'); ?>});</script>
      <?php elseif($status==='duplikat_nik_id'):?>
      <script>
        Swal.fire({
          icon:'warning', title:'NIK sudah ada',
          text:'NIK milik <?php echo e($dup_nama); ?>. Edit data ini?',
          showCancelButton:true, confirmButtonText:'Ya', cancelButtonText:'Tidak'
        }).then(function(ans){
          if(ans.isConfirmed){ location.href='home-admin.php?page=form-master-data-anak&id=<?php echo (int)$dup_id; ?>&uid=<?php echo e($uid); ?>'; }
        });
      </script>
      <?php endif; ?>

      <?php if(!$pegawai): ?>
        <div class="mb-3">
          <label class="form-label">Pilih Pegawai</label>
          <select id="uid_picker" class="form-select">
            <option value="">- Pilih Pegawai -</option>
            <?php $qp=mysqli_query($conn,"SELECT id_peg,nama FROM tb_pegawai WHERE status_aktif='1' ORDER BY nama ASC LIMIT 2000"); if($qp){while($p=mysqli_fetch_assoc($qp)){echo '<option value="'.e($p['id_peg']).'">'.e($p['nama'].' — '.$p['id_peg'])."</option>";}} ?>
          </select>
        </div>
        <script>
          $(function(){
            $('#uid_picker').select2({ theme:'bootstrap-5', width:'100%', placeholder:'- Pilih Pegawai -' });
            $('#uid_picker').on('select2:select', function(e){
               var v = $(this).val(); if(v) window.location.href='home-admin.php?page=form-master-data-anak&uid='+encodeURIComponent(v);
            });
          });
        </script>
      <?php else: ?>
        <div class="alert alert-info py-2">Pegawai: <b><?php echo e($pegawai['nama']); ?></b></div>
      <?php endif; ?>

      <form method="post" action="" autocomplete="off" id="frmAnak">
        <input type="hidden" name="url_asal" value="<?php echo e($referer); ?>">
        
        <input type="hidden" name="id_peg" value="<?php echo e($uid); ?>">
        <?php if($mode==='edit'): ?> <input type="hidden" name="id_anak" value="<?php echo (int)$id_anak; ?>"> <?php endif; ?>
        
        <div class="row">
          <div class="col-md-4 input-group-valid">
            <label class="form-label">NIK <span class="text-danger">*</span></label>
            <input name="nik" id="nik" class="form-control check-field" placeholder="NIK Anak" value="<?php echo e($anak ? $anak['nik'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
          <div class="col-md-8 input-group-valid">
            <label class="form-label">Nama Anak <span class="text-danger">*</span></label>
            <input name="nama" class="form-control check-field" required placeholder="Nama Lengkap" value="<?php echo e($anak ? $anak['nama'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-4 input-group-valid">
            <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
            <input name="tmp_lhr" class="form-control check-field" value="<?php echo e($anak ? $anak['tmp_lhr'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
          <div class="col-md-4 input-group-valid">
            <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
            <input type="date" name="tgl_lhr" class="form-control check-field" value="<?php echo e($anak ? $anak['tgl_lhr'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
          <div class="col-md-4 input-group-valid">
            <label class="form-label">Pendidikan <span class="text-danger">*</span></label>
            <select name="pendidikan" class="form-select check-field">
                <option value="">- Pilih -</option>
                <?php foreach(['Belum Sekolah','PAUD','TK','SD','SMP','SMA','D1','D2','D3','D4','S1','S2'] as $p):
                    $sel = ($anak && $anak['pendidikan'] == $p) ? 'selected' : ''; echo "<option value=\"$p\" $sel>$p</option>";
                endforeach; ?>
            </select>
            <span class="validation-icon"></span>
          </div>
        </div>
        
        <div class="row mt-2">
          <input type="hidden" name="id_pekerjaan" id="id_pekerjaan" value="<?php echo e($anak ? $anak['id_pekerjaan'] : ''); ?>">
          
          <div class="col-md-4 input-group-valid">
             <label class="form-label">Pekerjaan <span class="text-danger">*</span></label>
             <input type="hidden" name="pekerjaan" id="nama_pekerjaan" value="<?php echo e($anak ? $anak['pekerjaan'] : ''); ?>">
             <select id="picker_pekerjaan" class="form-select check-field">
                <option value="">- Pilih -</option>
                <?php foreach($list_pekerjaan as $job): 
                    $selected = ($anak && $anak['id_pekerjaan'] == $job['id_pekerjaan']) ? 'selected' : ''; 
                ?>
                    <option value="<?php echo $job['id_pekerjaan']; ?>" data-nama="<?php echo e($job['desc_pekerjaan']); ?>" <?php echo $selected; ?>><?php echo e($job['desc_pekerjaan']); ?></option>
                <?php endforeach; ?>
             </select>
             <span class="validation-icon" id="icon_pekerjaan"></span>
          </div>

          <div class="col-md-4 input-group-valid">
            <label class="form-label">BPJS Anak <span class="text-danger">*</span></label>
            <input name="bpjs_anak" class="form-control check-field" placeholder="No. BPJS" value="<?php echo e($anak ? $anak['bpjs_anak'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
          
          <div class="col-md-2 input-group-valid">
            <label class="form-label">Status Hub</label>
            <select name="status_hub" class="form-select check-field">
                <option value="">- Pilih -</option>
                <?php foreach(['Anak Kandung','Anak Tiri','Anak Angkat'] as $h): $sel = ($anak && $anak['status_hub'] == $h) ? 'selected' : ''; echo "<option value=\"$h\" $sel>$h</option>"; endforeach; ?>
            </select>
            <span class="validation-icon"></span>
          </div>

          <div class="col-md-2 input-group-valid">
            <label class="form-label">Anak ke</label>
            <input name="anak_ke" class="form-control check-field" maxlength="3" value="<?php echo e($anak ? $anak['anak_ke'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
          <a class="btn btn-outline-secondary" href="<?php echo $url_redirect; ?>">Kembali</a>
          <button class="btn btn-primary" type="button" id="btnCheckAndSubmit"><?php echo $mode==='edit' ? 'Update Data' : 'Simpan Data'; ?></button>
        </div>
      </form>

      <script>
        $(function(){
          $('#picker_pekerjaan').select2({ theme: 'bootstrap-5', width: '100%', placeholder: '- Pilih -' });
          $('#picker_pekerjaan').on('change', function(){
              var id = $(this).val(); 
              var text = $(this).find(':selected').data('nama');
              $('#id_pekerjaan').val(id);
              $('#nama_pekerjaan').val(text || '');
              validateSingleField($(this)); 
          });

          $('.check-field').on('input change', function(){
              if($(this).hasClass('is-invalid-custom') || $(this).hasClass('is-valid-custom')){
                  validateSingleField($(this));
              }
          });

          function validateSingleField($el) {
             let val = $el.val();
             let $icon = $el.siblings('.validation-icon');
             
             if($el.attr('id') === 'picker_pekerjaan') {
                $icon = $('#icon_pekerjaan');
                let $s2cont = $el.next('.select2-container').find('.select2-selection');
                if(val && $.trim(val) !== '') {
                    $s2cont.addClass('is-valid-custom').removeClass('is-invalid-custom');
                    $icon.addClass('icon-valid').removeClass('icon-invalid').show();
                    return true;
                } else {
                    $s2cont.removeClass('is-valid-custom').addClass('is-invalid-custom');
                    $icon.addClass('icon-invalid').removeClass('icon-valid').show();
                    return false;
                }
             }

             if(val && $.trim(val) !== '') {
                 $el.addClass('is-valid-custom').removeClass('is-invalid-custom');
                 $icon.addClass('icon-valid').removeClass('icon-invalid').show();
                 return true;
             } else {
                 $el.addClass('is-invalid-custom').removeClass('is-valid-custom');
                 $icon.addClass('icon-invalid').removeClass('icon-valid').show();
                 return false;
             }
          }

          $('#btnCheckAndSubmit').on('click', function(e){
              e.preventDefault();
              let allValid = true;
              $('.check-field').each(function(){
                  let isValid = validateSingleField($(this));
                  if(!isValid) allValid = false;
              });

              if(allValid) {
                  $('#frmAnak').submit();
              } else {
                  Swal.fire({
                      icon: 'warning',
                      title: 'Data Belum Lengkap',
                      text: 'Mohon lengkapi kolom yang bertanda silang merah.',
                      confirmButtonText: 'Oke, Saya Lengkapi'
                  });
              }
          });
        });
      </script>
    </div>
  </div>
</div>
</body>
</html>