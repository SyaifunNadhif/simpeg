<?php
// FILE: pages/ref-pendidikan/form-view-data-pendidikan.php
if (session_id()==='') session_start();
include "dist/koneksi.php";

$uid = isset($_GET['uid']) ? mysqli_real_escape_string($conn, $_GET['uid']) : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Data Pendidikan</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <style>
    body { background-color: #f4f6f9; font-family: sans-serif; }
    .card-modern { border: 0; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .table thead th { background: #f8f9fa; text-transform: uppercase; font-size: 0.75rem; color: #666; font-weight: 700; }
    
    /* Tombol Aksi */
    .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; border: 1px solid transparent; transition: 0.2s; }
    .btn-edit-row { background: #e0f2fe; color: #0284c7; } .btn-edit-row:hover { background: #bae6fd; }
    .btn-delete-row { background: #fee2e2; color: #dc2626; } .btn-delete-row:hover { background: #fecaca; }
  </style>
</head>
<body>

<div class="container-fluid py-4">
  <div class="card card-modern">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
      <h5 class="m-0 fw-bold text-dark"><i class="fas fa-graduation-cap text-primary me-2"></i> Riwayat Pendidikan</h5>
      <div>
        <a href="home-admin.php?page=form-master-data-pendidikan&uid=<?= $uid ?>" class="btn btn-primary btn-sm rounded-pill px-4"><i class="fas fa-plus me-1"></i> Tambah</a>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="tblPend" class="table table-hover w-100 mb-0">
          <thead>
            <tr>
              <th width="5%" class="text-center">No</th>
              <th>Nama Pegawai</th>
              <th>Jenjang</th>
              <th>Nama Sekolah</th>
              <th>Jurusan</th>
              <th>Lulus</th>
              <th width="10%" class="text-center">Aksi</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function(){
  
  // 1. INIT DATATABLE
  var table = $('#tblPend').DataTable({
    processing: true, serverSide: true, responsive: true,
    ajax: { 
        url: 'pages/ref-pendidikan/ajax-data-pendidikan.php', // Pastikan file ini ada (dari chat sebelumnya)
        type: 'GET', 
        data: { uid: '<?= $uid ?>' } 
    },
    columns: [
      { data: 'no', className: 'text-center' },
      { data: 'idpeg_nama' },
      { data: 'jenjang', className: 'text-center' },
      { data: 'nama_sekolah' },
      { data: 'jurusan' },
      { data: 'th_lulus', className: 'text-center' },
      { 
        data: null, className: 'text-center', orderable: false,
        render: function(data, type, row) {
            // Kita simpan data di atribut button untuk diambil SweetAlert nanti
            // PERHATIKAN: data-nama, data-jenjang, data-sekolah
            return `
                <a href="home-admin.php?page=form-master-data-pendidikan&mode=edit&id=${row.id_pendidikan}" class="btn-icon btn-edit-row me-1"><i class="fas fa-pen fa-xs"></i></a>
                
                <button type="button" class="btn-icon btn-delete-row btn-hapus-custom" 
                    data-id="${row.id_pendidikan}"
                    data-pegawai="${$(row.idpeg_nama).text()}" 
                    data-sekolah="${row.nama_sekolah}"
                    data-jenjang="${row.jenjang}"
                    data-lulus="${row.th_lulus}">
                    <i class="fas fa-trash fa-xs"></i>
                </button>
            `;
        }
      }
    ]
  });

  // 2. LOGIC HAPUS "SEPERTI SCREENSHOT"
  $(document).on('click', '.btn-hapus-custom', function() {
      // Ambil data dari tombol
      var id = $(this).data('id');
      var pegawai = $(this).data('pegawai');
      var sekolah = $(this).data('sekolah');
      var jenjang = $(this).data('jenjang');
      var lulus = $(this).data('lulus');

      // Tampilkan SweetAlert Custom HTML
      Swal.fire({
          title: 'Konfirmasi Hapus',
          text: 'Apakah Anda yakin ingin menghapus data ini?',
          icon: null, // Icon dimatikan biar mirip screenshot (Header merah)
          
          // HTML BODY (MIRIP SCREENSHOT)
          html: `
            <div class="text-start mt-3">
                <p class="mb-2 text-muted small">Detail Data:</p>
                <div class="p-3 border rounded bg-light mb-3" style="font-size: 0.9rem; line-height: 1.6;">
                    <strong>Jenjang:</strong> ${jenjang}<br>
                    <strong>Sekolah:</strong> ${sekolah}<br>
                    <strong>Pegawai:</strong> ${pegawai}<br>
                    <strong>Thn Lulus:</strong> ${lulus}
                </div>
                
                <label class="form-label fw-bold text-danger" style="font-size: 0.85rem;">Alasan Penghapusan *</label>
                <textarea id="swal-alasan" class="form-control" rows="3" placeholder="Contoh: Duplikat, Salah Input, dll..."></textarea>
            </div>
          `,
          
          showCancelButton: true,
          confirmButtonColor: '#dc3545', // Warna Merah
          cancelButtonColor: '#6c757d',  // Warna Abu
          confirmButtonText: 'Ya, Hapus',
          cancelButtonText: 'Batal',
          reverseButtons: true, // Tombol Batal di kiri
          customClass: {
            title: 'text-danger fw-bold', // Judul Merah
            popup: 'rounded-4'
          },
          
          // Validasi Input Alasan
          preConfirm: () => {
              const alasan = Swal.getPopup().querySelector('#swal-alasan').value;
              if (!alasan) {
                  Swal.showValidationMessage('Alasan penghapusan wajib diisi!')
              }
              return { alasan: alasan }
          }
      }).then((result) => {
          if (result.isConfirmed) {
              // Jika dikonfirmasi, Jalankan AJAX Delete
              var alasanHapus = result.value.alasan;
              
              // Tampilkan Loading
              Swal.fire({title: 'Memproses...', didOpen: () => {Swal.showLoading()}});

              $.ajax({
                  url: 'pages/ref-pendidikan/ajax-delete-pendidikan.php',
                  type: 'POST',
                  data: { id: id, alasan: alasanHapus },
                  dataType: 'json',
                  success: function(response) {
                      if(response.status === 'success') {
                          Swal.fire({
                              icon: 'success', 
                              title: 'Terhapus!', 
                              text: 'Data berhasil dipindahkan ke Recycle Bin.',
                              timer: 1500,
                              showConfirmButton: false
                          });
                          table.ajax.reload(null, false); // Reload tabel tanpa refresh
                      } else {
                          Swal.fire('Gagal!', response.message, 'error');
                      }
                  },
                  error: function() {
                      Swal.fire('Error!', 'Terjadi kesalahan server.', 'error');
                  }
              });
          }
      });
  });

});
</script>
</body>
</html>