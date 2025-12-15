<?php
/***********************
 * FILE    : pages/ref-pendidikan/form-master-data-pendidikan.php
 * VERSION : v1.3 (Fix Edit Populate & Update Logic)
 * DATE    : 2025-12-10
 ***********************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function postv($k,$d=''){ return isset($_POST[$k])?trim($_POST[$k]):$d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }

$today = date('Y-m-d');

// --- 1. LOGIC MODE EDIT/ADD ---
// Cek apakah ada parameter 'id' di URL
$id_pend = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uid_url = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$mode    = $id_pend > 0 ? 'edit' : 'add';

$data = null; // Variabel penampung data lama

if ($mode === 'edit') {
    // Ambil data pendidikan berdasarkan ID
    $qData = mysqli_query($conn, "SELECT * FROM tb_pendidikan WHERE id_pendidikan = '$id_pend' LIMIT 1");
    if ($qData && mysqli_num_rows($qData) > 0) {
        $data = mysqli_fetch_assoc($qData);
        // Override UID dari database agar konsisten dengan pemilik data
        $uid = $data['id_peg'];
    } else {
        // Jika ID tidak valid, balik ke mode add
        $mode = 'add';
        $uid = $uid_url;
    }
} else {
    $uid = $uid_url;
}

// Ambil Data Pegawai untuk Header
$pegawai = null; 
if ($uid !== '') { 
    $q = mysqli_query($conn, "SELECT id_peg, nama FROM tb_pegawai WHERE id_peg='".clean($conn, $uid)."' LIMIT 1"); 
    if ($q && mysqli_num_rows($q) > 0) $pegawai = mysqli_fetch_assoc($q); 
}

$status = ''; 
$msg = '';

// --- 2. PROSES SIMPAN / UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_peg       = clean($conn, postv('id_peg'));
    $id_sekolah   = clean($conn, postv('id_sekolah', ''));
    $jenjang      = clean($conn, postv('jenjang'));
    $nama_sekolah = clean($conn, postv('nama_sekolah'));
    $lokasi       = clean($conn, postv('lokasi'));
    $jurusan      = clean($conn, postv('jurusan'));
    $th_masuk     = clean($conn, postv('th_masuk'));
    $th_lulus     = clean($conn, postv('th_lulus'));
    $no_ijazah    = clean($conn, postv('no_ijazah'));
    $tgl_ijazah   = clean($conn, postv('tgl_ijazah'));
    $kepala       = clean($conn, postv('kepala'));
    $status_p     = clean($conn, postv('status'));
    
    // Ambil ID dari hidden input untuk mode update
    $id_pend_post = (int)postv('id_pendidikan', 0);
    $mode_post    = $id_pend_post > 0 ? 'edit' : 'add';

    if ($id_peg !== '' && $jenjang !== '' && $nama_sekolah !== '') {
        
        if ($mode_post === 'edit') {
            // --- QUERY UPDATE ---
            $sql = "UPDATE tb_pendidikan SET 
                    id_sekolah   = ".($id_sekolah!=='' ? "'$id_sekolah'" : "NULL").",
                    jenjang      = '$jenjang',
                    nama_sekolah = '$nama_sekolah',
                    lokasi       = '$lokasi',
                    jurusan      = '$jurusan',
                    no_ijazah    = '$no_ijazah',
                    tgl_ijazah   = '$tgl_ijazah',
                    kepala       = '$kepala',
                    status       = '$status_p',
                    th_masuk     = '$th_masuk',
                    th_lulus     = '$th_lulus'
                    WHERE id_pendidikan = $id_pend_post LIMIT 1";
        } else {
            // --- QUERY INSERT ---
            $sql = "INSERT INTO tb_pendidikan
                    (id_peg, id_peg_old, id_sekolah, jenjang, nama_sekolah, lokasi, jurusan, no_ijazah, tgl_ijazah, kepala, status, th_masuk, th_lulus, date_reg, created_by)
                    VALUES
                    ('$id_peg', NULL, ".($id_sekolah!=='' ? "'$id_sekolah'" : "''").", '$jenjang', '$nama_sekolah', '$lokasi', '$jurusan', '$no_ijazah', '$tgl_ijazah', '$kepala', '$status_p', '$th_masuk', '$th_lulus', '$today', 'admin')";
        }

        $ok = mysqli_query($conn, $sql);
        $status = $ok ? 'sukses' : 'gagal';
        
        if (!$ok) { 
            $msg = 'Gagal menyimpan data.'; 
        } else {
            // Jika sukses update, refresh data di variabel agar form terupdate
            if($mode_post === 'edit') { $mode = 'edit'; $id_pend = $id_pend_post; $data = $_POST; }
        }

    } else { 
        $status = 'gagal'; 
        $msg = 'Jenjang & Nama Sekolah wajib diisi.'; 
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?php echo $mode==='edit' ? 'Edit Data' : 'Entry Data'; ?> Pendidikan</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
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
        <h5 class="mb-0"><?php echo $mode==='edit' ? 'Edit Data Pendidikan' : 'Entry Data Pendidikan'; ?></h5>
        <small>Lengkapi data pendidikan pegawai</small>
      </div>
    </div>
    <div class="card-body">
      <?php if($status==='sukses'):?>
        <script>
            Swal.fire({icon:'success',title:'Tersimpan', timer: 1500, showConfirmButton: false})
            .then(function(){ location.href='home-admin.php?page=form-view-data-pendidikan&uid=<?php echo e($uid); ?>'; });
        </script>
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
        
        <?php if($mode==='edit'): ?>
            <input type="hidden" name="id_pendidikan" value="<?php echo (int)$id_pend; ?>">
        <?php endif; ?>

        <div class="row">
          <div class="col-md-3">
            <label class="form-label">Jenjang</label>
            <select name="jenjang" class="form-select" required>
              <option value="">- pilih -</option>
              <?php 
                $list_jenjang = ['SD','SMP','SMA','D1','D2','D3','D4','S1','S2','S3'];
                foreach($list_jenjang as $j): 
                    // Logic Selected: Jika data ada dan sama, tambahkan attribute selected
                    $sel = ($data && $data['jenjang'] == $j) ? 'selected' : '';
              ?>
                <option value="<?php echo $j; ?>" <?php echo $sel; ?>><?php echo $j; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-5">
            <label class="form-label">Nama Sekolah/Universitas</label>
            <input name="nama_sekolah" class="form-control" required value="<?php echo e($data ? $data['nama_sekolah'] : ''); ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Lokasi</label>
            <input name="lokasi" class="form-control" value="<?php echo e($data ? $data['lokasi'] : ''); ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Kode (id_sekolah) <small class="text-muted">opsional</small></label>
            <input name="id_sekolah" class="form-control" maxlength="8" placeholder="" value="<?php echo e($data ? $data['id_sekolah'] : ''); ?>">
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-4">
            <label class="form-label">Jurusan</label>
            <input name="jurusan" class="form-control" value="<?php echo e($data ? $data['jurusan'] : ''); ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Th Masuk</label>
            <input name="th_masuk" class="form-control" maxlength="4" value="<?php echo e($data ? $data['th_masuk'] : ''); ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Th Lulus</label>
            <input name="th_lulus" class="form-control" maxlength="4" value="<?php echo e($data ? $data['th_lulus'] : ''); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Kepala Sekolah/Dekan</label>
            <input name="kepala" class="form-control" value="<?php echo e($data ? $data['kepala'] : ''); ?>">
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-5">
            <label class="form-label">No. Ijazah</label>
            <input name="no_ijazah" class="form-control" value="<?php echo e($data ? $data['no_ijazah'] : ''); ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Tanggal Ijazah</label>
            <input type="date" name="tgl_ijazah" class="form-control" value="<?php echo e($data ? $data['tgl_ijazah'] : ''); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="">-</option>
              <?php 
                $list_status = ['Aktif','Non','Lulus','Belum Lulus'];
                foreach($list_status as $s): 
                    $sel = ($data && $data['status'] == $s) ? 'selected' : '';
              ?>
                <option value="<?php echo $s; ?>" <?php echo $sel; ?>><?php echo $s; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="d-flex justify-content-between mt-3">
          <a class="btn btn-outline-secondary" href="home-admin.php?page=form-view-data-pendidikan">Kembali</a>
          <button class="btn btn-primary" type="submit"><?php echo $mode==='edit' ? 'Update' : 'Simpan'; ?></button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>