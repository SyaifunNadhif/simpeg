<?php
/*********************************************************
 * FILE    : pages/ref-keluarga/form-master-data-ortu.php
 * MODULE  : SIMPEG — Data Orang Tua (Entry)
 * VERSION : v2.4 (Dropdown Pekerjaan & Smart Redirect)
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

// --- 1. AMBIL ID & CONTEXT ---
$id_ortu = isset($_GET['id_ortu']) ? (int)$_GET['id_ortu'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$uid_url = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$mode    = $id_ortu>0 ? 'edit' : 'add';

// Jika Edit, ambil UID asli dari database
$ortu = null;
if ($mode==='edit'){
  $qOr = mysqli_query($conn, "SELECT * FROM tb_ortu WHERE id_ortu=".(int)$id_ortu." LIMIT 1");
  if ($qOr && mysqli_num_rows($qOr)>0){
    $ortu = mysqli_fetch_assoc($qOr);
    $uid  = $ortu['id_peg']; 
  } else {
    $mode = 'add'; 
    $id_ortu = 0;
    $uid = $uid_url;
  }
} else {
    $uid = $uid_url;
}

// --- 2. LOGIC REDIRECT PINTAR ---
$url_redirect = 'home-admin.php?page=form-view-data-ortu'; // Default
if($uid !== '') {
    $url_redirect = 'home-admin.php?page=form-view-data-ortu&uid=' . urlencode($uid);
}

if (isset($_POST['url_asal']) && !empty($_POST['url_asal'])) {
    $url_redirect = $_POST['url_asal'];
} elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    $ref = $_SERVER['HTTP_REFERER'];
    if (strpos($ref, 'view-detail-data-pegawai') !== false) {
        $url_redirect = 'home-admin.php?page=view-detail-data-pegawai&id_peg=' . urlencode($uid);
    }
}
// --------------------------------

$pegawai=null; if($uid!==''){ $q=mysqli_query($conn,"SELECT id_peg,nama FROM tb_pegawai WHERE id_peg='".clean($conn,$uid)."' LIMIT 1"); if($q&&mysqli_num_rows($q)>0)$pegawai=mysqli_fetch_assoc($q); }

/* LIST PEKERJAAN */
$list_pekerjaan = [];
$q_job = mysqli_query($conn, "SELECT id_pekerjaan, desc_pekerjaan FROM tb_master_pekerjaan ORDER BY id_pekerjaan ASC");
if($q_job){ while($row_job = mysqli_fetch_assoc($q_job)){ $list_pekerjaan[] = $row_job; } }

$status=''; $msg='';

