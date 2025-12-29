<?php
/*********************************************************
 * FILE     : pages/ref-jabatan/form-master-create-history-jabatan.php
 * MODULE   : Input Jabatan (Auto Detect: History vs Active)
 * UPDATE   : FIX SEARCHABLE DROPDOWN (Select2 CDN)
 *********************************************************/

if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function postv($k,$d=''){ return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }

$today      = date('Y-m-d');
$user_login = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'system';

// --- 1. AMBIL DATA PEGAWAI ---
$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$pegawai = null;
$jabatan_sekarang = null;
$auto_mode = ''; 

if ($uid !== '') {
    // Ambil Info Pegawai
    $q = mysqli_query($conn, "SELECT id_peg, nama, nip FROM tb_pegawai WHERE id_peg='".clean($conn,$uid)."' LIMIT 1");
    if ($q && mysqli_num_rows($q)>0) { 
        $pegawai = mysqli_fetch_assoc($q); 
        
        // CEK STATUS JABATAN SAAT INI (LOGIC OTOMATIS)
        $qCek = mysqli_query($conn, "SELECT jabatan, unit_kerja FROM tb_jabatan WHERE id_peg='$uid' AND status_jab='Aktif' LIMIT 1");
        if (mysqli_num_rows($qCek) > 0) {
            $jabatan_sekarang = mysqli_fetch_assoc($qCek);
            $auto_mode = 'history'; // Punya jabatan aktif -> Mode History
        } else {
            $auto_mode = 'aktif';   // Belum punya -> Mode Aktif
        }
    } else {
        echo "<script>alert('Pegawai tidak ditemukan!'); window.location='home-admin.php?page=form-view-data-pegawai';</script>";
        exit;
    }
}

// --- 2. TAMPILAN SEARCH PEGAWAI (JIKA UID KOSONG) ---
if (!$pegawai) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8"><title>Cari Pegawai</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
        <style>
            body { background: #f4f6f9; font-family: sans-serif; }
            .search-box { max-width: 600px; margin: 80px auto; padding: 40px; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="search-box">
                <h4 class="fw-bold text-dark mb-3">Input Data Jabatan</h4>
                <p class="text-muted mb-4">Cari pegawai untuk melanjutkan input jabatan.</p>
                <form action="" method="GET">
                    <input type="hidden" name="page" value="form-master-create-history-jabatan">
                    <div class="mb-3 text-start">
                        <select name="uid" class="form-select select2-search" required>
                            <option value="">-- Cari Nama / NIP --</option>
                            <?php
                            $qAll = mysqli_query($conn, "SELECT id_peg, nama, nip FROM tb_pegawai WHERE status_aktif='1' ORDER BY nama ASC");
                            while($p = mysqli_fetch_assoc($qAll)){ 
                                echo "<option value='{$p['id_peg']}'>{$p['nama']} ({$p['id_peg']})</option>"; 
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Lanjutkan</button>
                </form>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                // Aktifkan Search pada Dropdown
                $('.select2-search').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Ketik Nama Pegawai...'
                });
            });
        </script>
    </body>
    </html>
    <?php exit;
}

// --- 3. REFERENSI ---
$rsJ = mysqli_query($conn, "SELECT kode_jabatan, nama_jabatan FROM tb_master_jabatan ORDER BY nama_jabatan ASC");
$ref_jabatan = [];
if ($rsJ) { while($r=mysqli_fetch_assoc($rsJ)){ $ref_jabatan[]=$r; } }

$rsU = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor ORDER BY kode_kantor_detail");
$ref_unit = [];
if ($rsU) { while($u=mysqli_fetch_assoc($rsU)){ $ref_unit[]=$u; } }


