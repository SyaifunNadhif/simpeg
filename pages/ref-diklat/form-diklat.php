<?php
/*********************************************************
 * FILE    : pages/diklat/form-diklat.php
 * MODULE  : Form Diklat (Fix Searchable Dropdown)
 * VERSION : v2.1
 *********************************************************/

if (session_id() == '') session_start();
include 'dist/koneksi.php';
include 'dist/library.php';

// --- 1. LOGIKA HAK AKSES & FILTER ---
$hak_akses      = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$kode_kantor    = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';

// Query Filter Pegawai
$where_pegawai = "WHERE 1=1";
if ($hak_akses !== 'admin') {
    // Hanya tampilkan pegawai di unit kerja user login (Aktif)
    $where_pegawai .= " AND id_peg IN (
        SELECT id_peg FROM tb_jabatan 
        WHERE unit_kerja = '$kode_kantor' AND status_jab = 'Aktif'
    )";
}

// --- 2. INISIALISASI DATA ---
$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
$isEdit = ($id != '');
$data = [
  "id_peg" => "", "diklat" => "", "penyelenggara" => "", "tempat" => "",
  "angkatan" => "", "tahun" => date('Y'), "date_reg" => date('Y-m-d')
];

if ($isEdit) {
  $q = mysqli_query($conn, "SELECT * FROM tb_diklat WHERE id_diklat = '$id'");
  if ($q && mysqli_num_rows($q) > 0) {
    $data = mysqli_fetch_assoc($q);
  } else {
    echo "<script>window.location='home-admin.php?page=master-data-diklat';</script>";
    exit;
  }
}

// Ambil Daftar Pegawai (Filtered)
$qPegawai = mysqli_query($conn, "SELECT id_peg, nama FROM tb_pegawai $where_pegawai ORDER BY nama ASC");
?>

<style>
    .card-modern { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: #fff; }
    .form-header-modern { background: #17a2b8; color: #fff; border-bottom: 1px solid #f1f1f1; padding: 20px; border-radius: 15px 15px 0 0; }
    .input-modern { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px; height: 45px; width: 100%; }
    .input-modern:focus { border-color: #17a2b8; box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.2); outline: none; }
    .btn-modern { border-radius: 50px; padding: 10px 30px; font-weight: 600; transition: 0.3s; }
    .btn-modern:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .form-label-modern { font-size: 0.8rem; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 8px; }
    
    /* Fix Style Select2 agar sesuai tema Modern */
    .select2-container--bootstrap4 .select2-selection--single {
        height: 45px !important;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        padding-top: 8px !important;
    }
</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.8rem;">Form Diklat</h1>
                <p class="text-muted mb-0"><?= $isEdit ? 'Edit data' : 'Tambah data baru' ?></p>
            </div>
            <div>
                <a href="home-admin.php?page=master-data-diklat" class="btn btn-light rounded-pill border shadow-sm">
                    <i class="fa fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content mt-3">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                
                <div class="card card-modern">
                    <div class="form-header-modern">
                        <h5 class="m-0 font-weight-bold"><i class="fas fa-edit mr-2"></i> <?= $isEdit ? 'Edit' : 'Input' ?> Data Diklat</h5>
                    </div>
                    
                    <div class="card-body p-4">
                        <form method="POST" action="home-admin.php?page=proses-diklat">
                            <input type="hidden" name="id_diklat" value="<?= $id ?>">

                            <div class="form-group mb-4">
                                <label class="form-label-modern">Pilih Pegawai <span class="text-danger">*</span></label>
                                <select name="id_peg" class="form-control select2bs4" required style="width: 100%;">
                                    <option value="">-- Ketik Nama Pegawai --</option>
                                    <?php while ($p = mysqli_fetch_assoc($qPegawai)) { ?>
                                        <option value="<?= $p['id_peg'] ?>" <?= $data['id_peg'] == $p['id_peg'] ? 'selected' : '' ?>>
                                            <?= $p['nama'] ?> (<?= $p['id_peg'] ?>)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Nama Diklat / Pelatihan <span class="text-danger">*</span></label>
                                        <input type="text" name="diklat" class="form-control input-modern" value="<?= htmlspecialchars($data['diklat']) ?>" placeholder="Contoh: Pelatihan Kepemimpinan" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Penyelenggara</label>
                                        <input type="text" name="penyelenggara" class="form-control input-modern" value="<?= htmlspecialchars($data['penyelenggara']) ?>" placeholder="Nama Lembaga / Instansi">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Tempat Pelaksanaan</label>
                                        <input type="text" name="tempat" class="form-control input-modern" value="<?= htmlspecialchars($data['tempat']) ?>" placeholder="Kota / Lokasi">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Tahun <span class="text-danger">*</span></label>
                                        <input type="number" name="tahun" class="form-control input-modern" value="<?= $data['tahun'] ?>" min="1900" max="2099" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Angkatan</label>
                                        <input type="text" name="angkatan" class="form-control input-modern" value="<?= htmlspecialchars($data['angkatan']) ?>" placeholder="Contoh: V, X, 2024">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Tanggal Input</label>
                                        <input type="date" name="date_reg" class="form-control input-modern" value="<?= $data['date_reg'] ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-right mt-4 border-top pt-4">
                                <a href="home-admin.php?page=master-data-diklat" class="btn btn-light btn-modern mr-2 border">Batal</a>
                                <button type="submit" name="<?= $isEdit ? 'update' : 'simpan' ?>" class="btn btn-info btn-modern shadow-sm">
                                    <i class="fa fa-save mr-2"></i> Simpan Data
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
    // Inisialisasi Select2 dengan tema Bootstrap 4
    $('.select2bs4').select2({
      theme: 'bootstrap4',
      placeholder: "Ketik Nama Pegawai...",
      allowClear: true,
      width: '100%' // Pastikan lebar 100%
    });
  });
</script>