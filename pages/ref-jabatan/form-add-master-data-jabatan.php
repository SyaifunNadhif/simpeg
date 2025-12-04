<?php
/*********************************************************
 * FILE    : pages/ref-jabatan/form-add-master-data-jabatan.php
 * MODULE  : SIMPEG — Entry Jabatan Baru (untuk Pegawai Tanpa Jabatan)
 * VERSION : v1.5
 * DATE    : 2025-11-29
 * AUTHOR  : EWS/SIMPEG BKK Jateng — by ChatGPT
 *
 * PURPOSE :
 * - Khusus untuk menambahkan jabatan pertama kali atau re-entry
 * bagi pegawai yang saat ini tidak memiliki jabatan aktif.
 *********************************************************/

if (session_id() === '') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function postv($k,$d=''){ return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }

$today      = date('Y-m-d');
$user_login = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'system';

// --- 1. AMBIL DATA REFERENSI ---

// A. Pegawai yang BELUM punya jabatan AKTIF
// Query ini penting agar user tidak double job aktif
$sqlPegawai = "SELECT p.id_peg, p.nama, p.nip 
               FROM tb_pegawai p
               WHERE p.status_aktif = '1'
               AND NOT EXISTS (
                   SELECT 1 FROM tb_jabatan j 
                   WHERE j.id_peg = p.id_peg AND j.status_jab = 'Aktif'
               )
               ORDER BY p.nama ASC";
$rsP = mysqli_query($conn, $sqlPegawai);
$listPegawai = [];
if ($rsP) { while($r=mysqli_fetch_assoc($rsP)){ $listPegawai[]=$r; } }

// B. Master Jabatan
$rsJ = mysqli_query($conn, "SELECT kode_jabatan, nama_jabatan FROM tb_master_jabatan ORDER BY nama_jabatan ASC");
$ref_jabatan = [];
if ($rsJ) { while($r=mysqli_fetch_assoc($rsJ)){ $ref_jabatan[]=$r; } }

// C. Unit Kerja
$rsU = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor ORDER BY kode_kantor_detail");
$ref_unit = [];
if ($rsU) { while($u=mysqli_fetch_assoc($rsU)){ $ref_unit[]=$u; } }


// --- 2. PROSES SIMPAN ---
$status = '';
$msg_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_peg       = clean($conn, postv('id_peg'));
    $kode_jabatan = clean($conn, postv('kode_jabatan'));
    $nama_jabatan = clean($conn, postv('nama_jabatan')); // Dari input hidden JS
    $unit_kerja   = clean($conn, postv('unit_kerja'));
    $no_sk        = clean($conn, postv('no_sk'));
    $tgl_sk       = clean($conn, postv('tgl_sk'));
    $tmt_jabatan  = $tgl_sk; // Default TMT = Tgl SK
    $status_jab   = 'Aktif'; // Default langsung Aktif

    if ($id_peg === '' || $kode_jabatan === '') {
        $status = 'gagal';
        $msg_error = 'Pegawai dan Jabatan wajib dipilih.';
    } else {
        // Cek lagi apakah mendadak sudah punya jabatan aktif (concurrency check)
        $cekLagi = mysqli_query($conn, "SELECT id_jab FROM tb_jabatan WHERE id_peg='$id_peg' AND status_jab='Aktif'");
        if (mysqli_num_rows($cekLagi) > 0) {
            $status = 'gagal';
            $msg_error = 'Pegawai ini sudah memiliki jabatan aktif. Silakan gunakan menu Mutasi.';
        } else {
            // INSERT DATA
            $sql = "INSERT INTO tb_jabatan (
                        id_peg, kode_jabatan, jabatan, unit_kerja,
                        tmt_jabatan, sampai_tgl, status_jab, 
                        no_sk, tgl_sk, date_reg, created_by
                    ) VALUES (
                        '$id_peg', '$kode_jabatan', '$nama_jabatan', '$unit_kerja',
                        '$tmt_jabatan', NULL, '$status_jab',
                        '$no_sk', '$tgl_sk', '$today', '$user_login'
                    )";
            
            if (mysqli_query($conn, $sql)) {
                $status = 'sukses';
            } else {
                $status = 'gagal';
                $msg_error = mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Jabatan Pegawai</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  
  <style>
    .form-section { max-width: 900px; margin: 30px auto; }
    .card { border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
    .card-header { background: linear-gradient(135deg, #0d6efd, #0a58ca); color: white; border-radius: 12px 12px 0 0; padding: 20px; }
    .form-label { font-weight: 600; font-size: 0.9rem; color: #495057; }
    .select2-container--bootstrap-5 .select2-selection { border-radius: 8px; padding: 0.375rem 0.75rem; height: auto; }
  </style>
  
  <script src="assets/js/core/jquery.3.2.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background-color: #f8f9fa;">

<div class="container form-section">
  
  <div class="mb-3">
    <a href="home-admin.php?page=view-data-jabatan" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Jabatan
    </a>
  </div>

  <div class="card">
    <div class="card-header">
      <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Tetapkan Jabatan Pegawai</h5>
      <small class="opacity-75">Khusus untuk pegawai yang belum memiliki jabatan aktif.</small>
    </div>
    
    <div class="card-body p-4">

      <?php if ($status === 'sukses'): ?>
        <script>
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Jabatan pegawai berhasil ditetapkan.',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            window.location = 'home-admin.php?page=view-data-jabatan';
          });
        </script>
      <?php elseif ($status === 'gagal'): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div>
                <strong>Gagal Menyimpan!</strong><br>
                <?= $msg_error ?>
            </div>
        </div>
      <?php endif; ?>

      <form method="post" action="" autocomplete="off">
        
        <div class="mb-4">
            <label class="form-label text-primary">Pilih Pegawai (Non-Jabatan) <span class="text-danger">*</span></label>
            <select name="id_peg" id="id_peg" class="form-select select2" required>
                <option value="">-- Cari Nama atau NIP --</option>
                <?php foreach ($listPegawai as $p): ?>
                    <option value="<?= e($p['id_peg']) ?>">
                        <?= e($p['nama']) ?> (NIP: <?= e($p['id_peg']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Hanya menampilkan pegawai aktif yang belum punya jabatan.</div>
        </div>

        <hr class="my-4 text-muted">

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Jabatan <span class="text-danger">*</span></label>
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

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Nomor SK</label>
                <input type="text" name="no_sk" class="form-control" placeholder="Contoh: SK/001/HRD/2025" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tanggal SK (TMT)</label>
                <input type="date" name="tgl_sk" class="form-control" value="<?= $today ?>" required>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <a href="home-admin.php?page=view-data-jabatan" class="btn btn-light border px-4">Batal</a>
            <button type="submit" class="btn btn-primary px-4 fw-bold">
                <i class="fas fa-save me-1"></i> Simpan Data
            </button>
        </div>

      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    // Init Select2 Theme Bootstrap 5
    $('.select2').select2({
      theme: 'bootstrap-5',
      width: '100%',
      placeholder: $(this).data('placeholder'),
    });

    // Auto Fill Nama Jabatan ke Hidden Input saat Kode dipilih
    $('#kode_jabatan').on('change', function() {
        var namaJabatan = $(this).find(':selected').data('nama');
        $('#nama_jabatan_hidden').val(namaJabatan);
    });
  });
</script>

</body>
</html>