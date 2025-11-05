<?php
/*********************************************************
 * FILE      : pages/pegawai/form-view-data-pegawai.php
 * MODULE    : SIMPEG — Data Pegawai (Aktif & Purna)
 * VERSION   : v1.4 (PHP 5.6+)
 * NOTE      : versi stabil utk lokal & server
 *********************************************************/

if (session_status() === PHP_SESSION_NONE) session_start();

/* ==== Resolve path absolut untuk header & base URL ==== */
$ROOT_DIR = dirname(__DIR__, 2);                 // .../ (naik 2 dari /pages/pegawai)
$HEADER   = $ROOT_DIR . '/komponen/header.php';
if (!file_exists($HEADER)) { die("Header tidak ditemukan: {$HEADER}"); }
require_once $HEADER;

/* ==== Validasi koneksi ==== */
if (!isset($conn) || !($conn instanceof mysqli)) {
  die("Koneksi DB (\$conn) belum terinisialisasi di header.php");
}

/* ==== Helper ==== */
if (!function_exists('esc')) {
  function esc($c,$s){ return mysqli_real_escape_string($c, (string)$s); }
}

/* ==== Base href & URL AJAX absolut ==== */
$scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // biasanya posisi home-admin.php
$baseHref = $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath;
$ajaxAktifURL = $baseHref . '/pages/pegawai/ajax-data-pegawai.php';
$ajaxPurnaURL = $baseHref . '/pages/pegawai/ajax-pegawai-purna.php';

