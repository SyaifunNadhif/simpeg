<?php
/***********************
 * FILE    : pages/ref-pendidikan/form-master-data-pendidikan.php
 * VERSION : v1.2 (PHP 5.6)
 * DATE    : 2025-09-07
 * CHANGELOG
 * - v1.2: Insert tanpa ID manual (pakai id_pendidikan). Tambah field opsional id_sekolah (kode referensi).
 ***********************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function postv($k,$d=''){ return isset($_POST[$k])?trim($_POST[$k]):$d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }

$today=date('Y-m-d');
$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$pegawai=null; if($uid!==''){ $q=mysqli_query($conn,"SELECT id_peg,nama FROM tb_pegawai WHERE id_peg='".clean($conn,$uid)."' LIMIT 1"); if($q&&mysqli_num_rows($q)>0)$pegawai=mysqli_fetch_assoc($q); }
$status=''; $msg='';

if($_SERVER['REQUEST_METHOD']==='POST'){
  $id_peg=clean($conn,postv('id_peg'));
  $id_sekolah=clean($conn,postv('id_sekolah','')); // opsional (kode referensi)
  $jenjang=clean($conn,postv('jenjang'));
  $nama_sekolah=clean($conn,postv('nama_sekolah'));
  $lokasi=clean($conn,postv('lokasi'));
  $jurusan=clean($conn,postv('jurusan'));
  $th_masuk=clean($conn,postv('th_masuk'));
  $th_lulus=clean($conn,postv('th_lulus'));
  $no_ijazah=clean($conn,postv('no_ijazah'));
  $tgl_ijazah=clean($conn,postv('tgl_ijazah'));
  $kepala=clean($conn,postv('kepala'));
  $status_p=clean($conn,postv('status'));

  if($id_peg!=='' && $jenjang!=='' && $nama_sekolah!==''){
    $sql="INSERT INTO tb_pendidikan
         (id_peg,id_peg_old,id_sekolah,jenjang,nama_sekolah,lokasi,jurusan,no_ijazah,tgl_ijazah,kepala,status,th_masuk,th_lulus,date_reg,created_by)
         VALUES
         ('{$id_peg}',NULL,".($id_sekolah!==''?"'{$id_sekolah}'":"''").", '{$jenjang}','{$nama_sekolah}','{$lokasi}','{$jurusan}','{$no_ijazah}','{$tgl_ijazah}','{$kepala}','{$status_p}','{$th_masuk}','{$th_lulus}','{$today}','admin')";
    $ok=mysqli_query($conn,$sql); $status=$ok?'sukses':'gagal';
    if(!$ok){ $msg='Gagal menyimpan data.'; }
  } else { $status='gagal'; $msg='Jenjang & Nama Sekolah wajib diisi.'; }
}
?>
<!DOCTYPE html><html lang="id"><head>
  <meta charset="utf-8"><title>Entry Pendidikan</title>
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
      <div>
        <h5 class="mb-0">Entry Data Pendidikan</h5>
        <small>Lengkapi data pendidikan pegawai</small>
      </div>
    </div>
    <div class="card-body">
      <?php if($status==='sukses'):?>
        <script>Swal.fire({icon:'success',title:'Tersimpan'}).then(function(){location.href='home-admin.php?page=form-view-data-pendidikan&uid=<?php echo e($uid); ?>';});</script>
      <?php elseif($status==='gagal'):?>
        <script>Swal.fire({icon:'error',title:'Gagal',text:<?php echo json_encode($msg?:'Periksa isian.'); ?>});</script>
      <?php endif; ?>

      <?php if($pegawai): ?>
        <div class="alert alert-info">Pegawai: <b><?php echo e($pegawai['nama']); ?></b> — ID: <?php echo e($pegawai['id_peg']); ?></div>
      <?php else: ?>
        <div class="mb-3">
          <label class="form-label">Pilih Pegawai</label>
          <select id="uid_picker" class="form-select" style="width:100%">
            <option value="">- Pilih Pegawai -</option>
            <?php $qp=mysqli_query($conn,"SELECT id_peg,nama FROM tb_pegawai WHERE status_aktif='1' ORDER BY nama ASC LIMIT 2000"); if($qp){ while($p=mysqli_fetch_assoc($qp)){ echo '<option value="'.e($p['id_peg']).'">'.e($p['nama'].' — '.$p['id_peg']).'</option>'; } } ?>
          </select>
          <script>$(function(){ $('#uid_picker').select2({theme:'bootstrap-5',width:'100%'}).on('select2:select',function(){var v=$(this).val(); if(v){ location.href='home-admin.php?page=form-master-data-pendidikan&uid='+encodeURIComponent(v); }}); });</script>
        </div>
      <?php endif; ?>

      <form method="post" action="" autocomplete="off">
        <input type="hidden" name="id_peg" value="<?php echo e($uid); ?>">
        <div class="row">
          <div class="col-md-3">
            <label class="form-label">Jenjang</label>
            <select name="jenjang" class="form-select" required>
              <option value="">- pilih -</option>
              <option>SD</option><option>SMP</option><option>SMA</option><option>D1</option><option>D2</option><option>D3</option><option>D4</option><option>S1</option><option>S2</option><option>S3</option>
            </select>
          </div>
          <div class="col-md-5"><label class="form-label">Nama Sekolah/Universitas</label><input name="nama_sekolah" class="form-control" required></div>
          <div class="col-md-2"><label class="form-label">Lokasi</label><input name="lokasi" class="form-control"></div>
          <div class="col-md-2"><label class="form-label">Kode (id_sekolah) <small class="text-muted">opsional</small></label><input name="id_sekolah" class="form-control" maxlength="8" placeholder=""></div>
        </div>
        <div class="row mt-2">
          <div class="col-md-4"><label class="form-label">Jurusan</label><input name="jurusan" class="form-control"></div>
          <div class="col-md-2"><label class="form-label">Th Masuk</label><input name="th_masuk" class="form-control" maxlength="4"></div>
          <div class="col-md-2"><label class="form-label">Th Lulus</label><input name="th_lulus" class="form-control" maxlength="4"></div>
          <div class="col-md-4"><label class="form-label">Kepala Sekolah/Dekan</label><input name="kepala" class="form-control"></div>
        </div>
        <div class="row mt-2">
          <div class="col-md-5"><label class="form-label">No. Ijazah</label><input name="no_ijazah" class="form-control"></div>
          <div class="col-md-3"><label class="form-label">Tanggal Ijazah</label><input type="date" name="tgl_ijazah" class="form-control"></div>
          <div class="col-md-2"><label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="">-</option><option>Aktif</option><option>Non</option>
            </select>
          </div>
        </div>
        <div class="d-flex justify-content-between mt-3">
          <a class="btn btn-outline-secondary" href="home-admin.php?page=form-view-data-pendidikan">Kembali</a>
          <button class="btn btn-primary" type="submit">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body></html>
