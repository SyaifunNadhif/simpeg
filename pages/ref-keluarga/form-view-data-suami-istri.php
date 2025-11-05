<?php
/*********************************************************
 * FILE    : pages/ref-keluarga/form-view-data-suami-istri.php
 * MODULE  : SIMPEG — Data Pasangan (Listing)
 * VERSION : v1.2 (PHP 5.6 compatible)
 * DATE    : 2025-10-11
 * CHANGELOG
 * - v1.2: Diseragamkan dengan form-view-data-anak (HTML full, card header gradient,
 *         tombol berjarak, DataTables server-side via ajax, dukung filter uid).
 * - v1.1.1: Path koneksi disederhanakan include "dist/koneksi.php"; changelog + badge versi.
 * - v1.1:  Standardisasi header/breadcrumb, join master pekerjaan, pagination biru.
 *********************************************************/
?>
<!DOCTYPE html>
<html lang="id">
<head>
<?php
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
// Kompat: jika proyek lama masih pakai $conn, sinkronkan ke $koneksi
if (!isset($koneksi) && isset($conn)) { $koneksi = $conn; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$pegawai = null;
if ($uid!==''){
  $q = @mysqli_query($koneksi, "SELECT id_peg, nama FROM tb_pegawai WHERE id_peg='".mysqli_real_escape_string($koneksi,$uid)."' LIMIT 1");
  if ($q && mysqli_num_rows($q)>0){ $pegawai = mysqli_fetch_assoc($q); }
}
?>
  <meta charset="utf-8">
  <title>Daftar Pasangan Pegawai</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <style>
    .card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}
    .card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}
    .btn+.btn{margin-left:.5rem}
  </style>
</head>
<body>
<div class="container-fluid mt-3">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">Daftar Pasangan Pegawai</h5>
        <small>Menampilkan seluruh data pasangan<?php echo $pegawai? ' — <b>'.e($pegawai['nama']).'</b> ('.e($pegawai['id_peg']).')' : ''; ?></small>
      </div>
      <div class="ml-auto d-flex">
        <a href="home-admin.php" class="btn btn-light"><i class="fa fa-home"></i> Dashboard</a>
        <a href="home-admin.php?page=form-master-data-suami-istri<?php echo $uid? '&uid='.urlencode($uid):''; ?>" class="btn btn-warning">Tambah Data</a>
        <a href="home-admin.php?page=form-import-data-pasangan" class="btn btn-success">Impor Kolektif</a>
      </div>
    </div>
    <div class="card-body">
      <table id="tblPasangan" class="display table table-striped" style="width:100%">
        <thead>
          <tr>
            <th>No</th>
            <th>ID Peg</th>
            <th>Nama Pegawai</th>
            <th>Nama Pasangan</th>
            <th>NIK</th>
            <th>Pendidikan</th>
            <th>Pekerjaan</th>
            <th>Status Hub</th>
            <th>Aksi</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(function(){
  $('#tblPasangan').DataTable({
    processing: true, serverSide: true, searching: true,
    ajax: { url: 'pages/ref-keluarga/ajax-data-pasangan.php', type: 'GET', data: { uid: <?php echo json_encode($uid); ?> } },
    columns: [
      { data: 'no', orderable:false },
      { data: 'id_peg' },
      { data: 'nama_peg' },
      { data: 'nama' },
      { data: 'nik' },
      { data: 'pendidikan' },
      { data: 'pekerjaan_desc' },
      { data: 'status_hub' },
      { data: 'aksi', orderable:false }
    ],
    order: [[2,'asc']]
  });
});
</script>
</body>
</html>
