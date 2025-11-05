<?php
include 'dist/koneksi.php';
include 'dist/library.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$isEdit = $id != '';
$data = [
  "id_peg" => "", "diklat" => "", "penyelenggara" => "", "tempat" => "",
  "angkatan" => "", "tahun" => "", "date_reg" => date('Y-m-d')
];

if ($isEdit) {
  $q = mysqli_query($conn, "SELECT * FROM tb_diklat WHERE id_diklat = '$id'");
  if ($q && mysqli_num_rows($q) > 0) {
    $data = mysqli_fetch_assoc($q);
  } else {
    echo "<script>alert('Data tidak ditemukan'); window.location='master-data-diklat.php';</script>";
    exit;
  }
}

// Ambil daftar pegawai
$qPegawai = mysqli_query($conn, "SELECT id_peg, nama FROM tb_pegawai ORDER BY nama ASC");
?>

<div class="card">
  <div class="card-header bg-info text-white">
    <h5 class="card-title mb-0"><?= $isEdit ? 'Edit' : 'Tambah' ?> Data Diklat</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="proses-diklat.php">
      <input type="hidden" name="id_diklat" value="<?= $id ?>">

      <div class="form-group">
        <label>Nama Pegawai</label>
        <select name="id_peg" class="form-control" required>
          <option value="">-- Pilih Pegawai --</option>
          <?php while ($p = mysqli_fetch_assoc($qPegawai)) { ?>
            <option value="<?= $p['id_peg'] ?>" <?= $data['id_peg'] == $p['id_peg'] ? 'selected' : '' ?>>
              <?= $p['nama'] ?>
            </option>
          <?php } ?>
        </select>
      </div>

      <div class="form-group">
        <label>Nama Diklat</label>
        <input type="text" name="diklat" class="form-control" value="<?= $data['diklat'] ?>" required>
      </div>

      <div class="form-group">
        <label>Penyelenggara</label>
        <input type="text" name="penyelenggara" class="form-control" value="<?= $data['penyelenggara'] ?>">
      </div>

      <div class="form-group">
        <label>Tempat</label>
        <input type="text" name="tempat" class="form-control" value="<?= $data['tempat'] ?>">
      </div>

      <div class="form-group">
        <label>Angkatan</label>
        <input type="text" name="angkatan" class="form-control" value="<?= $data['angkatan'] ?>">
      </div>

      <div class="form-group">
        <label>Tahun</label>
        <input type="number" name="tahun" class="form-control" value="<?= $data['tahun'] ?>" required>
      </div>

      <div class="form-group">
        <label>Tanggal Pelaksanaan</label>
        <input type="date" name="date_reg" class="form-control" value="<?= $data['date_reg'] ?>" required>
      </div>

      <div class="form-group mt-4">
        <button type="submit" name="<?= $isEdit ? 'update' : 'simpan' ?>" class="btn btn-success">
          <i class="fa fa-save"></i> Simpan
        </button>
        <a href="home-admin.php?page=master-data-diklat" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
