<?php
/*********************************************************
 * FILE     : pages/ref-keluarga/form-master-data-anak.php
 * MODULE   : SIMPEG — Data Anak (Entry)
 * STATUS   : PHP 5.6 Compatible & Edit Fixed (Old Design)
 *********************************************************/

if (session_id() === '') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';

// Fallback koneksi
if (!isset($conn) || !$conn) { @include_once __DIR__ . '/../../config/koneksi.php'; }
if (!isset($conn) && isset($koneksi) && $koneksi) { $conn = $koneksi; }

// --- HELPERS (PHP 5.6 SAFE) ---
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function postv($k, $d=''){ return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
function clean($c, $s){ return mysqli_real_escape_string($c, trim($s)); }

// Fungsi ambil value aman (pengganti ??)
function v($arr, $key, $default=''){ 
    return (isset($arr[$key]) && $arr[$key] !== null) ? $arr[$key] : $default; 
}

// Fungsi format tanggal ke Y-m-d untuk input date
function toDateInput($dateStr){
    if(empty($dateStr) || $dateStr == '0000-00-00') return '';
    return date('Y-m-d', strtotime($dateStr));
}

$today = date('Y-m-d');

// --- 1. INITIALIZE VARIABLES ---
$uid_get = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$id_anak = isset($_GET['id_anak']) ? (int)$_GET['id_anak'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$mode    = isset($_GET['mode']) ? $_GET['mode'] : ($id_anak > 0 ? 'edit' : 'add');

$anak = null;
$uid  = $uid_get;

// --- 2. AMBIL DATA JIKA EDIT ---
if ($mode === 'edit' && $id_anak > 0) {
    $qa = mysqli_query($conn, "SELECT * FROM tb_anak WHERE id_anak = $id_anak LIMIT 1");
    if ($qa && mysqli_num_rows($qa) > 0) {
        $anak = mysqli_fetch_assoc($qa);
        $uid = $anak['id_peg']; // Overwrite UID dari data DB
    } else {
        $mode = 'add'; // Data tidak ketemu, reset ke add
    }
}

// --- 3. REDIRECT URL ---
$url_redirect = 'home-admin.php?page=form-view-data-anak';
if ($uid !== '') {
    $url_redirect .= '&uid=' . urlencode($uid);
}
// Cek history referer
$referer = isset($_POST['url_asal']) ? $_POST['url_asal'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
if ($referer !== '' && strpos($referer, 'view-detail-data-pegawai') !== false) {
    $url_redirect = 'home-admin.php?page=view-detail-data-pegawai&id_peg=' . urlencode($uid);
}

// --- 4. PROSES SIMPAN ---
$status = ''; $msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_peg       = clean($conn, postv('id_peg'));
    $nik          = clean($conn, postv('nik'));
    $nama         = clean($conn, postv('nama'));
    $tmp          = clean($conn, postv('tmp_lhr'));
    $tgl          = clean($conn, postv('tgl_lhr')); // Format Y-m-d dari input type date
    $pend         = clean($conn, postv('pendidikan'));
    $id_pekerjaan = clean($conn, postv('id_pekerjaan'));
    $pekerjaan    = clean($conn, postv('pekerjaan')); 
    $status_hub   = clean($conn, postv('status_hub'));
    $anak_ke      = clean($conn, postv('anak_ke'));
    $bpjs         = clean($conn, postv('bpjs_anak'));
    
    $id_anak_post = (int)postv('id_anak_post', 0);
    $mode_post    = postv('mode_post', 'add');

    if ($id_peg !== '' && $nama !== '') {
        // Simple Duplicate Check
        $cekDup = false;
        if ($nik !== '') {
            $sqlNik = "SELECT id_anak, nama FROM tb_anak WHERE nik='$nik' " . ($mode_post === 'edit' ? "AND id_anak <> $id_anak_post" : "") . " LIMIT 1";
            $qNik = mysqli_query($conn, $sqlNik);
            if ($qNik && mysqli_num_rows($qNik) > 0) {
                $dup = mysqli_fetch_assoc($qNik);
                $status = 'gagal';
                $msg = 'NIK sudah dipakai oleh: ' . $dup['nama'];
                $cekDup = true;
            }
        }

        if (!$cekDup) {
            if ($mode_post === 'edit') {
                $sql = "UPDATE tb_anak SET 
                        nik='$nik', nama='$nama', tmp_lhr='$tmp', tgl_lhr='$tgl', 
                        pendidikan='$pend', id_pekerjaan='$id_pekerjaan', pekerjaan='$pekerjaan',
                        status_hub='$status_hub', anak_ke='$anak_ke', bpjs_anak='$bpjs'
                        WHERE id_anak=$id_anak_post";
            } else {
                $sql = "INSERT INTO tb_anak (id_peg, nik, nama, tmp_lhr, tgl_lhr, pendidikan, id_pekerjaan, pekerjaan, status_hub, anak_ke, bpjs_anak, date_reg)
                        VALUES ('$id_peg', '$nik', '$nama', '$tmp', '$tgl', '$pend', '$id_pekerjaan', '$pekerjaan', '$status_hub', '$anak_ke', '$bpjs', '$today')";
            }

            if (mysqli_query($conn, $sql)) {
                $status = 'sukses';
            } else {
                $status = 'gagal';
                $msg = 'DB Error: ' . mysqli_error($conn);
            }
        }
    } else {
        $status = 'gagal';
        $msg = 'Nama & Pegawai Wajib Diisi!';
    }
}

// Data Pegawai (Header Info)
$info_pegawai = null;
if ($uid !== '') {
    $qp = mysqli_query($conn, "SELECT id_peg, nama FROM tb_pegawai WHERE id_peg='" . clean($conn, $uid) . "' LIMIT 1");
    if ($qp && mysqli_num_rows($qp) > 0) $info_pegawai = mysqli_fetch_assoc($qp);
}

// List Pekerjaan
$list_pekerjaan = [];
$qj = mysqli_query($conn, "SELECT id_pekerjaan, desc_pekerjaan FROM tb_master_pekerjaan ORDER BY desc_pekerjaan ASC");
if ($qj) { while ($j = mysqli_fetch_assoc($qj)) { $list_pekerjaan[] = $j; } }

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?php echo $mode === 'edit' ? 'Edit Data Anak' : 'Tambah Data Anak'; ?></title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
    .card { border-radius: 14px; border: 1px solid rgba(0,0,0,.05); box-shadow: 0 6px 24px rgba(0,0,0,.06); }
    .card-header { background: linear-gradient(90deg, #2563eb, #0ea5e9); color: #fff; border-radius: 14px 14px 0 0; }
    .form-label { font-weight: 500; }
  </style>
</head>
<body>

<div class="container mt-4 mb-5">
  <div class="card">
    <div class="card-header py-3">
        <h5 class="mb-0"><?php echo $mode === 'edit' ? 'Edit Data Anak' : 'Entry Data Anak'; ?></h5>
        <small class="text-white-50">Lengkapi data anak pegawai</small>
    </div>
    
    <div class="card-body p-4">
        
        <?php if ($status === 'sukses'): ?>
            <script>
                Swal.fire({
                    icon: 'success', 
                    title: 'Berhasil', 
                    text: 'Data telah disimpan', 
                    timer: 1500, 
                    showConfirmButton: false
                }).then(function() { window.location.href = '<?php echo $url_redirect; ?>'; });
            </script>
        <?php elseif ($status === 'gagal'): ?>
            <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?php echo $msg; ?>' });</script>
        <?php endif; ?>

        <form method="post" action="" id="formAnak" autocomplete="off">
            <input type="hidden" name="mode_post" value="<?php echo $mode; ?>">
            <input type="hidden" name="id_anak_post" value="<?php echo $id_anak; ?>">
            <input type="hidden" name="url_asal" value="<?php echo e($referer); ?>">

            <div class="mb-3">
                <label class="form-label">Pilih Pegawai</label>
                <?php if ($uid !== ''): ?>
                    <input type="hidden" name="id_peg" value="<?php echo e($uid); ?>">
                    <input type="text" class="form-control" value="<?php echo e(v($info_pegawai, 'nama', 'Unknown')); ?> (<?php echo e($uid); ?>)" readonly disabled>
                <?php else: ?>
                    <select class="form-select select2-pegawai" name="id_peg" required>
                        <option value="">- Cari Nama Pegawai -</option>
                        <?php
                        $qp = mysqli_query($conn, "SELECT id_peg, nama FROM tb_pegawai WHERE status_aktif='1' ORDER BY nama ASC");
                        while ($p = mysqli_fetch_assoc($qp)) {
                            echo '<option value="' . e($p['id_peg']) . '">' . e($p['nama']) . ' (' . e($p['id_peg']) . ')</option>';
                        }
                        ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">NIK <span class="text-danger">*</span></label>
                    <input type="text" name="nik" class="form-control" placeholder="NIK Anak" value="<?php echo e(v($anak, 'nik')); ?>" maxlength="16">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Anak <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" value="<?php echo e(v($anak, 'nama')); ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                    <input type="text" name="tmp_lhr" class="form-control" value="<?php echo e(v($anak, 'tmp_lhr')); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_lhr" class="form-control" value="<?php echo toDateInput(v($anak, 'tgl_lhr')); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Pendidikan <span class="text-danger">*</span></label>
                    <select name="pendidikan" class="form-select">
                        <option value="">- Pilih -</option>
                        <?php 
                        $levels = array('Belum Sekolah', 'PAUD', 'TK', 'SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3');
                        $curPend = v($anak, 'pendidikan');
                        foreach ($levels as $l) {
                            $sel = ($curPend == $l) ? 'selected' : '';
                            echo "<option value='$l' $sel>$l</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Pekerjaan <span class="text-danger">*</span></label>
                    <input type="hidden" name="pekerjaan" id="nama_pekerjaan" value="<?php echo e(v($anak, 'pekerjaan')); ?>">
                    
                    <select id="picker_pekerjaan" name="id_pekerjaan" class="form-select select2-job">
                        <option value="">- Pilih -</option>
                        <?php 
                        $curJobId = v($anak, 'id_pekerjaan');
                        foreach($list_pekerjaan as $job): 
                            $sel = ($curJobId == $job['id_pekerjaan']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $job['id_pekerjaan']; ?>" data-nama="<?php echo e($job['desc_pekerjaan']); ?>" <?php echo $sel; ?>>
                                <?php echo e($job['desc_pekerjaan']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">BPJS Anak <span class="text-danger">*</span></label>
                    <input type="text" name="bpjs_anak" class="form-control" placeholder="No. BPJS" value="<?php echo e(v($anak, 'bpjs_anak')); ?>">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Status Hub</label>
                    <select name="status_hub" class="form-select">
                        <option value="">- Pilih -</option>
                        <?php 
                        $hubs = array('Anak Kandung','Anak Tiri','Anak Angkat');
                        $curHub = v($anak, 'status_hub');
                        foreach($hubs as $h): 
                            $sel = ($curHub == $h) ? 'selected' : '';
                            echo "<option value=\"$h\" $sel>$h</option>"; 
                        endforeach; 
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Anak ke</label>
                    <input type="number" name="anak_ke" class="form-control" value="<?php echo e(v($anak, 'anak_ke')); ?>">
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="<?php echo $url_redirect; ?>" class="btn btn-outline-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Init Select2
    $('.select2-pegawai').select2({ theme: 'bootstrap-5', width: '100%' });
    $('.select2-job').select2({ theme: 'bootstrap-5', width: '100%' });

    // Logic Update Nama Pekerjaan saat dropdown berubah
    $('#picker_pekerjaan').on('change', function(){
        var id = $(this).val(); 
        var text = $(this).find(':selected').data('nama');
        // Fallback jika data-nama kosong, ambil text opsi
        if(!text && id) text = $(this).find(':selected').text().trim();
        
        $('#nama_pekerjaan').val(text || '');
    });
});
</script>

</body>
</html>