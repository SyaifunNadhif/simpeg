<?php
/*********************************************************
 * FILE    : pages/diklat/form-diklat.php
 * MODULE  : Form Diklat (Locked Edit, Compact UI, Auto Rupiah)
 *********************************************************/

if (session_id() == '') session_start();
include 'dist/koneksi.php';
include 'dist/library.php';

// --- 1. INISIALISASI DATA ---
$hak_akses   = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$kode_kantor = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
$isEdit = ($id != '');

// Data Default
$data = [
    "id_peg" => "", "diklat" => "", "penyelenggara" => "", 
    "tempat" => "", "biaya" => "", "angkatan" => "", 
    "tahun" => date('Y'), "date_reg" => date('Y-m-d')
];

// Jika Edit, Ambil Data
if ($isEdit) {
    $q = mysqli_query($conn, "SELECT * FROM tb_diklat WHERE id_diklat = '$id'");
    if ($q && mysqli_num_rows($q) > 0) {
        $data = mysqli_fetch_assoc($q);
    } else {
        echo "<script>window.location='home-admin.php?page=master-data-diklat';</script>";
        exit;
    }
}

// Filter Pegawai
$where_pegawai = "WHERE 1=1";
if ($hak_akses !== 'admin') {
    $where_pegawai .= " AND id_peg IN (SELECT id_peg FROM tb_jabatan WHERE unit_kerja = '$kode_kantor' AND status_jab = 'Aktif')";
}
$qPegawai = mysqli_query($conn, "SELECT id_peg, nama FROM tb_pegawai $where_pegawai ORDER BY nama ASC");
?>

