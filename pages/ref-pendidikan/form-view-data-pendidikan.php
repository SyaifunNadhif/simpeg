<?php
/***********************
 * FILE    : pages/ref-pendidikan/form-view-data-pendidikan.php
 * VERSION : v1.2 (PHP 5.6)
 * DATE    : 2025-09-07
 * CHANGELOG
 * - v1.2: Urutan pakai id_pendidikan (AUTO_INCREMENT), kolom menyesuaikan skema.
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
<!DOCTYPE html><html lang="id"><head>
  <meta charset="utf-8"><title>Daftar Pendidikan Pegawai</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
  <style>.hdr{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:10px 10px 0 0;padding:14px 16px}.btnbar{position:absolute;right:18px;top:10px}</style>
</head><body>
<div class="container-fluid mt-3">
  <div class="position-relative">
    <div class="hdr">
      <h4 class="mb-0">Daftar Pendidikan Pegawai</h4>
      <small>Menampilkan seluruh data pendidikan<?php echo $pegawai? ' — '.e($pegawai['nama']).' ('.$pegawai['id_peg'].')' : ''; ?></small>
    </div>
    <div class="btnbar">
      <a href="home-admin.php" class="btn btn-light btn-sm me-2"><i class="fa fa-home"></i> Dashboard</a>
      <a href="home-admin.php?page=form-master-data-pendidikan<?php echo $uid?'&uid='.urlencode($uid):''; ?>" class="btn btn-warning btn-sm me-2">Tambah Data</a>
      <a href="home-admin.php?page=form-import-data-pendidikan" class="btn btn-success btn-sm">Impor Kolektif</a>
    </div>
  </div>

  <div class="card p-3">
    <table id="tblPend" class="display table table-striped" style="width:100%">
      <thead>
        <tr>
          <th>No</th><th>ID Peg</th><th>Jenjang</th><th>Nama Sekolah</th><th>Lokasi</th>
          <th>Jurusan</th><th>Th Masuk</th><th>Th Lulus</th><th>No Ijazah</th><th>Tgl Ijazah</th><th>Kepala</th><th>Status</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
$(function(){
$('#tblPend').DataTable({
  processing:true, serverSide:true, searching:true,
  responsive: true, autoWidth:false,
    ajax:{ url:'pages/ref-pendidikan/ajax-data-pendidikan.php', type:'GET', data:{ uid: <?php echo json_encode($uid); ?> } },
    columns:[
      { data:'no', orderable:false },
      { data:'idpeg_nama' },       // <— ganti dari 'id_peg'
      { data:'jenjang' },
      { data:'nama_sekolah' },
      { data:'lokasi' },
      { data:'jurusan' },
      { data:'th_masuk' },
      { data:'th_lulus' },
      { data:'no_ijazah' },
      { data:'tgl_ijazah' },
      { data:'kepala' },
      { data:'status' }
    ]
  });
});
</script>
</body></html>
