<?php
/*********************************************************
 * FILE     : pages/ref-biaya-pendidikan/form-biaya-pendidikan.php
 * MODULE   : Input & Edit Biaya (Auto Format Rupiah & Validation)
 *********************************************************/

if (session_id() == '') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';

// Fallback koneksi
if (!isset($conn) || !$conn) { @include_once __DIR__ . '/../../config/koneksi.php'; if(isset($koneksi)) $conn=$koneksi; }

// --- HELPER FUNCTIONS ---
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function postv($k,$d=''){ return isset($_POST[$k])?trim($_POST[$k]):$d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }

$created_by = isset($_SESSION['id_user']) ? clean($conn, $_SESSION['id_user']) : 'admin';
$redirect_url = 'home-admin.php?page=view-data-biaya-pendidikan';

// --- 1. TENTUKAN MODE (ADD / EDIT) ---
$id_biaya = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode     = $id_biaya > 0 ? 'edit' : 'add';
$data     = null;

// Jika Edit, Ambil Data Lama
if ($mode === 'edit') {
    $qData = mysqli_query($conn, "SELECT * FROM tb_biaya_pendidikan WHERE biaya_id = '$id_biaya' LIMIT 1");
    if ($qData && mysqli_num_rows($qData) > 0) {
        $data = mysqli_fetch_assoc($qData);
    } else {
        $mode = 'add';
    }
}

// --- 2. AMBIL DATA REFERENSI ---
$ref_kat = mysqli_query($conn, "SELECT kode_sandi, kategori FROM tb_ref_pengembangan ORDER BY kode_sandi ASC");
$ref_pihak = mysqli_query($conn, "SELECT kode_pihak, nama_pihak FROM tb_ref_pelaksana ORDER BY kode_pihak ASC");


