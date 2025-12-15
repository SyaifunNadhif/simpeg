<?php
// edit-pasangan.php
// Pastikan koneksi database sudah ada
if (!isset($conn)) {
    include "dist/koneksi.php"; // sesuaikan path
}

// Helper keamanan output
function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Ambil ID_SI dari URL (lebih aman pakai filter)
$id_si = isset($_GET['id_si']) ? trim($_GET['id_si']) : '';

// Ambil Data Existing Pasangan & Nama Pegawai
$sql_cek = "SELECT s.*, p.nama AS nama_peg 
            FROM tb_suamiistri s 
            LEFT JOIN tb_pegawai p ON s.id_peg = p.id_peg 
            WHERE s.id_si = ?";
if ($stmt = $conn->prepare($sql_cek)) {
    $stmt->bind_param("s", $id_si);
    $stmt->execute();
    $res = $stmt->get_result();
    $data_cek = $res->fetch_array(MYSQLI_BOTH);
    $stmt->close();
} else {
    die("Query error: " . $conn->error);
}

if (!$data_cek) {
    echo "<div class='alert alert-danger'>Data tidak ditemukan!</div>";
    exit;
}

// Ambil list pekerjaan untuk dropdown pekerjaan
$pekerjaan_list = array();
$rpek = mysqli_query($conn, "SELECT id_pekerjaan, desc_pekerjaan FROM tb_master_pekerjaan ORDER BY desc_pekerjaan ASC");
while ($p = mysqli_fetch_assoc($rpek)) $pekerjaan_list[] = $p;

// Format tampilan tanggal (untuk form value) dd-mm-yyyy
$tgl_display = '';
if (!empty($data_cek['tgl_lhr']) && $data_cek['tgl_lhr'] != '0000-00-00') {
    $tgl_display = date('d-m-Y', strtotime($data_cek['tgl_lhr']));
}

// ==========================================
// PROSES SIMPAN DATA (UPDATE) - dengan prepared statement
// ==========================================
$success = false;
$error_msg = '';

