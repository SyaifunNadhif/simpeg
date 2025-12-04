<?php
/*********************************************************
 * FILE    : pages/user/form-user.php
 * MODULE  : Form User (Save Name & Created By)
 * VERSION : v3.3
 *********************************************************/

include "dist/koneksi.php";

// --- 1. INISIALISASI MODE ---
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'create';
$id   = isset($_GET['id']) ? $_GET['id'] : '';

$data = [
    'id_user'      => '',
    'nama_user'    => '',
    'password'     => '',
    'hak_akses'    => '',
    'id_pegawai'   => '',
    'jabatan'      => '', 
    'status_aktif' => 'Y'
];

if ($mode == 'edit' && !empty($id)) {
    $q = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user = '$id'");
    if (mysqli_num_rows($q) > 0) {
        $row = mysqli_fetch_assoc($q);
        $data = array_merge($data, $row); 
    }
}

// --- QUERY PENDUKUNG ---

// 1. Ambil Pegawai & NAMA Jabatan Terakhir (Join ke tb_ref_jabatan)
// Kita ambil nama jabatannya langsung (string)
$sqlPegawai = "SELECT p.id_peg, p.nama, 
              (
                SELECT r.jabatan 
                FROM tb_jabatan j
                JOIN tb_ref_jabatan r ON j.kode_jabatan = r.kode_jabatan
                WHERE j.id_peg = p.id_peg 
                ORDER BY j.tmt_jabatan DESC LIMIT 1
              ) as nama_jabatan_terkini
              FROM tb_pegawai p ORDER BY p.nama ASC";
$qPegawai = mysqli_query($conn, $sqlPegawai);

// 2. Ambil Master Jabatan untuk List Dropdown
$qMasterJabatan = mysqli_query($conn, "SELECT kode_jabatan, jabatan FROM tb_ref_jabatan ORDER BY jabatan ASC");
?>