// --- 4. PROSES SIMPAN ---
$status = ''; $msg_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_peg      = clean($conn, postv('id_peg'));
    $current_mode = clean($conn, postv('jenis_input')); 

    $input_raw = clean($conn, postv('jabatan_input')); 
    $cekMaster = mysqli_query($conn, "SELECT nama_jabatan FROM tb_master_jabatan WHERE kode_jabatan='$input_raw'");
    if(mysqli_num_rows($cekMaster) > 0){
        $dM = mysqli_fetch_assoc($cekMaster);
        $kode_jabatan = $input_raw;
        $nama_jabatan = $dM['nama_jabatan'];
    } else {
        $kode_jabatan = '0';
        $nama_jabatan = $input_raw;
    }

    $unit_kerja = clean($conn, postv('unit_kerja'));
    $no_sk      = clean($conn, postv('no_sk'));
    $tgl_sk     = clean($conn, postv('tgl_sk'));
    $tmt_mulai  = clean($conn, postv('tmt_mulai'));
    
    if ($current_mode == 'history') {
        $status_jab  = 'Non';
        $tgl_selesai = clean($conn, postv('tgl_selesai'));
    } else {
        $status_jab  = 'Aktif';
        $tgl_selesai = 'NULL';
    }

    if ($current_mode == 'history' && empty($tgl_selesai)) {
        $status = 'gagal'; $msg_error = 'TMT Selesai wajib diisi untuk data History!';
    } else {
        if ($tgl_selesai !== 'NULL') { $tgl_selesai = "'$tgl_selesai'"; }

        // Safety: Jika mode aktif, pastikan yg lama non-aktif
        if ($status_jab == 'Aktif') {
            mysqli_query($conn, "UPDATE tb_jabatan SET status_jab='Non', sampai_tgl='$tmt_mulai', updated_at=NOW() WHERE id_peg='$id_peg' AND status_jab='Aktif'");
        }

        $sqlInsert = "INSERT INTO tb_jabatan 
            (id_peg, kode_jabatan, jabatan, unit_kerja, no_sk, tgl_sk, tmt_jabatan, sampai_tgl, status_jab, date_reg, created_by) 
            VALUES 
            ('$id_peg', '$kode_jabatan', '$nama_jabatan', '$unit_kerja', '$no_sk', '$tgl_sk', '$tmt_mulai', $tgl_selesai, '$status_jab', NOW(), '$user_login')";

        if (mysqli_query($conn, $sqlInsert)) {
            $status = 'sukses';
        } else {
            $status = 'gagal'; $msg_error = "Error DB: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><title>Input Jabatan</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
    .form-section { max-width: 900px; margin: 30px auto; }
    .card { border-radius: 12px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .bg-history { background: linear-gradient(45deg, #e11d48, #be123c); }
    .bg-active { background: linear-gradient(45deg, #059669, #10b981); }
    .card-header { color: white; padding: 20px 25px; border-radius: 12px 12px 0 0 !important; }
    .form-container { background-color: #fff; padding: 20px; border-radius: 10px; border: 1px solid #f0f0f0; }
  </style>
</head>
<body style="background-color: #f4f6f9;">

<div class="container form-section">

 

  <div class="card">
    <div class="card-header <?= ($auto_mode == 'history') ? 'bg-history' : 'bg-active' ?>">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 fw-bold">
                    <?php if($auto_mode == 'history'): ?>
                        INPUT DATA HISTORY (MASA LALU)
                    <?php else: ?>
                        INPUT JABATAN UTAMA (AKTIF)
                    <?php endif; ?>
                </h5>
                <small class="opacity-75">Pegawai: <?= e($pegawai['nama']) ?></small>
            </div>
            <span class="badge bg-white text-dark px-3 py-2 rounded-pill">
                <?= ($auto_mode == 'history') ? 'Status: NON-AKTIF' : 'Status: AKTIF' ?>
            </span>
        </div>
    </div>
    
    <div class="card-body p-4">
        
        <?php if($auto_mode == 'history'): ?>
            <div class="alert alert-warning border-0 shadow-sm mb-4">
                Pegawai ini sudah memiliki jabatan aktif. Form ini khusus untuk menambahkan <b>Riwayat Masa Lalu</b>.
            </div>
        <?php else: ?>
            <div class="alert alert-success border-0 shadow-sm mb-4">
                Pegawai ini belum memiliki jabatan. Data ini akan menjadi <b>Jabatan Aktif</b>.
            </div>
        <?php endif; ?>

        <?php if ($status === 'sukses'): ?>
            <script>Swal.fire({icon:'success',title:'Tersimpan!',text:'Data berhasil ditambahkan.',timer:1500,showConfirmButton:false}).then(()=>{window.location='home-admin.php?page=view-detail-data-pegawai&id_peg=<?= $uid ?>'});</script>
        <?php elseif ($status === 'gagal'): ?>
            <div class="alert alert-danger mb-3"><?= $msg_error ?></div>
        <?php endif; ?>

        <form method="post" action="" autocomplete="off">
            <input type="hidden" name="id_peg" value="<?= e($pegawai['id_peg']) ?>">
            <input type="hidden" name="jenis_input" value="<?= $auto_mode ?>">

            <div class="form-container">
                <div class="row g-4 mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold small text-muted">NAMA JABATAN <span class="text-danger">*</span></label>
                        <select name="jabatan_input" class="form-select select2-tags" required>
                            <option value="">-- Pilih atau Ketik Manual --</option>
                            <?php foreach ($ref_jabatan as $j): ?>
                                <option value="<?= e($j['kode_jabatan']) ?>"><?= e($j['nama_jabatan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text"><i>Tips: Jika nama jabatan tidak ada di list, ketik lalu tekan Enter.</i></div>
                    </div>
                </div>

                <div class="row g-4 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">UNIT KERJA <span class="text-danger">*</span></label>
                        <select name="unit_kerja" class="form-select select2" required>
                            <option value="">-- Pilih Unit Kerja --</option>
                            <?php foreach ($ref_unit as $u): ?>
                                <option value="<?= e($u['kode_kantor_detail']) ?>"><?= e($u['nama_kantor']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">NOMOR SK <span class="text-danger">*</span></label>
                        <input type="text" name="no_sk" class="form-control" placeholder="Nomor SK..." required>
                    </div>
                </div>

                <div class="row g-4 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">TANGGAL SK</label>
                        <input type="date" name="tgl_sk" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">TMT MULAI</label>
                        <input type="date" name="tmt_mulai" class="form-control" required>
                    </div>
                    
                    <?php if($auto_mode == 'history'): ?>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-danger">TMT SELESAI <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_selesai" class="form-control" required>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm">SIMPAN DATA</button>
            </div>
        </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    // Select2 Biasa (Unit Kerja)
    $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

    // Select2 Tags (Jabatan - Bisa Ketik Manual)
    $('.select2-tags').select2({ 
        theme: 'bootstrap-5', width: '100%', tags: true, 
        placeholder: "Pilih dari list atau ketik manual...",
        createTag: function (params) {
            var term = $.trim(params.term); if (term === '') { return null; }
            return { id: term, text: term + ' (Manual Input)', newTag: true }
        }
    });
  });
</script>

</body>
</html>