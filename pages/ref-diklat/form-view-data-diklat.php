<?php
/*********************************************************
 * FILE    : pages/ref-diklat/form-view-data-diklat.php
 * MODULE  : SIMPEG — Daftar Diklat (With Soft Delete)
 * VERSION : v1.3
 *********************************************************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';

/* options filter */
$optDiklat = array();
$rs=mysqli_query($conn,"SELECT DISTINCT diklat FROM tb_diklat WHERE diklat<>'' ORDER BY diklat ASC");
if($rs){ while($r=mysqli_fetch_assoc($rs)){ $optDiklat[]=$r['diklat']; } }
$optTh = array();
$rs2=mysqli_query($conn,"SELECT DISTINCT tahun FROM tb_diklat WHERE tahun<>'' ORDER BY tahun DESC");
if($rs2){ while($r=mysqli_fetch_assoc($rs2)){ $optTh[]=$r['tahun']; } }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Daftar Diklat Pegawai</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <style>
    .card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}
    .card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}
    .toolbar-right .btn{margin-left:.5rem}
    .dataTables_wrapper .dataTables_filter{display:none}
  </style>
</head>
<body>
<div class="container-fluid mt-3">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">Daftar Diklat Pegawai</h5>
        <small>Menampilkan data diklat</small>
      </div>
      <div class="ml-auto d-flex">
        <a class="btn btn-light me-2" href="home-admin.php"><span class="me-1">🏠</span>Dashboard</a>
        <a class="btn btn-warning me-2" href="home-admin.php?page=ref-diklat/form-master<?php echo $uid? '&uid='.urlencode($uid):''; ?>">Tambah Data</a>
        <a class="btn btn-success" href="home-admin.php?page=form-import-data-diklat">Impor Kolektif</a>
      </div>
    </div>

    <div class="card-body">
      <div class="card p-3 mb-3">
        <div class="row g-2 align-items-end">
          <div class="col-md-6">
            <label class="form-label">Filter Nama Diklat</label>
            <select id="f_diklat" class="form-select" style="width:100%">
              <option value="">— Semua —</option>
              <?php foreach($optDiklat as $v){ echo '<option value="'.e($v).'">'.e($v).'</option>'; } ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Tahun</label>
            <select id="f_tahun" class="form-select" style="width:100%">
              <option value="">— Semua —</option>
              <?php foreach($optTh as $v){ echo '<option value="'.e($v).'">'.e($v).'</option>'; } ?>
            </select>
          </div>
          <div class="col-md-3 text-end">
            <button id="btnResetDiklat" class="btn btn-outline-secondary">Reset</button>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table id="tblDiklat" class="display nowrap table table-striped" style="width:100%">
          <thead>
            <tr>
              <th>No</th>
              <th>Aksi</th> <th>ID Peg — Nama</th>
              <th>Diklat</th>
              <th>Penyelenggara</th>
              <th>Tempat</th>
              <th>Angkatan</th>
              <th>Tahun</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus data ini? Data akan dipindahkan ke Recycle Bin.</p>
        <div id="dataSummary" class="alert alert-secondary py-2 small mb-3">
            Loading data...
        </div>
        <div class="mb-3">
            <label for="deleteReason" class="form-label fw-bold">Alasan Penghapusan <span class="text-danger">*</span></label>
            <textarea class="form-control" id="deleteReason" rows="3" placeholder="Contoh: Data duplikat, Salah input, dll..."></textarea>
        </div>
        <input type="hidden" id="deleteId">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="btnConfirmDelete">Ya, Hapus</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script> 
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function(){
  try{ $('#f_diklat,#f_tahun').select2({theme:'bootstrap-5',width:'100%',placeholder:'— Semua —',allowClear:true}); }catch(e){}

  var tbl = $('#tblDiklat').DataTable({
    processing:true, serverSide:true, searching:true,
    responsive:true, autoWidth:false,
    ajax:{
      url:'pages/ref-diklat/ajax-data-diklat.php',
      type:'GET',
      data:function(d){
        d.uid = <?php echo json_encode($uid); ?>;
        d.f_diklat = $('#f_diklat').val()||'';
        d.f_tahun  = $('#f_tahun').val()||'';
      }
    },
    columns:[
      {data:'no', orderable:false},
      // Define Action Column
      {
          data: null, 
          orderable: false,
          className: 'text-center',
          render: function(data, type, row) {
              // Assuming your API returns 'id_diklat'. Adjust if it's just 'id'
              var id = row.id_diklat || row.id; 
              return '<button class="btn btn-sm btn-danger btn-delete" data-id="'+id+'">Hapus</button>';
          }
      },
      {data:'idpeg_nama'},
      {data:'diklat'},
      {data:'penyelenggara'},
      {data:'tempat'},
      {data:'angkatan'},
      {data:'tahun'}
    ],
    columnDefs:[
      {targets:[0,1,2,7], className:'all'},
      {targets:0, responsivePriority:1},
      {targets:1, responsivePriority:2}, // Priority for Action column
      {targets:7, responsivePriority:3},
      {targets:'_all', responsivePriority:100}
    ],
    language:{search:'', searchPlaceholder:'Cari...'}
  });

  $('#f_diklat,#f_tahun').on('change', function(){ tbl.ajax.reload(null,false); });
  $('#btnResetDiklat').on('click', function(){
    $('#f_diklat').val(null).trigger('change');
    $('#f_tahun').val(null).trigger('change');
  });

  // --- DELETE LOGIC ---

  // 1. Open Modal and Fetch Summary
  $('#tblDiklat').on('click', '.btn-delete', function() {
      var id = $(this).data('id');
      $('#deleteId').val(id);
      $('#deleteReason').val(''); // Clear previous reason
      $('#dataSummary').html('<span class="text-muted spinner-border spinner-border-sm"></span> Mengambil info...');
      
      var modal = new bootstrap.Modal(document.getElementById('modalHapus'));
      modal.show();

      // Fetch simple summary to show user what they are deleting
      $.ajax({
          url: 'pages/ref-diklat/process_soft_delete.php',
          type: 'POST',
          data: { action: 'get_info', id: id },
          dataType: 'json',
          success: function(response) {
              if(response.status === 'success') {
                  var d = response.data;
                  var html = '<strong>Diklat:</strong> ' + d.diklat + '<br>' +
                             '<strong>Pegawai:</strong> ' + d.id_peg + '<br>' +
                             '<strong>Tahun:</strong> ' + d.tahun;
                  $('#dataSummary').html(html);
              } else {
                  $('#dataSummary').html('<span class="text-danger">Gagal mengambil data.</span>');
              }
          }
      });
  });

  // 2. Confirm Delete
  $('#btnConfirmDelete').on('click', function() {
      var id = $('#deleteId').val();
      var reason = $('#deleteReason').val().trim();

      if(reason === '') {
          Swal.fire('Peringatan', 'Mohon isi alasan penghapusan!', 'warning');
          return;
      }

      // Disable button to prevent double submit
      $(this).prop('disabled', true).text('Memproses...');

      $.ajax({
          url: 'pages/ref-diklat/process_soft_delete.php',
          type: 'POST',
          data: { 
              action: 'delete', 
              id: id, 
              reason: reason 
          },
          dataType: 'json',
          success: function(response) {
              $('#btnConfirmDelete').prop('disabled', false).text('Ya, Hapus');
              
              // Hide Modal (using bootstrap instance)
              var modalEl = document.getElementById('modalHapus');
              var modal = bootstrap.Modal.getInstance(modalEl);
              modal.hide();

              if(response.status === 'success') {
                  Swal.fire('Berhasil', 'Data berhasil dihapus ke Recycle Bin.', 'success');
                  tbl.ajax.reload(null, false); // Reload table
              } else {
                  Swal.fire('Gagal', response.message, 'error');
              }
          },
          error: function() {
              $('#btnConfirmDelete').prop('disabled', false).text('Ya, Hapus');
              Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
          }
      });
  });

});
</script>
</body>
</html>