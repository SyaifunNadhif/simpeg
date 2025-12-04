<?php
/*********************************************************
 * FILE    : pages/ref-sertifikasi/form-view-data-sertifikasi.php
 * MODULE  : View Data Sertifikasi (Final Fix Primary Key)
 *********************************************************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';

// Data Filter
$optSert = array();
$rs=mysqli_query($conn,"SELECT DISTINCT sertifikasi FROM tb_sertifikasi WHERE sertifikasi<>'' ORDER BY sertifikasi ASC");
if($rs){ while($r=mysqli_fetch_assoc($rs)){ $optSert[]=$r['sertifikasi']; } }

$optTh = array();
$rs2=mysqli_query($conn,"SELECT DISTINCT YEAR(tgl_sertifikat) th FROM tb_sertifikasi WHERE tgl_sertifikat IS NOT NULL AND tgl_sertifikat<>'0000-00-00' ORDER BY th DESC");
if($rs2){ while($r=mysqli_fetch_assoc($rs2)){ $optTh[]=$r['th']; } }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Daftar Sertifikasi Pegawai</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .card-modern { border: none; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: #fff; margin-bottom: 20px; }
    .card-header-modern { background: #fff; border-bottom: 1px solid #f1f1f1; padding: 20px 25px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; }
    .filter-box { background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef; }
    
    /* Table Styling */
    .table thead th { background-color: #f1f3f5; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #dee2e6; padding: 12px; }
    .table tbody td { vertical-align: middle; padding: 12px; color: #495057; }
    
    .btn-edit-action { background-color: #e0f2fe; color: #0284c7; border: none; padding: 6px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: 0.2s; }
    .btn-edit-action:hover { background-color: #0284c7; color: #fff; transform: translateY(-2px); }
  </style>
</head>
<body>

<div class="container-fluid mt-4">
  <div class="card card-modern">
    <div class="card-header-modern">
      <div>
        <h5 class="mb-1 font-weight-bold text-dark"><i class="fas fa-certificate text-primary me-2"></i>Daftar Sertifikasi Pegawai</h5>
        <p class="text-muted mb-0 small">Kelola data sertifikasi dan kompetensi pegawai</p>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-light border" href="home-admin.php"><i class="fas fa-home me-1"></i> Dashboard</a>
        <a class="btn btn-success" href="home-admin.php?page=form-import-data-sertifikasi"><i class="fas fa-file-excel me-1"></i> Impor</a>
        <a class="btn btn-primary" href="home-admin.php?page=form-master-data-sertifikasi"><i class="fas fa-plus me-1"></i> Tambah Data</a>
      </div>
    </div>

    <div class="card-body p-4">
      <div class="filter-box mb-4">
        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <label class="form-label text-muted small fw-bold text-uppercase">Filter Nama Sertifikasi</label>
            <select id="f_sertif" class="form-select"><option value="">— Semua Sertifikasi —</option><?php foreach($optSert as $v){ echo '<option value="'.e($v).'">'.e($v).'</option>'; } ?></select>
          </div>
          <div class="col-md-3">
            <label class="form-label text-muted small fw-bold text-uppercase">Tahun</label>
            <select id="f_tahun_s" class="form-select"><option value="">— Semua —</option><?php foreach($optTh as $v){ echo '<option value="'.e($v).'">'.e($v).'</option>'; } ?></select>
          </div>
          <div class="col-md-3 text-end">
            <button id="btnResetSertif" class="btn btn-outline-secondary w-100"><i class="fas fa-sync-alt me-1"></i> Reset Filter</button>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table id="tblSertif" class="display nowrap table table-hover" style="width:100%">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th>ID Peg — Nama</th>
              <th>Sertifikasi</th>
              <th>Penyelenggara</th>
              <th>Tgl Expired</th>
              <th class="text-center">Status</th>
              <th>No. Sertifikat</th>
              <th>Tgl Sertifikat</th>
              <th class="text-center" width="10%">Aksi</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(function(){
  try{ $('#f_sertif,#f_tahun_s').select2({theme:'bootstrap-5',width:'100%',placeholder:'— Pilih Filter —',allowClear:true}); }catch(e){}

  var tbl = $('#tblSertif').DataTable({
    processing: true, serverSide: true, searching: true, responsive: true, autoWidth: false,
    ajax:{
      url:'pages/ref-sertifikasi/ajax-data-sertifikasi.php', 
      type:'GET',
      data:function(d){
        d.uid = <?php echo json_encode($uid); ?>;
        d.f_sertif = $('#f_sertif').val()||'';
        d.f_tahun  = $('#f_tahun_s').val()||'';
      }
    },
    columns:[
      {data:'no', orderable:false},
      {data:'idpeg_nama'},
      {data:'sertifikasi'},
      {data:'penyelenggara'},
      {data:'tgl_expired'},   
      {data:'status_badge', className:'text-center'},
      {data:'sertifikat'},    
      {data:'tgl_sertifikat'},
      // --- PERBAIKAN DI SINI (Gunakan id_sertif) ---
      {
        data: 'id_sertif', 
        orderable: false,
        className: 'text-center',
        render: function(data, type, row) {
            // Gunakan data (which is id_sertif from AJAX)
            if(data) {
                return '<a href="home-admin.php?page=form-edit-data-sertifikasi&id='+data+'" class="btn-edit-action" title="Edit Data"><i class="fas fa-edit me-1"></i> Edit</a>';
            }
            return '-';
        }
      } 
    ],
    columnDefs:[
      {targets:[0,1,2,8], className:'all'},     
      {targets:[5], className:'min-tablet'},    
      {targets:[3,4,6,7], className:'none'}     
    ],
    language:{
        search: "_INPUT_",
        searchPlaceholder: "Cari data...",
        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
        emptyTable: "Tidak ada data sertifikasi ditemukan."
    }
  });

  $('#f_sertif,#f_tahun_s').on('change', function(){ tbl.ajax.reload(null,false); });
  $('#btnResetSertif').on('click', function(){
    $('#f_sertif').val(null).trigger('change');
    $('#f_tahun_s').val(null).trigger('change');
    tbl.ajax.reload(null,false);
  });
});
</script>
</body>
</html>