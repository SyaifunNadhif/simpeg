<?php
$page_title = "Data";
$page_subtitle = "Pegawai";
$breadcrumbs = [
  ["label" => "Dashboard", "url" => "home-admin.php"],
  ["label" => "Data Pegawai"]
];

include "komponen/header.php";
include 'dist/koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM tb_user ORDER BY created_at DESC");
?>


<div class="card shadow-sm">
  <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar User</h5>
    <div>
      <button onclick="history.back()" class="btn btn-outline-light btn-sm me-2">
        <i class="fas fa-arrow-left"></i> Kembali
      </button>
      <a href="home-admin.php?page=form-user&mode=create" class="btn btn-light btn-sm fw-bold">
        <i class="fas fa-plus"></i> Tambah User
      </a>
    </div>
  </div>

  <div class="card-body p-3">
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle mb-0">
        <thead class="table-light text-center">
          <tr>
            <th style="width: 5%">No</th>
            <th>Username</th>
            <th>Nama Lengkap</th>
            <th>Jabatan</th>
            <th>Level Akses</th>
            <th>Status User</th>
            <th style="width: 15%">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; while($row = mysqli_fetch_assoc($query)) : ?>
          <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['id_user']) ?></td>
            <td><?= htmlspecialchars($row['nama_user']) ?></td>
            <td><?= htmlspecialchars($row['jabatan']) ?></td>
            <td class="text-center">
              <span class="badge bg-<?= strtolower($row['hak_akses']) == 'admin' ? 'primary' : (strtolower($row['hak_akses']) == 'kepala' ? 'info' : 'secondary') ?>">
                <?= ucfirst($row['hak_akses']) ?>
              </span>
            </td>
            <td class="text-center">
              <span class="badge bg-<?= $row['status_aktif'] == 'Y' ? 'success' : 'danger' ?>">
                <?= $row['status_aktif'] == 'Y' ? 'Aktif' : 'Nonaktif' ?>
              </span>
            </td>
            <td class="text-center">
              <a href="home-admin.php?page=form-user&mode=edit&id=<?= $row['id_user'] ?>" class="btn btn-sm btn-warning me-1">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a href="modules/user/hapus-user.php?id=<?= $row['id_user'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus user ini?')">
                <i class="fas fa-trash"></i> Hapus
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
