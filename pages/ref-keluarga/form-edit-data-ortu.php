<?php
// Pastikan koneksi database sudah ada atau include manual
if (!isset($conn)) {
    include "dist/koneksi.php"; 
}

// Ambil ID ORTU
if (isset($_GET['id_ortu'])) {
    $id_ortu = mysqli_real_escape_string($conn, $_GET['id_ortu']);
} else {
    die("Error. No Kode Selected!");
}

// Ambil Data Orang Tua & Nama Pegawai
$query = "SELECT tb_ortu.*, 
          (SELECT nama FROM tb_pegawai WHERE id_peg=tb_ortu.id_peg) AS nama_peg 
          FROM tb_ortu 
          WHERE id_ortu='$id_ortu'";

$ambilData = mysqli_query($conn, $query);
$hasil = mysqli_fetch_array($ambilData);

if (!$hasil) {
    die("Data tidak ditemukan.");
}

$id_peg   = $hasil['id_peg'];
$nama_peg = $hasil['nama_peg'];
$nik      = $hasil['nik'];

// Format Tanggal Lahir untuk Tampilan (dd-mm-yyyy)
$tgl_lhr_view = "";
if (!empty($hasil['tgl_lhr']) && $hasil['tgl_lhr'] != '0000-00-00') {
    $tgl_lhr_view = date('d-m-Y', strtotime($hasil['tgl_lhr']));
}

