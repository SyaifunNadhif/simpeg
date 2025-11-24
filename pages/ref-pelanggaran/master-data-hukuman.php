<?php
/*********************************************************
 * FILE    : pages/pelanggaran/master-data-hukuman.php
 * MODULE  : Proses Simpan Hukuman (FIX FETCH ARRAY ERROR)
 * VERSION : v2.6
 *********************************************************/

include "dist/koneksi.php"; // Pastikan $conn ada di sini

// Sembunyikan warning PHP agar tidak merusak tampilan JSON/Alert (Opsional)
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

// --- FUNGSI GENERATE ID OTOMATIS ---
function kdauto($conn, $tabel, $inisial){
    $struktur = mysqli_query($conn, "SELECT * FROM $tabel LIMIT 1");
    $field    = mysqli_fetch_field_direct($struktur, 0)->name;
    
    $qry  = mysqli_query($conn, "SELECT max(".$field.") as max_id FROM ".$tabel);
    $row  = mysqli_fetch_array($qry);
    
    if ($row['max_id'] == "") {
        $angka = 0;
    } else {
        $angka = substr($row['max_id'], strlen($inisial));
    }
    
    $angka++;
    // Padding angka jadi 3 digit (misal: 001)
    return $inisial . str_pad($angka, 3, "0", STR_PAD_LEFT);
}

$status_simpan = '';

if (isset($_POST['save'])) {
    
    // 1. Tangkap & Sanitasi Input
    $id_peg         = isset($_POST['id_peg']) ? mysqli_real_escape_string($conn, $_POST['id_peg']) : '';
    $hukuman        = isset($_POST['hukuman']) ? mysqli_real_escape_string($conn, $_POST['hukuman']) : '';
    $keterangan     = isset($_POST['keterangan']) ? mysqli_real_escape_string($conn, $_POST['keterangan']) : '';
    $pejabat_sk     = isset($_POST['pejabat_sk']) ? mysqli_real_escape_string($conn, $_POST['pejabat_sk']) : '';
    $jabatan_sk     = isset($_POST['jabatan_sk']) ? mysqli_real_escape_string($conn, $_POST['jabatan_sk']) : '';
    $no_sk          = isset($_POST['no_sk']) ? mysqli_real_escape_string($conn, $_POST['no_sk']) : '';
    
    $tgl_sk = date('Y-m-d');
    if(!empty($_POST['tgl_sk'])){
        $tgl_sk = date('Y-m-d', strtotime($_POST['tgl_sk']));
    }

    // Data Tambahan
    $date_reg       = date('Y-m-d H:i:s');
    // $created_by     = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'admin';

    // Data Pemulihan
    $pejabat_pulih  = isset($_POST['pejabat_pulih']) ? mysqli_real_escape_string($conn, $_POST['pejabat_pulih']) : '';
    $jabatan_pulih  = isset($_POST['jabatan_pulih']) ? mysqli_real_escape_string($conn, $_POST['jabatan_pulih']) : '';
    $no_pulih       = isset($_POST['no_pulih']) ? mysqli_real_escape_string($conn, $_POST['no_pulih']) : '';
    
    $tgl_pulih_sql = "NULL";
    if(!empty($_POST['tgl_pulih'])){
        $tgl_pulih_val = date('Y-m-d', strtotime($_POST['tgl_pulih']));
        $tgl_pulih_sql = "'$tgl_pulih_val'";
    }

    // 2. Validasi Wajib
    if (empty($id_peg) || empty($hukuman)) {
        $status_simpan = 'kosong';
    } else {
        // 3. Generate ID
        $id_hukum = kdauto($conn, "tb_hukuman", "H");

        // 4. Ambil Data Pangkat Terakhir (SAFE MODE)
        // Default nilai jika data tidak ditemukan
        $gol = '-'; 
        $pangkat = '-';
        
        $tP = mysqli_query($conn, "SELECT gol, pangkat FROM tb_pangkat WHERE id_peg='$id_peg' AND status_pan='Aktif' ORDER BY tgl_sk DESC LIMIT 1");
        // Cek apakah query berhasil DAN ada datanya
        if ($tP && mysqli_num_rows($tP) > 0) {
            $gp = mysqli_fetch_array($tP);
            $gol     = $gp['gol'];
            $pangkat = $gp['pangkat'];
        }

        // 5. Ambil Data Jabatan/Eselon Terakhir (SAFE MODE - FIX LINE 76)
        // Default nilai jika data tidak ditemukan
        $eselon = '-';

        $tJ = mysqli_query($conn, "SELECT eselon FROM tb_jabatan WHERE id_peg='$id_peg' AND status_jab='Aktif' ORDER BY tmt_jabatan DESC LIMIT 1");
        // FIX: Cek dulu sebelum fetch_array agar tidak error boolean given
        if ($tJ && mysqli_num_rows($tJ) > 0) {
            $esl = mysqli_fetch_array($tJ);
            $eselon = $esl['eselon'];
        }

        // 6. Insert Data
        $insert = "INSERT INTO tb_hukuman (
            id_hukum, id_peg, hukuman, keterangan, 
            pejabat_sk, jabatan_sk, no_sk, tgl_sk, 
            pejabat_pulih, jabatan_pulih, no_pulih, tgl_pulih, 
            gol, pangkat, eselon, 
            date_reg
        ) VALUES (
            '$id_hukum', '$id_peg', '$hukuman', '$keterangan',
            '$pejabat_sk', '$jabatan_sk', '$no_sk', '$tgl_sk',
            '$pejabat_pulih', '$jabatan_pulih', '$no_pulih', $tgl_pulih_sql,
            '$gol', '$pangkat', '$eselon',
            '$date_reg'
        )";

        $query_insert = mysqli_query($conn, $insert);

        if ($query_insert) {
            $status_simpan = 'sukses';
        } else {
            // Jika gagal, log errornya (bisa dilihat di inspect element network response jika perlu)
            $status_simpan = 'gagal';
        }
    }
}
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Proses Data</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-outline card-danger text-center">
                    <div class="card-body">
                        <i class="fas fa-spinner fa-3x text-danger mb-3 fa-spin"></i>
                        <h4>Menyimpan Data...</h4>
                        <p class="text-muted">Mohon tunggu sebentar.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var status = "<?= $status_simpan ?>";

    if (status == 'sukses') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data pelanggaran berhasil disimpan.',
            showConfirmButton: false,
            timer: 2000
        }).then(function() {
            window.location.href = 'home-admin.php?page=form-view-data-pelanggaran'; 
        });
    } else if (status == 'kosong') {
        Swal.fire({
            icon: 'warning',
            title: 'Data Tidak Lengkap',
            text: 'Mohon lengkapi data wajib (Pegawai, Sanksi, No SK).'
        }).then(function() {
            window.history.back();
        });
    } else if (status == 'gagal') {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.'
        }).then(function() {
            window.history.back();
        });
    }
</script>