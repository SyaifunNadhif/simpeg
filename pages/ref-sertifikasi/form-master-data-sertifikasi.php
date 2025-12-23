<?php
/*********************************************************
 * FILE    : pages/ref-sertifikasi/form-master-data-sertifikasi.php
 * MODULE  : SIMPEG — Data Sertifikasi (Entry & Edit FIX)
 * VERSION : v3.2 (Fix ID Column & Auto Populate)
 *********************************************************/

if (session_id() == '') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn) || !$conn) { @include_once __DIR__ . '/../../config/koneksi.php'; if(isset($koneksi)) $conn=$koneksi; }

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function postv($k,$d=''){ return isset($_POST[$k])?trim($_POST[$k]):$d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }

$today = date('Y-m-d');
$created_by = isset($_SESSION['id_user']) ? clean($conn, $_SESSION['id_user']) : 'admin';

// --- 1. AMBIL ID & TENTUKAN MODE (ADD/EDIT) ---
$id_sertif = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uid_url   = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$mode      = $id_sertif > 0 ? 'edit' : 'add';

// --- [FIX] LOGIC AMBIL DATA LAMA UTK DI-EDIT ---
$data = null;
if ($mode === 'edit') {
    // PERBAIKAN: Gunakan kolom 'id_sertif' sesuai database
    $qData = mysqli_query($conn, "SELECT * FROM tb_sertifikasi WHERE id_sertif = '$id_sertif' LIMIT 1");
    
    if ($qData && mysqli_num_rows($qData) > 0) {
        $data = mysqli_fetch_assoc($qData);
        $uid  = $data['id_peg']; // Override UID agar sesuai dengan pemilik data
    } else {
        // ID tidak ketemu? Balik ke mode add
        $mode = 'add';
        $uid = $uid_url;
    }
} else {
    $uid = $uid_url;
}

// --- 2. LOGIC REDIRECT PINTAR ---
$url_redirect = 'home-admin.php?page=form-view-data-sertifikasi';
if ($uid !== '') {
    $url_redirect .= '&uid=' . urlencode($uid);
}
if (isset($_POST['url_asal']) && !empty($_POST['url_asal'])) {
    $url_redirect = $_POST['url_asal'];
} elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    $ref = $_SERVER['HTTP_REFERER'];
    if (strpos($ref, 'view-detail-data-pegawai') !== false) {
        $url_redirect = 'home-admin.php?page=view-detail-data-pegawai&id_peg=' . urlencode($uid);
    }
}