// ==========================================
// PROSES SIMPAN DATA (UPDATE)
// ==========================================
if (isset($_POST['edit'])) {
    $nik_baru       = $_POST['nik'];
    $nama_baru      = $_POST['nama'];
    $tmp_lhr        = $_POST['tmp_lhr'];
    $tgl_lhr_input  = $_POST['tgl_lhr'];
    $pendidikan     = $_POST['pendidikan'];
    $id_pekerjaan   = $_POST['id_pekerjaan']; // Kita ambil ID yang dipilih (misal: 8)
    $status_hub     = $_POST['status_hub'];

    // 1. Cari Nama Pekerjaan berdasarkan ID yang dipilih agar kolom 'pekerjaan' terisi otomatis
    $pekerjaan_teks = ""; 
    if(!empty($id_pekerjaan)){
        $cek_job = mysqli_query($conn, "SELECT desc_pekerjaan FROM tb_master_pekerjaan WHERE id_pekerjaan='$id_pekerjaan'");
        if($data_job = mysqli_fetch_assoc($cek_job)){
            $pekerjaan_teks = $data_job['desc_pekerjaan']; // Ini isinya "Wiraswasta", "PNS", dll
        }
    }

    // 2. Konversi Tanggal ke Format MySQL (yyyy-mm-dd)
    $tgl_lhr_sql = NULL;
    if (!empty($tgl_lhr_input)) {
        $date = DateTime::createFromFormat('d-m-Y', $tgl_lhr_input);
        if ($date) {
            $tgl_lhr_sql = $date->format('Y-m-d');
        }
    }

    // 3. Update Database (Simpan ID dan Teks Pekerjaan sekaligus)
    $update = mysqli_query($conn, "UPDATE tb_ortu SET 
        nik         = '$nik_baru',
        nama        = '$nama_baru',
        tmp_lhr     = '$tmp_lhr',
        tgl_lhr     = '$tgl_lhr_sql',
        pendidikan  = '$pendidikan',
        id_pekerjaan= '$id_pekerjaan',
        pekerjaan   = '$pekerjaan_teks', 
        status_hub  = '$status_hub'
        WHERE id_ortu='$id_ortu'");

    if ($update) {
        echo "<script>
            alert('Data Berhasil Diubah');
            window.location='home-admin.php?page=view-detail-data-pegawai&id_peg=$id_peg#ortu';
        </script>";
    } else {
        echo "<script>alert('Gagal Mengubah Data: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Data Orang Tua</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
                    <li class="breadcrumb-item active">Edit Data Orang Tua</li>
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
                        <h3 class="card-title">Edit Data Orang Tua dari <b><?= $nama_peg ?></b> (<?= $id_peg ?>)</h3>
                    </div>
                    <div class="card-body">
                        <form action="" class="form-horizontal" method="POST" enctype="multipart/form-data">
                            
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>ID Pegawai</label>
                                        <input type="text" value="<?= $id_peg ?>" class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="col-sm-8">
                                    <div class="form-group">
                                        <label>Nama Pegawai</label>
                                        <input type="text" style="text-transform:uppercase" value="<?= $nama_peg ?>" class="form-control" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>NIK</label>
                                        <input type="text" name="nik" value="<?= $hasil['nik'] ?>" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-sm-8">
                                    <div class="form-group">
                                        <label>Nama Orang Tua</label>
                                        <input type="text" name="nama" value="<?= $hasil['nama'] ?>" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Tempat Lahir</label>
                                        <input type="text" name="tmp_lhr" value="<?= $hasil['tmp_lhr'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Tanggal Lahir</label>
                                        <div class="input-group date" id="tgl_lhr_picker" data-target-input="nearest">
                                            <input type="text" name="tgl_lhr" value="<?= $tgl_lhr_view ?>" class="form-control datetimepicker-input" data-target="#tgl_lhr_picker" placeholder="dd-mm-yyyy"/>
                                            <div class="input-group-append" data-target="#tgl_lhr_picker" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Pendidikan</label>
                                        <select name="pendidikan" class="form-control select2bs4">
                                            <?php
                                            $pends = ["SD", "SLTP", "SLTA", "D3", "S1", "S2", "S3", "Tidak Sekolah"];
                                            foreach ($pends as $p) {
                                                $selected = ($hasil['pendidikan'] == $p) ? "selected" : "";
                                                echo "<option value='$p' $selected>$p</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Pekerjaan</label>
                                        <select name="id_pekerjaan" class="form-control select2bs4">
                                            <option value="">- Pilih Pekerjaan -</option>
                                            <?php
                                            // Ambil data master pekerjaan
                                            $sql_pek = mysqli_query($conn, "SELECT * FROM tb_master_pekerjaan ORDER BY desc_pekerjaan ASC");
                                            while($pk = mysqli_fetch_array($sql_pek)) {
                                                // Cek jika id_pekerjaan sama dengan data di database
                                                $selected_pek = ($hasil['id_pekerjaan'] == $pk['id_pekerjaan']) ? "selected" : "";
                                                echo "<option value='".$pk['id_pekerjaan']."' ".$selected_pek.">".$pk['desc_pekerjaan']."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Hubungan</label>
                                        <select name="status_hub" class="form-control select2bs4">
                                            <option value="Ayah Kandung" <?= ($hasil['status_hub'] == 'Ayah Kandung') ? "selected" : "" ?>>Ayah Kandung</option>
                                            <option value="Ibu Kandung" <?= ($hasil['status_hub'] == 'Ibu Kandung') ? "selected" : "" ?>>Ibu Kandung</option>
                                            <option value="Ayah Mertua" <?= ($hasil['status_hub'] == 'Ayah Mertua') ? "selected" : "" ?>>Ayah Mertua</option>
                                            <option value="Ibu Mertua" <?= ($hasil['status_hub'] == 'Ibu Mertua') ? "selected" : "" ?>>Ibu Mertua</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <div class="d-flex justify-content-between">
                                    <a href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?= $id_peg ?>#ortu" class="btn btn-default">Batal</a>
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
        // Initialize Select2 untuk tampilan dropdown yang lebih bagus
        if($('.select2bs4').length) {
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
        }

        // Date picker
        $('#tgl_lhr_picker').datetimepicker({
            format: 'DD-MM-YYYY', // Format Indonesia
            locale: 'id'
        });
    });
</script>