<?php
/*********************************************************
 * FILE    : pages/diklat/form-import-diklat.php
 * MODULE  : Form Import Diklat (Modern UI)
 * VERSION : v1.0
 *********************************************************/
?>

<style>
    .card-modern { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: #fff; }
    .form-header-modern { background: #17a2b8; color: #fff; border-bottom: 1px solid #f1f1f1; padding: 20px; border-radius: 15px 15px 0 0; }
    .input-modern { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px; height: 45px; width: 100%; }
    .btn-modern { border-radius: 50px; padding: 10px 30px; font-weight: 600; transition: 0.3s; }
    .btn-modern:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .alert-info-modern { background-color: #e3f2fd; border-left: 5px solid #17a2b8; color: #0c5460; border-radius: 8px; }
</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark">Import Data Diklat</h1>
                <p class="text-muted mb-0">Upload data pelatihan pegawai secara kolektif</p>
            </div>
            <div>
                <a href="home-admin.php?page=master-data-diklat" class="btn btn-light rounded-pill border shadow-sm">Kembali</a>
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
                        <h5 class="m-0 font-weight-bold"><i class="fas fa-file-excel mr-2"></i> Upload File CSV</h5>
                    </div>
                    
                    <div class="card-body p-4">
                        
                        <div class="alert alert-info-modern p-3 mb-4">
                            <h5><i class="icon fas fa-info-circle"></i> Petunjuk Import:</h5>
                            <ol class="pl-3 mb-2">
                                <li>Siapkan data menggunakan <b>Microsoft Excel</b>.</li>
                                <li>Susunan Kolom (Header):<br>
                                    <code>NIP, NAMA_DIKLAT, PENYELENGGARA, TEMPAT, ANGKATAN, TAHUN, TGL_INPUT</code>
                                </li>
                                <li>Format Tanggal: <b>YYYY-MM-DD</b> (Contoh: 2025-11-24).</li>
                                <li>Simpan file sebagai <b>CSV (Comma delimited) (*.csv)</b>.</li>
                            </ol>
                            
                            <?php
                                // Generate Template CSV Otomatis
                                $csv_header = "NIP;NAMA_DIKLAT;PENYELENGGARA;TEMPAT;ANGKATAN;TAHUN;TGL_INPUT";
                                $csv_sample = "199001012022031001;Pelatihan Kepemimpinan;BPSDM;Jakarta;Angkatan V;2025;2025-11-24";
                                $csv_content = "data:text/csv;charset=utf-8,%EF%BB%BF" . rawurlencode($csv_header . "\n" . $csv_sample);
                            ?>

                            <a href="<?php echo $csv_content; ?>" download="template_diklat.csv" class="btn btn-sm btn-info mt-2 shadow-sm">
                                <i class="fa fa-download mr-1"></i> Download Template CSV
                            </a>
                        </div>

                        <form action="home-admin.php?page=proses-import-diklat" method="POST" enctype="multipart/form-data">
                            
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-muted small text-uppercase mb-2">Pilih File CSV</label>
                                <div class="custom-file">
                                    <input type="file" name="file_csv" class="custom-file-input" id="file_csv" required accept=".csv">
                                    <label class="custom-file-label input-modern pt-2" for="file_csv">Pilih file .csv...</label>
                                </div>
                            </div>

                            <div class="form-group text-right mt-4 border-top pt-4">
                                <button type="submit" name="upload" class="btn btn-info btn-modern shadow-sm">
                                    <i class="fa fa-cloud-upload-alt mr-2"></i> Proses Import
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>  
</section>

<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
<script>
  $(function () {
    bsCustomFileInput.init();
    
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = document.getElementById("file_csv").files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
  });
</script>