<?php
/*********************************************************
 * FILE     : pages/user/form-master-data-user.php
 * MODULE   : Manajemen User (Final Fix: No Auto-Complete)
 * STATUS   : PHP 5.6 Ready | Validation OK | Clean Inputs
 *********************************************************/

if (session_id() == '') session_start();
include "dist/koneksi.php";

// --- HELPERS (PHP 5.6 SAFE) ---
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function clean($c, $s){ return mysqli_real_escape_string($c, trim($s)); }
function v($arr, $key, $def=''){ return (isset($arr[$key]) && $arr[$key]!==null) ? $arr[$key] : $def; }

// --- 1. INISIALISASI ---
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'create';
$id   = isset($_GET['id']) ? clean($conn, $_GET['id']) : '';
$admin_login = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'System';

// Default Data
$data = array('id_user'=>'','nama_user'=>'','hak_akses'=>'','id_pegawai'=>'','status_aktif'=>'Y');

// Ambil Data Edit
if ($mode == 'edit' && !empty($id)) {
    $q = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user = '$id'");
    if (mysqli_num_rows($q) > 0) { $data = mysqli_fetch_assoc($q); }
    else { echo "<script>window.location='home-admin.php?page=form-view-data-user';</script>"; exit; }
}

// --- 2. PROSES SIMPAN ---
$status_process = '';
$msg_process = '';

if (isset($_POST['btn_simpan'])) {
    $mode_post = $_POST['mode'];
    $id_user   = clean($conn, $_POST['id_user']);
    $nama      = clean($conn, $_POST['nama_user']); 
    $akses     = $_POST['hak_akses'];
    $pass_raw  = $_POST['password'];
    $status    = isset($_POST['status_aktif']) ? 'Y' : 'N';
    $id_peg    = !empty($_POST['id_pegawai']) ? clean($conn, $_POST['id_pegawai']) : 'NULL';

    $error = '';
    
    // Validasi
    if ($mode_post == 'create') {
        $cek = mysqli_query($conn, "SELECT id_user FROM tb_user WHERE id_user = '$id_user'");
        if (mysqli_num_rows($cek) > 0) $error = "Username '$id_user' sudah dipakai!";
        elseif ($id_peg != 'NULL') {
            $cekPeg = mysqli_query($conn, "SELECT id_user FROM tb_user WHERE id_pegawai = '$id_peg'");
            if (mysqli_num_rows($cekPeg) > 0) $error = "Pegawai ini sudah punya akun!";
        }
    }

    if (empty($error)) {
        $sqlValPeg = ($id_peg == 'NULL') ? "NULL" : "'$id_peg'";
        
        if ($mode_post == 'create') {
            $pass_hash = md5($pass_raw);
            $sql = "INSERT INTO tb_user (id_user, nama_user, password, hak_akses, id_pegawai, status_aktif, created_by, created_at) 
                    VALUES ('$id_user', '$nama', '$pass_hash', '$akses', $sqlValPeg, '$status', '$admin_login', NOW())";
        } else {
            $id_lama  = clean($conn, $_POST['id_user_lama']);
            $sql_pass = !empty($pass_raw) ? ", password = '".md5($pass_raw)."'" : "";
            $sql = "UPDATE tb_user SET nama_user='$nama', hak_akses='$akses', id_pegawai=$sqlValPeg, 
                    status_aktif='$status', updated_by='$admin_login', updated_at=NOW() $sql_pass 
                    WHERE id_user='$id_lama'";
        }

        if (mysqli_query($conn, $sql)) { $status_process = 'sukses'; }
        else { $status_process = 'gagal'; $msg_process = mysqli_error($conn); }
    } else {
        $status_process = 'warning'; $msg_process = $error;
    }
}