if (isset($_POST['Ubah'])) {
    // Ambil data dari form dengan trim
    $id_si_post       = trim($_POST['id_si']);
    $id_peg_post      = trim($_POST['id_peg']);
    $nik              = trim($_POST['nik']);
    $nama             = trim($_POST['nama']);
    $tmp_lhr          = trim($_POST['tmp_lhr']);
    $tgl_lhr_input    = trim($_POST['tgl_lhr']); // Format: dd-mm-yyyy
    $pendidikan       = trim($_POST['pendidikan']);
    $id_pekerjaan     = trim($_POST['id_pekerjaan']);
    $status_hub       = trim($_POST['status_hub']);

    // Validasi sederhana (tambahan jika mau)
    if (empty($nama)) {
        $error_msg = "Nama pasangan tidak boleh kosong.";
    } else {
        // Ubah format tanggal dari dd-mm-yyyy ke yyyy-mm-dd untuk MySQL
        $tgl_sql = NULL;
        if (!empty($tgl_lhr_input)) {
            $date_parts = explode('-', $tgl_lhr_input);
            if (count($date_parts) == 3) {
                $tgl_sql = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
            } else {
                // biarkan null jika format salah
                $tgl_sql = NULL;
            }
        }

        // Prepared statement update
        $sql_ubah = "UPDATE tb_suamiistri SET
            nik = ?, nama = ?, tmp_lhr = ?, tgl_lhr = ?, pendidikan = ?, id_pekerjaan = ?, status_hub = ?
            WHERE id_si = ?";

        if ($stmt = $conn->prepare($sql_ubah)) {
            // Jika $tgl_sql null, kirim NULL ke DB -> gunakan bind dengan variable
            // bind_param tidak menerima null secara langsung dengan tipe s; kita pakai trick:
            // selalu bind sebagai string; jika null kita kirimkan NULL string dan gunakan COALESCE di DB ataupun biarkan.
            // Simpler: gunakan mysqli_stmt_bind_param normal, dan jika $tgl_sql === NULL kirimkan '' (kosong).
            $tgl_for_bind = $tgl_sql ?: NULL; // we'll handle null via bind_param with 's' and later convert empty string to NULL using IF(...='')
            // However MySQLi will insert empty string; for real NULL you'd need dynamic query. For simplicity: send NULL as NULL via types.
            // Implement dynamic parameter: if $tgl_sql === NULL, set tgl_lhr = NULL in query.
            $stmt->close();

            if ($tgl_sql === NULL) {
                $sql_ubah2 = "UPDATE tb_suamiistri SET
                    nik = ?, nama = ?, tmp_lhr = ?, tgl_lhr = NULL, pendidikan = ?, id_pekerjaan = ?, status_hub = ?
                    WHERE id_si = ?";
                $stmt2 = $conn->prepare($sql_ubah2);
                $stmt2->bind_param("sssssss", $nik, $nama, $tmp_lhr, $pendidikan, $id_pekerjaan, $status_hub, $id_si_post);
                $exec = $stmt2->execute();
                if ($exec) {
                    $success = true;
                } else {
                    $error_msg = "Gagal update: " . $stmt2->error;
                }
                $stmt2->close();
            } else {
                $stmt3 = $conn->prepare($sql_ubah);
                $stmt3->bind_param("ssssssss", $nik, $nama, $tmp_lhr, $tgl_sql, $pendidikan, $id_pekerjaan, $status_hub, $id_si_post);
                $exec = $stmt3->execute();
                if ($exec) {
                    $success = true;
                } else {
                    $error_msg = "Gagal update: " . $stmt3->error;
                }
                $stmt3->close();
            }
        } else {
            $error_msg = "Prepare failed: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Edit Data Pasangan - Modern</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <!-- Bootstrap 4.6 (kompatibel dengan AdminLTE 3) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
    <!-- Select2 (Bootstrap4 theme) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.6/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <style>
        /* sedikit polesan agar form terasa modern */
        .card-modern {
            border-radius: .75rem;
            box-shadow: 0 6px 18px rgba(20,20,50,0.08);
            overflow: hidden;
        }
        .card-header-modern {
            background: linear-gradient(90deg,#4f46e5,#06b6d4);
            color: #fff;
        }
        .required:after { content: " *"; color: #d9534f; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="row mb-3">
        <div class="col">
            <h2 class="mb-0">Edit Data Pasangan</h2>
            <small class="text-muted">Mengubah data pasangan untuk <strong><?php echo h($data_cek['nama_peg']); ?></strong></small>
        </div>
        <div class="col-auto">
            <a href="home-admin.php?page=profil-pegawai&id_peg=<?php echo h($data_cek['id_peg']); ?>#suamiistri" class="btn btn-outline-secondary">
                Kembali ke Profil
            </a>
        </div>
    </div>

    <div class="card card-modern">
        <div class="card-header card-header-modern d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="fas fa-user-edit mr-2"></i> Edit Pasangan</h5>
                <small>Perbarui informasi pasangan secara cepat dan aman.</small>
            </div>
            <div>
                <!-- placeholder untuk icon atau tombol tambahan -->
            </div>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" id="formEditPasangan" class="needs-validation" novalidate>
            <div class="card-body">
                <?php if ($error_msg): ?>
                    <div class="alert alert-danger"><?php echo h($error_msg); ?></div>
                <?php endif; ?>

                <input type="hidden" name="id_si" value="<?php echo h($data_cek['id_si']); ?>">
                <input type="hidden" name="id_peg" value="<?php echo h($data_cek['id_peg']); ?>">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="required">NIK</label>
                        <input type="text" name="nik" class="form-control" value="<?php echo h($data_cek['nik']); ?>" placeholder="Nomor Induk Kependudukan" maxlength="32" required>
                        <div class="invalid-feedback">NIK wajib diisi.</div>
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Nama Pasangan</label>
                        <input type="text" name="nama" class="form-control" value="<?php echo h($data_cek['nama']); ?>" placeholder="Nama Lengkap" required>
                        <div class="invalid-feedback">Nama wajib diisi.</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Tempat Lahir</label>
                        <input type="text" name="tmp_lhr" class="form-control" value="<?php echo h($data_cek['tmp_lhr']); ?>" placeholder="Kota Kelahiran">
                    </div>

                    <div class="form-group col-md-4">
                        <label>Tanggal Lahir</label>
                        <input type="text" name="tgl_lhr" id="tgl_lhr" class="form-control" value="<?php echo h($tgl_display); ?>" placeholder="dd-mm-yyyy" autocomplete="off">
                        <small class="form-text text-muted">Format: dd-mm-yyyy (contoh: 11-09-1993)</small>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Pendidikan</label>
                        <select name="pendidikan" class="form-control">
                            <option value="">- Pilih -</option>
                            <?php
                            $levels = array('SD', 'SLTP', 'SLTA', 'D3', 'S1', 'S2', 'S3');
                            foreach ($levels as $lvl) {
                                $selected = ($data_cek['pendidikan'] == $lvl) ? "selected" : "";
                                echo "<option value='".h($lvl)."' $selected>".h($lvl)."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row align-items-end">
                    <div class="form-group col-md-8">
                        <label>Pekerjaan</label>
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

                    <div class="form-group col-md-4">
                        <label>Hubungan</label>
                        <select name="status_hub" class="form-control">
                            <option value="Suami" <?php echo ($data_cek['status_hub'] == 'Suami') ? "selected" : ""; ?>>Suami</option>
                            <option value="Istri" <?php echo ($data_cek['status_hub'] == 'Istri') ? "selected" : ""; ?>>Istri</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <!-- <div>
                    <a href="home-admin.php?page=profil-pegawai&id_peg=<?php echo h($data_cek['id_peg']); ?>#suamiistri" class="btn btn-outline-secondary">
                        Batal
                    </a>
                </div> -->
                <div>
                    <button type="submit" name="Ubah" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- FontAwesome (untuk icon) -->
<script src="https://kit.fontawesome.com/a2d9b6f4c9.js" crossorigin="anonymous"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
$(function(){
    // Inisialisasi Select2 dengan tema bootstrap4
    if ($('.select2bs4').length) {
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    // Bootstrap custom validation
    (function() {
      'use strict';
      window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
          form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add('was-validated');
          }, false);
        });
      }, false);
    })();

    // Simple date input helper: bantu user memasukkan dd-mm-yyyy
    $('#tgl_lhr').on('input', function(){
        // otomatis tambahkan '-' setelah 2 dan 5 digit
        var v = $(this).val().replace(/[^0-9]/g, '');
        if (v.length >= 3 && v.length <= 4) v = v.slice(0,2) + '-' + v.slice(2);
        if (v.length >= 5 && v.length <= 8) v = v.slice(0,2) + '-' + v.slice(2,4) + '-' + v.slice(4,8);
        $(this).val(v);
    });
});
</script>

<?php if ($success): ?>
<script>
    // Tampilkan modal sukses dengan SweetAlert2 lalu redirect ke halaman profil
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        html: 'Data pasangan berhasil diubah. Anda akan diarahkan ke profil pegawai.',
        showConfirmButton: false,
        timer: 2000,
        backdrop: true,
        didClose: () => {
            // Redirect ke profil (gunakan id_peg dari PHP)
            window.location.href = 'home-admin.php?page=profil-pegawai&id_peg=<?php echo rawurlencode($id_peg_post); ?>#suamiistri';
        }
    });
</script>
<?php endif; ?>

</body>
</html>
