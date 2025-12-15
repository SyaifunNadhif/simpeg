<?php
/*********************************************************
 * FILE    : pages/ref-keluarga/form-view-data-ortu.php
 * MODULE  : SIMPEG — View Data Orang Tua (Fixed UI)
 *********************************************************/

if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Ambil parameter UID
$uid = isset($_GET['uid']) ? trim($_GET['uid']) : '';

// Lookup Data Pegawai untuk Header (Opsional)
$pegawai_nama = '';
if ($uid !== '' && $conn) {
    $uid_safe = mysqli_real_escape_string($conn, $uid);
    // Coba cari nama pegawai berdasarkan id_peg atau id_peg_code
    // Pastikan kolom 'nama' disesuaikan jika di DB anda namanya 'nama_lengkap'
    $col_nama = 'nama'; 
    $check = mysqli_query($conn, "SHOW COLUMNS FROM tb_pegawai LIKE 'nama_lengkap'");
    if($check && mysqli_num_rows($check)>0) $col_nama = 'nama_lengkap';

    $q = mysqli_query($conn, "SELECT $col_nama as nama FROM tb_pegawai WHERE id_peg='$uid_safe' OR pegawai_uid='$uid_safe' LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        $d = mysqli_fetch_assoc($q);
        $pegawai_nama = $d['nama'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Data Orang Tua Pegawai</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { background-color: #f4f6f9; font-family: 'Inter', sans-serif; color: #343a40; }
    .card-modern { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.04); background: #fff; margin-bottom: 20px; overflow: hidden; }
    .card-header-modern { background: #fff; border-bottom: 1px solid #f0f2f5; padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .page-title { font-size: 1.1rem; font-weight: 700; color: #111827; margin: 0; }
    .page-subtitle { font-size: 0.85rem; color: #6b7280; margin-top: 4px; }
    .badge-user { background-color: #eef2ff; color: #4338ca; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; border: 1px solid #c7d2fe; }
    .table thead th { background-color: #f9fafb; color: #374151; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e5e7eb !important; padding: 12px 15px; }
    .table tbody td { vertical-align: middle; padding: 12px 15px; font-size: 0.9rem; border-bottom: 1px solid #f3f4f6; }
    .btn-custom { border-radius: 8px; font-weight: 500; font-size: 0.875rem; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; text-decoration: none; }
    .btn-primary-soft { background: #4f46e5; color: white; border: none; } .btn-primary-soft:hover { background: #4338ca; color: white; }
    .btn-success-soft { background: #10b981; color: white; border: none; }
    .btn-light-soft { background: #fff; border: 1px solid #d1d5db; color: #374151; }
  </style>
</head>
<body>

<div class="container-fluid py-4">
  <div class="card card-modern">
    <div class="card-header-modern">
      <div>
        <div class="d-flex align-items-center gap-2"><h4 class="page-title"><i class="fas fa-user-friends text-primary me-2"></i>Data Orang Tua Pegawai</h4></div>
        
        <?php if ($uid !== ''): ?>
            <div class="mt-2">
              <span class="badge-user">
                <i class="fas fa-id-badge"></i>
                <?php 
                  // Tampilkan NAMA jika ada, jika tidak UID saja
                  if($pegawai_nama) echo e($pegawai_nama) . ' <span class="opacity-75 ms-1 fw-normal">('.e($uid).')</span>';
                  else echo 'Pegawai: ' . e($uid);
                ?>
              </span>
            </div>
            <div class="page-subtitle">Menampilkan seluruh data orang tua dari pegawai terpilih.</div>
        <?php else: ?>
            <div class="page-subtitle">Menampilkan seluruh data orang tua dari semua pegawai aktif.</div>
        <?php endif; ?>
      </div>

      <div class="d-flex gap-2">
        <a href="home-admin.php" class="btn-custom btn-light-soft"><i class="fas fa-home"></i></a>
        <a href="home-admin.php?page=form-import-data-ortu" class="btn-custom btn-success-soft"><i class="fas fa-file-excel"></i> Import</a>
        <a href="home-admin.php?page=form-master-data-ortu&mode=add<?= $uid? '&uid='.urlencode($uid):''; ?>" class="btn-custom btn-primary-soft"><i class="fas fa-plus"></i> Tambah Data</a>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive pt-2">
        <table id="tblOrtu" class="table table-hover w-100">
          <thead>
            <tr>
              <th width="5%" class="text-center">No</th>
              <th width="20%">Nama Pegawai</th>
              <th width="20%">Nama Orang Tua</th>
              <th width="10%">Status</th>
              <th>NIK</th>
              <th>TTL</th>
              <th>Pekerjaan</th>
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
  var uidParam = "<?= $uid ?>";

  $('#tblOrtu').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: {
      url: 'pages/ref-keluarga/ajax-data-ortu.php', // Pastikan path ini benar sesuai struktur folder Anda
      type: 'GET',
      data: function(d){
         d.uid = uidParam;
      }
    },
    columns: [
      { 
        data: null, orderable:false, className: 'text-center fw-bold text-secondary', 
        render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } 
      },

      // KOLOM NAMA PEGAWAI (FIXED)
      { 
        data: 'nama_peg', 
        className: 'fw-bold text-dark', 
        render: function(data, type, row) {
            var nama = data || '-'; // ambil dari key nama_peg
            var id = row.id_peg || '-';
            if(nama === '-') return '<span class="text-muted fst-italic">Tidak diketahui</span>';
            return '<div>'+nama+'</div><small class="text-muted">'+id+'</small>';
        } 
      },

      // KOLOM NAMA ORTU (FIXED)
      { 
        data: 'nama_ortu', 
        className: 'text-primary fw-medium',
        render: function(data) { return data || '-'; }
      },

      { 
        data: 'status_hub', 
        render: function(data) {
           var txt = data || '-';
           var cls = 'bg-light text-dark border';
           if(txt.toLowerCase().includes('ayah')) cls = 'bg-info bg-opacity-10 text-info border-info border-opacity-25';
           if(txt.toLowerCase().includes('ibu')) cls = 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
           return '<span class="badge '+cls+' px-2 py-1 rounded-pill">'+txt+'</span>';
        }
      },

      { data: 'nik', defaultContent: '-' },
      { data: 'ttl', defaultContent: '-' },
      { data: 'pekerjaan', defaultContent: '-' },

      { 
        data: null, orderable: false, className: 'text-center',
        render: function(data, type, row) {
            var id = row.id_ortu;
            var uidRaw = row.uid_raw || '';
            var editUrl = 'home-admin.php?page=form-master-data-ortu&mode=edit&id_ortu=' + id + '&uid=' + uidRaw;
            return '<a href="'+editUrl+'" class="btn btn-primary btn-sm btn-edit-action" title="Edit Data"><i class="fas fa-pencil-alt"></i></a>';
        }
      }
    ],
    language: {
      search: "", 
      searchPlaceholder: "Cari data...", 
      processing: '<div class="spinner-border text-primary spinner-border-sm"></div> Memuat...' 
    },
    dom: "<'row px-3 pt-3 align-items-center'<'col-6 col-md-6'l><'col-6 col-md-6'f>>" + "<'row px-3'<'col-sm-12'tr>>" + "<'row px-3 pb-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
  });
});
</script>
</body>
</html>