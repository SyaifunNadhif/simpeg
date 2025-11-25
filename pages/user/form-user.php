<?php
/*********************************************************
 * FILE    : pages/user/form-user.php
 * MODULE  : Form User (Auto-Fill Unit Kerja)
 * VERSION : v2.1
 *********************************************************/

include "dist/koneksi.php";

// --- 1. INISIALISASI MODE (TAMBAH / EDIT) ---
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'create';
$id   = isset($_GET['id']) ? $_GET['id'] : '';

// Default Data
$data = [
    'id_user'      => '',
    'nama_user'    => '',
    'password'     => '',
    'hak_akses'    => '',
    'id_pegawai'   => '',
    'unit_kerja'   => '',
    'status_aktif' => 'Y'
];

if ($mode == 'edit' && !empty($id)) {
    $q = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user = '$id'");
    if (mysqli_num_rows($q) > 0) {
        $data = mysqli_fetch_assoc($q);
    } else {
        echo "<script>window.location='home-admin.php?page=view-data-user';</script>";
    }
}

// --- QUERY PENDUKUNG ---

// 1. Ambil Pegawai + Unit Kerjanya (Join ke Jabatan Aktif)
// Trik: Kita ambil unit_kerja dari jabatan terakhir yang aktif
$sqlPegawai = "SELECT p.id_peg, p.nama, 
              (SELECT unit_kerja FROM tb_jabatan WHERE id_peg = p.id_peg AND status_jab = 'Aktif' ORDER BY tmt_jabatan DESC LIMIT 1) as unit_kerja_peg
              FROM tb_pegawai p ORDER BY p.nama ASC";
$qPegawai = mysqli_query($conn, $sqlPegawai);

// 2. Ambil Data Kantor
$qKantor  = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor ORDER BY nama_kantor ASC");
?>

<style>
    .card-modern { border: none; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: #fff; }
    .form-header-modern { 
        background: #007bff; color: #fff; 
        border-bottom: 1px solid #f1f1f1; padding: 20px; 
        border-radius: 16px 16px 0 0; 
    }
    .input-modern { 
        border-radius: 10px; border: 1px solid #e2e8f0; 
        padding: 10px 15px; height: 45px; width: 100%; 
    }
    .input-modern:focus { border-color: #007bff; box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2); outline: none; }
    .form-label-modern { font-size: 0.8rem; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 8px; }
    
    /* Select2 Style Fix */
    .select2-container--bootstrap4 .select2-selection--single {
        height: 45px !important; border-radius: 10px !important; border: 1px solid #e2e8f0 !important; padding-top: 8px !important;
    }
</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark"><?= ($mode == 'edit') ? 'Edit User' : 'Tambah User' ?></h1>
            </div>
            <div>
                <a href="home-admin.php?page=view-data-user" class="btn btn-light rounded-pill border shadow-sm">
                    <i class="fa fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content mt-3">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="card card-modern">
                    <div class="form-header-modern">
                        <h5 class="m-0 font-weight-bold">
                            <i class="fas <?= ($mode == 'edit') ? 'fa-user-edit' : 'fa-user-plus' ?> mr-2"></i> 
                            Form Data User
                        </h5>
                    </div>
                    
                    <div class="card-body p-4">
                        <form action="pages/user/proses-user.php" method="POST">
                            
                            <input type="hidden" name="mode" value="<?= $mode ?>">
                            <input type="hidden" name="id_user_lama" value="<?= $data['id_user'] ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Username (ID User) <span class="text-danger">*</span></label>
                                        <input type="text" name="id_user" class="form-control input-modern" 
                                               value="<?= htmlspecialchars($data['id_user']) ?>" 
                                               <?= ($mode == 'edit') ? 'readonly' : 'required' ?> 
                                               placeholder="Masukkan username unik">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_user" class="form-control input-modern" 
                                               value="<?= htmlspecialchars($data['nama_user']) ?>" required placeholder="Nama pengguna">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Password <?= ($mode == 'edit') ? '(Opsional)' : '<span class="text-danger">*</span>' ?></label>
                                        <input type="password" name="password" class="form-control input-modern" 
                                               placeholder="<?= ($mode == 'edit') ? 'Kosongkan jika tidak ingin mengubah' : 'Masukkan password' ?>"
                                               <?= ($mode == 'create') ? 'required' : '' ?>>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Level Akses <span class="text-danger">*</span></label>
                                        <select name="hak_akses" class="form-control input-modern" required>
                                            <option value="">-- Pilih Level --</option>
                                            <option value="Admin" <?= ($data['hak_akses'] == 'Admin') ? 'selected' : '' ?>>Admin</option>
                                            <option value="Kepala" <?= ($data['hak_akses'] == 'Kepala') ? 'selected' : '' ?>>Kepala</option>
                                            <option value="User" <?= ($data['hak_akses'] == 'User') ? 'selected' : '' ?>>User (Pegawai)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="text-muted font-weight-bold mb-3">Tautkan Data Pegawai (Auto-Fill Unit Kerja)</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Pegawai Terkait</label>
                                        <select name="id_pegawai" id="pilih_pegawai" class="form-control select2bs4">
                                            <option value="" data-unit="">-- Tidak Ada --</option>
                                            <?php while($p = mysqli_fetch_assoc($qPegawai)) { 
                                                // Simpan unit kerja di attribute data-unit
                                                $unit = isset($p['unit_kerja_peg']) ? $p['unit_kerja_peg'] : '';
                                            ?>
                                                <option value="<?= $p['id_peg'] ?>" 
                                                        data-unit="<?= $unit ?>"
                                                        <?= ($data['id_pegawai'] == $p['id_peg']) ? 'selected' : '' ?>>
                                                    <?= $p['nama'] ?> (<?= $p['id_peg'] ?>)
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Unit Kerja</label>
                                        <select name="unit_kerja" id="pilih_unit" class="form-control select2bs4">
                                            <option value="">-- Pilih Kantor --</option>
                                            <?php while($k = mysqli_fetch_assoc($qKantor)) { ?>
                                                <option value="<?= $k['kode_kantor_detail'] ?>" <?= ($data['unit_kerja'] == $k['kode_kantor_detail']) ? 'selected' : '' ?>>
                                                    <?= $k['nama_kantor'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label-modern">Status Akun</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="status_aktif" name="status_aktif" value="Y" <?= ($data['status_aktif'] == 'Y') ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="status_aktif">Aktifkan User ini</label>
                                </div>
                            </div>

                            <div class="form-group text-right mt-4 pt-3 border-top">
                                <a href="home-admin.php?page=view-data-user" class="btn btn-light btn-modern mr-2 border">Batal</a>
                                <button type="submit" class="btn btn-primary btn-modern shadow-sm">
                                    <i class="fa fa-save mr-2"></i> Simpan Data
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Init Select2
    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    // LOGIKA AUTO FILL UNIT KERJA
    $('#pilih_pegawai').on('change', function() {
        // Ambil data-unit dari option yang dipilih
        var unitKerja = $(this).find(':selected').data('unit');
        
        // Jika ada datanya, set value dropdown unit kerja
        if(unitKerja && unitKerja !== '') {
            // Set value lalu trigger change agar Select2 merender ulang
            $('#pilih_unit').val(unitKerja).trigger('change');
        } else {
            // Jika kosong (pegawai belum punya unit), biarkan user memilih manual (opsional: bisa di-reset ke kosong)
            // $('#pilih_unit').val('').trigger('change'); 
        }
    });
});
</script>