if($_SERVER['REQUEST_METHOD']==='POST'){
  $id_peg=clean($conn,postv('id_peg'));
  $nik=clean($conn,postv('nik'));
  $nama=clean($conn,postv('nama'));
  $tmp=clean($conn,postv('tmp_lhr'));
  $tgl=clean($conn,toDate(postv('tgl_lhr')));
  $pend=clean($conn,postv('pendidikan'));
  
  // Ambil dari Hidden Input yang diisi JS
  $id_pekerjaan=clean($conn,postv('id_pekerjaan'));
  $pekerjaan=clean($conn,postv('pekerjaan')); 
  
  $status_hub=clean($conn,postv('status_hub'));

  $id_ortu_post = (int)postv('id_ortu', 0);
  $mode_post = $id_ortu_post>0 ? 'edit' : 'add';

  if($id_peg!=='' && $nama!==''){
      if($mode_post==='edit'){
        $sql="UPDATE tb_ortu SET nik='{$nik}', nama='{$nama}', tmp_lhr='{$tmp}', tgl_lhr='{$tgl}', 
              pendidikan='{$pend}', id_pekerjaan='{$id_pekerjaan}', pekerjaan='{$pekerjaan}',
              status_hub='{$status_hub}'
              WHERE id_ortu={$id_ortu_post} LIMIT 1";
      } else {
        $sql="INSERT INTO tb_ortu(id_peg,nik,nama,tmp_lhr,tgl_lhr,pendidikan,id_pekerjaan,pekerjaan,status_hub,date_reg)
              VALUES('{$id_peg}','{$nik}','{$nama}','{$tmp}','{$tgl}','{$pend}','{$id_pekerjaan}','{$pekerjaan}','{$status_hub}','{$today}')";
      }
      $ok=mysqli_query($conn,$sql); $status=$ok?'sukses':'gagal';
      if(!$ok){ $msg = 'Gagal menyimpan data orang tua.'; }
      if($ok && $mode_post==='edit') { $mode='edit'; $id_ortu=$id_ortu_post; }
  } else { $status='gagal'; $msg='Periksa isian wajib (pegawai & nama).'; }
}
?>
  <meta charset="utf-8">
  <title><?php echo $mode==='edit' ? 'Edit Data Orang Tua' : 'Entry Data Orang Tua'; ?></title>
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
       <h5 class="mb-0"><?php echo $mode==='edit' ? 'Edit Data Orang Tua' : 'Entry Data Orang Tua'; ?></h5>
       <small><?php echo $mode==='edit' ? 'Perbarui data orang tua pegawai' : 'Lengkapi data orang tua pegawai'; ?></small>
    </div>
    <div class="card-body">
      <?php if($status==='sukses'):?>
      <script>
        Swal.fire({icon:'success',title:'<?php echo $mode==='edit' ? 'Diperbarui' : 'Tersimpan'; ?>', timer: 1500, showConfirmButton: false})
        .then(function(){ location.href='<?php echo $url_redirect; ?>'; });
      </script>
      <?php elseif($status==='gagal'):?>
      <script>Swal.fire({icon:'error',title:'Gagal',text:<?php echo json_encode($msg?:'Periksa isian.'); ?>});</script>
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
               var v = $(this).val(); if(v) window.location.href='home-admin.php?page=form-master-data-ortu&uid='+encodeURIComponent(v);
            });
          });
        </script>
      <?php else: ?>
        <div class="alert alert-info py-2 d-flex justify-content-between align-items-center">
             <span>Pegawai: <b><?php echo e($pegawai['nama']); ?></b> — ID: <?php echo e($pegawai['id_peg']); ?></span>
        </div>
      <?php endif; ?>

      <form method="post" action="" autocomplete="off" id="frmOrtu">
        <input type="hidden" name="url_asal" value="<?php echo e($url_redirect); ?>">
        <input type="hidden" name="id_peg" value="<?php echo e($uid); ?>">
        <?php if($mode==='edit'): ?> <input type="hidden" name="id_ortu" value="<?php echo (int)$id_ortu; ?>"> <?php endif; ?>
        
        <div class="row">
          <div class="col-md-4 input-group-valid">
            <label class="form-label">NIK Orang Tua</label>
            <input name="nik" id="nik" class="form-control check-field" placeholder="NIK (KTP)" value="<?php echo e($ortu ? $ortu['nik'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
          <div class="col-md-8 input-group-valid">
            <label class="form-label">Nama Orang Tua <span class="text-danger">*</span></label>
            <input name="nama" class="form-control check-field" required placeholder="Nama Lengkap" value="<?php echo e($ortu ? $ortu['nama'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-6 input-group-valid">
            <label class="form-label">Tempat Lahir</label>
            <input name="tmp_lhr" class="form-control check-field" value="<?php echo e($ortu ? $ortu['tmp_lhr'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
          <div class="col-md-6 input-group-valid">
            <label class="form-label">Tanggal Lahir</label>
            <input type="date" name="tgl_lhr" class="form-control check-field" value="<?php echo e($ortu ? $ortu['tgl_lhr'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
        </div>
        
        <div class="row mt-2">
          <div class="col-md-4 input-group-valid">
            <label class="form-label">Pendidikan Terakhir</label>
            <select name="pendidikan" class="form-select check-field">
                <option value="">- Pilih -</option>
                <?php foreach(['SD','SMP','SMA','D1','D2','D3','D4','S1','S2','S3'] as $p):
                    $sel = ($ortu && $ortu['pendidikan'] == $p) ? 'selected' : ''; echo "<option value=\"$p\" $sel>$p</option>";
                endforeach; ?>
            </select>
            <span class="validation-icon"></span>
          </div>

          <input type="hidden" name="id_pekerjaan" id="id_pekerjaan" value="<?php echo e($ortu ? $ortu['id_pekerjaan'] : ''); ?>">
          
          <div class="col-md-4 input-group-valid">
             <label class="form-label">Pekerjaan</label>
             <input type="hidden" name="pekerjaan" id="nama_pekerjaan" value="<?php echo e($ortu ? $ortu['pekerjaan'] : ''); ?>">
             
             <select id="picker_pekerjaan" class="form-select check-field">
                <option value="">- Pilih -</option>
                <?php foreach($list_pekerjaan as $job): 
                    $selected = ($ortu && $ortu['id_pekerjaan'] == $job['id_pekerjaan']) ? 'selected' : ''; 
                ?>
                    <option value="<?php echo $job['id_pekerjaan']; ?>" data-nama="<?php echo e($job['desc_pekerjaan']); ?>" <?php echo $selected; ?>><?php echo e($job['desc_pekerjaan']); ?></option>
                <?php endforeach; ?>
             </select>
             <span class="validation-icon" id="icon_pekerjaan"></span>
          </div>
          
          <div class="col-md-4 input-group-valid">
            <label class="form-label">Status Hubungan <span class="text-danger">*</span></label>
            <select name="status_hub" class="form-select check-field" required>
                <option value="">- Pilih -</option>
                <?php foreach(['Ayah Kandung','Ibu Kandung','Ayah Tiri','Ibu Tiri','Mertua L','Mertua P','Wali'] as $h): 
                    $sel = ($ortu && $ortu['status_hub'] == $h) ? 'selected' : ''; echo "<option value=\"$h\" $sel>$h</option>"; 
                endforeach; ?>
            </select>
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
          // Init Select2
          $('#picker_pekerjaan').select2({ theme: 'bootstrap-5', width: '100%', placeholder: '- Pilih -' });
          
          // Logic: Update Hidden Inputs saat Dropdown Berubah
          $('#picker_pekerjaan').on('change', function(){
              var id = $(this).val(); 
              var text = $(this).find(':selected').data('nama');
              $('#id_pekerjaan').val(id);
              $('#nama_pekerjaan').val(text || ''); // Isi input hidden 'pekerjaan'
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
                  $('#frmOrtu').submit();
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