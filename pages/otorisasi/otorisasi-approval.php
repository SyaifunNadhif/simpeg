<?php
/*********************************************************
 * FILE    : pages/otorisasi/otorisasi-approval.php
 * MODULE  : SIMPEG — Daftar Otorisasi Edit Data (Kepala)
 * VERSION : v1.8 (PHP 5.6)
 * DATE    : 2025-10-12
 *
 * CHANGELOG
 * - v1.8: Gunakan DATE() pada filter rentang tanggal + opsi "Tanggal Berdasar":
 *         Pengajuan / Otorisasi (default Pengajuan) → aman untuk DATETIME.
 * - v1.7: Terima dd/mm/yyyy atau yyyy-mm-dd; hilangkan ketergantungan kolom otorisator;
 *         scope kantor: pemohon OR pegawai; default status=Menunggu.
 * - v1.6: Tambah filter status & rentang tanggal; akses semua kantor jika '000'/'000000'.
 *********************************************************/

if (session_id()==='') session_start();

/* ==== include koneksi + normalisasi variabel ==== */
$__paths = array(
  __DIR__ . '/../../dist/koneksi.php',
  __DIR__ . '/../../../dist/koneksi.php',
  __DIR__ . '/../dist/koneksi.php',
  __DIR__ . '/dist/koneksi.php'
);
foreach ($__paths as $__p) { if (is_file($__p)) { @include_once $__p; } }
if (!isset($koneksi)) { if (isset($conn)) { $koneksi = $conn; } }

/* ===== Guard ===== */
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
if ($hak_akses !== 'kepala') {
  echo "<script>alert('Anda tidak memiliki akses.'); window.location='home-admin.php';</script>";
  exit;
}

/* ===== Helpers ===== */
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function norm_date($s){
  if (!$s) return '';
  $s = trim($s);
  if (strpos($s,'/') !== false) { // dd/mm/yyyy
    $p = explode('/', $s);
    if (count($p)===3) return sprintf('%04d-%02d-%02d', (int)$p[2], (int)$p[1], (int)$p[0]);
  }
  $t = strtotime($s);
  return $t ? date('Y-m-d', $t) : '';
}

/* ===== Params ===== */
$kode_kantor   = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$is_all_kantor = in_array($kode_kantor, array('000','000000'));

$status_opt = array('Semua','Menunggu','Disetujui','Ditolak');
$status = isset($_GET['status']) ? $_GET['status'] : 'Menunggu';
if (!in_array($status, $status_opt)) $status = 'Menunggu';

/* sumber tanggal untuk filter: pengajuan/otorisasi */
$date_by_opt = array('pengajuan','otorisasi');
$date_by = isset($_GET['date_by']) ? strtolower($_GET['date_by']) : 'pengajuan';
if (!in_array($date_by, $date_by_opt)) $date_by = 'pengajuan';
$date_col = ($date_by === 'otorisasi') ? 'ep.tanggal_otorisasi' : 'ep.tanggal_pengajuan';

$tgl_awal_raw  = isset($_GET['tgl_awal'])  ? $_GET['tgl_awal']  : '';
$tgl_akhir_raw = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '';

$tgl_awal  = norm_date($tgl_awal_raw);
$tgl_akhir = norm_date($tgl_akhir_raw);

/* ===== Build WHERE ===== */
$where = array();

/* Status */
if ($status !== 'Semua') {
  $where[] = "ep.status_otorisasi = '".mysqli_real_escape_string($koneksi, $status)."'";
}

/* Date range —> selalu bungkus DATE() agar aman untuk DATETIME */
if ($tgl_awal && $tgl_akhir) {
  $where[] = "DATE($date_col) BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."'";
} elseif ($tgl_awal) {
  $where[] = "DATE($date_col) >= '".$tgl_awal."'";
} elseif ($tgl_akhir) {
  $where[] = "DATE($date_col) <= '".$tgl_akhir."'";
}

/* Scope kantor (semua kantor jika 000/000000) */
if (!$is_all_kantor) {
  $kantor_safe = mysqli_real_escape_string($koneksi, $kode_kantor);
  $where[] = "(pengedit.unit_kerja = '".$kantor_safe."' OR p.kode_kantor = '".$kantor_safe."')";
}

$where_sql = count($where) ? 'WHERE '.implode(' AND ', $where) : '';

