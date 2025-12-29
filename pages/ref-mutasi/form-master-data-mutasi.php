<?php
/*********************************************************
 * FILE    : pages/ref-mutasi/form-master-data-mutasi.php
 * MODULE  : Form Pemberhentian (Compact 3-Grid Layout)
 * LOGIC   : Auto Detect Mode (Insert/Edit)
 *********************************************************/

include "dist/koneksi.php";
include "dist/library.php";

// --- 1. DETEKSI MODE ---
$mode = "Tambah";
$id_mutasi = "";
$data_edit = [];

if (isset($_GET['id_mutasi'])) {
    $mode = "Edit";
    $id_mutasi = mysqli_real_escape_string($conn, $_GET['id_mutasi']);
    
    $qryEdit = mysqli_query($conn, "SELECT * FROM tb_mutasi WHERE id_mutasi='$id_mutasi'");
    if (mysqli_num_rows($qryEdit) > 0) {
        $data_edit = mysqli_fetch_array($qryEdit);
    } else {
        echo "<script>alert('Data tidak ditemukan!');window.location='home-admin.php?page=form-view-data-mutasi';</script>";
    }
}

// --- 2. SET VALUE DEFAULT ---
$id_peg     = isset($data_edit['id_peg']) ? $data_edit['id_peg'] : '';
$jns_mutasi = isset($data_edit['jns_mutasi']) ? $data_edit['jns_mutasi'] : '';
$no_mutasi  = isset($data_edit['no_mutasi']) ? $data_edit['no_mutasi'] : '';
$tgl_mutasi = isset($data_edit['tgl_mutasi']) ? $data_edit['tgl_mutasi'] : date('Y-m-d');
$tmt        = isset($data_edit['tmt']) ? $data_edit['tmt'] : date('Y-m-d');
$sk_lama    = isset($data_edit['sk_mutasi']) ? $data_edit['sk_mutasi'] : '';
?>

