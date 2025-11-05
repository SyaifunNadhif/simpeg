<?php
/*********************************************************
 * FILE    : aksi-otorisasi.php
 * MODULE  : SIMPEG — Otorisasi Perubahan Data Pegawai (per poin)
 * VERSION : v1.5 (PHP 5.6)
 * DATE    : 2025-10-12
 *
 * CHANGELOG
 * - v1.5: Konsisten pakai $koneksi; field sesuai tabel: id_edit,
 *         status_otorisasi, tanggal_otorisasi, otorisator, komentar_otorisasi.
 *         Transaksi DB; whitelist kolom update tb_pegawai; notifikasi ref_id;
 *         hanya proses status 'Menunggu'; hardening input.
 *********************************************************/

if (session_id()==='') session_start();
@include_once __DIR__ . '/dist/koneksi.php';

/* ----- Guard minimal login ----- */
if (!isset($_SESSION['id_user'])) {
  echo "<script>alert('Sesi berakhir. Silakan login kembali.'); window.location='login.php';</script>";
  exit;
}

/* ----- Ambil & validasi input ----- */
$id_edit = isset($_POST['id_edit']) ? $_POST['id_edit'] : (isset($_POST['id_pending']) ? $_POST['id_pending'] : '');
$aksi    = isset($_POST['aksi']) ? $_POST['aksi'] : '';
$komentar= isset($_POST['komentar']) ? $_POST['komentar'] : '';

if ($id_edit === '' || ($aksi !== 'setuju' && $aksi !== 'tolak' && $aksi !== 'approve' && $aksi !== 'reject')) {
  echo "<script>alert('Permintaan tidak valid'); window.location='home-admin.php?page=otorisasi-approval';</script>";
  exit;
}

/* Samakan istilah aksi */
if ($aksi === 'approve') $aksi = 'setuju';
if ($aksi === 'reject')  $aksi = 'tolak';

$id_edit_safe = mysqli_real_escape_string($koneksi, $id_edit);
$id_otorisasi = mysqli_real_escape_string($koneksi, $_SESSION['id_user']);
$komentar_safe= mysqli_real_escape_string($koneksi, $komentar);

/* ----- Ambil data pengajuan ----- */
$sqlGet = "SELECT id_edit, id_peg, jenis_data, data_lama, data_baru, id_user, status_otorisasi
           FROM tb_edit_pending
           WHERE id_edit = '$id_edit_safe'
           LIMIT 1";
$rs = mysqli_query($koneksi, $sqlGet);

if (!$rs || mysqli_num_rows($rs) === 0) {
  echo "<script>alert('Data pengajuan tidak ditemukan'); window.location='home-admin.php?page=otorisasi-approval';</script>";
  exit;
}

$row = mysqli_fetch_assoc($rs);
if ($row['status_otorisasi'] !== 'Menunggu') {
  echo "<script>alert('Pengajuan sudah diproses sebelumnya'); window.location='home-admin.php?page=otorisasi-approval';</script>";
  exit;
}

$idPeg     = mysqli_real_escape_string($koneksi, $row['id_peg']);
$jenisData = $row['jenis_data'];  // contoh: 'gol_darah', 'status_nikah', dst.
$dataBaru  = $row['data_baru'];
$idPemohon = $row['id_user'];

/* ----- Whitelist kolom tb_pegawai (hindari injection kolom) ----- */
$allowed_map = array(
  // 'nama_field_pengajuan' => 'nama_kolom_di_tb_pegawai'
  'gol_darah'    => 'gol_darah',
  'status_nikah' => 'status_nikah',
  'alamat'       => 'alamat',
  'no_hp'        => 'no_hp',
  'email'        => 'email',
  // tambahkan sesuai kebutuhan Anda...
);

/* ----- Mulai transaksi ----- */
mysqli_autocommit($koneksi, false);
$okAll = true;

