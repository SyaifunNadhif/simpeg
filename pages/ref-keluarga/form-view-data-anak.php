<?php
/*********************************************************
 * FILE    : pages/keluarga/form-view-data-anak.php
 * MODULE  : SIMPEG — Data Anak (Modern View + Actions)
 * VERSION : v1.2
 *********************************************************/

if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$pegawai = null;
if ($uid!==''){
  $q = mysqli_query($conn, "SELECT id_peg, id_peg_old, nama FROM tb_pegawai WHERE id_peg='".mysqli_real_escape_string($conn,$uid)."' LIMIT 1");
  if ($q && mysqli_num_rows($q)>0){ $pegawai = mysqli_fetch_assoc($q); }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Daftar Anak Pegawai</title>
  
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    
    /* Card Modern */
    .card-modern { border: none; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: #fff; margin-bottom: 20px; }
    .card-header-modern { background: #fff; border-bottom: 1px solid #f1f1f1; padding: 20px 25px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; }
    
    /* Table Styling */
    .table thead th { background-color: #f1f3f5; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #dee2e6; padding: 12px; }
    .table tbody td { vertical-align: middle; padding: 12px; color: #495057; }

    /* Action Buttons */
    .btn-action {
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: 0.2s;
        display: inline-block;
        margin-right: 4px;
    }
    .btn-edit { background-color: #e0f2fe; color: #0284c7; }
    .btn-edit:hover { background-color: #0284c7; color: #fff; transform: translateY(-2px); }
    
    .btn-profile { background-color: #eef2ff; color: #4f46e5; }
    .btn-profile:hover { background-color: #4338ca; color: #fff; transform: translateY(-2px); }

    .btn-modern { border-radius: 8px; font-weight: 500; font-size: 0.9rem; padding: 8px 16px; }
  </style>
</head>
<body>

<div class="container-fluid mt-4">
  <div class="card card-modern">
    
    <div class="card-header-modern">
      <div>
        <h5 class="mb-1 font-weight-bold text-dark"><i class="fas fa-child text-primary me-2"></i>Daftar Anak Pegawai</h5>
        <small class="text-muted">
            <?php echo $pegawai ? 'Data anak dari: <b>'.e($pegawai['nama']).'</b> ('.e($pegawai['id_peg']).')' : 'Menampilkan seluruh data anak pegawai'; ?>
        </small>
      </div>
      <div class="d-flex gap-2">
        <a href="home-admin.php" class="btn btn-light btn-modern border"><i class="fas fa-home me-1"></i> Dashboard</a>
        <a href="home-admin.php?page=form-import-data-anak" class="btn btn-success btn-modern"><i class="fas fa-file-excel me-1"></i> Impor</a>
        <a href="home-admin.php?page=form-master-data-anak<?php echo $uid? '&uid='.urlencode($uid):''; ?>" class="btn btn-primary btn-modern"><i class="fas fa-plus me-1"></i> Tambah Data</a>
      </div>
    </div>

    <div class="card-body p-4">
      <div class="table-responsive">
        <table id="tblAnak" class="display nowrap table table-hover" style="width:100%">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th>ID Peg — Nama</th>
              <th>Nama Anak</th>
              <th>Tgl Lahir</th>
              <th>Pendidikan</th>
              <th>Pekerjaan</th>
              <th>Status Hub</th>
              <th>Anak ke</th>
              <th>BPJS</th>
              <th width="15%" class="text-center">Aksi</th>
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

<script>
$(function(){
  $('#tblAnak').DataTable({
    processing: true, 
    serverSide: true, 
    searching: true,
    responsive: true,
    autoWidth: false,
    ajax: { 
        url: 'pages/ref-keluarga/ajax-data-anak.php', 
        type: 'GET', 
        data: { uid: <?php echo json_encode($uid); ?> } 
    },
    columns: [
      { data: 'no', orderable:false },
      { data: 'idpeg_nama' }, 
      { data: 'nama' },
      { data: 'tgl_lhr' },
      { data: 'pendidikan' },
      { data: 'pekerjaan' },
      { data: 'status_hub' },
      { data: 'anak_ke' },
      { data: 'bpjs_anak' },
      // KOLOM AKSI
      { 
        data: null, 
        orderable: false,
        className: 'text-center',
        render: function(data, type, row) {
            // Gunakan ID Pegawai untuk tombol profil
            var idPeg = row.id_peg || ''; 
            
            // Gunakan ID Anak (Primary Key) untuk tombol edit
            // Pastikan file AJAX mengembalikan kolom 'id_anak' atau 'id'
            var idAnak = row.id_anak || row.id; 

            var btnHtml = '';

            // Tombol 1: Profile Pegawai
            if(idPeg) {
                btnHtml += '<a href="home-admin.php?page=view-detail-pegawai&id='+idPeg+'" class="btn-action btn-profile" title="Lihat Profil Pegawai"><i class="fas fa-user-tie"></i></a>';
            }

            // Tombol 2: Edit Data Anak
            if(idAnak) {
                btnHtml += '<a href="home-admin.php?page=form-edit-data-anak&id='+idAnak+'" class="btn-action btn-edit" title="Edit Data Anak"><i class="fas fa-edit"></i></a>';
            }

            return btnHtml;
        }
      }
    ],
    columnDefs: [
        { targets: [0, 2, 9], className: 'all' }, // No, Nama Anak, Aksi selalu tampil
        { targets: [3, 4, 5, 6, 7, 8], className: 'min-tablet' } 
    ],
    language: {
        search: "_INPUT_",
        searchPlaceholder: "Cari data...",
        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
        emptyTable: "Tidak ada data anak ditemukan."
    }
  });
});
</script>
</body>
</html>