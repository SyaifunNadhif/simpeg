<?php
$page_title = "Tambah";
$page_subtitle = "Pegawai";
$breadcrumbs = [
  ["label" => "Dashboard", "url" => "home-admin.php"],
  ["label" => "Tambah Pegawai"]
];
include "komponen/header.php";
include 'dist/koneksi.php';
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'tambah';
$id_peg = isset($_GET['id']) ? $_GET['id'] : null;

$data = array(
  'id_peg' => '', 'nip' => '', 'nama' => '', 'tempat_lhr' => '', 'tgl_lhr' => '',
  'agama' => '', 'jk' => '', 'gol_darah' => '', 'status_nikah' => '', 'status_kepeg' => '',
  'alamat' => '', 'telp' => '', 'email' => '', 'bpjstk' => '', 'bpjskes' => '', 'foto' => ''
);

if ($mode == 'edit' && $id_peg) {
  $q = mysqli_query($conn, "SELECT * FROM tb_pegawai WHERE id_peg='".$id_peg."'");
  $data = mysqli_fetch_assoc($q);
  if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.location='home-admin.php?page=master-data-pegawai';</script>";
    exit;
  }
}
?>

<div class="container" style="max-width: 100%;">
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h5 class="m-0">Form <?= ucfirst($mode) ?> Data Pegawai</h5>
    </div>
    <div class="card-body">
      <form action="pages/pegawai/simpan-data-pegawai.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">
        <?php if ($mode == 'edit'): ?>
          <input type="hidden" name="id_peg" value="<?php echo htmlspecialchars($data['id_peg']); ?>">
        <?php endif; ?>

        <div class="card mb-3">
          <div class="card-header bg-primary text-white"><strong>Data Utama</strong></div>
          <div class="card-body">
            <div class="form-group">
              <label>ID Pegawai</label>
              <input type="text" name="id_peg" class="form-control" maxlength="12" value="<?php echo htmlspecialchars($data['id_peg']); ?>" <?php echo $mode == 'edit' ? 'readonly' : 'required'; ?> autofocus>
            </div>
            <div class="form-group">
              <label>Nama Pegawai</label>
              <input type="text" name="nama" class="form-control" maxlength="64" value="<?php echo htmlspecialchars($data['nama']); ?>" required>
            </div>
            <div class="form-group">
              <label>NIK</label>
              <input type="text" name="nip" class="form-control" maxlength="16" value="<?php echo htmlspecialchars($data['nip']); ?>" required>
            </div>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header bg-info text-white"><strong>Data Pribadi</strong></div>
          <div class="card-body">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Tempat Lahir</label>
                <input type="text" name="tempat_lhr" class="form-control" maxlength="64" value="<?php echo htmlspecialchars($data['tempat_lhr']); ?>" required>
              </div>
              <div class="form-group col-md-6">
                <label>Tanggal Lahir</label>
                <input type="date" name="tgl_lhr" class="form-control" value="<?php echo htmlspecialchars($data['tgl_lhr']); ?>" required>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-4">
                <label>Agama</label>
                <select name="agama" class="form-control" required>
                  <option value="">Pilih</option>
                  <?php
                    $agama = array('Islam','Protestan','Katolik','Hindu','Budha','KongHuCu');
                    foreach ($agama as $a) {
                      echo "<option value='$a'" . ($data['agama'] == $a ? " selected" : "") . ">$a</option>";
                    }
                  ?>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label>Jenis Kelamin</label>
                <select name="jk" class="form-control" required>
                  <option value="">Pilih</option>
                  <option value="Laki-laki" <?php echo $data['jk'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                  <option value="Perempuan" <?php echo $data['jk'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label>Golongan Darah</label>
                <select name="gol_darah" class="form-control">
                  <option value="">Pilih</option>
                  <?php foreach (array('A','B','AB','O') as $gol) {
                    echo "<option value='$gol'" . ($data['gol_darah'] == $gol ? " selected" : "") . ">$gol</option>";
                  } ?>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Status Pernikahan</label>
                <select name="status_nikah" class="form-control">
                  <option value="">Pilih</option>
                  <?php foreach (array('Menikah','Belum Menikah','Janda','Duda') as $s) {
                    echo "<option value='$s'" . ($data['status_nikah'] == $s ? " selected" : "") . ">$s</option>";
                  } ?>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label>Status Kepegawaian</label>
                <select name="status_kepeg" class="form-control" required>
                  <option value="">Pilih</option>
                  <?php foreach (array('Outsource','Kontrak','Tetap') as $s) {
                    echo "<option value='$s'" . ($data['status_kepeg'] == $s ? " selected" : "") . ">$s</option>";
                  } ?>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header bg-success text-white"><strong>Kontak & Jaminan</strong></div>
          <div class="card-body">
            <div class="form-group">
              <label>Alamat</label>
              <textarea name="alamat" class="form-control" required><?php echo htmlspecialchars($data['alamat']); ?></textarea>
            </div>
            <div class="form-row">
              <div class="form-group col-md-3">
                <label>No. Telp</label>
                <input type="text" name="telp" class="form-control" maxlength="13" value="<?php echo htmlspecialchars($data['telp']); ?>" required>
              </div>
              <div class="form-group col-md-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($data['email']); ?>" required>
              </div>
              <div class="form-group col-md-3">
                <label>No. BPJS TK</label>
                <input type="text" name="bpjstk" class="form-control" maxlength="12" value="<?php echo htmlspecialchars($data['bpjstk']); ?>" required>
              </div>
              <div class="form-group col-md-3">
                <label>No. BPJS Kesehatan</label>
                <input type="text" name="bpjskes" class="form-control" maxlength="12" value="<?php echo htmlspecialchars($data['bpjskes']); ?>">
              </div>
            </div>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header bg-secondary text-white"><strong>Lampiran</strong></div>
          <div class="card-body">
            <div class="form-group">
              <label>Foto</label>
              <input type="file" name="foto" class="form-control">
              <?php if ($mode == 'edit' && $data['foto']): ?>
                <small>File saat ini: <?php echo htmlspecialchars($data['foto']); ?></small>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="text-left">
          <button type="submit" class="btn btn-primary">Simpan</button>
          <a href="javascript:history.back()" class="btn btn-secondary">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var forms = document.getElementsByClassName('needs-validation');
    Array.prototype.forEach.call(forms, function (form) {
      form.addEventListener('submit', function (event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });

    var inputs = document.querySelectorAll('input, select, textarea');
    for (var i = 0; i < inputs.length - 1; i++) {
      inputs[i].addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          var index = Array.prototype.indexOf.call(inputs, this);
          inputs[index + 1].focus();
        }
      });
    }
  });
</script>
