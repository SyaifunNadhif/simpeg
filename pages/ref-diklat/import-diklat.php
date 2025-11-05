<?php
// import-diklat.php
include 'dist/koneksi.php';

require 'vendor/autoload.php'; // pastikan PhpSpreadsheet tersedia
use PhpOffice\PhpSpreadsheet\IOFactory;

$msg = '';
$preview = [];

if (isset($_POST['preview']) && isset($_FILES['file_excel']['tmp_name'])) {
  $file = $_FILES['file_excel']['tmp_name'];
  $spreadsheet = IOFactory::load($file);
  $sheet = $spreadsheet->getActiveSheet();
  $rows = $sheet->toArray();

  // Lewati baris header
  foreach (array_slice($rows, 1) as $row) {
    $nip         = trim($row[0]);
    $diklat      = trim($row[1]);
    $tempat      = trim($row[2]);
    $penyelenggara = trim($row[3]);
    $angkatan    = trim($row[4]);
    $tahun       = trim($row[5]);
    $tanggal     = trim($row[6]);

    // Ambil ID Pegawai
    $qPeg = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE nip = '$nip'");
    if ($qPeg && mysqli_num_rows($qPeg) > 0) {
      $id_peg = mysqli_fetch_assoc($qPeg)['id_peg'];
      $preview[] = [
        'id_peg' => $id_peg,
        'nip' => $nip,
        'diklat' => $diklat,
        'penyelenggara' => $penyelenggara,
        'tempat' => $tempat,
        'angkatan' => $angkatan,
        'tahun' => $tahun,
        'date_reg' => $tanggal
      ];
    }
  }
}

if (isset($_POST['simpan']) && isset($_POST['data'])) {
  $data = $_POST['data'];
  $sukses = 0;
  foreach ($data as $row) {
    $id_peg = $row['id_peg'];
    $diklat = mysqli_real_escape_string($conn, $row['diklat']);
    $penyelenggara = mysqli_real_escape_string($conn, $row['penyelenggara']);
    $tempat = mysqli_real_escape_string($conn, $row['tempat']);
    $angkatan = mysqli_real_escape_string($conn, $row['angkatan']);
    $tahun = $row['tahun'];
    $date_reg = $row['date_reg'];

    $ins = mysqli_query($conn, "INSERT INTO tb_diklat (id_peg, diklat, penyelenggara, tempat, angkatan, tahun, date_reg, created_by) VALUES
      ('$id_peg', '$diklat', '$penyelenggara', '$tempat', '$angkatan', '$tahun', '$date_reg', '{$_SESSION['id_user']}')");

    if ($ins) $sukses++;
  }
  $msg = "$sukses data berhasil disimpan.";
  $preview = [];
}
?>

<div class="card">
  <div class="card-header bg-info text-white">
    <h5 class="card-title">Upload Data Diklat (Excel)</h5>
  </div>
  <div class="card-body">
    <?php if ($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label>Pilih File Excel</label>
        <input type="file" name="file_excel" class="form-control" accept=".xlsx" required>
      </div>
      <button type="submit" name="preview" class="btn btn-primary">Preview</button>
      <a href="template/template_import_diklat.xlsx" class="btn btn-outline-secondary">Download Template</a>
      <a href="home-admin.php?page=master-data-diklat" class="btn btn-secondary">Batal</a>
    </form>

    <?php if ($preview) { ?>
    <hr>
    <h6>Preview Data:</h6>
    <form method="POST">
      <input type="hidden" name="simpan" value="1">
      <table class="table table-sm table-bordered">
        <thead>
          <tr>
            <th>NIP</th><th>Diklat</th><th>Tempat</th><th>Penyelenggara</th><th>Angkatan</th><th>Tahun</th><th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($preview as $row) { ?>
          <tr>
            <td><?= $row['nip'] ?></td>
            <td><?= $row['diklat'] ?></td>
            <td><?= $row['tempat'] ?></td>
            <td><?= $row['penyelenggara'] ?></td>
            <td><?= $row['angkatan'] ?></td>
            <td><?= $row['tahun'] ?></td>
            <td><?= $row['date_reg'] ?></td>
          </tr>
          <?php foreach ($row as $key => $val) {
            echo "<input type='hidden' name='data[][{$key}]' value='".htmlspecialchars($val, ENT_QUOTES)."'>";
          }} ?>
        </tbody>
      </table>
      <button type="submit" class="btn btn-success">Simpan Semua</button>
    </form>
    <?php } ?>
  </div>
</div>