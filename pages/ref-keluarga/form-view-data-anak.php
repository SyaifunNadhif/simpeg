<?php
/*********************************************************
 * FILE    : pages/keluarga/form-view-data-anak.php
 * MODULE  : SIMPEG — Data Anak (Listing)
 * VERSION : v1.1 (PHP 5.6 compatible)
 * DATE    : 2025-09-07
 * CHANGELOG
 * - v1.1: Card header, tombol berjarak, DataTables server-side.
 * - v1.0: Listing awal dengan filter uid & tombol aksi.
 *********************************************************/
?>
<!DOCTYPE html>
<html lang="id">
<head>
<?php
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
  <meta charset="utf-8">
  <title>Daftar Anak Pegawai</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <style>.card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}.card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}</style>
</head>
<body>
<div class="container-fluid mt-3">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">Daftar Anak Pegawai</h5>
        <small>Menampilkan seluruh data anak<?php echo $pegawai? ' — <b>'.e($pegawai['nama']).'</b> ('.e($pegawai['id_peg']).')' : ''; ?></small>
      </div>
      <div class="ml-auto d-flex">
        <a href="home-admin.php" class="btn btn-light me-2"><i class="fa fa-home"></i> Dashboard</a>
        <a href="home-admin.php?page=form-master-data-anak<?php echo $uid? '&uid='.urlencode($uid):''; ?>" class="btn btn-warning me-2">Tambah Data</a>
        <a href="home-admin.php?page=form-import-data-anak" class="btn btn-success">Impor Kolektif</a>
      </div>
    </div>
    <div class="card-body">
      <table id="tblAnak" class="display table table-striped" style="width:100%">
        <thead>
          <tr>
            <th>No</th><th>ID Peg</th><th>Nama Anak</th><th>Tgl Lahir</th><th>Pendidikan</th><th>Pekerjaan</th><th>Status Hub</th><th>Anak ke</th><th>BPJS</th>
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
  $('#tblAnak').DataTable({
    processing: true, serverSide: true, searching: true,
    ajax: { url: 'pages/ref-keluarga/ajax-data-anak.php', type: 'GET', data: { uid: <?php echo json_encode($uid); ?> } },
    columns: [
      { data: 'no', orderable:false },
      { data: 'idpeg_nama' },     // <— ganti dari 'id_peg'
      { data: 'nama' },
      { data: 'tgl_lhr' },
      { data: 'pendidikan' },
      { data: 'pekerjaan' },
      { data: 'status_hub' },
      { data: 'anak_ke' },
      { data: 'bpjs_anak' }
    ]
  });
});
</script>
</body>
</html>