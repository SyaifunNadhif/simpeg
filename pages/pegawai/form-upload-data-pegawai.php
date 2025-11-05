<?php
// form-upload-data-pegawai.php
?>
<?php
$page_title = "Tambah";
$page_subtitle = "Kolektif Pegawai";
$breadcrumbs = [
  ["label" => "Dashboard", "url" => "home-admin.php"],
  ["label" => "Tambah Kolektif Pegawai"]
];
include "komponen/header.php";
?>


<section class="content">
  <div class="container-fluid">
    <div class="card card-info">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-upload"></i> Form Upload Data Pegawai Kolektif</h3>
      </div>
      <div class="card-body">
        <div class="alert">
          <i class="fas fa-file-excel"></i> Unduh template upload:
          <a href="pages/pegawai/template-upload-pegawai.xlsx" download class="btn btn-sm btn-success ml-2">
            <i class="fa fa-download"></i> Download Template
          </a>
        </div>

        <form id="uploadForm" method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label>Pilih File Excel (.xlsx)</label>
            <input type="file" name="file_excel" id="file_excel" accept=".xlsx" class="form-control" required>
          </div>
          <a href="home-admin.php?page=form-view-data-pegawai" class="btn btn-secondary mr-2"><i class="fa fa-arrow-left"></i> Kembali</a>
          <button type="submit" name="preview" class="btn btn-info">Preview Data</button>
        </form>
        <div id="preview-area" class="mt-4"></div>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const fileInput = document.getElementById('file_excel');
  const file = fileInput.files[0];
  if (!file) return;

  if (file.size > 2 * 1024 * 1024) { // 2MB limit
    Swal.fire('Ukuran file terlalu besar', 'Maksimum 2MB.', 'error');
    return;
  }

  const formData = new FormData();
  formData.append('file_excel', file);

  fetch('pages/pegawai/upload-data-pegawai.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(html => {
    document.getElementById('preview-area').innerHTML = html;
  })
  .catch(error => {
    Swal.fire('Gagal Upload', error.message, 'error');
  });
});
</script>

<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const fileInput = document.getElementById('file_excel');
  const file = fileInput.files[0];
  if (!file) return;

  if (file.size > 2 * 1024 * 1024) {
    Swal.fire('Ukuran file terlalu besar', 'Maksimum 2MB.', 'error');
    return;
  }

  const formData = new FormData();
  formData.append('file_excel', file);

  fetch('pages/pegawai/upload-data-pegawai.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(res => {
    if (res.status === 'success') {
      document.getElementById('preview-area').innerHTML = res.html;
      Swal.fire({
        icon: 'success',
        title: 'File berhasil dibaca',
        text: 'Silakan periksa data sebelum disimpan.',
        timer: 2000,
        showConfirmButton: false
      });
    } else {
      Swal.fire({
        icon: res.status,
        title: res.status === 'error' ? 'Gagal' : 'Peringatan',
        text: res.message
      });
    document.getElementById('preview-area').innerHTML = ''; // bersihkan output JSON
  }
})

  .catch(error => {
    Swal.fire('Gagal Upload', error.message, 'error');
  });
});
</script>
