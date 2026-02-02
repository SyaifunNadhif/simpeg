<?php
/*********************************************************
 * FILE    : pages/ref-pelanggaran/form-master-data-hukuman.php
 * MODULE  : Form Master Pelanggaran (AJAX Submit Mode)
 * VERSION : v10.0 Final
 *********************************************************/

include "dist/koneksi.php";
include "dist/library.php";

// --- 1. DETEKSI MODE ---
$mode = "Tambah";
$id_hukum = "";
$data_edit = [];

if (isset($_GET['id_hukum'])) {
    $mode = "Edit";
    $id_hukum = mysqli_real_escape_string($conn, $_GET['id_hukum']);
    $qryEdit = mysqli_query($conn, "SELECT * FROM tb_hukuman WHERE id_hukum='$id_hukum'");
    if (mysqli_num_rows($qryEdit) > 0) {
        $data_edit = mysqli_fetch_array($qryEdit);
    } else {
        echo "<script>alert('Data tidak ditemukan!');window.location='home-admin.php?page=form-view-data-pelanggaran';</script>";
        exit;
    }
}

// --- 2. AMBIL DATA PEJABAT (GROUP 'PE') ---
// --- 2. AMBIL DATA PEJABAT KHUSUS (Direktur Ops & Kadiv SDM) ---
$listPejabat = [];

// Array Jabatan yang Diizinkan (Bisa disesuaikan text-nya dengan database kamu)
$allowed_jabatan = "('Direktur Operasional', 'Kepala Divisi SDM dan Umum')";

