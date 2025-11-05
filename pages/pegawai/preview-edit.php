<?php
/*********************************************************
 * FILE    : pages/preview-edit.php
 * MODULE  : SIMPEG — Riwayat Permintaan Edit Data (User)
 * VERSION : v1.6 (PHP 5.6)
 * DATE    : 2025-10-12
 *
 * CHANGELOG
 * - v1.6: Konsisten koneksi `$conn`; status_otorisasi=Menunggu/Disetujui/Ditolak;
 *         guard akses: role 'user' hanya bisa melihat data miliknya;
 *         sanitasi input; escape output; empty-state; jam pakai waktu aksi terakhir.
 * - v1.5: Join nama user pengaju; kelompokkan timeline per tanggal.
 * - v1.4: Perapihan markup AdminLTE + badge warna status.
 *********************************************************/

if (session_id()==='') session_start();
@include_once __DIR__ . '/dist/koneksi.php';

$page_title   = "Riwayat";
$page_subtitle= "Permintaan Edit Data";
$breadcrumbs  = array(
  array("label"=>"Dashboard","url"=>"home-admin.php"),
  array("label"=>"Riwayat Permintaan Edit Data")
);
@include_once __DIR__ . '/komponen/header.php';

/* ===== Helper ===== */
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/* ===== Session ===== */
$id_user   = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : '';
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';

/* ===== Target pegawai =====
   - Jika role 'user', batasi ke pegawai miliknya.
   - Jika admin/kepala bisa via ?id_peg=...
*/
$id_peg_req = isset($_GET['id_peg']) ? $_GET['id_peg'] : '';
$id_peg_safe = '';

if ($hak_akses === 'user') {
  // ambil id pegawai milik user login
  $idu_safe = mysqli_real_escape_string($conn, $id_user);
  $ru = mysqli_query($conn, "SELECT u.id_user, u.id_pegawai, p.id_peg FROM tb_user u LEFT JOIN tb_pegawai p ON u.id_pegawai=p.id_peg WHERE u.id_user='$idu_safe' LIMIT 1");
  $usr = $ru ? mysqli_fetch_assoc($ru) : null;
  $id_peg_safe = $usr ? mysqli_real_escape_string($conn, $usr['id_peg']) : '';
} else {
  $id_peg_safe = mysqli_real_escape_string($conn, $id_peg_req);
}

if ($id_peg_safe === '' || $id_peg_safe === null) {
  echo '<div class="container mt-4"><div class="alert alert-danger">Data pegawai tidak ditemukan atau akses ditolak.</div></div>';
  exit;
}

/* ===== Data pegawai ===== */
$qPegawai = mysqli_query($conn, "SELECT id_peg, nama, nip FROM tb_pegawai WHERE id_peg='".$id_peg_safe."' LIMIT 1");
$pegawai  = $qPegawai ? mysqli_fetch_assoc($qPegawai) : null;
if (!$pegawai) {
  echo '<div class="container mt-4"><div class="alert alert-danger">Pegawai tidak ditemukan.</div></div>';
  exit;
}

/* ===== Riwayat perubahan =====
   Catatan:
   - status_otorisasi: 'Menunggu' | 'Disetujui' | 'Ditolak'
   - waktu tampil = terakhir aksi (approved_at) bila ada, else tanggal_pengajuan
*/
$sqlRiwayat = "
  SELECT
    ep.id_edit,
    ep.id_peg,
    ep.jenis_data,
    ep.data_lama,
    ep.data_baru,
    ep.status_otorisasi,
    ep.tanggal_pengajuan,
    ep.tanggal_otorisasi,
    u.nama_user,
    u.hak_akses
  FROM tb_edit_pending ep
  LEFT JOIN tb_user u ON ep.id_user = u.id_user
  WHERE ep.id_peg = '".$id_peg_safe."'
  ORDER BY COALESCE(ep.tanggal_otorisasi, ep.tanggal_pengajuan) DESC, ep.id_edit DESC
";
$qRiwayat = mysqli_query($conn, $sqlRiwayat);
?>
<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-primary text-white">
      <h5 class="card-title mb-0">Preview Perubahan Data Pegawai</h5>
    </div>
    <div class="card-body">
      <h6>Informasi Pegawai</h6>
      <table class="table table-sm table-bordered mb-4">
        <tr><th style="width:30%">ID Pegawai</th><td><?php echo e($pegawai['id_peg']); ?></td></tr>
        <tr><th>Nama</th><td><?php echo e($pegawai['nama']); ?></td></tr>
        <tr><th>NIP</th><td><?php echo e($pegawai['nip']); ?></td></tr>
      </table>

      <h6>Riwayat Perubahan</h6>
      <div class="timeline">
        <?php
        $lastDate = '';
        if ($qRiwayat && mysqli_num_rows($qRiwayat) > 0):
          while ($row = mysqli_fetch_assoc($qRiwayat)):
            $waktu = $row['tanggal_otorisasi'] ? $row['tanggal_otorisasi'] : $row['tanggal_pengajuan'];
            $tanggal = date('d/m/Y', strtotime($waktu));
            $jam     = date('H:i',     strtotime($waktu));

            if ($tanggal !== $lastDate){
              echo '<div class="time-label"><span class="bg-primary">'.$tanggal.'</span></div>';
              $lastDate = $tanggal;
            }

            // badge & icon warna
            $status = $row['status_otorisasi'];
            $iconClass  = 'bg-warning text-dark';
            $badgeClass = 'badge bg-warning text-dark';
            $label      = 'Menunggu';

            if ($status === 'Disetujui'){
              $iconClass  = 'bg-success';
              $badgeClass = 'badge bg-success';
              $label      = 'Disetujui';
            } elseif ($status === 'Ditolak'){
              $iconClass  = 'bg-danger';
              $badgeClass = 'badge bg-danger';
              $label      = 'Ditolak';
            }
        ?>
        <div>
          <i class="fas fa-edit <?php echo $iconClass; ?>"></i>
          <div class="timeline-item">
            <span class="time"><i class="far fa-clock"></i> <?php echo $jam; ?></span>
            <h3 class="timeline-header">
              Perubahan <strong><?php echo e(ucfirst($row['jenis_data'])); ?></strong> oleh
              <i><?php echo e($row['nama_user']); ?></i>
              <span class="<?php echo $badgeClass; ?> ms-2"><?php echo $label; ?></span>
            </h3>
            <div class="timeline-body">
              Dari: <strong><?php echo e($row['data_lama']); ?></strong>
              &nbsp;→&nbsp;
              Ke: <strong><?php echo e($row['data_baru']); ?></strong>
            </div>
          </div>
        </div>
        <?php
          endwhile;
        else:
          echo '<div class="alert alert-light border">Belum ada riwayat perubahan.</div>';
        endif;
        ?>
        <!-- End marker -->
        <div><i class="fas fa-clock bg-gray"></i></div>
      </div>
    </div>
    <div class="card-footer text-end">
      <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
    </div>
  </div>
</div>
