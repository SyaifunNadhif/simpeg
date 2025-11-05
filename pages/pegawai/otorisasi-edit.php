<?php
/*********************************************************
 * FILE    : pages/otorisasi/otorisasi-edit.php
 * MODULE  : SIMPEG — Antrian Otorisasi Perubahan Data
 * VERSION : v1.4 (PHP 5.6)
 * DATE    : 2025-10-12
 *
 * CHANGELOG
 * - v1.4: Konsisten koneksi `$koneksi`; filter status_otorisasi='Menunggu';
 *         filter antrian berdasar `kode_kantor` kepala; hardening session & input;
 *         perbaiki JOIN, escaped output, empty-state message.
 * - v1.3: Tambah kolom editor (user pengaju) dan tombol Detail (by id_edit).
 * - v1.2: Struktur card + table responsive (AdminLTE/Atlantis kompatibel).
 * - v1.1: Versi awal halaman antrian kepala.
 *********************************************************/

if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';

/* ===== Guard akses kepala ===== */
if (!isset($_SESSION['hak_akses']) || $_SESSION['hak_akses'] !== 'kepala') {
  include __DIR__ . '/../404.php';
  exit;
}

/* ===== Ambil identitas kepala & kantor ===== */
$id_kepala = isset($_SESSION['id_pegawai']) ? $_SESSION['id_pegawai'] : '';

$id_kepala_safe = mysqli_real_escape_string($koneksi, $id_kepala);
$sqlKepala = "
  SELECT kode_kantor
    FROM tb_pegawai
   WHERE id_peg = '".$id_kepala_safe."'
   LIMIT 1";
$rk = mysqli_query($koneksi, $sqlKepala);
$rowKepala = $rk ? mysqli_fetch_assoc($rk) : null;
$kode_kantor_kepala = $rowKepala ? $rowKepala['kode_kantor'] : '';

/* Jika tidak ditemukan kantor kepala, hentikan */
if ($kode_kantor_kepala === '' || $kode_kantor_kepala === null) {
  echo '<div class="content-wrapper"><section class="content-header"><div class="container-fluid"><h4>Antrian Otorisasi</h4><p class="text-danger">Kantor Kepala tidak ditemukan. Hubungi admin.</p></div></section></div>';
  exit;
}

$kantor_safe = mysqli_real_escape_string($koneksi, $kode_kantor_kepala);

/* ===== Ambil antrian pengajuan dengan kantor sama & status Menunggu =====
   ep.id_user   = user pemohon
   pengedit     = data pegawai dari user pemohon (untuk baca kode_kantor pemohon)
   Catatan: LEFT JOIN agar baris tetap muncul meskipun ada data referensi yg kosong.
*/
$sqlAntrian = "
  SELECT
      ep.id_edit,
      ep.id_peg,
      ep.jenis_data,
      ep.data_baru,
      ep.tanggal_pengajuan,
      u.username,
      p.nama AS nama_pegawai
  FROM tb_edit_pending ep
  LEFT JOIN tb_user u
         ON ep.id_user = u.id_user
  LEFT JOIN tb_pegawai p
         ON ep.id_peg = p.id_peg
  LEFT JOIN tb_pegawai pengedit
         ON u.id_pegawai = pengedit.id_peg
  WHERE ep.status_otorisasi = 'Menunggu'
    AND pengedit.kode_kantor = '".$kantor_safe."'
  ORDER BY ep.tanggal_pengajuan DESC";

$qAntrian = mysqli_query($koneksi, $sqlAntrian);

/* ===== Helper escape ===== */
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h4>Antrian Otorisasi Edit Data Pegawai</h4>
      <small>Kantor: <strong><?php echo e($kode_kantor_kepala); ?></strong></small>
    </div>
  </section>

  <section class="content">
    <div class="card">
      <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>Pegawai</th>
              <th>Jenis Data</th>
              <th>Editor</th>
              <th>Waktu Pengajuan</th>
              <th style="width:110px">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $no = 1;
          if ($qAntrian && mysqli_num_rows($qAntrian) > 0):
            while ($row = mysqli_fetch_assoc($qAntrian)):
              // Sorotan nilai baru (opsional, tampilkan ringkas)
              $dataBaru = $row['data_baru'];
              // jika data_baru tersimpan sebagai string sederhana, tampilkan langsung;
              // bila JSON, bisa di-decode, namun di banyak kasus cukup ringkas saja.
          ?>
            <tr>
              <td><?php echo $no++; ?></td>
              <td><?php echo e($row['nama_pegawai']); ?></td>
              <td><?php echo e(ucfirst($row['jenis_data'])); ?></td>
              <td><?php echo e($row['username']); ?></td>
              <td><?php echo date('d-m-Y H:i', strtotime($row['tanggal_pengajuan'])); ?></td>
              <td>
                <a href="home-admin.php?page=otorisasi-detail&id_edit=<?php echo e($row['id_edit']); ?>" class="btn btn-info btn-sm">
                  Detail
                </a>
              </td>
            </tr>
          <?php
            endwhile;
          else:
          ?>
            <tr>
              <td colspan="6" class="text-center text-muted">Belum ada antrian otorisasi (status Menunggu) di kantor Anda.</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