/* ==== Header & breadcrumb ==== */
$page_title    = "Data";
$page_subtitle = "Pegawai";
$breadcrumbs   = [ ["label"=>"Dashboard","url"=>"home-admin.php"], ["label"=>"Data Pegawai"] ];
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <a href="home-admin.php" class="btn btn-secondary mr-2"><i class="fa fa-arrow-left"></i> Kembali</a>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <!-- Nav Tabs -->
        <ul class="nav nav-tabs mb-3" id="pegawaiTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="aktif-tab" data-bs-toggle="tab" href="#aktif" role="tab">Pegawai Aktif</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="purna-tab" data-bs-toggle="tab" href="#purna" role="tab">Pegawai Purna</a>
          </li>
        </ul>

        <div class="tab-content" id="pegawaiTabContent">
          <!-- ================= Tab: Pegawai Aktif ================= -->
          <div class="tab-pane fade show active" id="aktif" role="tabpanel" aria-labelledby="aktif-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h4 class="mb-0">Data Pegawai Aktif</h4>
              <div>
                <?php if (isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) === 'admin'): ?>
                  <a href="home-admin.php?page=form-master-data-pegawai" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Tambah Data</a>
                  <a href="home-admin.php?page=form-upload-data-pegawai" class="btn btn-sm btn-danger"><i class="fa fa-plus"></i> Tambah Data Kolektif</a>
                <?php endif; ?>
              </div>
            </div>

            <!-- Filter Unit Kerja -->
            <div class="row mb-3">
              <div class="col-md-4">
                <select id="filter_unit_kerja" class="form-control form-control-sm">
                  <option value="">-- Semua Unit Kerja --</option>
                  <?php
                    $isKepala = (isset($_SESSION['hak_akses']) && strtolower((string)$_SESSION['hak_akses']) === 'kepala');
                    if ($isKepala) {
                      $kode_kantor = esc($conn, $_SESSION['kode_kantor'] ?? '');
                      $sql = "
                        SELECT DISTINCT k.nama_kantor, j.unit_kerja
                        FROM tb_jabatan j
                        JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
                        WHERE j.status_jab = 'Aktif' AND j.unit_kerja = '{$kode_kantor}'
                      ";
                    } else {
                      $sql = "
                        SELECT DISTINCT k.nama_kantor, j.unit_kerja
                        FROM tb_jabatan j
                        JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
                        WHERE j.status_jab = 'Aktif'
                        ORDER BY k.nama_kantor ASC
                      ";
                    }
                    $unitList = mysqli_query($conn, $sql);
                    if (!$unitList) echo '<option value="">(Gagal load: '.htmlspecialchars(mysqli_error($conn)).')</option>';
                    while ($unit = $unitList ? mysqli_fetch_assoc($unitList) : null) {
                      if (!$unit) break;
                      $val = htmlspecialchars($unit['unit_kerja'] ?? '', ENT_QUOTES, 'UTF-8');
                      $txt = htmlspecialchars($unit['nama_kantor'] ?? '', ENT_QUOTES, 'UTF-8');
                      echo "<option value='{$val}'>{$txt}</option>";
                    }
                  ?>
                </select>
              </div>
            </div>

            <!-- Tabel Pegawai Aktif -->
            <table id="pegawai" class="table table-hover table-bordered" style="width:100%">
              <thead class="thead-light">
                <tr>
                  <th>Foto</th>
                  <th>ID Pegawai</th>
                  <th>Nama</th>
                  <th>Tempat, Tgl Lahir</th>
                  <th>Unit Kerja</th>
                  <th>Jabatan</th>
                  <th>Tgl. Mulai Bekerja</th>
                  <th>No. Telp</th>
                  <th>Action</th>
                </tr>
              </thead>
            </table>
          </div>

          <!-- ================= Tab: Pegawai Purna ================= -->
          <div class="tab-pane fade" id="purna" role="tabpanel" aria-labelledby="purna-tab">
            <table id="pegawaiPurna" class="table table-hover table-bordered" style="width:100%">
              <thead class="thead-light">
                <tr>
                  <th>ID Pegawai</th>
                  <th>Nama</th>
                  <th>Tempat, Tgl Lahir</th>
                  <th>Jabatan</th>
                  <th>Jenis Mutasi</th>
                  <th>Tgl. Mutasi</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<!-- DataTables (CDN); Bootstrap & jQuery sudah dari header -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(function() {
  // ======= TABEL PEGAWAI AKTIF =======
  var tableAktif = $('#pegawai').DataTable({
    processing: true,
    serverSide: true,
    deferRender: true,
    ajax: {
      url: '<?= $ajaxAktifURL ?>',
      type: 'GET',
      data: function(d){ d.unit_kerja = $('#filter_unit_kerja').val(); },
      error: function(xhr){
        console.error('[DT Aktif] ', xhr.status, xhr.responseText);
        alert('Gagal memuat data pegawai aktif. Cek konsol (F12) untuk detail error.');
      }
    },
    columns: [
      { data: 'foto', orderable: false, render: function(data){
          if (data && typeof data==='string' && data.indexOf('<img')!==-1) return data;
          var file = (data||'').trim();
          var src  = file ? ('uploads/foto/'+file) : 'assets/img/user.png';
          var cb   = file ? ('?cb='+Date.now()) : '';
          return '<img class="rounded-circle" src="'+src+cb+'" alt="Foto" onerror="this.onerror=null;this.src=\'assets/img/user.png\'">';
        }
      },
      { data:'id_peg' },
      { data:'nama' },
      { data:'ttl' },
      { data:'unit_kerja' },
      { data:'jabatan' },
      { data:'tgl_masuk' },
      { data:'no_telp' },
      { data:'action', orderable:false }
    ],
    pageLength: 10,
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'Semua']]
  });

  $('#filter_unit_kerja').on('change', function(){ tableAktif.ajax.reload(); });

  // ======= TABEL PEGAWAI PURNA (lazy init) =======
  var purnaInitialized = false;
  $('#purna-tab').on('click', function(){
    if (purnaInitialized) return;
    $('#pegawaiPurna').DataTable({
      processing: true,
      serverSide: true,
      deferRender: true,
      ajax: {
        url: '<?= $ajaxPurnaURL ?>',
        type: 'GET',
        error: function(xhr){
          console.error('[DT Purna] ', xhr.status, xhr.responseText);
          alert('Gagal memuat data pegawai purna. Cek konsol untuk detail error.');
        }
      },
      columns: [
        { data:'id_peg' },
        { data:'nama' },
        { data:'ttl' },
        { data:'jabatan' },
        { data:'status_kepeg' },
        { data:'tgl_pensiun' }
      ]
    });
    purnaInitialized = true;
  });
});
</script>

<style>
#pegawai_wrapper, #pegawaiPurna_wrapper { font-size: 0.875rem; }
#pegawai img { border-radius: 6px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
#pegawai img.rounded-circle { width:40px; height:40px; object-fit:cover; aspect-ratio:1/1; border-radius:50%; box-shadow:0 0 3px rgba(0,0,0,0.1); }
.nav-tabs .nav-link.active { background:#007bff; color:#fff; font-weight:600; border-color:#007bff #007bff #fff; border-radius:.25rem .25rem 0 0; }
.nav-tabs .nav-link { color:#007bff; }
</style>
