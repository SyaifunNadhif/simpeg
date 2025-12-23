<style>
    .upload-container { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); padding: 30px; }
    .drop-zone { border: 2px dashed #cbd5e0; border-radius: 15px; padding: 40px; text-align: center; background-color: #f8fafc; transition: all 0.3s ease; cursor: pointer; position: relative; }
    .drop-zone:hover, .drop-zone.dragover { border-color: #007bff; background-color: #e3f2fd; transform: scale(1.01); }
    .file-input-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
    .file-preview { display: none; margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; }
    .step-badge { background: #007bff; color: white; width: 28px; height: 28px; border-radius: 50%; display: inline-block; text-align: center; line-height: 28px; font-weight: bold; margin-right: 10px; }
</style>

<section class="content p-2">
  <div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="upload-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="m-0 font-weight-bold text-dark"><i class="fas fa-users text-primary mr-2"></i> Import Pegawai Baru</h3>
                    <a href="home-admin.php?page=form-view-data-pegawai" class="btn btn-light btn-sm rounded-pill px-3"><i class="fas fa-times"></i> Tutup</a>
                </div>

                <div class="alert alert-secondary bg-white border shadow-sm rounded-lg mb-4">
                    <div class="d-flex align-items-center">
                        <span class="step-badge">1</span>
                        <div class="flex-grow-1">
                            <h6 class="m-0 text-dark font-weight-bold">Persiapan Data</h6>
                            <small class="text-muted">Unduh template Excel Data Pegawai.</small>
                        </div>
                        <a href="pages/pegawai/download-template-pegawai.php" target="_blank" class="btn btn-success btn-sm rounded-pill px-4 shadow-sm">
                            <i class="fas fa-download mr-1"></i> Download Template
                        </a>
                    </div>
                </div>

                <form id="uploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">

                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-3">
                            <span class="step-badge">2</span>
                            <h6 class="m-0 text-dark font-weight-bold">Upload File Excel</h6>
                        </div>
                        <div class="drop-zone" id="dropZone">
                            <div class="content-wrap">
                                <i class="fas fa-cloud-upload-alt fa-4x mb-3 text-secondary"></i>
                                <h5 class="font-weight-bold text-dark">Klik atau Tarik File ke Sini</h5>
                                <p class="text-muted mb-0 small">Support: .xlsx, .xls (Maks 5MB)</p>
                            </div>
                            <input type="file" name="file_excel" id="file_excel" class="file-input-overlay" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required>
                        </div>
                        <div id="filePreview" class="file-preview">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-excel text-success fa-2x mr-3"></i>
                                <div><h6 class="m-0 font-weight-bold text-dark" id="fileName">file.xlsx</h6><small class="text-muted" id="fileSize">0 KB</small></div>
                                <div class="ml-auto"><span class="text-success fw-bold"><i class="fas fa-check-circle"></i> Siap Upload</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm"><i class="fas fa-eye mr-2"></i> Preview Data</button>
                    </div>
                </form>

                <div id="preview-area" class="mt-5"></div>
            </div>
        </div>
    </div>
  </div>
</section>

<script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>

<script>
// Cek apakah SweetAlert berhasil diload
if (typeof Swal === 'undefined') {
    alert("Error: File plugins/sweetalert2/sweetalert2.all.min.js tidak ditemukan. Aplikasi mungkin tidak berjalan optimal.");
}

const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('file_excel');
const filePreview = document.getElementById('filePreview');
const fileNameTxt = document.getElementById('fileName');
const fileSizeTxt = document.getElementById('fileSize');

// Drag & Drop Effects
['dragenter', 'dragover'].forEach(evt => dropZone.addEventListener(evt, (e) => { e.preventDefault(); dropZone.classList.add('dragover'); }));
['dragleave', 'drop'].forEach(evt => dropZone.addEventListener(evt, (e) => { e.preventDefault(); dropZone.classList.remove('dragover'); }));

// File Input Change
fileInput.addEventListener('change', function() {
    if (this.files.length) {
        // Validasi Ukuran File (Client Side) - Max 5MB
        if(this.files[0].size > 5 * 1024 * 1024) {
            if(typeof Swal !== 'undefined') {
                Swal.fire('File Terlalu Besar', 'Maksimal ukuran file adalah 5MB', 'warning');
            } else {
                alert('File Terlalu Besar. Maksimal 5MB');
            }
            this.value = ""; // Reset input
            return;
        }

        filePreview.style.display = 'block';
        fileNameTxt.textContent = this.files[0].name;
        fileSizeTxt.textContent = (this.files[0].size / 1024).toFixed(2) + ' KB';
    }
});

// Handle Preview
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!fileInput.files.length) { 
        typeof Swal !== 'undefined' ? Swal.fire('Warning', 'Pilih file terlebih dahulu!', 'warning') : alert('Pilih file dulu!'); 
        return; 
    }

    const formData = new FormData(this); // Mengambil semua input termasuk CSRF token
    formData.append('action', 'preview');

    if(typeof Swal !== 'undefined') Swal.fire({title: 'Memproses Preview...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});

    fetch('pages/pegawai/upload-data-pegawai.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(res => {
        if(typeof Swal !== 'undefined') Swal.close();
        
        if (res.status === 'success') {
            // [SECURITY NOTE] Pastikan 'res.html' dari PHP sudah dibersihkan (htmlspecialchars) 
            document.getElementById('preview-area').innerHTML = res.html;
            document.getElementById('preview-area').scrollIntoView({ behavior: 'smooth' });
            if(typeof Swal !== 'undefined') Swal.fire({icon: 'success', title: 'Preview Berhasil', timer: 1500, showConfirmButton: false});
        } else {
            if(typeof Swal !== 'undefined') Swal.fire('Gagal', res.message, 'error'); else alert(res.message);
        }
    }).catch(err => { 
        if(typeof Swal !== 'undefined') { Swal.close(); Swal.fire('Error', 'Terjadi kesalahan server.', 'error'); } 
        console.error(err);
    });
});

// Handle Simpan (Event Delegation)
document.body.addEventListener('click', function(e) {
    if (e.target && (e.target.id == 'btnSimpanKolektif' || e.target.closest('#btnSimpanKolektif'))) {
        e.preventDefault();
        const textArea = document.getElementById('json_data_pegawai');
        
        if(!textArea) { 
            typeof Swal !== 'undefined' ? Swal.fire('Error', 'Data preview tidak ditemukan.', 'error') : alert('Data tidak ditemukan'); 
            return; 
        }

        const confirmAction = () => {
            const formData = new FormData();
            formData.append('action', 'save');
            formData.append('data_pegawai', textArea.value);
            // Append CSRF token manual jika perlu, atau ambil dari form hidden input jika ada di scope

            if(typeof Swal !== 'undefined') Swal.fire({title: 'Menyimpan Data...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});

            fetch('pages/pegawai/upload-data-pegawai.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    if(typeof Swal !== 'undefined') {
                        Swal.fire('Selesai!', res.message, 'success').then(() => { window.location.href = "home-admin.php?page=form-view-data-pegawai"; });
                    } else {
                        alert(res.message);
                        window.location.href = "home-admin.php?page=form-view-data-pegawai";
                    }
                } else {
                    if(typeof Swal !== 'undefined') Swal.fire('Gagal', res.message, 'error'); else alert(res.message);
                }
            }).catch(err => { 
                if(typeof Swal !== 'undefined') { Swal.close(); Swal.fire('Error', 'Koneksi gagal.', 'error'); }
            });
        };

        if(typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Simpan Data Pegawai?',
                text: "Data yang sudah ada (ID sama) tidak akan disimpan.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Proses!'
            }).then((result) => {
                if (result.isConfirmed) confirmAction();
            });
        } else {
            if(confirm('Simpan Data Pegawai?')) confirmAction();
        }
    }
});
</script>