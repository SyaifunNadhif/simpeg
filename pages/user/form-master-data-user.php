<?php
// FILE: pages/user/form-master-data-user.php
include "dist/koneksi.php";

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'create';
$id   = isset($_GET['id']) ? $_GET['id'] : '';
$admin_login = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'System';

// --- 1. PROSES BACKEND (SIMPAN/UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. DELETE AMAN
    if (isset($_POST['act']) && $_POST['act'] == 'delete_aman') {
        $id_del = mysqli_real_escape_string($conn, $_POST['id_user']);
        $alasan = mysqli_real_escape_string($conn, $_POST['alasan']);
        
        $qCek = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user = '$id_del'");
        if(mysqli_num_rows($qCek) > 0) {
            $dataLama = mysqli_fetch_assoc($qCek);
            $json = mysqli_real_escape_string($conn, json_encode($dataLama));
            
            mysqli_query($conn, "INSERT INTO tb_recycle_bin (tabel_asal, id_data, data_json, alasan, dihapus_oleh) VALUES ('tb_user', '$id_del', '$json', '$alasan', '$admin_login')");
            mysqli_query($conn, "DELETE FROM tb_user WHERE id_user = '$id_del'");
            
            echo "<script>Swal.fire('Terhapus!', 'User berhasil diamankan.', 'success').then(() => { window.location='home-admin.php?page=view-data-user'; });</script>";
            exit;
        }
    }

    // B. SIMPAN / UPDATE DATA
    if (isset($_POST['btn_simpan'])) {
        $mode_post = $_POST['mode'];
        $id_user   = mysqli_real_escape_string($conn, $_POST['id_user']);
        $nama      = mysqli_real_escape_string($conn, $_POST['nama_user']);
        $akses     = $_POST['hak_akses'];
        $pass_raw  = $_POST['password'];
        $status    = isset($_POST['status_aktif']) ? 'Y' : 'N';
        
        // Data Pegawai & Jabatan
        $id_peg    = !empty($_POST['id_pegawai']) ? $_POST['id_pegawai'] : NULL;
        
        // Logika Jabatan: Prioritas Input Hidden (Auto) -> Jika kosong ambil Select Manual
        $jabatan   = !empty($_POST['jabatan_auto']) ? $_POST['jabatan_auto'] : (!empty($_POST['jabatan_manual']) ? $_POST['jabatan_manual'] : NULL);

        if ($mode_post == 'create') {
            $cek = mysqli_query($conn, "SELECT id_user FROM tb_user WHERE id_user = '$id_user'");
            if (mysqli_num_rows($cek) > 0) {
                echo "<script>Swal.fire('Gagal', 'Username sudah ada!', 'error');</script>";
            } else {
                $pass_hash = md5($pass_raw);
                $sql = "INSERT INTO tb_user (id_user, nama_user, password, hak_akses, id_pegawai, jabatan, status_aktif, created_by, created_at) 
                        VALUES ('$id_user', '$nama', '$pass_hash', '$akses', '$id_peg', '$jabatan', '$status', '$admin_login', NOW())";
                if(mysqli_query($conn, $sql)) {
                    echo "<script>Swal.fire('Sukses', 'User berhasil ditambahkan', 'success').then(() => { window.location='home-admin.php?page=view-data-user'; });</script>";
                } else {
                    echo "<script>Swal.fire('Gagal', 'Error: ".mysqli_error($conn)."', 'error');</script>";
                }
            }
        } 
        elseif ($mode_post == 'edit') {
            $id_lama = $_POST['id_user_lama'];
            $sql_pass = !empty($pass_raw) ? ", password = '".md5($pass_raw)."'" : "";
            
            // Update Data (Nama & Jabatan tidak diupdate jika mode edit, sesuai request terkunci)
            // Kecuali jika admin memaksa ubah lewat backend, tapi di UI kita lock.
            
            $sql = "UPDATE tb_user SET hak_akses='$akses', status_aktif='$status', updated_by='$admin_login', updated_at=NOW() $sql_pass WHERE id_user='$id_lama'";
                    
            if(mysqli_query($conn, $sql)) {
                echo "<script>Swal.fire('Sukses', 'Data user diperbarui', 'success').then(() => { window.location='home-admin.php?page=view-data-user'; });</script>";
            }
        }
    }
}

