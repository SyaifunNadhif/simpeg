<?php
/*********************************************************
 * FILE    : pages/ref-jabatan/form-view-data-jabatan.php
 * MODULE  : SIMPEG — Daftar Jabatan (Aktif)
 * VERSION : v1.0 (PHP 5.6 compatible)
 * DATE    : 2025-09-05
 * AUTHOR  : EWS/SIMPEG BKK Jateng — by ChatGPT
 *
 * PURPOSE :
 *   - Menampilkan daftar jabatan pegawai yang statusnya "Aktif".
 *   - Dilengkapi filter cepat (Unit Kerja & Jabatan), pencarian, dan DataTables.
 *   - Aksi cepat: Lihat Profil Pegawai, Entry Jabatan baru (redirect ke form jabatan).
 *
 * CHANGELOG :
 * - v1.0 (2025-09-05) — Rilis awal: tabel jabatan aktif + filter + tombol aksi.
 *
 * CATATAN :
 * - Menggunakan variabel koneksi $conn (mysqli). Jika project Anda memakai $koneksi, ubah semua referensi $conn.
 * - Sesuaikan path asset DataTables dengan yang sudah dimuat di layout Atlantis.
 *********************************************************/

if (session_id()==='') session_start();
// Koneksi & util
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; }

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function fmt_date($s){ return ($s && $s!='0000-00-00') ? date('d-m-Y', strtotime($s)) : '-'; }

$today = date('Y-m-d');

// ====== Ambil data filter (unit kerja & jabatan) ======
$units = array();
$rsUnit = mysqli_query($conn, "SELECT DISTINCT k.kode_kantor_detail, k.nama_kantor
             FROM tb_jabatan j
             LEFT JOIN tb_kantor k ON k.kode_kantor_detail=j.unit_kerja
             WHERE j.status_jab='Aktif' AND k.nama_kantor IS NOT NULL
             ORDER BY k.nama_kantor");
if ($rsUnit) { while($r=mysqli_fetch_assoc($rsUnit)){ $units[] = $r['nama_kantor']; } }

$jabs = array();
$rsJab = mysqli_query($conn, "SELECT DISTINCT jabatan FROM tb_jabatan WHERE status_jab='Aktif' AND jabatan IS NOT NULL AND jabatan<>'' ORDER BY jabatan");
if ($rsJab) { while($r=mysqli_fetch_assoc($rsJab)){ $jabs[] = $r['jabatan']; } }

// ====== Query daftar jabatan aktif (join pegawai untuk nama) ======
$sql = "
SELECT j.id_jab, j.id_peg, p.nama, j.kode_jabatan, j.jabatan, j.unit_kerja,
       j.tmt_jabatan, j.sampai_tgl, j.no_sk, j.tgl_sk, j.status_jab
FROM tb_jabatan j
LEFT JOIN tb_pegawai p ON p.id_peg = j.id_peg
WHERE j.status_jab='Aktif'
ORDER BY j.unit_kerja, j.jabatan, p.nama
";
$q = mysqli_query($conn, $sql);
$rows = array();
if ($q) {
  while($r = mysqli_fetch_assoc($q)) { $rows[] = $r; }
} else {
  $err = mysqli_error($conn);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Jabatan Aktif</title>
  <!-- Asumsikan layout utama sudah meload Bootstrap & DataTables. Jika belum, aktifkan baris di bawah sesuai aset Anda. -->
  <!--
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/js/plugin/datatables/datatables.min.css">
  <script src="assets/js/core/jquery.3.2.1.min.js"></script>
  <script src="assets/js/plugin/datatables/datatables.min.js"></script>
  -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .page-wrap{max-width:1200px;margin:18px auto}
    .card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}
    .card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}
    .card-body{background:#fff}
    .filter-bar .form-control{min-width:200px}
    /* Kustom pagination (sesuai preferensi proyek) */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
      background: #3b82f6 !important; border-color: #3b82f6 !important; color:#fff !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
      border-radius: 8px; margin: 0 2px; border:1px solid #e5e7eb; padding:2px 8px;
    }
    .table thead th{white-space:nowrap}
    .aksi-btn .btn{margin-right:6px}
  </style>
