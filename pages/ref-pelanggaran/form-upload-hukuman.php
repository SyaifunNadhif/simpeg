<?php
/*********************************************************
 * FILE    : pages/pelanggaran/form-upload-hukuman.php
 * MODULE  : Form Upload Kolektif (Modern UI)
 * VERSION : v1.0
 *********************************************************/
?>

<style>
    .card-modern { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: #fff; }
    .form-header-modern { background: #dc3545; color: #fff; border-bottom: 1px solid #f1f1f1; padding: 20px; border-radius: 15px 15px 0 0; }
    .input-modern { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px; height: 45px; width: 100%; }
    .btn-modern { border-radius: 50px; padding: 10px 30px; font-weight: 600; transition: 0.3s; }
    .btn-modern:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .alert-info-modern { background-color: #e3f2fd; border-left: 5px solid #2196f3; color: #0d47a1; border-radius: 8px; }
</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark">Upload Kolektif</h1>
                <p class="text-muted mb-0">Import data pelanggaran dari Excel (CSV)</p>
            </div>
            <div>
                <a href="home-admin.php?page=form-view-data-hukuman" class="btn btn-light rounded-pill border">Kembali</a>
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
                        <h5 class="m-0 font-weight-bold"><i class="fas fa-file-upload mr-2"></i> Upload Data Pelanggaran</h5>
                    </div>
                    
                    <div class="card-body p-4">
                        
                        <div class="alert alert-info-modern p-3 mb-4">
                            <h5><i class="icon fas fa-info-circle"></i> Petunjuk:</h5>
                            <ol class="pl-3 mb-2">
                                <li>Siapkan data menggunakan <b>Microsoft Excel</b>.</li>
                                <li>Susunan kolom: <b>ID Pegawai, Jenis Sanksi, Keterangan, Pejabat SK, Jabatan SK, No SK, Tgl SK (YYYY-MM-DD)</b>.</li>
                                <li>Simpan file (Save As) dengan format <b>CSV (Comma delimited) (*.csv)</b>.</li>
                            </ol>
                            <a href="pages/pelanggaran/template_pelanggaran.csv" class="btn btn-sm btn-primary mt-2" download>
                                <i class="fa fa-download mr-1"></i> Download Template CSV
                            </a>
                        </div>

                        <form action="home-admin.php?page=proses-upload-hukuman" method="POST" enctype="multipart/form-data">
                            
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-muted small text-uppercase mb-2">Pilih File CSV</label>
                                <div class="custom-file">
                                    <input type="file" name="file_csv" class="custom-file-input" id="file_csv" required accept=".csv">
                                    <label class="custom-file-label input-modern pt-2" for="file_csv">Pilih file .csv...</label>
                                </div>
                            </div>

                            <div class="form-group text-right mt-4 border-top pt-4">
                                <button type="submit" name="upload" class="btn btn-danger btn-modern shadow-sm">
                                    <i class="fa fa-cloud-upload-alt mr-2"></i> Import Data
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
  document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    var fileName = document.getElementById("file_csv").files[0].name;
    var nextSibling = e.target.nextElementSibling;
    nextSibling.innerText = fileName;
  });
</script>