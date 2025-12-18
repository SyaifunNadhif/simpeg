<?php
// FILE: pages/user/proses-user.php
include '../../dist/koneksi.php'; // Sesuaikan path ini jika perlu!
session_start();

// Variabel default
$admin_login = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'System';
$act = isset($_POST['act']) ? $_POST['act'] : '';

// 1. CREATE
if ($act == 'create') {
    $id_user = $_POST['id_user'];
    
    // Cek Duplikat
    $cek = mysqli_query($conn, "SELECT id_user FROM tb_user WHERE id_user = '$id_user'");
    if(mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah ada!'); window.history.back();</script>";
        exit;
    }

    $nama_user  = $_POST['nama_user'];
    $password   = md5($_POST['password']);
    $hak_akses  = $_POST['hak_akses'];
    $id_pegawai = !empty($_POST['id_pegawai']) ? $_POST['id_pegawai'] : NULL;
    $jabatan    = !empty($_POST['jabatan']) ? $_POST['jabatan'] : NULL;
    $status     = isset($_POST['status_aktif']) ? 'Y' : 'N';

    $query = "INSERT INTO tb_user (id_user, nama_user, password, hak_akses, id_pegawai, jabatan, status_aktif, created_by, created_at) 
              VALUES ('$id_user', '$nama_user', '$password', '$hak_akses', '$id_pegawai', '$jabatan', '$status', '$admin_login', NOW())";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Berhasil simpan data!'); window.location='../../home-admin.php?page=view-data-user';</script>";
    } else {
        echo "<script>alert('Gagal simpan: ".mysqli_error($conn)."'); window.history.back();</script>";
    }
}

// 2. UPDATE
elseif ($act == 'update') {
    $id_user      = $_POST['id_user_lama']; // ID asli (PK)
    $id_user_baru = $_POST['id_user'];      // Jika username diganti
    $nama_user    = $_POST['nama_user'];
    $hak_akses    = $_POST['hak_akses'];
    $id_pegawai   = !empty($_POST['id_pegawai']) ? $_POST['id_pegawai'] : NULL;
    $jabatan      = !empty($_POST['jabatan']) ? $_POST['jabatan'] : NULL;
    $status       = isset($_POST['status_aktif']) ? 'Y' : 'N';
    
    $sql_pass = "";
    if (!empty($_POST['password'])) {
        $pass = md5($_POST['password']);
        $sql_pass = ", password = '$pass'";
    }

    $query = "UPDATE tb_user SET 
              id_user='$id_user_baru', nama_user='$nama_user', hak_akses='$hak_akses', 
              id_pegawai='$id_pegawai', jabatan='$jabatan', status_aktif='$status', updated_by='$admin_login', updated_at=NOW() 
              $sql_pass WHERE id_user='$id_user'";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data berhasil diupdate!'); window.location='../../home-admin.php?page=view-data-user';</script>";
    } else {
        echo "<script>alert('Gagal update: ".mysqli_error($conn)."'); window.history.back();</script>";
    }
}

// 3. DELETE (WITH RECYCLE BIN)
elseif ($act == 'delete_aman') {
    $id_user = $_POST['id_user'];
    $alasan  = mysqli_real_escape_string($conn, $_POST['alasan']);

    // A. SELECT DATA LAMA
    $qCek = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user = '$id_user'");
    if(mysqli_num_rows($qCek) > 0) {
        $dataLama = mysqli_fetch_assoc($qCek);
        $dataJson = mysqli_real_escape_string($conn, json_encode($dataLama));

        // B. INSERT KE RECYCLE BIN
        $sqlBackup = "INSERT INTO tb_recycle_bin (tabel_asal, id_data, data_json, alasan, dihapus_oleh) 
                      VALUES ('tb_user', '$id_user', '$dataJson', '$alasan', '$admin_login')";
        
        if(mysqli_query($conn, $sqlBackup)) {
            // C. DELETE DARI TABEL UTAMA
            $del = mysqli_query($conn, "DELETE FROM tb_user WHERE id_user = '$id_user'");
            if($del) {
                echo "<script>alert('Data berhasil dihapus dan diamankan ke Recycle Bin.'); window.location='../../home-admin.php?page=view-data-user';</script>";
            } else {
                echo "<script>alert('Gagal menghapus data user.'); window.location='../../home-admin.php?page=view-data-user';</script>";
            }
        } else {
            echo "<script>alert('Gagal backup data! Hapus dibatalkan.'); window.location='../../home-admin.php?page=view-data-user';</script>";
        }
    } else {
        echo "<script>alert('Data tidak ditemukan!'); window.location='../../home-admin.php?page=view-data-user';</script>";
    }
}
?>