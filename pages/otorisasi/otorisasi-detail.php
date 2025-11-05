<?php
$page_title = "Detail Otorisasi";
$page_subtitle = "Persetujuan Perubahan Data Pegawai";
$breadcrumbs = [
  ["label" => "Dashboard", "url" => "home-admin.php"],
  ["label" => "Otorisasi Perubahan Data"]
];
include "komponen/header.php";
include 'dist/koneksi.php';

$id_user = $_SESSION['id_user'];
$hak_akses = strtolower($_SESSION['hak_akses']);

$id_peg = isset($_GET['id_peg']) ? $_GET['id_peg'] : '';

// Ambil data pegawai
$qPegawai = mysqli_query($conn, "SELECT * FROM tb_pegawai WHERE id_peg = '$id_peg'");
$pegawai = mysqli_fetch_assoc($qPegawai);

// Ambil riwayat perubahan dari tb_edit_pending
$qRiwayat = mysqli_query($conn, "
  SELECT ep.*, u.nama_user, u.hak_akses 
  FROM tb_edit_pending ep
  LEFT JOIN tb_user u ON ep.id_user = u.id_user
  WHERE ep.id_peg = '$id_peg' AND ep.status_otorisasi = 'pending'
  ORDER BY ep.tanggal_pengajuan DESC
");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Otorisasi Perubahan Data</title>
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
  <link rel="stylesheet" href="../../plugins/bootstrap5/css/bootstrap.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-primary text-white">
      <h5 class="card-title mb-0">Otorisasi Perubahan Data Pegawai</h5>
    </div>
    <div class="card-body">
      <h6>Informasi Pegawai</h6>
      <table class="table table-sm table-bordered mb-4">
        <tr><th style="width: 30%">ID Pegawai</th><td><?= $pegawai['id_peg'] ?></td></tr>
        <tr><th>Nama</th><td><?= $pegawai['nama'] ?></td></tr>
        <tr><th>NIP</th><td><?= $pegawai['nip'] ?></td></tr>
      </table>

      <h6>Persetujuan Perubahan</h6>
      <form action="pages/otorisasi/proses-otorisasi.php" method="POST">
        <input type="hidden" name="id_peg" value="<?= $id_peg ?>">
        <div class="timeline">
          <?php
          $lastDate = '';
          while ($row = mysqli_fetch_assoc($qRiwayat)):
            $tanggal = date('d/m/Y', strtotime($row['tanggal_pengajuan']));
            $jam = date('H:i', strtotime($row['tanggal_pengajuan']));
            if ($tanggal != $lastDate):
              echo "<div class='time-label'><span class='bg-primary'>$tanggal</span></div>";
              $lastDate = $tanggal;
            endif;
          ?>
          <div>
            <i class="fas fa-edit bg-warning text-dark"></i>
            <div class="timeline-item">
              <span class="time"><i class="far fa-clock"></i> <?= $jam ?></span>
              <h3 class="timeline-header">
                Perubahan <strong><?= ucfirst($row['jenis_data']) ?></strong> oleh <i><?= $row['nama_user'] ?></i>
              </h3>
              <div class="timeline-body">
                Dari: <strong><?= $row['data_lama'] ?></strong> → Ke: <strong><?= $row['data_baru'] ?></strong>
                <div class="mt-3">
                  <input type="hidden" name="id_edit[]" value="<?= $row['id_edit'] ?>">
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="aksi[<?= $row['id_edit'] ?>]" value="approve" required>
                    <label class="form-check-label text-primary">Setujui</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="aksi[<?= $row['id_edit'] ?>]" value="reject">
                    <label class="form-check-label text-danger">Tolak</label>
                  </div>
                  <div class="form-group mt-2">
                    <input type="text" name="komentar[<?= $row['id_edit'] ?>]" class="form-control form-control-sm" placeholder="Komentar (opsional)">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
          <div><i class="fas fa-clock bg-gray"></i></div>
        </div>

        <div class="mt-4 text-end">
          <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i> Proses Otorisasi</button>
          <a href="home-admin.php?page=otorisasi-approval" class="btn btn-secondary">Kembali</a>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