// --- 3. PROSES SIMPAN (POST) ---
$status = ''; $msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tangkap Input
    $kode_pengembangan    = clean($conn, postv('kode_pengembangan'));
    $kode_pihak           = clean($conn, postv('kode_pihak'));
    $pengembangan_sdm     = clean($conn, postv('pengembangan_sdm')); 
    $pihak_pelaksana      = clean($conn, postv('pihak_pelaksana')); 
    $waktu_pelaksanaan    = clean($conn, postv('waktu_pelaksanaan'));
    $tgl_pengembangan_sdm = clean($conn, postv('tgl_pengembangan_sdm'));
    
    // --- KHUSUS ANGKA (Validation & Cleaning) ---
    $jumlah_sdm = (int)postv('jumlah_sdm', 0);
    
    // Bersihkan format Rupiah (Hapus titik sebelum simpan ke DB)
    // Contoh input: "5.000.000" -> jadi "5000000"
    $raw_biaya   = postv('total_biaya', '0');
    $clean_biaya = str_replace('.', '', $raw_biaya); 
    $total_biaya = (float)$clean_biaya;

    $id_post   = (int)postv('id_biaya_post', 0);
    $mode_post = $id_post > 0 ? 'edit' : 'add';

    // Validasi Sederhana
    if ($kode_pengembangan && $pengembangan_sdm && $tgl_pengembangan_sdm) {
        
        // Validasi Logic Tambahan
        if ($jumlah_sdm < 1) {
            $status = 'gagal';
            $msg = 'Jumlah peserta minimal 1 orang.';
        } else {
            if ($mode_post === 'edit') {
                // --- UPDATE QUERY ---
                $sql = "UPDATE tb_biaya_pendidikan SET 
                        kode_pengembangan    = '$kode_pengembangan',
                        kode_pihak           = '$kode_pihak',
                        pengembangan_sdm     = '$pengembangan_sdm',
                        pihak_pelaksana      = '$pihak_pelaksana',
                        waktu_pelaksanaan    = '$waktu_pelaksanaan',
                        jumlah_sdm           = '$jumlah_sdm',
                        total_biaya          = '$total_biaya',
                        tgl_pengembangan_sdm = '$tgl_pengembangan_sdm',
                        updated_at           = NOW(),
                        updated_by           = '$created_by'
                        WHERE biaya_id       = '$id_post'";
            } else {
                // --- INSERT QUERY ---
                $sql = "INSERT INTO tb_biaya_pendidikan 
                       (kode_pengembangan, kode_pihak, pengembangan_sdm, pihak_pelaksana, waktu_pelaksanaan, jumlah_sdm, total_biaya, tgl_pengembangan_sdm, created, created_by)
                       VALUES 
                       ('$kode_pengembangan', '$kode_pihak', '$pengembangan_sdm', '$pihak_pelaksana', '$waktu_pelaksanaan', '$jumlah_sdm', '$total_biaya', '$tgl_pengembangan_sdm', CURDATE(), '$created_by')";
            }

            if (mysqli_query($conn, $sql)) {
                $status = 'sukses';
            } else {
                $status = 'gagal';
                $msg = 'Database Error: ' . mysqli_error($conn);
            }
        }

    } else {
        $status = 'gagal';
        $msg = 'Mohon lengkapi data wajib (Kategori, Judul, Tanggal).';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?php echo $mode==='edit' ? 'Edit Biaya' : 'Input Biaya'; ?></title>
  
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
    .card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}
    .card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}
    .is-invalid-custom { border-color: #dc3545 !important; background-color: #fff8f8 !important; }
    .select2-container .select2-selection--single { height: 38px !important; display: flex; align-items: center; border-color: #ced4da; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px !important; }
  </style>
</head>
<body>
<div class="container mt-4">
  <div class="card">
    
    <div class="card-header d-flex justify-content-between align-items-center">
       <div>
           <h5 class="mb-0"><?php echo $mode==='edit' ? 'Edit Data Biaya Pendidikan' : 'Input Biaya Pendidikan Baru'; ?></h5>
           <small class="text-white-50"><?php echo $mode==='edit' ? 'Perbarui informasi kegiatan & anggaran.' : 'Silakan isi formulir di bawah ini.'; ?></small>
       </div>
       <a href="<?php echo $redirect_url; ?>" class="btn btn-sm btn-light text-primary rounded-circle"><i class="fas fa-times"></i></a>
    </div>

    <div class="card-body p-4">
      
      <?php if($status==='sukses'):?>
        <script>
            Swal.fire({icon:'success', title:'Berhasil Disimpan', text:'Data biaya pendidikan telah diperbarui.', timer: 1500, showConfirmButton: false})
            .then(function(){ location.href='<?php echo $redirect_url; ?>'; });
        </script>
      <?php elseif($status==='gagal'):?>
        <script>Swal.fire({icon:'error', title:'Gagal Menyimpan', text:<?php echo json_encode($msg); ?>});</script>
      <?php endif; ?>

      <form method="post" action="" autocomplete="off" id="frmBiaya">
        
        <?php if($mode==='edit'): ?> 
            <input type="hidden" name="id_biaya_post" value="<?php echo (int)$id_biaya; ?>"> 
        <?php endif; ?>

        <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-tag mr-2"></i>Klasifikasi Kegiatan</h6>
        <div class="row mb-3">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Kategori Pengembangan <span class="text-danger">*</span></label>
            <select name="kode_pengembangan" class="form-select select2 check-field" required>
                <option value="">- Pilih Kategori -</option>
                <?php 
                if($ref_kat){
                    while($r = mysqli_fetch_assoc($ref_kat)){
                        $sel = ($data && $data['kode_pengembangan'] == $r['kode_sandi']) ? 'selected' : '';
                        echo '<option value="'.e($r['kode_sandi']).'" '.$sel.'>'.e($r['kode_sandi'].' - '.$r['kategori']).'</option>';
                    }
                }
                ?>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Jenis Pihak Pelaksana <span class="text-danger">*</span></label>
            <select name="kode_pihak" class="form-select select2 check-field" required>
                <option value="">- Pilih Jenis Pihak -</option>
                <?php 
                if($ref_pihak){
                    while($r = mysqli_fetch_assoc($ref_pihak)){
                        $sel = ($data && $data['kode_pihak'] == $r['kode_pihak']) ? 'selected' : '';
                        echo '<option value="'.e($r['kode_pihak']).'" '.$sel.'>'.e($r['kode_pihak'].' - '.$r['nama_pihak']).'</option>';
                    }
                }
                ?>
            </select>
          </div>
        </div>

        <hr class="my-4">

        <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-calendar-alt mr-2"></i>Detail Pelaksanaan</h6>
        <div class="row mb-3">
            <div class="col-md-12 mb-3">
                <label class="form-label">Judul Kegiatan / Pengembangan SDM <span class="text-danger">*</span></label>
                <input type="text" name="pengembangan_sdm" class="form-control check-field" required 
                       placeholder="Contoh: Workshop Manajemen Risiko Level 1"
                       value="<?php echo e($data ? $data['pengembangan_sdm'] : ''); ?>">
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label">Nama Pihak Pelaksana (Instansi/Vendor)</label>
                <input type="text" name="pihak_pelaksana" class="form-control" 
                       placeholder="Contoh: PT. Infra Digital / Internal HRD"
                       value="<?php echo e($data ? $data['pihak_pelaksana'] : ''); ?>">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tgl_pengembangan_sdm" class="form-control check-field" required
                       value="<?php echo e($data ? $data['tgl_pengembangan_sdm'] : date('Y-m-d')); ?>">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Durasi / Waktu</label>
                <input type="text" name="waktu_pelaksanaan" class="form-control" 
                       placeholder="Contoh: 3 Hari"
                       value="<?php echo e($data ? $data['waktu_pelaksanaan'] : ''); ?>">
            </div>
        </div>

        <hr class="my-4">

        <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-coins mr-2"></i>Anggaran & Peserta</h6>
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Jumlah Peserta (SDM) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="jumlah_sdm" class="form-control check-field" 
                           min="1" placeholder="0" required
                           value="<?php echo e($data ? $data['jumlah_sdm'] : '1'); ?>">
                    <span class="input-group-text bg-light">Orang</span>
                </div>
                <small class="text-muted">Minimal 1 orang.</small>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Total Biaya (Rp)</label>
                <div class="input-group">
                    <span class="input-group-text fw-bold">Rp</span>
                    <input type="text" name="total_biaya" id="inputBiaya" class="form-control fw-bold text-end" 
                           placeholder="0" onkeyup="formatRupiah(this)"
                           value="<?php echo e($data ? number_format($data['total_biaya'], 0, ',', '.') : '0'); ?>">
                </div>
                <small class="text-muted">*Otomatis diformat (Contoh: ketik 5000000 jadi 5.000.000)</small>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4 pt-3 border-top gap-2">
            <a href="<?php echo $redirect_url; ?>" class="btn btn-light border px-4">Batal</a>
            <button class="btn btn-primary px-4 shadow-sm" type="button" id="btnCheckAndSubmit">
                <i class="fas fa-save mr-1"></i> <?php echo $mode==='edit' ? 'Update Data' : 'Simpan Data'; ?>
            </button>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
// Fungsi Format Rupiah (Titik Ribuan)
function formatRupiah(el) {
    let angka = el.value.replace(/\D/g, ""); // Hapus karakter selain angka
    if (angka === "") {
        el.value = "";
        return;
    }
    // Tambah titik setiap 3 digit
    el.value = new Intl.NumberFormat('id-ID').format(angka);
}

$(function(){
    $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

    $('.check-field').on('input change', function(){
        if($(this).val()) $(this).removeClass('is-invalid-custom');
    });

    $('#btnCheckAndSubmit').on('click', function(e){
        e.preventDefault();
        let allValid = true;

        // Validasi Required
        $('#frmBiaya [required]').each(function(){
            if( !$(this).val() ) {
                $(this).addClass('is-invalid-custom');
                allValid = false;
            } else {
                $(this).removeClass('is-invalid-custom');
            }
        });

        // Validasi Jumlah SDM Min 1
        let jmlSdm = $('input[name="jumlah_sdm"]').val();
        if(jmlSdm < 1) {
            $('input[name="jumlah_sdm"]').addClass('is-invalid-custom');
            allValid = false;
            Swal.fire({icon: 'warning', title: 'Data Peserta Salah', text: 'Jumlah peserta minimal 1 orang.'});
            return;
        }

        if(allValid) {
            let btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
            $('#frmBiaya').submit();
        } else {
            Swal.fire({icon: 'warning', title: 'Belum Lengkap', text: 'Mohon lengkapi data wajib yang ditandai merah.'});
        }
    });
});
</script>

</body>
</html>