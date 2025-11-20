<?php
// Pastikan koneksi database sudah ada
if (!isset($conn)) {
    include "dist/koneksi.php"; // Sesuaikan path jika perlu
}

// Helper untuk keamanan output
function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// 1. Ambil ID_SI dari URL
$id_si = (isset($_GET['id_si'])) ? $_GET['id_si'] : '';

// 2. Ambil Data Existing Pasangan & Nama Pegawai
$sql_cek = "SELECT s.*, p.nama AS nama_peg 
            FROM tb_suamiistri s 
            LEFT JOIN tb_pegawai p ON s.id_peg = p.id_peg 
            WHERE s.id_si = '" . mysqli_real_escape_string($conn, $id_si) . "'";
$query_cek = mysqli_query($conn, $sql_cek);
$data_cek = mysqli_fetch_array($query_cek, MYSQLI_BOTH);

// Validasi jika data tidak ditemukan
if (!$data_cek) {
    echo "<div class='alert alert-danger'>Data tidak ditemukan!</div>";
    exit;
}

// Ambil list pekerjaan untuk dropdown pekerjaan
$pekerjaan_list = array();
$rpek = mysqli_query($conn, "SELECT id_pekerjaan, desc_pekerjaan FROM tb_master_pekerjaan ORDER BY desc_pekerjaan ASC");
while ($p = mysqli_fetch_assoc($rpek)) $pekerjaan_list[] = $p;

// Format tampilan tanggal (untuk form value)
$tgl_display = '';
if (!empty($data_cek['tgl_lhr']) && $data_cek['tgl_lhr'] != '0000-00-00') {
    $tgl_display = date('d-m-Y', strtotime($data_cek['tgl_lhr']));
}

// ==========================================
// PROSES SIMPAN DATA (UPDATE)
// ==========================================
if (isset($_POST['Ubah'])) {
    // Ambil data dari form
    $id_si_post       = $_POST['id_si'];
    $id_peg_post      = $_POST['id_peg']; // diambil dari hidden input
    $nik              = $_POST['nik'];
    $nama             = $_POST['nama'];
    $tmp_lhr          = $_POST['tmp_lhr'];
    $tgl_lhr_input    = $_POST['tgl_lhr']; // Format: dd-mm-yyyy
    $pendidikan       = $_POST['pendidikan'];
    $id_pekerjaan     = $_POST['id_pekerjaan'];
    $status_hub       = $_POST['status_hub'];

    // Ubah format tanggal dari dd-mm-yyyy ke yyyy-mm-dd untuk MySQL
    $tgl_sql = NULL;
    if (!empty($tgl_lhr_input)) {
        $date_parts = explode('-', $tgl_lhr_input);
        if (count($date_parts) == 3) {
            // $date_parts[2] = Tahun, [1] = Bulan, [0] = Hari
            $tgl_sql = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
        }
    }

    // Query Update
    $sql_ubah = "UPDATE tb_suamiistri SET
        nik          = '$nik',
        nama         = '$nama',
        tmp_lhr      = '$tmp_lhr',
        tgl_lhr      = '$tgl_sql',
        pendidikan   = '$pendidikan',
        id_pekerjaan = '$id_pekerjaan',
        status_hub   = '$status_hub'
        WHERE id_si  = '$id_si_post'";

    $query_ubah = mysqli_query($conn, $sql_ubah);

    if ($query_ubah) {
        echo "<script>
            alert('Ubah Data Berhasil');
            window.location = 'home-admin.php?page=profil-pegawai&id_peg=" . $id_peg_post . "#suamiistri';
        </script>";
    } else {
        echo "<script>
            alert('Ubah Data Gagal: " . mysqli_error($conn) . "');
        </script>";
    }
}
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Data Pasangan</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Edit Data Pasangan</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Data Pasangan dari <b><?php echo h($data_cek['nama_peg']); ?></b></h3>
            </div>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="card-body">
                    
                    <input type="hidden" name="id_si" value="<?php echo h($data_cek['id_si']); ?>">

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">ID Pegawai</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" value="<?php echo h($data_cek['id_peg'] . ' - ' . $data_cek['nama_peg']); ?>" readonly>
                            <input type="hidden" name="id_peg" value="<?php echo h($data_cek['id_peg']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">NIK</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="nik" value="<?php echo h($data_cek['nik']); ?>" placeholder="Nomor Induk Kependudukan">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Nama Pasangan</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="nama" value="<?php echo h($data_cek['nama']); ?>" placeholder="Nama Lengkap">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Tempat Lahir</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="tmp_lhr" value="<?php echo h($data_cek['tmp_lhr']); ?>" placeholder="Kota Kelahiran">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Tanggal Lahir</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="tgl_lhr" value="<?php echo h($tgl_display); ?>" placeholder="dd-mm-yyyy" autocomplete="off">
                            <small class="text-muted">Format: Tanggal-Bulan-Tahun (Contoh: 11-09-1993)</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Pendidikan</label>
                        <div class="col-sm-4">
                            <select name="pendidikan" class="form-control">
                                <option value="">- Pilih -</option>
                                <?php
                                $levels = array('SD', 'SLTP', 'SLTA', 'D3', 'S1', 'S2', 'S3');
                                foreach ($levels as $lvl) {
                                    $selected = ($data_cek['pendidikan'] == $lvl) ? "selected" : "";
                                    echo "<option value='$lvl' $selected>$lvl</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Pekerjaan</label>
                        <div class="col-sm-6">
                            <select name="id_pekerjaan" class="form-control select2bs4">
                                <option value="">- Pilih Pekerjaan -</option>
                                <?php
                                foreach ($pekerjaan_list as $pk) {
                                    $sel = ($data_cek['id_pekerjaan'] == $pk['id_pekerjaan']) ? 'selected' : '';
                                    echo '<option value="'.h($pk['id_pekerjaan']).'" '.$sel.'>'.h($pk['desc_pekerjaan']).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Hubungan</label>
                        <div class="col-sm-4">
                            <select name="status_hub" class="form-control">
                                <option value="Suami" <?php echo ($data_cek['status_hub'] == 'Suami') ? "selected" : ""; ?>>Suami</option>
                                <option value="Istri" <?php echo ($data_cek['status_hub'] == 'Istri') ? "selected" : ""; ?>>Istri</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <input type="submit" name="Ubah" value="Simpan Perubahan" class="btn btn-primary">
                    <a href="home-admin.php?page=profil-pegawai&id_peg=<?php echo $data_cek['id_peg']; ?>#suamiistri" title="Kembali" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
  $(function () {
    if($('.select2bs4').length) {
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });
    }
  });
</script>