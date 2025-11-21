<?php
/*********************************************************
 * FILE    : pages/mutasi/master-data-mutasi.php
 * MODULE  : Proses Simpan Mutasi
 * VERSION : v2.0 (MySQLi & SweetAlert)
 *********************************************************/

include "dist/koneksi.php"; // Pastikan $conn ada di sini

// --- FUNGSI GENERATE ID OTOMATIS (Updated to MySQLi) ---
function kdauto($conn, $tabel, $inisial){
    // Ambil nama kolom pertama (biasanya Primary Key)
    $struktur = mysqli_query($conn, "SELECT * FROM $tabel LIMIT 1");
    $field    = mysqli_fetch_field_direct($struktur, 0)->name;
    
    // Cari nilai max
    $qry  = mysqli_query($conn, "SELECT max(".$field.") as max_id FROM ".$tabel);
    $row  = mysqli_fetch_array($qry);
    
    if ($row['max_id'] == "") {
        $angka = 0;
    } else {
        $angka = substr($row['max_id'], strlen($inisial));
    }
    
    $angka++;
    $angka_str = strval($angka);
    $tmp = "";
    // Padding 0 (misal 001) - sesuaikan panjang jika perlu
    for($i=1; $i <= (3 - strlen($angka_str)); $i++) {
        $tmp = $tmp."0";
    }
    return $inisial.$tmp.$angka_str;
}

// Variabel status untuk SweetAlert
$status_simpan = '';

if (isset($_POST['save'])) {
    // 1. Tangkap & Sanitasi Input
    $id_peg     = mysqli_real_escape_string($conn, $_POST['id_peg']);
    $jns_mutasi = mysqli_real_escape_string($conn, $_POST['jns_mutasi']);
    $tgl_mutasi = date('Y-m-d', strtotime($_POST['tgl_mutasi']));
    $no_mutasi  = mysqli_real_escape_string($conn, $_POST['no_mutasi']);
    $tmt        = date('Y-m-d', strtotime($_POST['tmt']));
    
    // 2. Generate ID Mutasi
    // Format ID bisa disesuaikan, misal M-001
    $id_mutasi  = kdauto($conn, "tb_mutasi", "M"); 

    // 3. Ambil Jabatan Terakhir (Sebelum Mutasi)
    $tJ = mysqli_query($conn, "SELECT jabatan FROM tb_jabatan WHERE id_peg='$id_peg' AND status_jab='Aktif' ORDER BY tmt_jabatan DESC LIMIT 1");
    $jab = mysqli_fetch_array($tJ);
    $jabatan_lama = isset($jab['jabatan']) ? $jab['jabatan'] : '-';

    // 4. Proses Upload SK (Jika ada)
    $sk_mutasi_name = '';
    if (!empty($_FILES['sk_mutasi']['name'])) {
        $sk_mutasi_name = $_FILES['sk_mutasi']['name'];
        $x = explode('.', $sk_mutasi_name);
        $ekstensi = strtolower(end($x));
        $sk_baru = "SK_MUTASI_".$id_peg."_".rand(1,999).".".$ekstensi;
        
        $tmp_file = $_FILES['sk_mutasi']['tmp_name'];
        // Pastikan folder ada
        $target_dir = "pages/assets/sk_mutasi/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        move_uploaded_file($tmp_file, $target_dir . $sk_baru);
        $sk_mutasi_name = $sk_baru;
    }

    // 5. Validasi Sederhana
    if (empty($id_peg) || empty($jns_mutasi) || empty($no_mutasi)) {
        $status_simpan = 'kosong';
    } else {
        // 6. Insert Data Mutasi
        $insert = "INSERT INTO tb_mutasi (id_mutasi, id_peg, jns_mutasi, tgl_mutasi, tmt, no_mutasi, sk_mutasi, jabatan) 
                   VALUES ('$id_mutasi', '$id_peg', '$jns_mutasi', '$tgl_mutasi', '$tmt', '$no_mutasi', '$sk_mutasi_name', '$jabatan_lama')";
        
        $query_insert = mysqli_query($conn, $insert);

        if ($query_insert) {
            // 7. Update Status Pegawai (Misal: 3 = Non Aktif/Mutasi Keluar, sesuaikan logika bisnis)
            // Jika mutasi jabatan internal, mungkin status_aktif tetap 1? 
            // Asumsi kode lama: status_aktif='3' (Pensiun/Keluar/Mutasi)
            $status_pegawai_baru = '3'; 
            if($jns_mutasi == 'Mutasi Jabatan' || $jns_mutasi == 'Mutasi Unit Kerja'){
                $status_pegawai_baru = '1'; // Tetap aktif jika cuma pindah posisi
            }

            $update = "UPDATE tb_pegawai SET status_aktif='$status_pegawai_baru' WHERE id_peg='$id_peg'";
            mysqli_query($conn, $update);
            
            $status_simpan = 'sukses';
        } else {
            $status_simpan = 'gagal';
        }
    }
}
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Proses Data Mutasi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
                    <li class="breadcrumb-item active">Proses Mutasi</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-outline card-primary text-center">
                    <div class="card-body">
                        <i class="fas fa-sync fa-3x text-primary mb-3 fa-spin"></i>
                        <h4>Sedang Memproses Data...</h4>
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
            text: 'Data mutasi berhasil disimpan.',
            showConfirmButton: false,
            timer: 2000
        }).then(function() {
            window.location.href = 'home-admin.php?page=form-view-data-mutasi';
        });
    } else if (status == 'kosong') {
        Swal.fire({
            icon: 'warning',
            title: 'Data Tidak Lengkap',
            text: 'Mohon lengkapi semua form yang tersedia.'
        }).then(function() {
            window.history.back();
        });
    } else if (status == 'gagal') {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menyimpan data ke database.'
        }).then(function() {
            window.history.back();
        });
    }
</script>