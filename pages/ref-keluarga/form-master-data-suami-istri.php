<?php
/*********************************************************
 * DIR     : pages/ref-keluarga/form-master-data-suami-istri.php
 * MODULE  : SIMPEG — Data Suami/Istri (tb_suamiistri)
 * VERSION : v1.5 (PHP 5.6 + CDN Select2)
 * DATE    : 2025-10-11
 *
 * CHANGELOG
 * - v1.5: Saat mode=edit -> dropdown Pegawai otomatis terpilih & dikunci (disabled),
 *         namun nilai tetap terkirim via hidden field. Inisialisasi Select2 disesuaikan.
 * - v1.4: Pakai CDN Select2; cek otomatis pasangan (SweetAlert);
 *         dropdown pekerjaan tampil desc_pekerjaan tapi simpan id_pekerjaan;
 *         auto-isi nama pekerjaan dari opsi; semua select pakai Select2.
 *********************************************************/

if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; }

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function postv($k,$d=''){ return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }
function fmt_date($s){ return ($s && $s!='0000-00-00') ? date('d-m-Y', strtotime($s)) : '-'; }

$today = date('Y-m-d');
$mode  = isset($_GET['mode']) ? $_GET['mode'] : 'tambah';
$id_si = isset($_GET['id_si']) ? trim($_GET['id_si']) : '';
$uid   = isset($_GET['uid'])   ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';

$pegawai = null;
if ($uid!=='') {
  $q = mysqli_query($conn, "SELECT id_peg,nama,jk,tempat_lhr,tgl_lhr FROM tb_pegawai WHERE id_peg='".clean($conn,$uid)."' LIMIT 1");
  if ($q && mysqli_num_rows($q)>0) $pegawai = mysqli_fetch_assoc($q);
}

$rowEdit = null;
if ($mode==='edit' && $id_si!=='') {
  $qe = mysqli_query($conn, "SELECT * FROM tb_suamiistri WHERE id_si='".clean($conn,$id_si)."' LIMIT 1");
  if ($qe && mysqli_num_rows($qe)>0) {
    $rowEdit = mysqli_fetch_assoc($qe);
    $uid = $rowEdit['id_peg'];
    if (!$pegawai && $uid!=='') {
      $p2 = mysqli_query($conn, "SELECT id_peg,nama,jk,tempat_lhr,tgl_lhr FROM tb_pegawai WHERE id_peg='".clean($conn,$uid)."' LIMIT 1");
      if ($p2 && mysqli_num_rows($p2)>0) $pegawai = mysqli_fetch_assoc($p2);
    }
  }
}

$status=''; $errMsg='';

// generator id_si: SI0001, SI0002, ...
function generate_id_si($conn){
  $res = mysqli_query($conn,"SELECT MAX(CAST(SUBSTRING(id_si,3) AS UNSIGNED)) AS maxid FROM tb_suamiistri");
  $num = 0;
  if($res && ($r=mysqli_fetch_assoc($res))) $num = (int)$r['maxid'];
  return 'SI'.str_pad($num+1, 4, '0', STR_PAD_LEFT);
}