// Ambil Nama Pegawai (Untuk Header/Lock)
$pegawai = null;
if ($uid !== '') {
    $q = mysqli_query($conn, "SELECT id_peg, nama FROM tb_pegawai WHERE id_peg = '" . clean($conn, $uid) . "' LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) $pegawai = mysqli_fetch_assoc($q);
}

$status = ''; $msg = '';

// --- 3. PROSES SIMPAN (INSERT / UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_peg         = clean($conn, postv('id_peg'));
    $sertifikasi    = clean($conn, postv('sertifikasi'));
    $penyelenggara  = clean($conn, postv('penyelenggara'));
    $no_sertifikat  = clean($conn, postv('no_sertifikat'));
    $tgl_sertifikat = clean($conn, postv('tgl_sertifikat'));
    $tgl_expired    = clean($conn, postv('tgl_expired'));
    
    // Ambil ID dari hidden input
    $id_sertif_post = (int)postv('id_sertifikasi', 0); // Nama field POST tetap id_sertifikasi gpp
    $mode_post      = $id_sertif_post > 0 ? 'edit' : 'add';

    if ($id_peg !== '' && $sertifikasi !== '') {
        
        if ($mode_post === 'edit') {
            // --- [FIX] QUERY UPDATE ---
            // Perbaikan: WHERE id_sertif (bukan id_sertifikasi)
            $sql = "UPDATE tb_sertifikasi SET 
                    sertifikasi    = '$sertifikasi',
                    penyelenggara  = '$penyelenggara',
                    sertifikat     = '$no_sertifikat',
                    tgl_sertifikat = ".($tgl_sertifikat ? "'$tgl_sertifikat'" : "NULL").",
                    tgl_expired    = ".($tgl_expired ? "'$tgl_expired'" : "NULL")."
                    WHERE id_sertif = $id_sertif_post LIMIT 1";
        } else {
            // --- QUERY INSERT ---
            // Cek Duplikat
            $qDup = mysqli_query($conn, "SELECT 1 FROM tb_sertifikasi WHERE id_peg='$id_peg' AND sertifikasi='$sertifikasi' AND tgl_sertifikat='$tgl_sertifikat' LIMIT 1");
            if ($qDup && mysqli_num_rows($qDup) > 0) {
                $status = 'duplikat';
                $msg = 'Sertifikasi ini sudah ada.';
            } else {
                // Perbaikan: Kolom DB 'sertifikat', bukan 'no_sertifikat'
                $sql = "INSERT INTO tb_sertifikasi 
                        (id_peg, sertifikasi, penyelenggara, sertifikat, tgl_sertifikat, tgl_expired, date_reg, created_by)
                        VALUES 
                        ('$id_peg', '$sertifikasi', '$penyelenggara', '$no_sertifikat', ".($tgl_sertifikat ? "'$tgl_sertifikat'" : "NULL").", ".($tgl_expired ? "'$tgl_expired'" : "NULL").", NOW(), '$created_by')";
            }
        }

        if ($status !== 'duplikat') {
            $ok = mysqli_query($conn, $sql);
            $status = $ok ? 'sukses' : 'gagal';
            if (!$ok) $msg = 'Gagal menyimpan data: ' . mysqli_error($conn);
            
            // Refresh data agar form tidak kosong setelah simpan
            if ($ok && $mode_post === 'edit') { 
                $mode = 'edit'; 
                $id_sertif = $id_sertif_post; 
                // Simulasi data baru untuk tampilan form
                $data = [
                    'sertifikasi' => $sertifikasi,
                    'penyelenggara' => $penyelenggara,
                    'sertifikat' => $no_sertifikat,
                    'tgl_sertifikat' => $tgl_sertifikat,
                    'tgl_expired' => $tgl_expired
                ];
            }
        }

    } else {
        $status = 'gagal';
        $msg = 'Pegawai & Nama Sertifikasi wajib diisi.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?php echo $mode==='edit' ? 'Edit Sertifikasi' : 'Entry Sertifikasi'; ?></title>
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
    .is-valid-custom { border-color: #198754 !important; background-color: #f8fff9 !important; }
    .is-invalid-custom { border-color: #dc3545 !important; background-color: #fff8f8 !important; }
    .select2-container--bootstrap-5 .select2-selection { border-color: #ced4da; }
  </style>
</head>
<body>
<div class="container mt-3">
  <div class="card">
    <div class="card-header">
       <h5 class="mb-0"><?php echo $mode==='edit' ? 'Edit Data Sertifikasi' : 'Entry Data Sertifikasi'; ?></h5>
       <small><?php echo $mode==='edit' ? 'Perbarui data sertifikasi pegawai' : 'Lengkapi data sertifikasi pegawai'; ?></small>
    </div>
    <div class="card-body">
      
      <?php if($status==='sukses'):?>
        <script>
            Swal.fire({icon:'success',title:'Tersimpan', timer: 1500, showConfirmButton: false})
            .then(function(){ location.href='<?php echo $url_redirect; ?>'; });
        </script>
      <?php elseif($status==='gagal'):?>
        <script>Swal.fire({icon:'error',title:'Gagal',text:<?php echo json_encode($msg?:'Periksa isian.'); ?>});</script>
      <?php elseif($status==='duplikat'):?>
        <script>Swal.fire({icon:'warning',title:'Duplikat',text:<?php echo json_encode($msg); ?>});</script>
      <?php endif; ?>

      <?php if($pegawai): ?>
        <div class="alert alert-info py-2 d-flex align-items-center">
            <div class="me-3"><i class="fas fa-user-circle fa-2x"></i></div>
            <div>
                <strong>Pegawai: <?php echo e($pegawai['nama']); ?></strong> <br>
                <small>ID: <?php echo e($pegawai['id_peg']); ?></small>
            </div>
        </div>
      <?php else: ?>
        <div class="mb-3">
          <label class="form-label">Pilih Pegawai <span class="text-danger">*</span></label>
          <select id="uid_picker" class="form-select">
            <option value="">- Cari Pegawai -</option>
            <?php 
            $qp=mysqli_query($conn,"SELECT id_peg,nama FROM tb_pegawai WHERE status_aktif='1' ORDER BY nama ASC LIMIT 2000"); 
            if($qp){ while($p=mysqli_fetch_assoc($qp)){ echo '<option value="'.e($p['id_peg']).'">'.e($p['nama'].' — '.$p['id_peg'])."</option>"; }} 
            ?>
          </select>
          <script>
            $(function(){ 
                $('#uid_picker').select2({ theme:'bootstrap-5', width:'100%' });
                $('#uid_picker').on('select2:select', function(e){
                   var v = $(this).val(); if(v) window.location.href='home-admin.php?page=form-master-data-sertifikasi&uid='+encodeURIComponent(v);
                });
            });
          </script>
        </div>
      <?php endif; ?>

      <form method="post" action="" autocomplete="off" id="frmSertif">
        <input type="hidden" name="url_asal" value="<?php echo e($url_redirect); ?>">
        
        <input type="hidden" name="id_peg" value="<?php echo e($uid); ?>">
        
        <?php if($mode==='edit'): ?> 
            <input type="hidden" name="id_sertifikasi" value="<?php echo (int)$id_sertif; ?>"> 
        <?php endif; ?>

        <div class="row">
          <div class="col-md-6 input-group-valid">
            <label class="form-label">Nama Sertifikasi <span class="text-danger">*</span></label>
            <input name="sertifikasi" class="form-control check-field" required placeholder="Contoh: Ahli K3 Umum" 
                   value="<?php echo e($data ? $data['sertifikasi'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
          <div class="col-md-6 input-group-valid">
            <label class="form-label">Penyelenggara</label>
            <input name="penyelenggara" class="form-control check-field" placeholder="Contoh: BNSP" 
                   value="<?php echo e($data ? $data['penyelenggara'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-4 input-group-valid">
            <label class="form-label">No. Sertifikat</label>
            <input name="no_sertifikat" class="form-control check-field" placeholder="Nomor Seri" 
                   value="<?php echo e($data ? $data['sertifikat'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
          <div class="col-md-4 input-group-valid">
            <label class="form-label">Tanggal Sertifikat</label>
            <input type="date" name="tgl_sertifikat" class="form-control check-field" 
                   value="<?php echo e($data ? $data['tgl_sertifikat'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
          <div class="col-md-4 input-group-valid">
            <label class="form-label">Tanggal Expired <small class="text-muted">(Opsional)</small></label>
            <input type="date" name="tgl_expired" class="form-control check-field" 
                   value="<?php echo e($data ? $data['tgl_expired'] : ''); ?>">
            <span class="validation-icon"></span>
          </div>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
          <a class="btn btn-outline-secondary" href="<?php echo $url_redirect; ?>">Kembali</a>
          <button class="btn btn-primary" type="button" id="btnCheckAndSubmit">
              <?php echo $mode==='edit' ? 'Update Data' : 'Simpan Data'; ?>
          </button>
        </div>
      </form>

      <script>
        $(function(){
          // Hapus merah saat diketik
          $('.check-field').on('input change', function(){
              if($(this).val()) $(this).removeClass('is-invalid-custom');
          });

          $('#btnCheckAndSubmit').on('click', function(e){
              e.preventDefault();
              let allValid = true;
              
              // Cek ID Pegawai
              if ($('input[name="id_peg"]').val() === '') {
                  Swal.fire('Error', 'Silakan pilih pegawai terlebih dahulu.', 'error');
                  return;
              }

              // Cek Required Fields
              $('#frmSertif [required]').each(function(){
                  if( !$(this).val() ) {
                      $(this).addClass('is-invalid-custom');
                      allValid = false;
                  } else {
                      $(this).removeClass('is-invalid-custom').addClass('is-valid-custom');
                  }
              });

              if(allValid) {
                  $('#frmSertif').submit();
              } else {
                  Swal.fire({
                      icon: 'warning',
                      title: 'Data Belum Lengkap',
                      text: 'Mohon lengkapi Nama Sertifikasi.',
                      confirmButtonText: 'Oke'
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