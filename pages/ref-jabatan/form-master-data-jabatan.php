<?php
/*********************************************************
 * FILE    : pages/ref-jabatan/form-master-data-jabatan.php
 * MODULE  : SIMPEG — Entry Jabatan (Dual Mode: Mutasi & Baru)
 *********************************************************/

if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }

// --- HELPER ---
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function postv($k,$d=''){ return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }

$today      = date('Y-m-d');
$user_login = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'system';

// --- 1. CEK MODE (UID DARI URL) ---
$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$pegawai = null;
$jabAktif = null;

// Jika ada UID, berarti Mode MUTASI
if ($uid !== '') {
    // Ambil Data Pegawai
    $q = mysqli_query($conn, "SELECT id_peg, nama, nip, jk, foto FROM tb_pegawai WHERE id_peg='".clean($conn,$uid)."' LIMIT 1");
    if ($q && mysqli_num_rows($q)>0) { 
        $pegawai = mysqli_fetch_assoc($q); 
        
        // Cek Jabatan Terakhirnya (Info saja)
        $qJ = mysqli_query($conn, "SELECT j.jabatan, k.nama_kantor 
                                   FROM tb_jabatan j 
                                   LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail 
                                   WHERE j.id_peg='$uid' AND j.status_jab='Aktif' LIMIT 1");
        if(mysqli_num_rows($qJ)>0) $jabAktif = mysqli_fetch_assoc($qJ);
    }
}

// --- 2. REFERENSI DATA (Jabatan & Unit) ---
$rsJ = mysqli_query($conn, "SELECT kode_jabatan, nama_jabatan FROM tb_master_jabatan ORDER BY nama_jabatan ASC");
$ref_jabatan = [];
if ($rsJ) { while($r=mysqli_fetch_assoc($rsJ)){ $ref_jabatan[]=$r; } }

$rsU = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor ORDER BY kode_kantor_detail");
$ref_unit = [];
if ($rsU) { while($u=mysqli_fetch_assoc($rsU)){ $ref_unit[]=$u; } }


// --- 3. PROSES SIMPAN ---
$status = '';
$msg_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil ID PEG. Jika form disable (mode mutasi), ambil dari hidden input.
    $id_peg       = clean($conn, postv('id_peg')); 
    
    $kode_jabatan = clean($conn, postv('kode_jabatan'));
    $nama_jabatan = clean($conn, postv('nama_jabatan')); 
    $unit_kerja   = clean($conn, postv('unit_kerja'));
    $no_sk        = clean($conn, postv('no_sk'));
    $tgl_sk       = clean($conn, postv('tgl_sk'));
    $tmt_jabatan  = $tgl_sk; 
    $status_jab   = 'Aktif'; 

    // Validasi
    if ($id_peg === '' || $kode_jabatan === '') {
        $status = 'gagal';
        $msg_error = 'Pegawai dan Jabatan wajib diisi.';
    } else {
        // PROSES DATABASE
        mysqli_begin_transaction($conn);
        $ok = true;

        // 1. Matikan Jabatan Lama (Auto Close)
        // Kita update SEMUA jabatan aktif pegawai ini jadi Non (H-1 dari TMT baru)
        $tgl_tutup = date('Y-m-d', strtotime('-1 day', strtotime($tgl_sk)));
        $sqlClose = "UPDATE tb_jabatan SET 
                     status_jab='Non', 
                     sampai_tgl='$tgl_tutup', 
                     updated_at=NOW(), 
                     updated_by='$user_login' 
                     WHERE id_peg='$id_peg' AND status_jab='Aktif'";
        $ok = mysqli_query($conn, $sqlClose);

        // 2. Insert Jabatan Baru
        if ($ok) {
            $sql = "INSERT INTO tb_jabatan (
                        id_peg, kode_jabatan, jabatan, unit_kerja,
                        tmt_jabatan, sampai_tgl, status_jab, 
                        no_sk, tgl_sk, date_reg, created_by
                    ) VALUES (
                        '$id_peg', '$kode_jabatan', '$nama_jabatan', '$unit_kerja',
                        '$tmt_jabatan', NULL, '$status_jab',
                        '$no_sk', '$tgl_sk', '$today', '$user_login'
                    )";
            $ok = mysqli_query($conn, $sql);
        }

        if ($ok) {
            mysqli_commit($conn);
            $status = 'sukses';
            // Update UID agar redirect benar
            $uid = $id_peg;
        } else {
            mysqli_rollback($conn);
            $status = 'gagal';
            $msg_error = "Database Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Entry Jabatan</title>
  
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  
  <style>
    .form-section { max-width: 900px; margin: 30px auto; }
    .card { border-radius: 12px; border:none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .card-header { background: #0d6efd; color: white; border-radius: 12px 12px 0 0; padding: 15px 20px; }
    .bg-light-info { background-color: #e7f1ff; border: 1px solid #b6d4fe; }
  </style>
  
  <script src="assets/js/core/jquery.3.2.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background-color: #f8f9fa;">

<div class="container form-section">
  
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="text-muted mb-0"><i class="fas fa-briefcase me-2"></i>Kepegawaian</h5>
    <a href="home-admin.php?page=form-view-data-jabatan" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
  </div>

  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">
          <?php echo ($pegawai) ? '<i class="fas fa-exchange-alt me-2"></i>Mutasi Jabatan' : '<i class="fas fa-user-plus me-2"></i>Penetapan Jabatan Baru'; ?>
      </h5>
    </div>
    
    <div class="card-body p-4">

      <?php if ($status === 'sukses'): ?>
        <script>
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data jabatan berhasil disimpan.',
            timer: 2000, showConfirmButton: false
          }).then(() => {
            // Redirect ke halaman detail pegawai
            window.location = 'home-admin.php?page=view-detail-data-pegawai&id_peg=<?= $uid ?>';
          });
        </script>
      <?php elseif ($status === 'gagal'): ?>
        <div class="alert alert-danger"><strong>Gagal!</strong> <?= $msg_error ?></div>
      <?php endif; ?>

      <form method="post" action="" autocomplete="off">
        
        <?php if ($pegawai): ?>
            <input type="hidden" name="id_peg" value="<?= e($pegawai['id_peg']) ?>">
            
            <div class="alert bg-light-info d-flex align-items-center mb-4">
                <div class="me-3">
                    <img src="pages/assets/foto/<?= ($pegawai['jk']=='Perempuan'?'no-foto-female.png':'no-foto-male.png') ?>" class="rounded-circle" width="55" style="border:2px solid white; box-shadow:0 2px 5px rgba(0,0,0,0.1)">
                </div>
                <div>
                    <h5 class="mb-0 text-primary fw-bold">
                        <?= e($pegawai['nama']) ?> <i class="fas fa-lock ms-2 text-muted" title="Terkunci"></i>
                    </h5>
                    <div class="text-muted small">NIP: <?= e($pegawai['id_peg']) ?></div>
                    <?php if($jabAktif): ?>
                        <div class="badge bg-warning text-dark mt-1">Jabatan Saat Ini: <?= e($jabAktif['jabatan']) ?></div>
                    <?php else: ?>
                        <div class="badge bg-secondary mt-1">Belum memiliki jabatan aktif</div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="mb-4">
                <label class="form-label text-primary fw-bold">Pilih Pegawai (Wajib) <span class="text-danger">*</span></label>
                <select name="id_peg" id="id_peg" class="form-select select2" required>
                    <option value="">-- Cari Nama Pegawai (Non-Jabatan) --</option>
                    <?php
                    // Query: Hanya pegawai yg BELUM punya jabatan aktif
                    $sqlList = "SELECT p.id_peg, p.nama FROM tb_pegawai p 
                                WHERE p.status_aktif = '1' 
                                AND NOT EXISTS (SELECT 1 FROM tb_jabatan j WHERE j.id_peg = p.id_peg AND j.status_jab = 'Aktif')
                                ORDER BY p.nama ASC";
                    $qList = mysqli_query($conn, $sqlList);
                    while($p = mysqli_fetch_assoc($qList)){
                        echo '<option value="'.e($p['id_peg']).'">'.e($p['nama']).' ('.e($p['id_peg']).')</option>';
                    }
                    ?>
                </select>
                <div class="form-text">Hanya menampilkan pegawai yang <b>belum punya jabatan aktif</b>.</div>
            </div>
        <?php endif; ?>

        <hr class="my-4 text-muted opacity-25">

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Jabatan Baru <span class="text-danger">*</span></label>
                <select name="kode_jabatan" id="kode_jabatan" class="form-select select2" required>
                    <option value="">-- Pilih Jabatan --</option>
                    <?php foreach ($ref_jabatan as $j): ?>
                        <option value="<?= e($j['kode_jabatan']) ?>" data-nama="<?= e($j['nama_jabatan']) ?>">
                            <?= e($j['nama_jabatan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="nama_jabatan" id="nama_jabatan_hidden">
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Unit Kerja <span class="text-danger">*</span></label>
                <select name="unit_kerja" id="unit_kerja" class="form-select select2" required>
                    <option value="">-- Pilih Unit Kerja --</option>
                    <?php foreach ($ref_unit as $u): ?>
                        <option value="<?= e($u['kode_kantor_detail']) ?>">
                            <?= e($u['nama_kantor']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Nomor SK</label>
                <input type="text" name="no_sk" class="form-control" placeholder="Contoh: SK/001/HRD/2025" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tanggal SK / TMT</label>
                <input type="date" name="tgl_sk" class="form-control" value="<?= $today ?>" required>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-4">
            <a href="home-admin.php?page=form-view-data-jabatan" class="btn btn-light border px-4">Batal</a>
            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                <i class="fas fa-save me-1"></i> Simpan Data
            </button>
        </div>

      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

<script>
  $(document).ready(function() {
    // Init Select2
    $('.select2').select2({
      theme: 'bootstrap-5',
      width: '100%',
      placeholder: $(this).data('placeholder'),
    });

    // Auto Fill Nama Jabatan ke Hidden Input
    $('#kode_jabatan').on('change', function() {
        var nama = $(this).find(':selected').data('nama');
        if(nama) {
            $('#nama_jabatan_hidden').val(nama);
        }
    });
  });
</script>

</body>
</html>