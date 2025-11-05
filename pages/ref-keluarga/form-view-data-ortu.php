<?php
/*********************************************************
 * DIR     : pages/ref-keluarga/form-view-data-ortu.php
 * MODULE  : SIMPEG — Data Orang Tua Pegawai (tb_ortu)
 * VERSION : v1.3 (PHP 5.6 compatible)
 * DATE    : 2025-09-06
 * AUTHOR  : EWS/SIMPEG BKK Jateng — by ChatGPT
 *
 * PURPOSE :
 *   - Daftar orang tua pegawai (server-side DataTables)
 *   - Mendukung konteks per pegawai via parameter ?uid=...
 *   - Aksi "Tambah Data" & "Edit" selalu membawa uid agar relasi terjaga
 *
 * CHANGELOG
 * - v1.3 (2025-09-06)
 *   • Tambah dukungan konteks pegawai (GET uid) + header identitas pegawai.
 *   • Tombol Tambah/Edit menyertakan uid; Ajax DT juga mengirim uid.
 *   • Perapihan CSS (pagination biru #3b82f6) sesuai guideline global.
 *   • Perbaikan include koneksi ke ../../config/koneksi.php (standar proyek).
 * - v1.2 (2025-09-05)
 *   • Server-side DataTables + filter ID pegawai & status hubungan.
 *   • Komponen UI dirapikan (card, gradient header).
 * - v1.1 (2025-09-03)
 *   • Versi awal tampilan daftar ortu.
 *********************************************************/
if (session_id()==='') session_start();
require_once __DIR__ . '/../../dist/koneksi.php'; // gunakan standar proyek
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function getv($k,$d=''){ return isset($_GET[$k]) ? trim($_GET[$k]) : $d; }

// --- Parameter konteks pegawai ---
$uid = getv('uid','');
$peg = null; $idpeg_code='';
if ($uid !== '') {
  $sql = "SELECT pegawai_uid, id_peg_code, nama_lengkap, jk, unit_kerja FROM tb_pegawai
          WHERE pegawai_uid='".mysqli_real_escape_string($conn,$uid)."' LIMIT 1";
  $q = mysqli_query($conn,$sql);
  if ($q && mysqli_num_rows($q)===1){
    $peg = mysqli_fetch_assoc($q);
    $idpeg_code = $peg['id_peg_code'];
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Orang Tua</title>

  <style>
    body{background:#f7f8fb}
    .page-wrap{max-width:1200px;margin:18px auto}
    .card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06);background:#fff}
    .card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0;padding:14px 18px}
    .card-header .btn{margin-left:8px}
    .badge{display:inline-block;padding:3px 8px;border-radius:999px;font-size:12px;background:#eef2ff;color:#1e40af}
    .muted{color:#6b7280;font-size:12px}
    .form-inline .form-control{min-width:220px}
    /* Pagination style konsisten (aktif biru) */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
      background:#3b82f6 !important; color:#fff !important; border:1px solid #3b82f6 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
      color:#111 !important; border:1px solid #d1d5db !important; background:#f3f4f6 !important;
    }
  </style>
</head>
<body>
<div class="page-wrap">
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <div class="mr-auto">
        <h5 class="mb-0">Data Orang Tua</h5>
        <small>Daftar orang tua pegawai berdasarkan entri di tabel <b>tb_ortu</b>.</small>
        <?php if ($peg): ?>
          <div class="mt-1">
            <span class="badge">Pegawai: <?php echo e($peg['nama_lengkap']); ?> (<?php echo e($peg['id_peg_code']); ?>)</span>
            <span class="muted">— <?php echo e($peg['unit_kerja']); ?> · <?php echo e($peg['jk']); ?></span>
          </div>
        <?php endif; ?>
      </div>
      <div class="ml-auto d-flex">
        <a href="home-admin.php" class="btn btn-light btn-sm me-1"><i class="fa fa-home"></i> Dashboard</a>
        <?php if ($uid!==''): ?>
          <a href="home-admin.php?page=form-master-data-ortu&mode=add&uid=<?php echo urlencode($uid); ?>" class="btn btn-warning btn-sm">Tambah Data</a>
        <?php else: ?>
          <a href="home-admin.php?page=form-master-data-ortu&mode=add" class="btn btn-warning btn-sm">Tambah Data</a>
        <?php endif; ?>
        <a href="home-admin.php?page=form-import-data-ortu" class="btn btn-success btn-sm">Impor Kolektif</a>
      </div>
    </div>

    <div class="card-body" style="padding:18px">
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="small muted">Filter ID Pegawai</label>
          <input type="text" id="filterIdPeg" class="form-control" placeholder="cth: 135-001" value="<?php echo e($idpeg_code); ?>" <?php echo $uid!==''?'readonly':''; ?>>
        </div>
        <div class="col-md-6">
          <label class="small muted">Status Hubungan</label>
          <select id="filterStatus" class="form-control">
            <option value="">— semua —</option>
            <option>Ayah Kandung</option>
            <option>Ibu Kandung</option>
            <option>Ayah Tiri</option>
            <option>Ibu Tiri</option>
          </select>
        </div>
      </div>

      <div class="table-responsive">
        <table id="tblOrtu" class="table table-striped table-hover" style="width:100%">
          <thead>
            <tr>
              <th>No</th>
              <th>ID Pegawai</th>
              <th>Nama Pegawai</th>
              <th>Status Hub</th>
              <th>Nama Ortu</th>
              <th>NIK</th>
              <th>TTL</th>
              <th>Pendidikan</th>
              <th>Pekerjaan</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- DataTables (CDN) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(function(){
  var table = $('#tblOrtu').DataTable({
    processing:true,
    serverSide:true,
    ajax:{
      url: 'pages/ref-keluarga/ajax-data-ortu.php',
      type:'GET',
      data: function(d){
        d.filter_idpeg  = $('#filterIdPeg').val() || '';
        d.filter_status = $('#filterStatus').val() || '';
        d.uid           = <?php echo json_encode($uid); ?>; // penting: kirim uid ke server
      }
    },
    columns:[
      { data:null, orderable:false, render:function(d,t,r,m){ return m.row + m.settings._iDisplayStart + 1; } },
      { data:'id_peg' },
      { data:'nama_peg' },
      { data:'status_hub' },
      { data:'nama_ortu' },
      { data:'nik' },
      { data:'ttl' },
      { data:'pendidikan' },
      { data:'pekerjaan' },
      { data:'action', orderable:false }
    ],
    order:[[1,'asc']],
    pageLength:10,
    language: {
      // fallback: pakai English bila file i18n lokal tidak ada
      url: 'plugins/datatables/i18n/Indonesian.json'
    }
  });

  $('#filterIdPeg, #filterStatus').on('keyup change', function(){ table.ajax.reload(); });
});
</script>
</body>
</html>