// --- 2. PERSIAPAN DATA ---
$data = ['id_user'=>'','nama_user'=>'','hak_akses'=>'','id_pegawai'=>'','jabatan'=>'','status_aktif'=>'Y'];

if ($mode == 'edit' && !empty($id)) {
    $q = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user = '$id'");
    if(mysqli_num_rows($q) > 0) { $data = mysqli_fetch_assoc($q); }
}

// QUERY PEGAWAI + JABATAN AKTIFNYA
// Pastikan nama kolom 'nama_jabatan' sesuai dengan struktur tabel tb_master_jabatan
$sqlPeg = "SELECT p.id_peg, p.nama, 
          (SELECT mj.nama_jabatan 
           FROM tb_jabatan j 
           JOIN tb_master_jabatan mj ON j.kode_jabatan = mj.kode_jabatan 
           WHERE j.id_peg = p.id_peg AND j.status_jab = 'Aktif' 
           ORDER BY j.tmt_jabatan DESC LIMIT 1) as jabatan_aktif
           FROM tb_pegawai p ORDER BY p.nama ASC";
$qPeg = mysqli_query($conn, $sqlPeg);

// QUERY MASTER JABATAN (Untuk Pilihan Manual)
$qMasterJab = mysqli_query($conn, "SELECT nama_jabatan FROM tb_master_jabatan ORDER BY nama_jabatan ASC");
?>

<link href="plugins/select2/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<script src="plugins/select2/js/select2.full.min.js"></script>

