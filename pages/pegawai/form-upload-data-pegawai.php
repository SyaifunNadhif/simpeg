<?php
// form-upload-data-pegawai.php
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
        
        <div class="alert alert-light border">
          <i class="fas fa-file-excel text-success"></i> Unduh template upload:
          <a href="pages/pegawai/template-upload-pegawai.xlsx" download class="btn btn-sm btn-success ml-2">
            <i class="fa fa-download"></i> Download Template
          </a>
        </div>

        <form id="uploadForm" method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label>Pilih File Excel (.xlsx)</label>
            <input type="file" name="file_excel" id="file_excel" accept=".xlsx, .xls" class="form-control" required>
            <small class="text-muted">Maksimal ukuran file 2MB.</small>
          </div>
          
          <div class="form-group">
            <a href="home-admin.php?page=form-view-data-pegawai" class="btn btn-secondary mr-2">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
            <button type="submit" name="preview" class="btn btn-info">
                <i class="fa fa-eye"></i> Preview Data
            </button>
          </div>
        </form>

        <div id="preview-area" class="mt-4"></div>

      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ==============================================
// 1. LOGIKA UNTUK PREVIEW DATA
// ==============================================
document.getElementById('uploadForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const fileInput = document.getElementById('file_excel');
  const file = fileInput.files[0];

  // Validasi sederhana
  if (!file) {
    Swal.fire('Error', 'Silakan pilih file terlebih dahulu.', 'warning');
    return;
  }
  if (file.size > 2 * 1024 * 1024) { // 2MB
    Swal.fire('Error', 'Ukuran file terlalu besar (Max 2MB).', 'error');
    return;
  }

  const formData = new FormData();
  formData.append('file_excel', file);
  formData.append('action', 'preview'); // Kita kirim sinyal 'preview' ke PHP

  // Tampilkan Loading
  Swal.fire({
      title: 'Membaca File Excel...',
      html: 'Mohon tunggu sebentar.',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading() }
  });

  // Kirim ke Backend
  fetch('pages/pegawai/upload-data-pegawai.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json()) // Pastikan response dianggap JSON
  .then(res => {
    Swal.close(); // Tutup loading

    if (res.status === 'success') {
      // Tampilkan Tabel HTML dari PHP ke div preview-area
      document.getElementById('preview-area').innerHTML = res.html;
      
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Data berhasil dibaca. Silakan cek tabel di bawah, lalu klik tombol Simpan.',
        timer: 2500,
        showConfirmButton: false
      });
    } else {
      Swal.fire('Gagal', res.message, 'error');
      document.getElementById('preview-area').innerHTML = '';
    }
  })
  .catch(error => {
    console.error(error);
    Swal.fire('System Error', 'Terjadi kesalahan pada server atau format respon bukan JSON.', 'error');
  });
});


// ==============================================
// 2. LOGIKA UNTUK SIMPAN DATA (EVENT DELEGATION)
// ==============================================
// Karena tombol 'btnSimpanKolektif' baru muncul SETELAH preview, 
// kita tidak bisa langsung pasang event listener padanya.
// Kita pasang di document body untuk memantau klik.

document.body.addEventListener('click', function(e) {
    // Cek apakah yang diklik adalah tombol simpan
    if (e.target && (e.target.id == 'btnSimpanKolektif' || e.target.closest('#btnSimpanKolektif'))) {
        e.preventDefault();

        // Ambil data JSON mentah dari textarea tersembunyi
        const textArea = document.getElementById('json_data_pegawai');
        if(!textArea) {
            Swal.fire('Error', 'Data tidak ditemukan. Silakan upload ulang.', 'error');
            return;
        }

        const rawData = textArea.value;

        // Konfirmasi User
        Swal.fire({
            title: 'Simpan Data ke Database?',
            text: "Pastikan data di tabel sudah benar.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan Semua!'
        }).then((result) => {
            if (result.isConfirmed) {
                simpanKeDatabase(rawData);
            }
        });
    }
});

// Fungsi Khusus Request Simpan
function simpanKeDatabase(jsonData) {
    const formData = new FormData();
    formData.append('action', 'save'); // Kirim sinyal 'save' ke PHP
    formData.append('data_pegawai', jsonData);

    // Loading Simpan
    Swal.fire({
        title: 'Menyimpan Data...',
        html: 'Jangan tutup halaman ini.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
    });

    fetch('pages/pegawai/upload-data-pegawai.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(res => {
        if (res.status === 'success') {
            Swal.fire('Sukses!', res.message, 'success').then(() => {
                // Redirect ke halaman daftar pegawai setelah sukses
                window.location.href = "home-admin.php?page=form-view-data-pegawai"; 
            });
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'Terjadi kesalahan saat menyimpan data.', 'error');
    });
}
</script>