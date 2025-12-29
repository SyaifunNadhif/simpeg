<style>
    .upload-container { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); padding: 30px; }
    .drop-zone { border: 2px dashed #cbd5e0; border-radius: 15px; padding: 40px; text-align: center; background-color: #f8fafc; transition: all 0.3s ease; cursor: pointer; position: relative; }
    .drop-zone:hover, .drop-zone.dragover { border-color: #dc3545; background-color: #fff5f5; transform: scale(1.01); }
    .file-preview { display: none; margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; }
    .file-input-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
</style>

<section class="content p-3">
  <div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="upload-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="m-0 font-weight-bold text-dark"><i class="fas fa-gavel text-danger mr-2"></i> Import Data Hukuman</h3>
                    <a href="home-admin.php?page=form-view-data-pelanggaran" class="btn btn-light btn-sm rounded-pill px-3"><i class="fas fa-times"></i> Tutup</a>
                </div>

                <div class="alert alert-secondary bg-white border shadow-sm rounded-lg mb-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="m-0 text-dark font-weight-bold">Template Excel</h6>
                            <small class="text-muted">Gunakan template terbaru. Kolom ID Pegawai wajib diisi.</small>
                        </div>
                        <a href="pages/ref-pelanggaran/download-template-hukuman.php" target="_blank" class="btn btn-danger btn-sm rounded-pill px-4 shadow-sm"><i class="fas fa-download mr-1"></i> Download</a>
                    </div>
                </div>

                <form id="uploadFormHukuman" enctype="multipart/form-data">
                    <div class="mb-3">
                        <div class="drop-zone" id="dropZoneHukuman">
                            <div class="content-wrap">
                                <i class="fas fa-cloud-upload-alt fa-4x mb-3 text-secondary"></i>
                                <h5 class="font-weight-bold text-dark">Drop File Excel Di Sini</h5>
                                <p class="text-muted mb-0 small">.xlsx, .xls (Maks 5MB)</p>
                            </div>
                            <input type="file" name="file_excel" id="file_excel_hukuman" class="file-input-overlay" accept=".xlsx, .xls" required>
                        </div>
                        <div id="filePreviewHukuman" class="file-preview">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-excel text-success fa-2x mr-3"></i>
                                <div><h6 class="m-0 font-weight-bold text-dark" id="fileNameHukuman">file.xlsx</h6><small class="text-muted" id="fileSizeHukuman">0 KB</small></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5 shadow-sm"><i class="fas fa-eye mr-2"></i> Preview Data</button>
                    </div>
                </form>

                <div id="preview-area-hukuman" class="mt-5"></div>
            </div>
        </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// --- Script Drag & Drop Khusus Hukuman ---
const dropZoneH = document.getElementById('dropZoneHukuman');
const fileInputH = document.getElementById('file_excel_hukuman');
const filePreviewH = document.getElementById('filePreviewHukuman');
const fileNameTxtH = document.getElementById('fileNameHukuman');
const fileSizeTxtH = document.getElementById('fileSizeHukuman');

['dragenter', 'dragover'].forEach(evt => dropZoneH.addEventListener(evt, (e) => { e.preventDefault(); dropZoneH.classList.add('dragover'); }));
['dragleave', 'drop'].forEach(evt => dropZoneH.addEventListener(evt, (e) => { e.preventDefault(); dropZoneH.classList.remove('dragover'); }));

fileInputH.addEventListener('change', function() {
    if (this.files.length) {
        filePreviewH.style.display = 'block';
        fileNameTxtH.textContent = this.files[0].name;
        fileSizeTxtH.textContent = (this.files[0].size / 1024).toFixed(2) + ' KB';
    }
});

// --- Handle Form Submit (Preview) ---
document.getElementById('uploadFormHukuman').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!fileInputH.files.length) { Swal.fire('Warning', 'Pilih file dulu!', 'warning'); return; }

    const formData = new FormData();
    formData.append('file_excel', fileInputH.files[0]);
    formData.append('action', 'preview');

    Swal.fire({title: 'Memproses...', didOpen: () => Swal.showLoading()});

    fetch('pages/ref-pelanggaran/upload-data-hukuman.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(res => {
        Swal.close();
        if (res.status === 'success') {
            document.getElementById('preview-area-hukuman').innerHTML = res.html;
            document.getElementById('preview-area-hukuman').scrollIntoView({ behavior: 'smooth' });
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }).catch(err => { Swal.close(); Swal.fire('Error', 'Server Error', 'error'); });
});

// --- Handle Tombol Simpan (Dynamic Click) ---
document.body.addEventListener('click', function(e) {
    if (e.target && (e.target.id == 'btnSimpanHukuman' || e.target.closest('#btnSimpanHukuman'))) {
        e.preventDefault();
        const textArea = document.getElementById('json_data_hukuman');
        if(!textArea) { Swal.fire('Error', 'Data hilang.', 'error'); return; }

        Swal.fire({
            title: 'Simpan Data Hukuman?',
            text: "Data duplikat (ID Pegawai + Jenis Hukuman + No SK) akan di-update.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Proses!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'save');
                formData.append('data_hukuman', textArea.value); 

                Swal.fire({title: 'Menyimpan...', didOpen: () => Swal.showLoading()});

                fetch('pages/ref-pelanggaran/upload-data-hukuman.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire('Selesai!', res.message, 'success').then(() => { 
                            window.location.href = "home-admin.php?page=form-view-data-pelanggaran"; 
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }).catch(err => { Swal.close(); Swal.fire('Error', 'Koneksi Gagal', 'error'); });
            }
        });
    }
});
</script>