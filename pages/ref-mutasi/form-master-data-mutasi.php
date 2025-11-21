<?php
/*********************************************************
 * FILE    : pages/mutasi/form-master-data-mutasi.php
 * MODULE  : Form Mutasi Pegawai (Fixed Date Picker)
 * VERSION : v2.1
 *********************************************************/

include "dist/koneksi.php";
include "dist/library.php";
?>

<style>
    .card-modern {
        border: none; border-radius: 15px; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.05); 
        background: #fff;
    }
    .form-header-modern {
        background: #f8f9fa; border-bottom: 1px solid #f1f1f1;
        padding: 20px; border-radius: 15px 15px 0 0;
    }
    .input-modern {
        border-radius: 10px; border: 1px solid #e2e8f0;
        padding: 10px 15px; height: 45px; font-size: 0.95rem;
        width: 100%;
        color: #495057;
    }
    .input-modern:focus { border-color: #007bff; box-shadow: 0 0 0 3px rgba(0,123,255,0.1); outline: none; }
    
    /* Fix tampilan input date di browser webkit */
    input[type="date"] {
        position: relative;
    }
    input[type="date"]::-webkit-calendar-picker-indicator {
        background: transparent;
        bottom: 0; color: transparent; cursor: pointer;
        height: auto; left: 0; position: absolute; right: 0; top: 0; width: auto;
    }
    /* Icon kalender custom di kanan */
    .date-wrapper { position: relative; }
    .date-icon {
        position: absolute; right: 15px; top: 12px; pointer-events: none; color: #6c757d;
    }

    .btn-modern {
        border-radius: 50px; padding: 10px 30px; font-weight: 600; transition: 0.3s;
    }
    .btn-modern:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.8rem;">Entry Mutasi</h1>
                <p class="text-muted mb-0">Input data mutasi kepegawaian baru</p>
            </div>
            <div>
                <ol class="breadcrumb bg-transparent p-0 m-0 small">
                    <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="home-admin.php?page=form-view-data-mutasi">Data Mutasi</a></li>
                    <li class="breadcrumb-item active">Entry</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content mt-3">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                
                <div class="card card-modern">
                    <div class="form-header-modern">
                        <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-edit mr-2"></i> Form Data Mutasi</h5>
                    </div>
                    
                    <div class="card-body p-4">
                        <form role="form" id="formMutasi" action="home-admin.php?page=master-data-mutasi" method="POST" enctype="multipart/form-data">
                            
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-muted small text-uppercase mb-2">Pilih Pegawai <span class="text-danger">*</span></label>
                                <select name="id_peg" id="id_peg" class="form-control select2bs4 input-modern" required style="width: 100%;">
                                    <option value="">-- Cari Nama / NIP Pegawai --</option>
                                    <?php
                                    $data = mysqli_query($conn, "SELECT id_peg, nama FROM tb_pegawai ORDER BY nama ASC");
                                    while ($row = mysqli_fetch_array($data)) {
                                        echo '<option value="'.$row['id_peg'].'">'.$row['nama'].' ('.$row['id_peg'].')</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-2">Jenis Mutasi <span class="text-danger">*</span></label>
                                        <select name="jns_mutasi" id="jns_mutasi" class="form-control select2bs4 input-modern" required>
                                            <option value="">-- Pilih Jenis --</option>
                                            <option value="Pensiun">Pensiun</option>
                                            <option value="Pensiun Dini">Pensiun Dini</option>
                                            <option value="Meninggal Dunia">Meninggal Dunia</option>
                                            <option value="Pengunduran Diri">Pengunduran Diri</option>
                                            <option value="PTDH">PTDH</option>
                                            <option value="Mutasi Jabatan">Mutasi Jabatan</option>
                                            <option value="Mutasi Unit Kerja">Mutasi Unit Kerja</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-2">Nomor SK <span class="text-danger">*</span></label>
                                        <input type="text" name="no_mutasi" class="form-control input-modern" placeholder="Contoh: 800/SK-01/2025" required style="text-transform:uppercase">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-2">Tanggal SK <span class="text-danger">*</span></label>
                                        <div class="date-wrapper">
                                            <input type="date" name="tgl_mutasi" class="form-control input-modern" required>
                                            <i class="fa fa-calendar date-icon"></i>
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">*Klik untuk pilih tanggal</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-2">TMT (Terhitung Mulai Tanggal) <span class="text-danger">*</span></label>
                                        <div class="date-wrapper">
                                            <input type="date" name="tmt" class="form-control input-modern" required>
                                            <i class="fa fa-calendar-check date-icon text-success"></i>
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">*Klik untuk pilih tanggal</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-muted small text-uppercase mb-2">Upload SK Mutasi (PDF/JPG)</label>
                                <div class="custom-file">
                                    <input type="file" name="sk_mutasi" class="custom-file-input" id="sk_mutasi">
                                    <label class="custom-file-label input-modern pt-2" for="sk_mutasi">Pilih file...</label>
                                </div>
                                <small class="text-muted ml-2">Maksimal ukuran file 2MB.</small>
                            </div>

                            <div class="form-group text-right mt-4 border-top pt-4">
                                <a href="home-admin.php?page=form-view-data-mutasi" class="btn btn-light btn-modern mr-2 text-muted border">Batal</a>
                                <button type="submit" name="save" value="save" class="btn btn-primary btn-modern shadow-sm">
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

<script>
  $(function () {
    // Initialize Select2
    $('.select2bs4').select2({
      theme: 'bootstrap4',
      placeholder: "Pilih opsi...",
      allowClear: true
    });

    // Input File Label Change (Agar nama file muncul setelah dipilih)
    $(".custom-file-input").on("change", function() {
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
  });
</script>