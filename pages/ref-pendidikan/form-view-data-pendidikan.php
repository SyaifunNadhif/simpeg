<?php
/***********************
 * FILE    : pages/ref-pendidikan/form-view-data-pendidikan.php
 * VERSION : v2.0 (Modern View & Simplified Columns)
 ***********************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$pegawai = null;
if ($uid!==''){
  $q = mysqli_query($conn, "SELECT id_peg, nama FROM tb_pegawai WHERE id_peg='".mysqli_real_escape_string($conn,$uid)."' LIMIT 1");
  if ($q && mysqli_num_rows($q)>0){ $pegawai = mysqli_fetch_assoc($q); }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Daftar Pendidikan Pegawai</title>
  
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body { background-color: #f4f6f9; font-family: 'Inter', sans-serif; color: #343a40; }
    
    /* Card Modern Style */
    .card-modern { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.04); background: #fff; margin-bottom: 20px; overflow: hidden; }
    .card-header-modern { background: #fff; border-bottom: 1px solid #f0f2f5; padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    
    .page-title { font-size: 1.1rem; font-weight: 700; color: #111827; margin: 0; }
    .page-subtitle { font-size: 0.85rem; color: #6b7280; margin-top: 4px; }
    .badge-user { background-color: #eef2ff; color: #4338ca; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; }

    /* Table Styling */
    .table thead th { background-color: #f9fafb; color: #374151; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e5e7eb !important; padding: 12px 15px; }
    .table tbody td { vertical-align: middle; padding: 12px 15px; font-size: 0.9rem; border-bottom: 1px solid #f3f4f6; }
    
    /* Tombol Header */
    .btn-custom { border-radius: 8px; font-weight: 500; font-size: 0.875rem; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; text-decoration: none; }
    .btn-primary-soft { background: #4f46e5; color: white; border: none; } .btn-primary-soft:hover { background: #4338ca; color: white; transform: translateY(-1px); }
    .btn-success-soft { background: #10b981; color: white; border: none; } .btn-success-soft:hover { background: #059669; color: white; transform: translateY(-1px); }
    .btn-light-soft { background: #fff; border: 1px solid #d1d5db; color: #374151; } .btn-light-soft:hover { background: #f9fafb; border-color: #9ca3af; }

    /* Tombol Edit Standard Bootstrap */
    .btn-edit-action { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }

    @media (max-width: 576px) { .dataTables_filter input { width: 120px !important; font-size: 0.8rem; } .dataTables_length select { font-size: 0.8rem; } .btn-custom { padding: 6px 12px; font-size: 0.8rem; } }
    .dataTables_wrapper .row { margin-bottom: 10px; padding: 0 15px; }
  </style>
</head>
<body>

<div class="container-fluid py-4">
  <div class="card card-modern">
    <div class="card-header-modern">
      <div>
        <div class="d-flex align-items-center gap-2"><h4 class="page-title"><i class="fas fa-graduation-cap text-primary me-2"></i>Daftar Pendidikan Pegawai</h4></div>
        <?php if($pegawai): ?>
            <div class="mt-2"><span class="badge-user"><i class="fas fa-user-circle"></i> <?= e($pegawai['nama']) ?> <span class="opacity-75 fw-normal">(<?= e($pegawai['id_peg']) ?>)</span></span></div>
        <?php else: ?>
            <div class="page-subtitle">Menampilkan seluruh data riwayat pendidikan pegawai.</div>
        <?php endif; ?>
      </div>
      <div class="d-flex gap-2">
        <a href="home-admin.php" class="btn-custom btn-light-soft"><i class="fas fa-home"></i></a>
        <a href="home-admin.php?page=form-import-data-pendidikan" class="btn-custom btn-success-soft"><i class="fas fa-file-excel"></i> Import</a>
        <a href="home-admin.php?page=form-master-data-pendidikan<?php echo $uid?'&uid='.urlencode($uid):''; ?>" class="btn-custom btn-primary-soft"><i class="fas fa-plus"></i> Tambah Data</a>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive pt-2">
        <table id="tblPend" class="table table-hover w-100">
          <thead>
            <tr>
              <th width="5%" class="text-center">No</th>
              <th>Nama Pegawai</th>
              <th>Jenjang</th>
              <th>Nama Sekolah</th>
              <th>Jurusan</th>
              <th>Lulus</th>
              <th>No Ijazah</th>
              <th>Tgl Ijazah</th>
              <th width="8%" class="text-center">Aksi</th>
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
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function(){
  $('#tblPend').DataTable({
    processing: true, serverSide: true, searching: true, responsive: true,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
    ajax: { 
        url: 'pages/ref-pendidikan/ajax-data-pendidikan.php', 
        type: 'GET', 
        data: { uid: <?php echo json_encode($uid); ?> } 
    },
    columns: [
      { data: 'no', orderable:false, className: 'text-center fw-bold text-secondary' },
      { data: 'idpeg_nama', className: 'fw-bold text-dark' },
      { data: 'jenjang', className: 'text-center' },
      { data: 'nama_sekolah' },
      { data: 'jurusan' },
      { data: 'th_lulus', className: 'text-center' },
      { data: 'no_ijazah' },
      { data: 'tgl_ijazah' },
      // KOLOM AKSI (EDIT)
      { 
        data: null, orderable: false, className: 'text-center',
        render: function(data, type, row) {
            var idPend = row.id_pendidikan; 
            var uidPeg = row.id_peg; 
            
            if(idPend) {
                return `
                    <a href="home-admin.php?page=form-master-data-pendidikan&mode=edit&id=${idPend}&uid=${uidPeg}" 
                       class="btn btn-primary btn-sm btn-edit-action" 
                       title="Edit Data" 
                       data-bs-toggle="tooltip">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                `;
            }
            return '-';
        }
      }
    ],
    language: { search: "", searchPlaceholder: "Cari data...", lengthMenu: "_MENU_", info: "Menampilkan _START_ - _END_ dari _TOTAL_ data", paginate: { first: "«", last: "»", next: "›", previous: "‹" }, processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"></div> Memuat...' },
    dom: "<'row px-3 pt-3 align-items-center'<'col-6 col-md-6'l><'col-6 col-md-6'f>>" + "<'row px-3'<'col-sm-12'tr>>" + "<'row px-3 pb-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
  });
});
</script>
</body>
</html>