// --- 3. DATA PEGAWAI ---
$sqlPeg = "SELECT id_peg, nama FROM tb_pegawai WHERE status_aktif = '1' ORDER BY nama ASC";
$qPeg = mysqli_query($conn, $sqlPeg);
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .card-form { border-radius: 12px; border:none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .form-header { background: #0d6efd; color: white; padding: 15px 20px; border-radius: 12px 12px 0 0; }
    .form-control-modern { border-radius: 6px; height: 40px; }
    .select2-container .select2-selection--single { height: 40px !important; display: flex; align-items: center; border-color: #ced4da; }
    .readonly-field { background-color: #e9ecef !important; cursor: not-allowed; pointer-events: none; }
</style>

<section class="content pt-4">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-9"> 
                <div class="card card-form">
                    
                    <div class="form-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-user-shield mr-2"></i><?= ($mode=='edit') ? 'Edit Data User' : 'Tambah User Baru' ?></h5>
                        <a href="home-admin.php?page=form-view-data-user" class="btn btn-sm btn-light rounded-pill px-3 font-weight-bold text-primary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                    </div>

                    <div class="card-body p-4">
                        
                        <?php if($status_process == 'sukses'): ?>
                            <script>
                                Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Data user tersimpan.', timer: 1500, showConfirmButton: false})
                                .then(function() { window.location.href = 'home-admin.php?page=form-view-data-user'; });
                            </script>
                        <?php elseif($status_process == 'gagal'): ?>
                            <script>Swal.fire({icon: 'error', title: 'Gagal', text: '<?= $msg_process ?>'});</script>
                        <?php elseif($status_process == 'warning'): ?>
                            <script>Swal.fire({icon: 'warning', title: 'Perhatian', text: '<?= $msg_process ?>'});</script>
                        <?php endif; ?>

                        <form action="" method="POST" autocomplete="off">
                            
                            <input type="text" style="display:none">
                            <input type="password" style="display:none">

                            <input type="hidden" name="mode" value="<?= $mode ?>">
                            <input type="hidden" name="id_user_lama" value="<?= v($data, 'id_user') ?>">
                            <input type="hidden" name="id_pegawai" id="final_id_peg" value="<?= v($data, 'id_pegawai') ?>">

                            <div class="alert alert-light border mb-4">
                                <label class="text-primary font-weight-bold small text-uppercase mb-2"><i class="fas fa-search mr-1"></i> Pilih Pegawai (Otomatis Isi Nama)</label>
                                
                                <select id="sumber_pegawai" class="form-control select2">
                                    <option value="">-- Ketik Nama / NIP Pegawai --</option>
                                    <?php 
                                    if($qPeg) {
                                        while($p = mysqli_fetch_assoc($qPeg)) { 
                                            $sel = (v($data, 'id_pegawai') == $p['id_peg']) ? 'selected' : ''; 
                                            $nama_full = e($p['nama']);
                                    ?>
                                        <option value="<?= $p['id_peg'] ?>" data-namauser="<?= $nama_full ?>" <?= $sel ?>>
                                            <?= $p['nama'] ?> (<?= $p['id_peg'] ?>)
                                        </option>
                                    <?php 
                                        } 
                                    }
                                    ?>
                                </select>
                                <small class="text-muted mt-2 d-block">*Ketik nama di kotak pencarian untuk memilih.</small>
                            </div>

                            <h6 class="font-weight-bold text-secondary mb-3 border-bottom pb-2">Detail Akun</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Username / ID User <span class="text-danger">*</span></label>
                                    <input type="text" name="id_user" class="form-control form-control-modern <?= ($mode=='edit')?'readonly-field':'' ?>" 
                                           placeholder="Contoh: admin" 
                                           value="<?= v($data, 'id_user') ?>" 
                                           autocomplete="new-user"
                                           <?= ($mode=='edit')?'readonly':'required' ?>>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Nama Lengkap User <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_user" id="nama_user_target" class="form-control form-control-modern" 
                                           placeholder="Akan terisi otomatis..." value="<?= v($data, 'nama_user') ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Level Akses <span class="text-danger">*</span></label>
                                    <select name="hak_akses" class="form-control form-control-modern" required>
                                        <option value="">-- Pilih Level --</option>
                                        <option value="Admin" <?= (strtolower(v($data, 'hak_akses'))=='admin')?'selected':'' ?>>Admin</option>
                                        <option value="Kepala" <?= (strtolower(v($data, 'hak_akses'))=='kepala')?'selected':'' ?>>Kepala</option>
                                        <option value="User" <?= (strtolower(v($data, 'hak_akses'))=='user')?'selected':'' ?>>User</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Password <?= ($mode=='edit') ? '<small class="text-muted">(Kosongkan jika tetap)</small>' : '<span class="text-danger">*</span>' ?></label>
                                    <input type="password" name="password" class="form-control form-control-modern" 
                                           placeholder="******" 
                                           autocomplete="new-password"
                                           <?= ($mode=='create')?'required':'' ?>>
                                </div>
                            </div>

                            <div class="row align-items-center mt-2">
                                <div class="col-md-6">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="sw_aktif" name="status_aktif" value="Y" <?= (v($data, 'status_aktif')=='Y')?'checked':'' ?>>
                                        <label class="custom-control-label font-weight-bold text-success" for="sw_aktif">Status Akun Aktif</label>
                                    </div>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" name="btn_simpan" class="btn btn-primary rounded-pill px-5 shadow-sm font-weight-bold">
                                        <i class="fas fa-save mr-2"></i> SIMPAN
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Init Select2
    $('#sumber_pegawai').select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: true,
        placeholder: "-- Pilih Pegawai --"
    });

    // AUTO FILL LOGIC
    $('#sumber_pegawai').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var id = $(this).val();
        var nama = selectedOption.attr('data-namauser');

        if(id) {
            $('#final_id_peg').val(id);
            if(nama) $('#nama_user_target').val(nama);
        } else {
            $('#final_id_peg').val('');
            $('#nama_user_target').val('');
        }
    });
    
    // Extra Clear untuk memastikan form bersih saat load
    <?php if($mode=='create'): ?>
    setTimeout(function() {
        $('input[name="id_user"]').val('');
        $('input[name="password"]').val('');
    }, 100);
    <?php endif; ?>
});
</script>