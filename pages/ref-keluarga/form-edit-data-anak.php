<?php
// Pastikan koneksi database sudah di-include di home-admin.php atau panggil ulang jika perlu
include "dist/koneksi.php";

if (isset($_GET['id_anak'])) {
    $id_anak = mysqli_real_escape_string($conn, $_GET['id_anak']);
} else {
    die("Error. No ID Selected!");
}

// Ambil Data Anak & Nama Pegawai
$query = "SELECT tb_anak.*, p.nama AS nama_peg 
          FROM tb_anak 
          JOIN tb_pegawai p ON tb_anak.id_peg = p.id_peg 
          WHERE id_anak='$id_anak'";

$ambilData = mysqli_query($conn, $query);
$hasil = mysqli_fetch_array($ambilData);

if (!$hasil) {
    die("Data tidak ditemukan.");
}

$id_peg   = $hasil['id_peg'];
$nama_peg = $hasil['nama_peg'];

// PROSES UPDATE DATA (Jika satu file untuk form & proses)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $anak_ke    = $_POST['anak_ke'];
    $nik        = $_POST['nik'];
    $nama       = $_POST['nama'];
    $tmp_lhr    = $_POST['tmp_lhr'];
    $tgl_lhr    = date('Y-m-d', strtotime($_POST['tgl_lhr'])); // Format ke Y-m-d untuk database
    $pendidikan = $_POST['pendidikan'];
    $pekerjaan  = $_POST['pekerjaan'];
    $status_hub = $_POST['status_hub'];
    
    // Update Query
    $update = mysqli_query($conn, "UPDATE tb_anak SET 
                anak_ke='$anak_ke',
                nik='$nik',
                nama='$nama',
                tmp_lhr='$tmp_lhr',
                tgl_lhr='$tgl_lhr',
                pendidikan='$pendidikan',
                pekerjaan='$pekerjaan',
                status_hub='$status_hub'
                WHERE id_anak='$id_anak'");

    if ($update) {
        echo "<script>alert('Data Berhasil Diubah'); window.location='home-admin.php?page=view-detail-data-pegawai&id_peg=$id_peg#anak';</script>";
    } else {
        echo "<script>alert('Gagal Mengubah Data');</script>";
    }
}
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit Data Anak</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
          <li class="breadcrumb-item active">Edit Data Anak</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Edit Data Anak dari <b><?= $nama_peg ?></b></h3>
          </div>
          <div class="card-body">
            <form action="" class="form-horizontal" method="POST" enctype="multipart/form-data">
              
              <div class="row">
                <div class="col-sm-4">
                  <div class="form-group">
                    <label>ID Pegawai</label>
                    <input type="text" class="form-control" value="<?= $id_peg ?>" disabled>
                  </div>
                </div>
                <div class="col-sm-8">  
                  <div class="form-group">
                    <label>Nama Pegawai</label>
                    <input type="text" class="form-control" value="<?= $nama_peg ?>" disabled>
                  </div>
                </div>
              </div>

              <div class="row"> 
                <div class="col-sm-2">
                  <div class="form-group">
                    <label>Anak Ke</label>
                    <input type="number" name="anak_ke" required class="form-control" value="<?= $hasil['anak_ke'] ?>">
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="form-group">
                    <label>NIK</label>
                    <input type="text" name="nik" required class="form-control" value="<?= $hasil['nik'] ?>">
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>Nama Anak</label>
                    <input type="text" name="nama" required class="form-control" value="<?= $hasil['nama'] ?>">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>Tempat Lahir</label>
                    <input type="text" name="tmp_lhr" class="form-control" value="<?= $hasil['tmp_lhr'] ?>">
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">  
                    <label>Tanggal Lahir</label>
                    <div class="input-group date" id="tgl_lhr_picker" data-target-input="nearest">
                        <input type="text" name="tgl_lhr" class="form-control datetimepicker-input" data-target="#tgl_lhr_picker" value="<?= date('d-m-Y', strtotime($hasil['tgl_lhr'])) ?>" required/>
                        <div class="input-group-append" data-target="#tgl_lhr_picker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                  </div>  
                </div>  
              </div>

              <div class="row">
                <div class="col-sm-4">
                  <div class="form-group">
                    <label>Pendidikan</label>
                    <select name="pendidikan" class="form-control select2bs4">
                      <?php 
                        $pends = ["Belum Sekolah", "TK", "SD", "SLTP", "SLTA", "D3", "S1", "S2", "S3"];
                        foreach($pends as $p) {
                            $selected = ($hasil['pendidikan'] == $p) ? "selected" : "";
                            echo "<option value='$p' $selected>$p</option>";
                        }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="form-group">
                    <label>Pekerjaan</label>
                    <input type="text" name="pekerjaan" class="form-control" value="<?= $hasil['pekerjaan'] ?>">
                  </div>
                </div> 
                <div class="col-sm-4">
                  <div class="form-group">
                    <label>Status Hubungan</label>
                      <select name="status_hub" class="form-control select2bs4">
                        <option value="Anak Kandung" <?= ($hasil['status_hub']=='Anak Kandung')?"selected":""; ?>>Anak Kandung</option>
                        <option value="Anak Angkat" <?= ($hasil['status_hub']=='Anak Angkat')?"selected":""; ?>>Anak Angkat</option>
                        <option value="Anak Tiri" <?= ($hasil['status_hub']=='Anak Tiri')?"selected":""; ?>>Anak Tiri</option>
                      </select>
                  </div>
                </div>    
              </div>

              <div class="form-group mt-3">
                <div class="d-flex justify-content-between">
                  <a href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?= $id_peg ?>#anak" class="btn btn-secondary">Batal</a>
                  <button type="submit" name="edit" value="edit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
              </div>

            </form>
          </div>  
        </div>
      </div>
    </div>
  </div>  
</section>

<script>
  $(function () {
    // Inisialisasi Select2
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    });

    // Date picker
    $('#tgl_lhr_picker').datetimepicker({
        format: 'DD-MM-YYYY', // Format tampilan Indonesia
        locale: 'id'
    });
  });
</script>