/* ----- Proses sesuai aksi ----- */
if ($aksi === 'setuju') {
  /* Update tb_pegawai hanya untuk kolom yang diizinkan */
  if (!isset($allowed_map[$jenisData])) {
    mysqli_rollback($koneksi);
    mysqli_autocommit($koneksi, true);
    echo "<script>alert('Jenis data tidak diizinkan untuk diubah: $jenisData'); window.location='home-admin.php?page=otorisasi-approval';</script>";
    exit;
  }

  $kolomPegawai = $allowed_map[$jenisData];
  $nilaiBaru    = mysqli_real_escape_string($koneksi, $dataBaru);

  $sqlUpdPeg = "UPDATE tb_pegawai
                   SET $kolomPegawai = '$nilaiBaru',
                       updated_by    = '$id_otorisasi',
                       updated_at    = NOW()
                 WHERE id_peg = '$idPeg'
                 LIMIT 1";
  $okPeg = mysqli_query($koneksi, $sqlUpdPeg);
  if (!$okPeg) $okAll = false;

  /* Update status pengajuan */
  $sqlUpdReq = "UPDATE tb_edit_pending
                   SET status_otorisasi  = 'Disetujui',
                       tanggal_otorisasi = NOW(),
                       otorisator        = '$id_otorisasi',
                       komentar_otorisasi= '$komentar_safe'
                 WHERE id_edit = '$id_edit_safe'
                   AND status_otorisasi = 'Menunggu'
                 LIMIT 1";
  $okReq = mysqli_query($koneksi, $sqlUpdReq);
  if (!$okReq) $okAll = false;

  /* Log history (abaikan error jika tabel tidak ada) */
  @mysqli_query($koneksi, "
    INSERT INTO tb_edit_history (id_edit, action, actor, komentar, acted_at)
    VALUES ('$id_edit_safe','Disetujui','$id_otorisasi','$komentar_safe',NOW())
  ");

  /* Notifikasi ke pemohon */
  $pesan = 'Beberapa perubahan data atas nama Anda telah disetujui oleh atasan.';
  $okNotif = mysqli_query($koneksi, "
    INSERT INTO tb_notifikasi
      (id_user, judul, pesan, ref_type, ref_id, status_baca, created_at)
    VALUES
      ('".$idPemohon."', 'Status Permintaan Edit', '".$pesan."', 'edit', '".$id_edit_safe."', 'Belum', NOW())
  ");
  if (!$okNotif) $okAll = false;

} else { // $aksi === 'tolak'
  /* Update status pengajuan saja */
  $sqlUpdReq = "UPDATE tb_edit_pending
                   SET status_otorisasi  = 'Ditolak',
                       tanggal_otorisasi = NOW(),
                       otorisator        = '$id_otorisasi',
                       komentar_otorisasi= '$komentar_safe'
                 WHERE id_edit = '$id_edit_safe'
                   AND status_otorisasi = 'Menunggu'
                 LIMIT 1";
  $okReq = mysqli_query($koneksi, $sqlUpdReq);
  if (!$okReq) $okAll = false;

  /* Log history (abaikan error jika tabel tidak ada) */
  @mysqli_query($koneksi, "
    INSERT INTO tb_edit_history (id_edit, action, actor, komentar, acted_at)
    VALUES ('$id_edit_safe','Ditolak','$id_otorisasi','$komentar_safe',NOW())
  ");

  /* Notifikasi ke pemohon */
  $pesan = 'Perubahan data Anda ditolak oleh atasan.';
  $okNotif = mysqli_query($koneksi, "
    INSERT INTO tb_notifikasi
      (id_user, judul, pesan, ref_type, ref_id, status_baca, created_at)
    VALUES
      ('".$idPemohon."', 'Status Permintaan Edit', '".$pesan."', 'edit', '".$id_edit_safe."', 'Belum', NOW())
  ");
  if (!$okNotif) $okAll = false;
}

/* ----- Commit / Rollback ----- */
if ($okAll) {
  mysqli_commit($koneksi);
  mysqli_autocommit($koneksi, true);
  echo "<script>
    window.location='home-admin.php?page=otorisasi-approval';
  </script>";
  exit;
} else {
  $err = mysqli_error($koneksi);
  mysqli_rollback($koneksi);
  mysqli_autocommit($koneksi, true);
  echo "<script>alert('Gagal memproses otorisasi. ".$err."'); window.location='home-admin.php?page=otorisasi-approval';</script>";
  exit;
}
