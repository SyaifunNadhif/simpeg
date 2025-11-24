<?php
/*********************************************************
 * FILE    : pages/diklat/proses-diklat.php
 * MODULE  : Proses Simpan/Edit/Hapus Diklat
 * VERSION : v2.4 (Integrated UI Fix)
 *********************************************************/

// Gunakan include_once agar tidak error jika koneksi sudah ada dari home-admin.php
include_once 'dist/koneksi.php'; 
// Fallback jika dipanggil langsung (jarang terjadi jika via routing home-admin)
if (!isset($conn)) { include_once '../../dist/koneksi.php'; }

$status_aksi = '';
$pesan_error = '';

// --- 1. PROSES SIMPAN ---
if (isset($_POST['simpan'])) {
    $id_peg        = mysqli_real_escape_string($conn, $_POST['id_peg']);
    $diklat        = mysqli_real_escape_string($conn, $_POST['diklat']);
    $penyelenggara = mysqli_real_escape_string($conn, $_POST['penyelenggara']);
    $tempat        = mysqli_real_escape_string($conn, $_POST['tempat']);
    $angkatan      = mysqli_real_escape_string($conn, $_POST['angkatan']);
    $tahun         = mysqli_real_escape_string($conn, $_POST['tahun']);
    $date_reg      = mysqli_real_escape_string($conn, $_POST['date_reg']);
    $created_by    = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'admin';

    if(empty($id_peg) || empty($diklat)) {
        $status_aksi = 'kosong';
    } else {
        $query = "INSERT INTO tb_diklat (id_peg, diklat, penyelenggara, tempat, angkatan, tahun, date_reg, created_by)
                  VALUES ('$id_peg', '$diklat', '$penyelenggara', '$tempat', '$angkatan', '$tahun', '$date_reg', '$created_by')";
        
        if (mysqli_query($conn, $query)) {
            $status_aksi = 'sukses_tambah';
        } else {
            $status_aksi = 'gagal';
            $pesan_error = mysqli_error($conn);
        }
    }
}

// --- 2. PROSES UPDATE ---
if (isset($_POST['update'])) {
    $id_diklat     = mysqli_real_escape_string($conn, $_POST['id_diklat']);
    $id_peg        = mysqli_real_escape_string($conn, $_POST['id_peg']);
    $diklat        = mysqli_real_escape_string($conn, $_POST['diklat']);
    $penyelenggara = mysqli_real_escape_string($conn, $_POST['penyelenggara']);
    $tempat        = mysqli_real_escape_string($conn, $_POST['tempat']);
    $angkatan      = mysqli_real_escape_string($conn, $_POST['angkatan']);
    $tahun         = mysqli_real_escape_string($conn, $_POST['tahun']);
    $date_reg      = mysqli_real_escape_string($conn, $_POST['date_reg']);
    $updated_by    = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'admin';

    $query = "UPDATE tb_diklat SET
              id_peg        = '$id_peg',
              diklat        = '$diklat',
              penyelenggara = '$penyelenggara',
              tempat        = '$tempat',
              angkatan      = '$angkatan',
              tahun         = '$tahun',
              date_reg      = '$date_reg',
              updated_by    = '$updated_by',
              updated_at    = NOW()
              WHERE id_diklat = '$id_diklat'";

    if (mysqli_query($conn, $query)) {
        $status_aksi = 'sukses_edit';
    } else {
        $status_aksi = 'gagal';
        $pesan_error = mysqli_error($conn);
    }
}

// --- 3. PROSES HAPUS ---
if (isset($_GET['act']) && $_GET['act'] == 'hapus' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "DELETE FROM tb_diklat WHERE id_diklat = '$id'";
    
    if (mysqli_query($conn, $query)) {
        $status_aksi = 'sukses_hapus';
    } else {
        $status_aksi = 'gagal';
        $pesan_error = mysqli_error($conn);
    }
}
?>

<style>
    /* Container Tengah */
    .process-container {
        display: flex; 
        justify-content: center; 
        align-items: center; 
        min-height: 70vh; /* Tinggi minimal agar di tengah layar */
        width: 100%;
    }
    
    /* Card Loading */
    .loading-card { 
        background: white; 
        padding: 40px; 
        border-radius: 15px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
        text-align: center; 
        width: 100%;
        max-width: 350px;
    }
    
    /* Animasi Spinner */
    .loading-icon { 
        color: #17a2b8; /* Warna Info */
        animation: spin 1s linear infinite; 
        margin-bottom: 20px;
    }
    
    @keyframes spin { 
        0% { transform: rotate(0deg); } 
        100% { transform: rotate(360deg); } 
    }
    
    .text-processing { font-size: 18px; font-weight: 600; color: #333; margin: 0; }
    .text-wait { font-size: 14px; color: #888; margin-top: 5px; }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="process-container">
            <div class="loading-card">
                <i class="fas fa-circle-notch fa-3x loading-icon"></i>
                <h4 class="text-processing">Memproses Data...</h4>
                <p class="text-wait">Mohon tunggu sebentar.</p>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Ambil status dari PHP
    var status = "<?= $status_aksi ?>";
    var errorMsg = "<?= $pesan_error ?>";
    
    // URL Redirect
    var redirectUrl = 'home-admin.php?page=master-data-diklat';

    // Delay sedikit agar loading terlihat (UX)
    setTimeout(function() {
        if (status == 'sukses_tambah') {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data diklat ditambahkan.', showConfirmButton: false, timer: 1500 })
            .then(() => { window.location.href = redirectUrl; });

        } else if (status == 'sukses_edit') {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data diklat diperbarui.', showConfirmButton: false, timer: 1500 })
            .then(() => { window.location.href = redirectUrl; });

        } else if (status == 'sukses_hapus') {
            Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'Data diklat dihapus.', showConfirmButton: false, timer: 1500 })
            .then(() => { window.location.href = redirectUrl; });

        } else if (status == 'kosong') {
            Swal.fire({ icon: 'warning', title: 'Data Tidak Lengkap', text: 'Lengkapi data wajib.' })
            .then(() => { window.history.back(); });

        } else if (status == 'gagal') {
            Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Error: ' + errorMsg })
            .then(() => { window.history.back(); });
        }
    }, 500); // Delay 0.5 detik
</script>