// submit
if ($_SERVER['REQUEST_METHOD']==='POST'){
  $id_si        = postv('id_si');
  $id_peg       = clean($conn, postv('id_peg'));
  $nik          = postv('nik');
  $nama         = postv('nama');
  $tmp_lhr      = postv('tmp_lhr');
  $tgl_lhr      = postv('tgl_lhr');
  $pendidikan   = postv('pendidikan');
  $id_pekerjaan = postv('id_pekerjaan');
  $pekerjaan    = postv('pekerjaan');
  $status_hub   = postv('status_hub');
  $hp           = postv('hp');
  $bpjs         = postv('bpjs_pasangan');

  if ($id_peg==='')           $errMsg='ID Pegawai belum diisi.';
  elseif ($nama==='')         $errMsg='Nama pasangan wajib diisi.';
  elseif ($tmp_lhr==='')      $errMsg='Tempat lahir wajib diisi.';
  elseif ($tgl_lhr==='')      $errMsg='Tanggal lahir wajib diisi.';
  elseif ($id_pekerjaan==='') $errMsg='Kategori pekerjaan wajib dipilih.';
  elseif ($status_hub==='')   $errMsg='Status hubungan wajib dipilih.';

  if ($errMsg==='') {
    if ($mode==='edit' && $id_si!=='') {
      $sql = "UPDATE tb_suamiistri SET ".
             "id_peg='{$id_peg}',".
             "nik=".($nik!==''?"'".clean($conn,$nik)."'":"NULL").",".
             "nama='".clean($conn,$nama)."',".
             "tmp_lhr='".clean($conn,$tmp_lhr)."',".
             "tgl_lhr='".clean($conn,$tgl_lhr)."',".
             "pendidikan=".($pendidikan!==''?"'".clean($conn,$pendidikan)."'":"NULL").",".
             "id_pekerjaan='".clean($conn,$id_pekerjaan)."',".
             "pekerjaan=".($pekerjaan!==''?"'".clean($conn,$pekerjaan)."'":"NULL").",".
             "status_hub='".clean($conn,$status_hub)."',".
             "hp=".($hp!==''?"'".clean($conn,$hp)."'":"NULL").",".
             "bpjs_pasangan=".($bpjs!==''?"'".clean($conn,$bpjs)."'":"NULL")." ".
             "WHERE id_si='".clean($conn,$id_si)."' LIMIT 1";
      $ok = mysqli_query($conn,$sql);
      $status = $ok?'sukses':'gagal';
    } else {
      $new_id = generate_id_si($conn);
      $sql = "INSERT INTO tb_suamiistri(id_si,id_peg,nik,nama,tmp_lhr,tgl_lhr,pendidikan,id_pekerjaan,pekerjaan,status_hub,hp,bpjs_pasangan,date_reg)
              VALUES('{$new_id}','{$id_peg}',".
              ($nik!==''?"'".clean($conn,$nik)."'":"NULL").",".
              "'".clean($conn,$nama)."','".clean($conn,$tmp_lhr)."','".clean($conn,$tgl_lhr)."',".
              ($pendidikan!==''?"'".clean($conn,$pendidikan)."'":"NULL").",".
              "'".clean($conn,$id_pekerjaan)."',".
              ($pekerjaan!==''?"'".clean($conn,$pekerjaan)."'":"NULL").",".
              "'".clean($conn,$status_hub)."',".
              ($hp!==''?"'".clean($conn,$hp)."'":"NULL").",".
              ($bpjs!==''?"'".clean($conn,$bpjs)."'":"NULL").",".
              "'{$today}')";
      $ok = mysqli_query($conn,$sql);
      $status = $ok?'sukses':'gagal';
    }
  } else { $status='gagal'; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo ($mode==='edit'?'Ubah':'Tambah'); ?> Data Suami/Istri</title>

<!-- Bootstrap 4 (CSS) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- Select2 (CDN) + Theme Bootstrap4 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
  .form-section{max-width:880px;margin:20px auto}
  .card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}
  .card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}
  .readonly-info{background:#f8fafc;border-radius:10px;padding:10px}
</style>
</head>
<body>
<div class="form-section">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0"><?php echo ($mode==='edit'?'Ubah':'Tambah'); ?> Data Suami/Istri</h5>
        <small>Lengkapi data pasangan sesuai dokumen kependudukan.</small>
      </div>
    </div>
    <div class="card-body">

<?php if($status==='sukses'): ?>
<script>
Swal.fire({icon:'success',title:'Tersimpan',text:'Data pasangan berhasil disimpan.'}).then(function(){
  window.location='home-admin.php?page=form-view-data-suami-istri<?php echo ($uid? '&uid='.urlencode($uid):''); ?>';
});
</script>
<?php elseif($status==='gagal' && $errMsg!=''): ?>
<script>Swal.fire({icon:'error',title:'Gagal',text:<?php echo json_encode($errMsg); ?>});</script>
<?php elseif($status==='gagal'): ?>
<script>Swal.fire({icon:'error',title:'Gagal',text:'Terjadi kesalahan penyimpanan.'});</script>
<?php endif; ?>

<form method="post" action="" autocomplete="off">
<?php if($mode==='edit'): ?>
  <input type="hidden" name="id_si" value="<?php echo e($rowEdit?$rowEdit['id_si']:''); ?>">
<?php endif; ?>

<?php
  // id pegawai yang harus terpilih pada select (edit -> id_peg dari rowEdit; tambah -> uid bila ada)
  $currentPeg = ($mode==='edit' && $rowEdit) ? $rowEdit['id_peg'] : $uid;
?>
<div class="form-group">
  <label>Pilih Pegawai <span class="text-danger">*</span></label>

  <!-- Select utama: dikunci saat edit, required saat tambah -->
  <select name="id_peg" id="id_peg" class="form-control select2bs4" <?php echo ($mode==='edit' ? 'disabled' : 'required'); ?> style="width:100%">
    <option value="">— pilih pegawai —</option>
    <?php
    $rp = mysqli_query($conn,"SELECT id_peg,nama FROM tb_pegawai WHERE status_aktif=1 ORDER BY id_peg");
    if($rp){ while($pg=mysqli_fetch_assoc($rp)){
      $sel = ($currentPeg === $pg['id_peg']) ? 'selected' : '';
      echo '<option value="'.e($pg['id_peg']).'" '.$sel.'>'.e($pg['id_peg'].' — '.$pg['nama'])."</option>";
    }}
    ?>
  </select>

  <?php if($mode==='edit' && $currentPeg!=''): ?>
    <!-- Hidden agar nilai tetap terkirim saat select disabled -->
    <input type="hidden" name="id_peg" value="<?php echo e($currentPeg); ?>">
  <?php endif; ?>
</div>

<div class="row">
  <div class="col-md-6">
    <label>Nama Pasangan <span class="text-danger">*</span></label>
    <input type="text" name="nama" class="form-control" required value="<?php echo e($rowEdit?$rowEdit['nama']:''); ?>">
  </div>
  <div class="col-md-6">
    <label>NIK</label>
    <input type="text" name="nik" maxlength="16" class="form-control" value="<?php echo e($rowEdit?$rowEdit['nik']:''); ?>">
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-6">
    <label>Tempat Lahir <span class="text-danger">*</span></label>
    <input type="text" name="tmp_lhr" class="form-control" required value="<?php echo e($rowEdit?$rowEdit['tmp_lhr']:''); ?>">
  </div>
  <div class="col-md-6">
    <label>Tanggal Lahir <span class="text-danger">*</span></label>
    <input type="date" name="tgl_lhr" class="form-control" required value="<?php echo e($rowEdit?$rowEdit['tgl_lhr']:''); ?>">
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-4">
    <label>Pendidikan</label>
    <?php $pendList=array('SD','SMP','SMA','D1','D2','D3','D4','S1','S2','S3'); $pdSel=$rowEdit?$rowEdit['pendidikan']:''; ?>
    <select name="pendidikan" class="form-control select2bs4" style="width:100%">
      <option value="">- pilih -</option>
      <?php foreach($pendList as $pd): ?>
        <option value="<?php echo e($pd); ?>" <?php echo ($pdSel===$pd?'selected':''); ?>><?php echo e($pd); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-4">
    <label>Kategori Pekerjaan <span class="text-danger">*</span></label>
    <select name="id_pekerjaan" id="id_pekerjaan" class="form-control select2bs4" required style="width:100%">
      <option value="">— pilih —</option>
      <?php
      $qpk=mysqli_query($conn,"SELECT id_pekerjaan,desc_pekerjaan FROM tb_master_pekerjaan ORDER BY id_pekerjaan");
      $sel=$rowEdit?$rowEdit['id_pekerjaan']:'';
      if($qpk){ while($pk=mysqli_fetch_assoc($qpk)){
        echo '<option value="'.e($pk['id_pekerjaan']).'" '.($sel==$pk['id_pekerjaan']?'selected':'').'>'.e($pk['desc_pekerjaan'])."</option>";
      }}
      ?>
    </select>
  </div>
  <div class="col-md-4">
    <label>Nama Pekerjaan</label>
    <input type="text" name="pekerjaan" id="pekerjaan_text" class="form-control" value="<?php echo e($rowEdit?$rowEdit['pekerjaan']:''); ?>" placeholder="cth: Wiraswasta" readonly>
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-4">
    <label>Status Hubungan <span class="text-danger">*</span></label>
    <?php $hubSel=$rowEdit?$rowEdit['status_hub']:''; ?>
    <select name="status_hub" class="form-control select2bs4" required style="width:100%">
      <option value="">— pilih —</option>
      <option value="Suami" <?php echo ($hubSel==='Suami'?'selected':''); ?>>Suami</option>
      <option value="Istri" <?php echo ($hubSel==='Istri'?'selected':''); ?>>Istri</option>
    </select>
  </div>
  <div class="col-md-4">
    <label>No. HP</label>
    <input type="text" name="hp" maxlength="13" class="form-control" value="<?php echo e($rowEdit?$rowEdit['hp']:''); ?>">
  </div>
  <div class="col-md-4">
    <label>No. BPJS Pasangan</label>
    <input type="text" name="bpjs_pasangan" maxlength="20" class="form-control" value="<?php echo e($rowEdit?$rowEdit['bpjs_pasangan']:''); ?>">
  </div>
</div>

<div class="mt-4 d-flex justify-content-between">
  <a href="home-admin.php?page=form-view-data-suami-istri" class="btn btn-outline-secondary">Kembali</a>
  <button type="submit" class="btn btn-primary">Simpan</button>
</div>
</form>

</div></div></div>

<script>
$(function () {
  $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

  // Auto-fill nama pekerjaan (tampilkan deskripsi, simpan id)
  $('#id_pekerjaan').on('change', function () {
    var txt = $('#id_pekerjaan option:selected').text();
    $('#pekerjaan_text').val($.trim(txt));
  }).trigger('change');

  // ====== MODE: EDIT — kunci dropdown pegawai & set value ======
  var pageMode    = <?php echo json_encode($mode); ?>; // 'tambah' | 'edit'
  var currentPeg  = <?php echo json_encode(isset($currentPeg)?$currentPeg:''); ?>;
  var currentIdSi = <?php echo json_encode(isset($rowEdit['id_si']) ? $rowEdit['id_si'] : ''); ?>;

  if (currentPeg) {
    $('#id_peg').val(currentPeg).trigger('change'); // tampilkan pilihan saat render
  }
  if (pageMode === 'edit') {
    $('#id_peg').prop('disabled', true); // readonly saat edit
  }

  // ====== CEK PASANGAN — hanya saat TAMBAH ======
  function checkExisting(idPeg) {
    if (!idPeg || pageMode === 'edit') return;
    $.getJSON('pages/ref-keluarga/api-mini.php', { act: 'check_si', id_peg: idPeg }, function (res) {
      if (!res || !res.exists) return;
      Swal.fire({
        icon: 'info',
        title: 'Pasangan sudah terdata',
        text: 'Pegawai ini sudah memiliki data pasangan. Ingin mengedit?',
        showCancelButton: true,
        confirmButtonText: 'Edit Sekarang',
        cancelButtonText: 'Tutup',
        allowOutsideClick: false,
        allowEscapeKey: false
      }).then(function (r) {
        if (r.isConfirmed) {
          window.location = 'home-admin.php?page=form-master-data-suami-istri&mode=edit&id_si=' + encodeURIComponent(res.id_si);
        }
      });
    });
  }

  $('#id_peg').on('select2:select', function () {
    var idPeg = $(this).val();
    checkExisting(idPeg);
  });
});
</script>

</body>
</html>