<style>
    .card-modern { border: none; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: #fff; }
    .form-header-modern { background: #007bff; color: #fff; border-bottom: 1px solid #f1f1f1; padding: 20px; border-radius: 16px 16px 0 0; }
    .input-modern { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px; height: 45px; width: 100%; }
    .form-label-modern { font-size: 0.8rem; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 8px; }
    .select2-container .select2-selection--single { height: 45px !important; border-radius: 10px !important; border: 1px solid #e2e8f0 !important; display: flex; align-items: center; }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { line-height: 45px; padding-left: 15px; color: #495057; }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="m-0 font-weight-bold text-dark"><?= ($mode == 'edit') ? 'Edit User' : 'Tambah User' ?></h1>
            <a href="home-admin.php?page=view-data-user" class="btn btn-light rounded-pill border shadow-sm"><i class="fa fa-arrow-left mr-2"></i> Kembali</a>
        </div>
    </div>
</section>

<section class="content mt-3">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-modern">
                    <div class="form-header-modern">
                        <h5 class="m-0 font-weight-bold"><i class="fas <?= ($mode == 'edit') ? 'fa-user-edit' : 'fa-user-plus' ?> mr-2"></i> Form Data User</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="pages/user/proses-user.php" method="POST">
                            <input type="hidden" name="mode" value="<?= $mode ?>">
                            <input type="hidden" name="id_user_lama" value="<?= isset($data['id_user']) ? $data['id_user'] : '' ?>">
                            <input type="hidden" name="id_pegawai" id="id_pegawai_target" value="<?= isset($data['id_pegawai']) ? $data['id_pegawai'] : '' ?>">

                            <div class="alert alert-info border-0 shadow-sm mb-4">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-info"><i class="fas fa-search mr-1"></i> Cari Nama Pegawai (Auto-Fill)</label>
                                    <select id="sumber_pegawai" class="form-control select2-search">
                                        <option value="" data-nama="" data-jabatan="">-- Ketik nama pegawai disini... --</option>
                                        <?php while($p = mysqli_fetch_assoc($qPegawai)) { 
                                            // Ambil NAMA jabatan (String)
                                            $namaJab = isset($p['nama_jabatan_terkini']) ? $p['nama_jabatan_terkini'] : '';
                                            $sel = (isset($data['id_pegawai']) && $data['id_pegawai'] == $p['id_peg']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $p['id_peg'] ?>" 
                                                    data-nama="<?= $p['nama'] ?>" 
                                                    data-jabatan="<?= $namaJab ?>"
                                                    <?= $sel ?>>
                                                <?= $p['nama'] ?> (<?= $p['id_peg'] ?>)
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Username (ID User) <span class="text-danger">*</span></label>
                                        <input type="text" name="id_user" class="form-control input-modern" 
                                               value="<?= isset($data['id_user']) ? htmlspecialchars($data['id_user']) : '' ?>" 
                                               <?= ($mode == 'edit') ? 'readonly' : 'required' ?>>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_user" id="nama_user_target" class="form-control input-modern" 
                                               value="<?= isset($data['nama_user']) ? htmlspecialchars($data['nama_user']) : '' ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Jabatan (Role)</label>
                                        <select name="jabatan" id="jabatan_target" class="form-control select2-search">
                                            <option value="">-- Pilih Jabatan --</option>
                                            <?php while($j = mysqli_fetch_assoc($qMasterJabatan)) { 
                                                // PERBAIKAN: Value menggunakan NAMA JABATAN (String)
                                                $selected = (isset($data['jabatan']) && $data['jabatan'] == $j['jabatan']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $j['jabatan'] ?>" <?= $selected ?>>
                                                    <?= $j['jabatan'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Level Akses <span class="text-danger">*</span></label>
                                        <select name="hak_akses" class="form-control input-modern" required>
                                            <option value="">-- Pilih Level --</option>
                                            <option value="Admin" <?= (isset($data['hak_akses']) && $data['hak_akses'] == 'Admin') ? 'selected' : '' ?>>Admin</option>
                                            <option value="Kepala" <?= (isset($data['hak_akses']) && $data['hak_akses'] == 'Kepala') ? 'selected' : '' ?>>Kepala</option>
                                            <option value="User" <?= (isset($data['hak_akses']) && $data['hak_akses'] == 'User') ? 'selected' : '' ?>>User (Pegawai)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Password <?= ($mode == 'edit') ? '(Opsional)' : '<span class="text-danger">*</span>' ?></label>
                                        <input type="password" name="password" class="form-control input-modern" 
                                               placeholder="<?= ($mode == 'edit') ? 'Kosongkan jika tetap' : 'Password' ?>"
                                               <?= ($mode == 'create') ? 'required' : '' ?>>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group pt-4">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="status_aktif" name="status_aktif" value="Y" 
                                            <?= (isset($data['status_aktif']) && $data['status_aktif'] == 'Y') ? 'checked' : '' ?>>
                                            <label class="custom-control-label font-weight-bold" for="status_aktif">Aktifkan User ini</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-right mt-4 pt-3 border-top">
                                <a href="home-admin.php?page=view-data-user" class="btn btn-light btn-modern mr-2 border">Batal</a>
                                <button type="submit" class="btn btn-primary btn-modern shadow-sm"><i class="fa fa-save mr-2"></i> Simpan Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-search').select2({ theme: 'bootstrap4', width: '100%', placeholder: "Ketik untuk mencari...", allowClear: true });

    // LOGIKA AUTO-FILL
    $('#sumber_pegawai').on('change', function() {
        var selected = $(this).find(':selected');
        var idPeg    = selected.val();
        var nama     = selected.data('nama');
        var namaJab  = selected.data('jabatan'); // Ini sekarang berisi Text (contoh: Kepala Cabang)

        if(idPeg) {
            $('#id_pegawai_target').val(idPeg);
            $('#nama_user_target').val(nama);
            
            // Set Dropdown Jabatan berdasarkan Text
            if(namaJab) {
                $('#jabatan_target').val(namaJab).trigger('change');
            }
        } else {
            $('#id_pegawai_target').val('');
            $('#nama_user_target').val('');
            $('#jabatan_target').val('').trigger('change');
        }
    });
});
</script>