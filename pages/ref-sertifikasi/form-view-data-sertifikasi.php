<?php
/*********************************************************
 * FILE    : pages/ref-sertifikasi/form-view-data-sertifikasi.php
 * MODULE  : SIMPEG — Daftar Sertifikasi
 * VERSION : v1.3 (PHP 5.6)
 * DATE    : 2025-09-07
 * CHANGELOG
 * - v1.3: Pisahkan badge status ke kolom sendiri (Status). Kolom Tgl Expired kini hanya tanggal.
 * - v1.2: Filter Select2 (Nama Sertifikasi, Tahun) + DataTables responsive.
 * - v1.1: Card layout + tombol Dashboard/Tambah/Impor.
 * - v1.0: DataTables server-side.
 *********************************************************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';

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
        <h5 class="mb-0">Daftar Sertifikasi Pegawai</h5>
        <small>Menampilkan data sertifikasi</small>
      </div>
      <div class="ml-auto d-flex">
        <a class="btn btn-light me-2" href="home-admin.php"><span class="me-1">🏠</span>Dashboard</a>
        <a class="btn btn-warning me-2" href="home-admin.php?page=ref-sertifikasi/form-master<?php echo $uid? '&uid='.urlencode($uid):''; ?>">Tambah Data</a>
        <a class="btn btn-success" href="home-admin.php?page=form-import-data-sertifikasi">Impor Kolektif</a>
      </div>
    </div>

    <div class="card-body">
      <!-- FILTER -->
      <div class="card p-3 mb-3">
        <div class="row g-2 align-items-end">
          <div class="col-md-6">
            <label class="form-label">Filter Nama Sertifikasi</label>
            <select id="f_sertif" class="form-select" style="width:100%">
              <option value="">— Semua —</option>
              <?php foreach($optSert as $v){ echo '<option value="'.e($v).'">'.e($v).'</option>'; } ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Tahun</label>
            <select id="f_tahun_s" class="form-select" style="width:100%">
              <option value="">— Semua —</option>
              <?php foreach($optTh as $v){ echo '<option value="'.e($v).'">'.e($v).'</option>'; } ?>
            </select>
          </div>
          <div class="col-md-3 text-end">
            <button id="btnResetSertif" class="btn btn-outline-secondary">Reset</button>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table id="tblSertif" class="display nowrap table table-striped" style="width:100%">
          <thead>
            <tr>
              <th>No</th>
              <th>ID Peg — Nama</th>
              <th>Sertifikasi</th>
              <th>Penyelenggara</th>
              <th>Tgl Expired</th>
              <th>Status</th>
              <th>No. Sertifikat</th>
              <th>Tgl Sertifikat</th> <!-- kolom badge terpisah -->
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
  try{ $('#f_sertif,#f_tahun_s').select2({theme:'bootstrap-5',width:'100%',placeholder:'— Semua —',allowClear:true}); }catch(e){}

  var tbl = $('#tblSertif').DataTable({
    processing:true, serverSide:true, searching:true,
    responsive:true, autoWidth:false,
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
      {data:'no'},
      {data:'idpeg_nama'},
      {data:'sertifikasi'},
      {data:'penyelenggara'},
      {data:'tgl_expired'},   // tampil
      {data:'status_badge'},  // tampil
      {data:'sertifikat'},    // pindah ke child
      {data:'tgl_sertifikat'} // pindah ke child
    ],
    columnDefs:[
      {targets:[0,1,2], className:'all'},  // selalu tampil
      {targets:3, className:'all'},        // penyelenggara tetap tampil
      {targets:4, className:'all text-nowrap'},  // tgl expired tampil
      {targets:5, className:'all text-center'},  // status tampil
      {targets:[6,7], className:'none'}    // no sertifikat & tgl sertifikat jadi child
    ],
    language:{search:'', searchPlaceholder:'Cari...'}
  });

  $('#f_sertif,#f_tahun_s').on('change', function(){ tbl.ajax.reload(null,false); });
  $('#btnResetSertif').on('click', function(){
    $('#f_sertif').val(null).trigger('change');
    $('#f_tahun_s').val(null).trigger('change');
  });
});
</script>
</body>
</html>