$qPejabat = mysqli_query($conn, "
    SELECT p.nama, j.jabatan 
    FROM tb_pegawai p
    JOIN tb_jabatan j ON p.id_peg = j.id_peg 
    
    -- Filter hanya jabatan yang aktif & sesuai nama jabatan
    WHERE j.status_jab = 'Aktif' 
    AND j.jabatan IN $allowed_jabatan
    
    ORDER BY j.jabatan ASC, p.nama ASC
");

while ($r = mysqli_fetch_assoc($qPejabat)) { 
    $listPejabat[] = $r; 
}

// --- 3. SET VALUE DEFAULT ---
$id_peg        = isset($data_edit['id_peg']) ? $data_edit['id_peg'] : '';
$hukuman       = isset($data_edit['hukuman']) ? $data_edit['hukuman'] : '';
$keterangan    = isset($data_edit['keterangan']) ? $data_edit['keterangan'] : '';
$pejabat_sk    = isset($data_edit['pejabat_sk']) ? $data_edit['pejabat_sk'] : '';
$jabatan_sk    = isset($data_edit['jabatan_sk']) ? $data_edit['jabatan_sk'] : '';
$no_sk         = isset($data_edit['no_sk']) ? $data_edit['no_sk'] : '';
$tgl_sk        = isset($data_edit['tgl_sk']) ? $data_edit['tgl_sk'] : date('Y-m-d');
$dokumen_lama  = isset($data_edit['dokumen']) ? $data_edit['dokumen'] : '';
$pejabat_pulih = isset($data_edit['pejabat_pulih']) ? $data_edit['pejabat_pulih'] : '';
$jabatan_pulih = isset($data_edit['jabatan_pulih']) ? $data_edit['jabatan_pulih'] : '';
$no_pulih      = isset($data_edit['no_pulih']) ? $data_edit['no_pulih'] : '';
$tgl_pulih     = (isset($data_edit['tgl_pulih']) && $data_edit['tgl_pulih'] != '0000-00-00') ? $data_edit['tgl_pulih'] : '';
?>

<style>
    .card-modern { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: #fff; overflow: hidden; }
    .form-header-modern { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 20px 30px; border-bottom: 1px solid #f1f1f1; color: white; }
    .input-modern { border-radius: 8px; border: 1px solid #e2e8f0; padding: 10px 15px; height: 45px; font-size: 0.95rem; width: 100%; color: #495057; background-color: #f8fafc; transition: all 0.3s; }
    .input-modern:focus { border-color: #f59e0b; background-color: #fff; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); outline: none; }
    .btn-modern { border-radius: 50px; padding: 12px 30px; font-weight: 700; transition: 0.3s; letter-spacing: 0.5px; }
    .btn-save { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none; color: white; box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3); }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(217, 119, 6, 0.4); color: white; }
    .btn-cancel { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
    .btn-cancel:hover { background: #e2e8f0; color: #334155; }
    .select2-container--bootstrap4 .select2-selection--single { height: 45px !important; line-height: 45px !important; border-radius: 8px !important; border: 1px solid #e2e8f0 !important; background-color: #f8fafc !important; }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { line-height: 45px !important; padding-left: 15px !important; }
    label { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px; }
    .section-title { font-size: 0.9rem; font-weight: 800; color: #d97706; text-transform: uppercase; border-bottom: 2px solid #fef3c7; padding-bottom: 10px; margin-bottom: 20px; margin-top: 30px; }
</style>

<section class="content p-4">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-12">
                <div class="card card-modern">
                    <div class="form-header-modern">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 bg-white text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-gavel fa-lg"></i>
                            </div>
                            <div>
                                <h4 class="m-0 font-weight-bold"><?= $mode ?> Data Pelanggaran</h4>
                                <small style="opacity: 0.9;">Form pencatatan hukuman disiplin pegawai.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-5">
                        <form id="formHukuman" enctype="multipart/form-data">
                            
                            <input type="hidden" name="act" value="<?= ($mode == 'Edit') ? 'update' : 'insert' ?>">
                            
                            <?php if($mode == 'Edit'): ?>
                                <input type="hidden" name="id_hukum" value="<?= $id_hukum ?>">
                                <input type="hidden" name="dokumen_lama" value="<?= $dokumen_lama ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label>Pilih Pegawai <span class="text-danger">*</span></label>
                                    <select name="id_peg" id="id_peg" class="form-control select2bs4" required style="width: 100%;">
                                        <option value="">-- Cari Nama / NIP Pegawai --</option>
                                        <?php
                                        $sqlPeg = "SELECT id_peg, nama FROM tb_pegawai ORDER BY nama ASC";
                                        $qPeg = mysqli_query($conn, $sqlPeg);
                                        while ($p = mysqli_fetch_array($qPeg)) {
                                            $sel = ($id_peg == $p['id_peg']) ? 'selected' : '';
                                            echo '<option value="'.$p['id_peg'].'" '.$sel.'>'.$p['nama'].' ('.$p['id_peg'].')</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label>Jenis Sanksi <span class="text-danger">*</span></label>
                                    <select name="hukuman" class="form-control select2bs4" required>
                                        <option value="">-- Pilih Jenis --</option>
                                        <?php 
                                        $opsi = ["Surat Peringatan I", "Surat Peringatan II", "Surat Peringatan III", "Skorsing", "PTDH", "Teguran Lisan", "Teguran Tertulis"];
                                        foreach($opsi as $op){
                                            $sel = ($hukuman == $op) ? 'selected' : '';
                                            echo "<option value='$op' $sel>$op</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label>Keterangan</label>
                                    <input type="text" name="keterangan" class="form-control input-modern" value="<?= $keterangan ?>" placeholder="Deskripsi singkat...">
                                </div>
                            </div>

                            <h6 class="section-title"><i class="fas fa-file-contract mr-2"></i> Detail SK Hukuman</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Nama Pejabat Pengesah <span class="text-danger">*</span></label>
                                    <select name="pejabat_sk" id="pejabat_sk" class="form-control select2bs4" required>
                                        <option value="">-- Pilih Pejabat --</option>
                                        <?php if($mode=='Edit' && !empty($pejabat_sk)): ?>
                                            <option value="<?= $pejabat_sk ?>" data-jabatan="<?= $jabatan_sk ?>" selected><?= $pejabat_sk ?></option>
                                        <?php endif; ?>
                                        <?php foreach($listPejabat as $pjb): ?>
                                            <option value="<?= $pjb['nama'] ?>" data-jabatan="<?= $pjb['jabatan'] ?>"><?= $pjb['nama'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Jabatan Pengesah <span class="text-danger">*</span></label>
                                    <input type="text" name="jabatan_sk" id="jabatan_sk" class="form-control input-modern bg-light" value="<?= $jabatan_sk ?>" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Nomor SK</label>
                                    <input type="text" name="no_sk" class="form-control input-modern" value="<?= $no_sk ?>" required style="text-transform:uppercase">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Tanggal SK</label>
                                    <div class="date-wrapper">
                                        <input type="date" name="tgl_sk" class="form-control input-modern" value="<?= $tgl_sk ?>" required>
                                        <i class="fa fa-calendar date-icon"></i>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Upload Dokumen (PDF/JPG)</label>
                                    <div class="custom-file">
                                        <input type="file" name="dokumen" class="custom-file-input" id="dokumen">
                                        <label class="custom-file-label input-modern pt-2" for="dokumen" style="overflow: hidden;">
                                            <?= ($dokumen_lama) ? substr($dokumen_lama, 0, 20).'...' : 'Pilih file...' ?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="section-title text-secondary border-secondary"><i class="fas fa-undo mr-2"></i> Detail Pemulihan (Opsional)</h6>
                            <div class="p-4 rounded" style="background-color: #f8fafc; border: 1px dashed #cbd5e1;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Nama Pejabat Pemulih</label>
                                        <select name="pejabat_pulih" id="pejabat_pulih" class="form-control select2bs4">
                                            <option value="">-- Pilih Pejabat --</option>
                                            <?php if($mode=='Edit' && !empty($pejabat_pulih)): ?>
                                                <option value="<?= $pejabat_pulih ?>" data-jabatan="<?= $jabatan_pulih ?>" selected><?= $pejabat_pulih ?></option>
                                            <?php endif; ?>
                                            <?php foreach($listPejabat as $pjb): ?>
                                                <option value="<?= $pjb['nama'] ?>" data-jabatan="<?= $pjb['jabatan'] ?>"><?= $pjb['nama'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Jabatan Pemulih</label>
                                        <input type="text" name="jabatan_pulih" id="jabatan_pulih" class="form-control input-modern bg-light" value="<?= $jabatan_pulih ?>" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>No. SK Pemulihan</label>
                                        <input type="text" name="no_pulih" class="form-control input-modern" value="<?= $no_pulih ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Tanggal Pemulihan</label>
                                        <div class="date-wrapper">
                                            <input type="date" name="tgl_pulih" class="form-control input-modern" value="<?= $tgl_pulih ?>">
                                            <i class="fa fa-calendar date-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-right mt-5">
                                <a href="home-admin.php?page=form-view-data-pelanggaran" class="btn btn-modern btn-cancel mr-2">Batal</a>
                                <button type="button" id="btnSimpan" class="btn btn-modern btn-save">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    $('.select2bs4').select2({ theme: 'bootstrap4', placeholder: "Pilih...", allowClear: true });
    
    // Auto Fill Jabatan
    $('#pejabat_sk').on('select2:select', function (e) { $('#jabatan_sk').val($(this).find(':selected').data('jabatan')); });
    $('#pejabat_pulih').on('select2:select', function (e) { $('#jabatan_pulih').val($(this).find(':selected').data('jabatan')); });
    $(".custom-file-input").on("change", function() { $(this).siblings(".custom-file-label").addClass("selected").html($(this).val().split("\\").pop()); });

    // --- LOGIC AJAX SUBMIT (MODAL SUKSES) ---
    $('#btnSimpan').click(function(e) {
        e.preventDefault();
        
        // Validasi Sederhana via JS
        if($('#id_peg').val() == '' || $('#hukuman').val() == '' || $('input[name="no_sk"]').val() == '') {
            Swal.fire('Peringatan', 'Harap lengkapi data wajib (Pegawai, Jenis, No SK)!', 'warning');
            return;
        }

        var formData = new FormData($('#formHukuman')[0]);
        
        // Tampilkan Loading
        Swal.fire({ title: 'Menyimpan...', text: 'Mohon tunggu sebentar', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });

        $.ajax({
            url: 'pages/ref-pelanggaran/proses-master-data-hukuman.php', // FILE BACKEND JSON
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'home-admin.php?page=form-view-data-pelanggaran';
                    });
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                Swal.fire('Error System!', 'Terjadi kesalahan pada server. Cek console.', 'error');
            }
        });
    });
});
</script>