<style>
    .card-form { border-radius: 15px; border:none; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .form-header { background: #0d6efd; color: white; padding: 15px 20px; border-radius: 15px 15px 0 0; }
    .form-control-modern { border-radius: 8px; height: 45px; }
    .select2-container .select2-selection--single { height: 45px !important; border-radius: 8px !important; display: flex; align-items: center; border-color: #ced4da; }
    .readonly-field { background-color: #e9ecef !important; cursor: not-allowed; pointer-events: none; }
</style>

<section class="content pt-4">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-form">
                    <div class="form-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold"><?= ($mode=='edit') ? 'Edit Data User' : 'Tambah User Baru' ?></h5>
                        <a href="home-admin.php?page=view-data-user" class="btn btn-sm btn-light rounded-pill"><i class="fas fa-times"></i></a>
                    </div>
                    <div class="card-body p-4">
                        
                        <form action="" method="POST">
                            <input type="hidden" name="mode" value="<?= $mode ?>">
                            <input type="hidden" name="id_user_lama" value="<?= $data['id_user'] ?>">
                            <input type="hidden" name="id_pegawai" id="final_id_peg" value="<?= $data['id_pegawai'] ?>">

                            <div class="card bg-light border-0 mb-4 shadow-sm">
                                <div class="card-body">
                                    <label class="text-primary font-weight-bold text-uppercase small">1. Cari Data Pegawai (Auto-Fill)</label>
                                    
                                    <select id="sumber_pegawai" class="form-control select2" style="width: 100%;" <?= ($mode=='edit') ? 'disabled' : '' ?>>
                                        <option value="">-- Ketik Nama Pegawai --</option>
                                        <?php while($p = mysqli_fetch_assoc($qPeg)) { 
                                            $sel = ($data['id_pegawai'] == $p['id_peg']) ? 'selected' : ''; 
                                            $jabatan_aktif = !empty($p['jabatan_aktif']) ? $p['jabatan_aktif'] : ''; 
                                        ?>
                                            <option value="<?= $p['id_peg'] ?>" 
                                                    data-nama="<?= $p['nama'] ?>" 
                                                    data-jabatan="<?= $jabatan_aktif ?>" 
                                                    <?= $sel ?>>
                                                <?= $p['nama'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle mr-1"></i> Otomatis mengisi Nama & Jabatan jika data tersedia.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Username / ID User <span class="text-danger">*</span></label>
                                    <input type="text" name="id_user" class="form-control form-control-modern <?= ($mode=='edit')?'readonly-field':'' ?>" 
                                           value="<?= $data['id_user'] ?>" <?= ($mode=='edit')?'readonly required':'required' ?>>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="nama_user" id="nama_user_target" class="form-control form-control-modern readonly-field" 
                                           value="<?= $data['nama_user'] ?>" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Jabatan</label>
                                    
                                    <input type="text" name="jabatan_auto" id="jabatan_display" 
                                           class="form-control form-control-modern readonly-field mb-2" 
                                           placeholder="Terisi Otomatis..." value="<?= $data['jabatan'] ?>" readonly>
                                    
                                    <div id="manual_jabatan_box" style="display:none;">
                                        <div class="alert alert-warning py-2 px-3 small font-weight-bold mb-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Jabatan tidak ditemukan. Pilih manual:
                                        </div>
                                        <select name="jabatan_manual" id="manual_jabatan_select" class="form-control select2" style="width: 100%;">
                                            <option value="">-- Pilih Master Jabatan --</option>
                                            <?php while($mj = mysqli_fetch_assoc($qMasterJab)) { ?>
                                                <option value="<?= $mj['nama_jabatan'] ?>"><?= $mj['nama_jabatan'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Level Akses <span class="text-danger">*</span></label>
                                    <select name="hak_akses" class="form-control form-control-modern" required>
                                        <option value="">-- Pilih Level --</option>
                                        <option value="Admin" <?= ($data['hak_akses']=='Admin')?'selected':'' ?>>Admin</option>
                                        <option value="Kepala" <?= ($data['hak_akses']=='Kepala')?'selected':'' ?>>Kepala</option>
                                        <option value="User" <?= ($data['hak_akses']=='User')?'selected':'' ?>>User</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Password <?= ($mode=='edit') ? '<small class="text-muted">(Biarkan kosong jika tidak diganti)</small>' : '<span class="text-danger">*</span>' ?></label>
                                    <input type="password" name="password" class="form-control form-control-modern" <?= ($mode=='create')?'required':'' ?>>
                                </div>
                                <div class="col-md-6 mb-3 pt-4">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="sw_aktif" name="status_aktif" value="Y" <?= ($data['status_aktif']=='Y')?'checked':'' ?>>
                                        <label class="custom-control-label font-weight-bold" for="sw_aktif">Akun Aktif?</label>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right mt-3 border-top pt-3">
                                <a href="home-admin.php?page=view-data-user" class="btn btn-light border rounded-pill px-4 mr-2">Batal</a>
                                <button type="submit" name="btn_simpan" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                    <i class="fas fa-save mr-1"></i> Simpan Data
                                </button>
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
    // 1. Inisialisasi Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: "Cari...",
        allowClear: true
    });

    // 2. LOGIC AUTO FILL (Event: select2:select)
    $('#sumber_pegawai').on('select2:select', function(e) {
        var data = e.params.data; // Mengambil data objek dari select2
        
        // Ambil atribut data custom dari elemen <option> yang dipilih
        var element = $(data.element);
        var idPeg = element.val();
        var nama  = element.data('nama');
        var jabatan = element.data('jabatan');

        console.log("Terpilih: ", nama, jabatan); // Debugging di Console

        if(idPeg) {
            // Isi Field
            $('#final_id_peg').val(idPeg);
            $('#nama_user_target').val(nama);
            
            // Logic Jabatan
            if(jabatan && jabatan !== "") {
                // Ada jabatan -> Isi otomatis & sembunyikan manual
                $('#jabatan_display').val(jabatan);
                $('#manual_jabatan_box').slideUp();
                $('#manual_jabatan_select').val(null).trigger('change'); // Reset manual
            } else {
                // Tidak ada jabatan -> Minta input manual
                $('#jabatan_display').val('');
                $('#manual_jabatan_box').slideDown();
            }
        }
    });

    // 3. Reset jika dihapus (select2:unselect)
    $('#sumber_pegawai').on('select2:unselect', function(e) {
        $('#final_id_peg').val('');
        $('#nama_user_target').val('');
        $('#jabatan_display').val('');
        $('#manual_jabatan_box').slideUp();
    });
});
</script>