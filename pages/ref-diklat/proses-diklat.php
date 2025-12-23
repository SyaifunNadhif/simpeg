<?php
/*********************************************************
 * FILE    : pages/diklat/proses-diklat.php
 * MODULE  : Proses Simpan/Edit/Hapus Diklat (+ Biaya)
 * VERSION : v2.6 (Secure & No Loading Delay)
 *********************************************************/

// Gunakan include_once agar tidak error jika koneksi sudah ada
include_once 'dist/koneksi.php'; 
if (!isset($conn)) { include_once '../../dist/koneksi.php'; }

// SECURITY: Cek Login
if (session_id() == '') session_start();
if (empty($_SESSION['id_user'])) {
    echo "<script>window.location='index.php';</script>";
    exit;
}

$status_aksi = '';
$pesan_error = '';

// --- 1. PROSES SIMPAN ---
if (isset($_POST['simpan'])) {
    $id_peg        = mysqli_real_escape_string($conn, $_POST['id_peg']);
    $diklat        = mysqli_real_escape_string($conn, $_POST['diklat']);
    $penyelenggara = mysqli_real_escape_string($conn, $_POST['penyelenggara']);
    $tempat        = mysqli_real_escape_string($conn, $_POST['tempat']);
    
    // Tangkap Biaya (Hapus karakter non-angka biar aman masuk DB)
    $biaya_raw     = !empty($_POST['biaya']) ? $_POST['biaya'] : 0;
    $biaya         = mysqli_real_escape_string($conn, preg_replace('/[^0-9]/', '', $biaya_raw));

    $angkatan      = mysqli_real_escape_string($conn, $_POST['angkatan']);
    $tahun         = mysqli_real_escape_string($conn, $_POST['tahun']);
    $date_reg      = mysqli_real_escape_string($conn, $_POST['date_reg']);
    $created_by    = $_SESSION['id_user'];

    if(empty($id_peg) || empty($diklat)) {
        $status_aksi = 'kosong';
    } else {
        $query = "INSERT INTO tb_diklat (id_peg, diklat, penyelenggara, tempat, biaya, angkatan, tahun, date_reg, created_by)
                  VALUES ('$id_peg', '$diklat', '$penyelenggara', '$tempat', '$biaya', '$angkatan', '$tahun', '$date_reg', '$created_by')";
        
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
    
    $biaya_raw     = !empty($_POST['biaya']) ? $_POST['biaya'] : 0;
    $biaya         = mysqli_real_escape_string($conn, preg_replace('/[^0-9]/', '', $biaya_raw));

    $angkatan      = mysqli_real_escape_string($conn, $_POST['angkatan']);
    $tahun         = mysqli_real_escape_string($conn, $_POST['tahun']);
    $date_reg      = mysqli_real_escape_string($conn, $_POST['date_reg']);
    $updated_by    = $_SESSION['id_user'];

    $query = "UPDATE tb_diklat SET
              id_peg        = '$id_peg',
              diklat        = '$diklat',
              penyelenggara = '$penyelenggara',
              tempat        = '$tempat',
              biaya         = '$biaya',
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

<script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>

<script>
    var status   = "<?= $status_aksi ?>";
    var errorMsg = "<?= htmlspecialchars($pesan_error) ?>"; // Anti XSS
    var redirectUrl = 'home-admin.php?page=master-data-diklat';

    if (status == 'sukses_tambah') {
        Swal.fire({ 
            icon: 'success', 
            title: 'Berhasil!', 
            text: 'Data diklat ditambahkan.', 
            showConfirmButton: false, 
            timer: 1500 
        }).then(() => { 
            window.location.href = redirectUrl; 
        });

    } else if (status == 'sukses_edit') {
        Swal.fire({ 
            icon: 'success', 
            title: 'Berhasil!', 
            text: 'Data diklat diperbarui.', 
            showConfirmButton: false, 
            timer: 1500 
        }).then(() => { 
            window.location.href = redirectUrl; 
        });

    } else if (status == 'sukses_hapus') {
        Swal.fire({ 
            icon: 'success', 
            title: 'Terhapus!', 
            text: 'Data diklat dihapus.', 
            showConfirmButton: false, 
            timer: 1500 
        }).then(() => { 
            window.location.href = redirectUrl; 
        });

    } else if (status == 'kosong') {
        Swal.fire({ 
            icon: 'warning', 
            title: 'Data Tidak Lengkap', 
            text: 'Lengkapi data wajib.' 
        }).then(() => { 
            window.history.back(); 
        });

    } else if (status == 'gagal') {
        Swal.fire({ 
            icon: 'error', 
            title: 'Gagal!', 
            text: 'Error: ' + errorMsg 
        }).then(() => { 
            window.history.back(); 
        });
    }
</script>