<style>
    /* Styling Kompak */
    .card-modern { border: none; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); background: #fff; overflow: hidden; }
    .form-header-modern { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); padding: 15px 25px; border-bottom: 1px solid #f1f1f1; color: white; }
    
    /* Input dibuat lebih compact tingginya (42px) biar hemat tempat */
    .input-modern { border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px 12px; height: 42px; font-size: 0.9rem; width: 100%; color: #495057; background-color: #f8fafc; transition: all 0.3s; }
    .input-modern:focus { border-color: #ef4444; background-color: #fff; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1); outline: none; }
    
    .date-wrapper { position: relative; }
    .date-icon { position: absolute; right: 12px; top: 12px; pointer-events: none; color: #a0aec0; font-size: 0.9rem; }

    .btn-modern { border-radius: 50px; padding: 10px 25px; font-weight: 700; font-size: 0.9rem; transition: 0.3s; width: 100%; } /* Tombol Full Width di kolomnya */
    .btn-save { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); border: none; color: white; box-shadow: 0 4px 10px rgba(185, 28, 28, 0.3); }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(185, 28, 28, 0.4); color: white; }
    .btn-cancel { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
    .btn-cancel:hover { background: #e2e8f0; color: #475569; }

    .select2-container--bootstrap4 .select2-selection--single { height: 42px !important; line-height: 42px !important; border-radius: 8px !important; border: 1px solid #e2e8f0 !important; background-color: #f8fafc !important; }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { line-height: 42px !important; padding-left: 12px !important; font-size: 0.9rem; }
    
    label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 5px; }
</style>

<section class="content pt-3 px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                
                <div class="card card-modern">
                    <div class="form-header-modern d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-times mr-2" style="font-size: 1.2rem;"></i>
                            <h5 class="m-0 font-weight-bold" style="font-size: 1rem;"><?= $mode ?> Data Pemberhentian</h5>
                        </div>
                        <small class="text-white-50"><i>*Semua kolom wajib diisi</i></small>
                    </div>
                    
                    <div class="card-body p-4">
                        <form role="form" id="formMutasi" action="pages/ref-mutasi/proses-master-data-mutasi.php?act=<?= ($mode == 'Edit') ? 'update' : 'insert' ?>" method="POST" enctype="multipart/form-data">
                            
                            <?php if($mode == 'Edit'): ?>
                                <input type="hidden" name="id_mutasi" value="<?= $id_mutasi ?>">
                                <input type="hidden" name="sk_lama" value="<?= $sk_lama ?>">
                            <?php endif; ?>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label>Pilih Pegawai <span class="text-danger">*</span></label>
                                    <select name="id_peg" id="id_peg" class="form-control select2bs4" required>
                                        <option value="">-- Cari Nama / NIP Pegawai --</option>
                                        <?php
                                        $filter = ($mode == 'Tambah') ? "WHERE status_aktif='1'" : ""; 
                                        $sqlPeg = "SELECT id_peg, nama FROM tb_pegawai $filter ORDER BY nama ASC";
                                        $qPeg = mysqli_query($conn, $sqlPeg);
                                        while ($p = mysqli_fetch_array($qPeg)) {
                                            $selected = ($id_peg == $p['id_peg']) ? 'selected' : '';
                                            echo '<option value="'.$p['id_peg'].'" '.$selected.'>'.$p['nama'].' ('.$p['id_peg'].')</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label>Jenis Pemberhentian <span class="text-danger">*</span></label>
                                        <select name="jns_mutasi" class="form-control select2bs4" required>
                                            <option value="">-- Pilih --</option>
                                            <?php 
                                            $jenis = ['Pensiun', 'Pensiun Dini', 'Meninggal Dunia', 'Pengunduran Diri', 'PTDH', 'Pemberhentian Lainnya'];
                                            foreach($jenis as $j){
                                                $selJ = ($jns_mutasi == $j) ? 'selected' : '';
                                                echo "<option value='$j' $selJ>$j</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label>Nomor SK <span class="text-danger">*</span></label>
                                        <input type="text" name="no_mutasi" class="form-control input-modern" value="<?= $no_mutasi ?>" placeholder="No SK..." required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label>Tanggal SK <span class="text-danger">*</span></label>
                                        <div class="date-wrapper">
                                            <input type="date" name="tgl_mutasi" class="form-control input-modern" value="<?= $tgl_mutasi ?>" required>
                                            <i class="fa fa-calendar date-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-end">
                                <div class="col-md-4 mb-2">
                                    <label>TMT (Efektif Non-Aktif) <span class="text-danger">*</span></label>
                                    <div class="date-wrapper">
                                        <input type="date" name="tmt" class="form-control input-modern" value="<?= $tmt ?>" required>
                                        <i class="fa fa-calendar-check date-icon text-danger"></i>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-2">
                                    <label>Upload SK (PDF/JPG)</label>
                                    <div class="custom-file" style="height: 42px;">
                                        <input type="file" name="sk_mutasi" class="custom-file-input" id="sk_mutasi" style="height: 42px;">
                                        <label class="custom-file-label input-modern pt-2" for="sk_mutasi" style="height: 42px; overflow: hidden;">
                                            <?= ($sk_lama) ? substr($sk_lama, 0, 20).'...' : 'Pilih file...' ?>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <div class="d-flex" style="gap: 10px;">
                                        <a href="home-admin.php?page=form-view-data-mutasi" class="btn btn-modern btn-cancel" style="width: 40%;">Batal</a>
                                        <button type="submit" class="btn btn-modern btn-save" style="width: 60%;">
                                            <i class="fa fa-save mr-1"></i> Simpan
                                        </button>
                                    </div>
                                </div>
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

<script>
  $(function () {
    $('.select2bs4').select2({ theme: 'bootstrap4', placeholder: "Pilih...", allowClear: true });
    $(".custom-file-input").on("change", function() {
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
  });
</script>