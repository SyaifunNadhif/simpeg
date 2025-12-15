<?php
/*********************************************************
 * FILE    : pages/ref-keluarga/form-view-data-suami-istri.php
 * MODULE  : SIMPEG — Data Pasangan (Modern View Clean)
 * VERSION : v2.5 (Fix Edit Button - parse id_si from aksi HTML if needed)
 *********************************************************/

if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Get UID jika ada (Filter per pegawai)
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
  <title>Daftar Pasangan Pegawai</title>
  
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
    .badge-user { background-color: #eef2ff; color: #4338ca; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; }
    .table thead th { background-color: #f9fafb; color: #374151; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e5e7eb !important; padding: 12px 15px; }
    .table tbody td { vertical-align: middle; padding: 12px 15px; font-size: 0.9rem; border-bottom: 1px solid #f3f4f6; }
    .btn-custom { border-radius: 8px; font-weight: 500; font-size: 0.875rem; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; text-decoration: none; }
    .btn-primary-soft { background: #4f46e5; color: white; border: none; } .btn-primary-soft:hover { background: #4338ca; color: white; transform: translateY(-1px); }
    .btn-success-soft { background: #10b981; color: white; border: none; } .btn-success-soft:hover { background: #059669; color: white; transform: translateY(-1px); }
    .btn-light-soft { background: #fff; border: 1px solid #d1d5db; color: #374151; } .btn-light-soft:hover { background: #f9fafb; border-color: #9ca3af; }
    @media (max-width: 576px) { .dataTables_filter input { width: 120px !important; font-size: 0.8rem; } .dataTables_length select { font-size: 0.8rem; } .btn-custom { padding: 6px 12px; font-size: 0.8rem; } }
    .dataTables_wrapper .row { margin-bottom: 10px; padding: 0 15px; }
  </style>
</head>
<body>

<div class="container-fluid py-4">
  <div class="card card-modern">
    <div class="card-header-modern">
      <div>
        <div class="d-flex align-items-center gap-2"><h4 class="page-title"><i class="fas fa-venus-mars text-primary me-2"></i>Daftar Pasangan Pegawai</h4></div>
        <?php if($pegawai): ?>
            <div class="mt-2"><span class="badge-user"><i class="fas fa-user-circle"></i> <?= e($pegawai['nama']) ?> <span class="opacity-75 fw-normal">(<?= e($pegawai['id_peg']) ?>)</span></span></div>
        <?php else: ?>
            <div class="page-subtitle">Menampilkan seluruh data pasangan (Suami/Istri) dari pegawai aktif.</div>
        <?php endif; ?>
      </div>
      <div class="d-flex gap-2">
        <a href="home-admin.php" class="btn-custom btn-light-soft" title="Beranda"><i class="fas fa-home"></i></a>
        <a href="home-admin.php?page=form-import-data-pasangan" class="btn-custom btn-success-soft"><i class="fas fa-file-excel"></i> Import</a>
        <a href="home-admin.php?page=form-master-data-suami-istri<?php echo $uid? '&uid='.urlencode($uid):''; ?>" class="btn-custom btn-primary-soft"><i class="fas fa-plus"></i> Tambah Data</a>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive pt-2">
        <table id="tblPasangan" class="table table-hover w-100">
          <thead>
            <tr>
              <th width="5%" class="text-center">No</th>
              <th>Nama Pegawai</th>
              <th>Nama Pasangan</th>
              <th>NIK</th>
              <th>Pendidikan</th>
              <th>Pekerjaan</th>
              <th>Status Hub</th>
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

  // ambil id_si dari beberapa sumber:
  // 1) row.id_si / row.id
  // 2) parse dari row.aksi HTML -> cari id_si=VALUE (preserve leading zeros)
  function extractIdSi(row) {
    if (!row) return null;
    // direct fields
    if (row.id_si && String(row.id_si).trim() !== '') return String(row.id_si);
    if (row.ID_SI && String(row.ID_SI).trim() !== '') return String(row.ID_SI);
    if (row.id && String(row.id).trim() !== '') return String(row.id);

    // check nested
    if (row.data && row.data.id_si) return String(row.data.id_si);
    if (row.DT_RowData && row.DT_RowData.id_si) return String(row.DT_RowData.id_si);

    // parse from aksi HTML if present
    if (row.aksi && typeof row.aksi === 'string') {
        // contoh: ... id_si=00001096 ...
        var re = /[?&]id_si=([0-9A-Za-z\-]+)/i;
        var m = row.aksi.match(re);
        if (m && m[1]) return String(m[1]);
        // kadang ada tanpa ? (just &id_si=...), so try more general
        var re2 = /id_si=([0-9A-Za-z\-]+)/i;
        m = row.aksi.match(re2);
        if (m && m[1]) return String(m[1]);
    }

    // nothing found
    return null;
  }

  var table = $('#tblPasangan').DataTable({
    processing: true,
    serverSide: true,
    searching: true,
    responsive: true,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
    ajax: { 
        url: 'pages/ref-keluarga/ajax-data-pasangan.php', 
        type: 'GET', 
        data: { uid: <?php echo json_encode($uid); ?> },
        dataSrc: function(json){
            // debug only: tampilkan 1 sample row agar bisa dilihat di console
            try {
                if (json && json.data && json.data.length) {
                    console.info('AJAX sample row:', json.data[0]);
                } else {
                    console.info('AJAX response (no data):', json);
                }
            } catch(e) {}
            return json.data || [];
        }
    },
    columns: [
      { data: 'no', orderable:false, className: 'text-center fw-bold text-secondary' },
      { data: 'nama_peg', className: 'fw-bold text-dark', render: function(data, type, row) { if(row.id_peg && data) return `<div>${data}</div><small class='text-muted'>${row.id_peg}</small>`; return data || '-'; } },
      { data: 'nama', className: 'text-primary fw-medium', defaultContent: '-' },
      { data: 'nik', defaultContent: '-' },
      { data: 'pendidikan', defaultContent: '-' },
      { data: 'pekerjaan_desc', defaultContent: '-' },
      { data: 'status_hub', render: function(data) {
            var txt = data?data:'-';
            var cls = 'bg-light text-dark border';
            if(data && data.toLowerCase() === 'suami') cls = 'bg-info bg-opacity-10 text-info border-info border-opacity-25';
            else if(data && data.toLowerCase() === 'istri') cls = 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
            return '<span class="badge '+cls+' px-2 py-1 rounded-pill">'+txt+'</span>';
        }, defaultContent: '-' 
      },
      { 
        data: null, orderable: false, className: 'text-center',
        render: function(data, type, row) {
            // ambil id_si dari row (direct atau parse dari aksi)
            var idSi = extractIdSi(row);
            // ambil uid dari id_peg (user requested)
            var uidPeg = row.id_peg || row.ID_PEG || (row.data && row.data.id_peg) || '';

            if (!idSi) {
                console.warn('Tidak menemukan id_si pada row:', row);
                return '<span class="text-muted">-</span>';
            }

            // pastikan string supaya leading zero aman
            idSi = String(idSi);
            uidPeg = String(uidPeg || '');

            var href = 'home-admin.php?page=form-master-data-suami-istri&mode=edit&id_si=' + encodeURIComponent(idSi) + '&uid=' + encodeURIComponent(uidPeg);

            // tombol edit kecil (bisa disesuaikan style)
            return '<a href="' + href + '" class="btn btn-primary btn-sm rounded-circle d-inline-flex justify-content-center align-items-center" style="width:34px;height:34px;" title="Edit Data"><i class="fas fa-pencil-alt" style="font-size:13px;"></i></a>';
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
