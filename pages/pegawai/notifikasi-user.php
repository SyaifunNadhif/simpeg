<?php
$page_title = "Riwayat";
$page_subtitle = "Notifikasi";
$breadcrumbs = [
  ["label" => "Dashboard", "url" => "home-admin.php"],
  ["label" => "Riwayat Notifikasi"]
];
include "komponen/header.php";
include 'dist/koneksi.php';

$id_user = $_SESSION['id_user'];
$qNotif = mysqli_query($conn, "
  SELECT * FROM tb_notifikasi
  WHERE id_user = '$id_user'
  ORDER BY waktu_notif DESC
");

// Tandai semua sebagai dibaca
mysqli_query($conn, "UPDATE tb_notifikasi SET status_baca = 'read' WHERE id_user = '$id_user'");
?>
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6 text-begin">
          <a href="javascript:history.back()" class="btn btn-secondary btn-sm mt-1">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="card shadow-sm">
            <div class="card-body table-responsive">
              <table class="table table-bordered table-striped w-100">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Judul</th>
                    <th>Pesan</th>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $no=1; while ($row = mysqli_fetch_assoc($qNotif)): ?>
                    <tr>
                      <td><?= $no++ ?></td>
                      <td><?= $row['judul'] ?></td>
                      <td><?= $row['pesan'] ?></td>
                      <td><?= date('d-m-Y H:i', strtotime($row['waktu_notif'])) ?></td>
                      <td>
                        <?php if ($row['status_baca'] == 'unread'): ?>
                          <span class="badge bg-warning text-dark">Belum dibaca</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Dibaca</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!empty($row['link_aksi'])): ?>
                          <a href="<?= $row['link_aksi'] ?>" class="btn btn-sm btn-info">Lihat</a>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