</head>
<body>
<div class="page-wrap">
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <div class="mr-auto">
        <h5 class="mb-0">Daftar Jabatan Aktif</h5>
        <small>Menampilkan seluruh jabatan pegawai yang berstatus <b>Aktif</b></small>
      </div>
      <div class="ml-auto d-flex">
        <a href="home-admin.php" class="btn btn-light btn-sm me-1"><i class="fa fa-home"></i> Dashboard</a>
        <a href="home-admin.php?page=form-master-data-ortu&mode=add" class="btn btn-warning btn-sm me-2">Tambah Data</a>
        <a href="home-admin.php?page=form-import-jabatan" class="btn btn-success btn-sm me-2">Impor Kolektif</a>
      </div>
    </div>
    <div class="card-body">

      <?php if (isset($err)): ?>
        <div class="alert alert-danger">Query error: <?php echo e($err); ?></div>
      <?php endif; ?>

      <div class="filter-bar mb-3">
        <div class="row g-2">
          <div class="col-md-4">
            <label class="small text-muted">Filter Unit Kerja</label>
            <select id="filterUnit" class="form-control select2" style="width:100%">
              <option value="">— semua unit —</option>
              <?php foreach($units as $u): ?>
                <option value="<?php echo e($u); ?>"><?php echo e($u); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="small text-muted">Filter Jabatan</label>
            <select id="filterJabatan" class="form-control select2" style="width:100%">
              <option value="">— semua jabatan —</option>
              <?php foreach($jabs as $j): ?>
                <option value="<?php echo e($j); ?>"><?php echo e($j); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table id="tblJabatan" class="table table-striped table-hover">
          <thead>
            <tr>
              <th>No</th>
              <th>ID Pegawai</th>
              <th>Nama</th>
              <th>Kode</th>
              <th>Jabatan</th>
              <th>Unit Kerja</th>
              <th>TMT</th>
              <th>Sampai</th>
              <th>No SK</th>
              <th>Tgl SK</th>
              <th>Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php $no=1; foreach($rows as $r): ?>
            <tr>
              <td><?php echo $no++; ?></td>
              <td><?php echo e($r['id_peg']); ?></td>
              <td><?php echo e($r['nama'] ? $r['nama'] : '-'); ?></td>
              <td><?php echo e($r['kode_jabatan']); ?></td>
              <td><?php echo e($r['jabatan']); ?></td>
              <td><?php echo e($r['unit_kerja']); ?></td>
              <td><?php echo e(fmt_date($r['tmt_jabatan'])); ?></td>
              <td><?php echo e(fmt_date($r['sampai_tgl'])); ?></td>
              <td><?php echo e($r['no_sk']); ?></td>
              <td><?php echo e(fmt_date($r['tgl_sk'])); ?></td>
              <td><span class="badge badge-success">Aktif</span></td>
              <td class="aksi-btn text-center">
                <a class="btn btn-xs btn-outline-info" title="Profil Pegawai" href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?php echo e($r['id_peg']); ?>">
                  <i class="fa fa-user"></i>
                </a>
                <a class="btn btn-xs btn-outline-primary" title="Entry Jabatan Baru" href="home-admin.php?page=form-master-data-jabatan&uid=<?php echo e($r['id_peg']); ?>">
                  <i class="fa fa-briefcase"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="assets/js/jabatan-tables.init.js"></script>

<script>
$(function(){
  // aktifkan select2
  $('.select2').select2({ theme: 'bootstrap4', placeholder: 'Pilih', allowClear: true });

  // inisialisasi DataTable serverSide
  var table = $('#tblJabatan').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: 'pages/ref-jabatan/ajax-jabatan-aktif.php',
      type: 'GET',
      data: function(d){
        d.filter_unit = $('#filterUnit').val() || '';
        d.filter_jab  = $('#filterJabatan').val() || '';
      }
    },
    columns: [
      { data: null, orderable:false, render: function(data, type, row, meta){ return meta.row + meta.settings._iDisplayStart + 1; }},
      { data: 'id_peg' }, { data: 'nama' }, { data: 'kode_jabatan' },
      { data: 'jabatan' }, { data: 'unit_kerja' },
      { data: 'tmt_jabatan' }, { data: 'sampai_tgl' }, { data: 'no_sk' },
      { data: 'tgl_sk' }, { data: 'status', orderable:false }, { data: 'action', orderable:false }
    ],
    order: [[5,'asc'],[4,'asc']],
    pageLength: 10,
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'Semua']],
    language: { url: 'assets/js/plugin/datatables/i18n/Indonesian.json' }
  });

  // reload ketika filter berubah (select2 menembakkan 'change')
  $('#filterUnit, #filterJabatan').on('change', function(){ table.ajax.reload(); });
});
</script>



</body>
</html>