/* ===== Query ===== */
$sql = "
  SELECT
      ep.id_edit,
      ep.id_peg,
      ep.jenis_data,
      ep.status_otorisasi,
      ep.tanggal_pengajuan,
      ep.tanggal_otorisasi,
      u.nama_user,
      p.nama AS nama_pegawai,
      pengedit.unit_kerja AS kantor_pemohon
  FROM tb_edit_pending ep
  LEFT JOIN tb_user u
         ON ep.id_user = u.id_user
  LEFT JOIN tb_pegawai p
         ON ep.id_peg = p.id_peg
  LEFT JOIN tb_jabatan pengedit
         ON u.id_pegawai = pengedit.id_peg
  $where_sql
  ORDER BY COALESCE(ep.tanggal_otorisasi, ep.tanggal_pengajuan) DESC, ep.id_edit DESC
";
$qPending = mysqli_query($koneksi, $sql);
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-md-8">
        <h4 class="fw-bold">Otorisasi Edit Data Pegawai</h4>
        <small>Kantor Akses: <strong><?php echo $is_all_kantor ? 'SEMUA KANTOR' : e($kode_kantor); ?></strong></small>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    <!-- Filter -->
    <div class="card mb-3">
      <div class="card-body">
        <form method="get" class="row g-2">
          <input type="hidden" name="page" value="otorisasi-approval">

          <div class="col-sm-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              <?php foreach ($status_opt as $opt): ?>
                <option value="<?php echo e($opt); ?>" <?php echo ($status===$opt?'selected':''); ?>>
                  <?php echo e($opt); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-sm-3">
            <label class="form-label">Tanggal Berdasar</label>
            <select name="date_by" class="form-control">
              <option value="pengajuan" <?php echo ($date_by==='pengajuan'?'selected':''); ?>>Pengajuan</option>
              <option value="otorisasi" <?php echo ($date_by==='otorisasi'?'selected':''); ?>>Otorisasi</option>
            </select>
          </div>

          <div class="col-sm-2">
            <label class="form-label">Tanggal Awal</label>
            <input type="date" name="tgl_awal" class="form-control"
                   value="<?php echo e($tgl_awal ? $tgl_awal : ''); ?>">
          </div>

          <div class="col-sm-2">
            <label class="form-label">Tanggal Akhir</label>
            <input type="date" name="tgl_akhir" class="form-control"
                   value="<?php echo e($tgl_akhir ? $tgl_akhir : ''); ?>">
          </div>

          <div class="col-sm-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Terapkan</button>
            <a href="home-admin.php?page=otorisasi-approval" class="btn btn-secondary">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabel -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body table-responsive">
            <table class="table table-bordered table-striped w-100">
              <thead class="table-light">
                <tr>
                  <th style="width:50px">#</th>
                  <th>ID Pegawai</th>
                  <th>Nama</th>
                  <th>Jenis Perubahan</th>
                  <th>Status</th>
                  <th>Diajukan Oleh</th>
                  <th>Kantor Pemohon</th>
                  <th>Tanggal</th>
                  <th style="width:110px">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                if ($qPending && mysqli_num_rows($qPending) > 0):
                  while ($row = mysqli_fetch_assoc($qPending)):
                    $badge = 'secondary';
                    if ($row['status_otorisasi']=='Menunggu')  $badge='warning';
                    if ($row['status_otorisasi']=='Disetujui') $badge='success';
                    if ($row['status_otorisasi']=='Ditolak')   $badge='danger';

                    // pilih tanggal untuk kolom tampilan
                    $waktu = ($date_by==='otorisasi' && $row['tanggal_otorisasi'])
                               ? $row['tanggal_otorisasi']
                               : $row['tanggal_pengajuan'];
                ?>
                <tr>
                  <td><?php echo $no++; ?></td>
                  <td><?php echo e($row['id_peg']); ?></td>
                  <td><?php echo e($row['nama_pegawai']); ?></td>
                  <td><?php echo e(ucfirst($row['jenis_data'])); ?></td>
                  <td><span class="badge bg-<?php echo $badge; ?>"><?php echo e($row['status_otorisasi']); ?></span></td>
                  <td><?php echo e($row['nama_user']); ?></td>
                  <td><?php echo e($row['kantor_pemohon']); ?></td>
                  <td><?php echo $waktu ? date('d-m-Y H:i', strtotime($waktu)) : '-'; ?></td>
                  <td>
                    <a href="home-admin.php?page=otorisasi-detail&id_edit=<?php echo e($row['id_edit']); ?>"
                       class="btn btn-sm btn-primary">
                      <i class="fas fa-eye"></i> Detail
                    </a>
                  </td>
                </tr>
                <?php
                  endwhile;
                else:
                ?>
                <tr>
                  <td colspan="9" class="text-center text-muted">Tidak ada data sesuai filter.</td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </div>
    </div>
  </div>
</section>
