<?php
include 'dist/koneksi.php';

$mode = $_GET['mode'];
$id = $_GET['id'] ?? '';
$data = ['username'=>'', 'nama'=>'', 'level'=>'', 'password'=>''];

if ($mode == 'edit' && $id) {
  $q = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user = '$id'");
  $data = mysqli_fetch_assoc($q);
}
?>

<h3><?= ucfirst($mode) ?> User</h3>
<form action="modules/user/simpan-user.php" method="post">
  <input type="hidden" name="mode" value="<?= $mode ?>">
  <input type="text" name="id_user" class="form-control" value="<?= $data['id_user'] ?>" <?= $mode == 'edit' ? 'readonly' : '' ?> required>


  <div class="form-group">
    <label>Username</label>
    <input type="text" name="nama_user" class="form-control" value="<?= $data['nama_user'] ?>" required>
  </div>

  <div class="form-group">
    <label>Nama Lengkap</label>
    <input type="text" name="jabatan" class="form-control" value="<?= $data['jabatan'] ?>" required>
  </div>

  <div class="form-group">
    <label>Level Akses</label>
    <select name="hak_akses">
      <option value="admin" <?= $data['hak_akses']=='admin'?'selected':'' ?>>Admin</option>
      <option value="user" <?= $data['hak_akses']=='user'?'selected':'' ?>>User</option>
    </select>
  </div>

  <div class="form-group">
    <label>Status Aktif</label>
    <select name="status_aktif">
      <option value="Y" <?= $data['status_aktif']=='Y'?'selected':'' ?>>Aktif</option>
      <option value="N" <?= $data['status_aktif']=='N'?'selected':'' ?>>Nonaktif</option>
    </select>
  </div>

  <div class="form-group">
    <label>Password <?= $mode == 'edit' ? '(kosongkan jika tidak diubah)' : '' ?></label>
    <input type="password" name="password" class="form-control">
  </div>

  <button type="submit" class="btn btn-success">Simpan</button>
</form>