<style>
    /* STYLE COMPACT & MODERN */
    .card-ref {
        border: none;
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        background: #fff;
        overflow: hidden;
    }
    
    /* Header Biru */
    .card-header-ref {
        background-color: #0088ff;
        color: #fff;
        padding: 12px 20px; /* Padding diperkecil */
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    /* Box Info Pegawai (Lebih Ramping) */
    .info-box-pegawai {
        background-color: #e0f7fa;
        border: 1px solid #b2ebf2;
        border-radius: 4px;
        padding: 10px 15px; /* Lebih rapat */
        color: #006064;
        margin-bottom: 15px;
    }

    /* Label & Input Compact */
    .label-ref {
        font-weight: 700;
        font-size: 0.8rem; /* Font agak kecil */
        color: #333;
        margin-bottom: 4px; /* Jarak label ke input didempetin */
    }
    .form-control-ref {
        border-radius: 4px;
        border: 1px solid #ced4da;
        height: 38px; /* Tinggi input dikurangi sedikit */
        font-size: 0.9rem;
        padding: 5px 10px;
    }
    .form-control-ref:focus {
        border-color: #0088ff;
        box-shadow: 0 0 0 0.2rem rgba(0,136,255,.25);
    }
    
    /* Jarak Antar Form Group Dikurangi */
    .form-group-compact {
        margin-bottom: 12px; /* Jarak antar baris diperkecil dari mb-3 (16px) jadi 12px */
    }

    /* Select2 Disabled Style */
    .select2-container--bootstrap4.select2-container--disabled .select2-selection--single {
        background-color: #e9ecef !important;
        border-color: #ced4da !important;
        color: #6c757d !important;
        cursor: not-allowed;
    }
    
    /* Tombol */
    .btn-update { background-color: #007bff; border-color: #007bff; color: white; font-weight: 600; padding: 8px 25px; border-radius: 4px; }
    .btn-update:hover { background-color: #0069d9; color: white; }
    .btn-kembali { background-color: #fff; border: 1px solid #ddd; color: #555; font-weight: 600; padding: 8px 20px; border-radius: 4px; }
    .btn-kembali:hover { background-color: #f8f9fa; }
</style>

<section class="content pt-3">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-11">
                
                <div class="card card-ref">
                    <div class="card-header-ref">
                        <h5 class="font-weight-bold m-0" style="font-size: 1.1rem;">
                            <?= $isEdit ? 'Edit Data Pendidikan' : 'Input Data Pendidikan' ?>
                        </h5>
                        <p class="m-0 small" style="opacity: 0.9; font-size: 0.8rem;">Lengkapi data pendidikan/diklat pegawai</p>
                    </div>

                    <div class="card-body p-3"> <form method="POST" action="home-admin.php?page=proses-diklat" onsubmit="return cleanRupiah()">
                            <input type="hidden" name="id_diklat" value="<?= $id ?>">

                            <div class="info-box-pegawai">
                                <div class="form-group mb-0">
                                    <label class="mb-1 small font-weight-bold">Pegawai:</label>
                                    
                                    <?php if($isEdit): ?>
                                        <input type="hidden" name="id_peg" value="<?= $data['id_peg'] ?>">
                                    <?php endif; ?>

                                    <select name="id_peg" class="form-control select2bs4" <?= $isEdit ? 'disabled' : 'required' ?>>
                                        <option value="">-- Pilih Pegawai --</option>
                                        <?php while ($p = mysqli_fetch_assoc($qPegawai)) { ?>
                                            <option value="<?= $p['id_peg'] ?>" <?= $data['id_peg'] == $p['id_peg'] ? 'selected' : '' ?>>
                                                <?= $p['nama'] ?> — ID: <?= $p['id_peg'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <?php if($isEdit): ?>
                                        <small class="text-muted font-italic mt-1 d-block"><i class="fa fa-lock mr-1"></i>Pegawai tidak dapat diubah saat edit.</small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-group-compact">
                                <label class="label-ref">Nama Diklat / Pelatihan <span class="text-danger">*</span></label>
                                <input type="text" name="diklat" class="form-control form-control-ref" value="<?= htmlspecialchars($data['diklat']) ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group-compact">
                                    <label class="label-ref">Penyelenggara</label>
                                    <input type="text" name="penyelenggara" class="form-control form-control-ref" value="<?= htmlspecialchars($data['penyelenggara']) ?>">
                                </div>
                                <div class="col-md-6 form-group-compact">
                                    <label class="label-ref">Lokasi / Tempat</label>
                                    <input type="text" name="tempat" class="form-control form-control-ref" value="<?= htmlspecialchars($data['tempat']) ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 form-group-compact">
                                    <label class="label-ref">Tahun <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun" class="form-control form-control-ref" value="<?= $data['tahun'] ?>" required>
                                </div>
                                <div class="col-md-3 form-group-compact">
                                    <label class="label-ref">Angkatan</label>
                                    <input type="text" name="angkatan" class="form-control form-control-ref" value="<?= htmlspecialchars($data['angkatan']) ?>" placeholder="Cth: X">
                                </div>
                                <div class="col-md-6 form-group-compact">
                                    <label class="label-ref">Biaya (Rp)</label>
                                    <input type="text" name="biaya" id="inputBiaya" class="form-control form-control-ref" 
                                           value="<?= $data['biaya'] ?>" placeholder="Input nominal" autocomplete="off">
                                    <small class="text-muted" style="font-size: 0.75rem;">Otomatis format rupiah (contoh: 5.000.000)</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 form-group-compact">
                                    <label class="label-ref">Tanggal Pelatihan</label>
                                    <input type="date" name="date_reg" class="form-control form-control-ref" value="<?= $data['date_reg'] ?>">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <a href="home-admin.php?page=master-data-diklat" class="btn btn-kembali">
                                    Kembali
                                </a>
                                <button type="submit" name="<?= $isEdit ? 'update' : 'simpan' ?>" class="btn btn-update">
                                    <?= $isEdit ? 'Update' : 'Simpan' ?>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // 1. Init Select2
    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    // 2. Format Rupiah Logic
    var inputBiaya = document.getElementById('inputBiaya');
    
    // Format saat load (edit mode)
    if(inputBiaya.value !== ""){
        inputBiaya.value = formatRupiah(inputBiaya.value, '');
    }

    // Format saat ketik
    inputBiaya.addEventListener('keyup', function(e){
        inputBiaya.value = formatRupiah(this.value, '');
    });

    function formatRupiah(angka, prefix){
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? rupiah : '');
    }
});

// 3. Bersihkan titik saat submit
function cleanRupiah() {
    var inputBiaya = document.getElementById('inputBiaya');
    var bersih = inputBiaya.value.replace(/\./g, ''); 
    inputBiaya.value = bersih;
    return true;
}
</script>