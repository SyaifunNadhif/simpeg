<?php
/*********************************************************
 * FILE    : pages/user/proses-user.php
 * MODULE  : Backend CRUD (Created By & Jabatan Name)
 * VERSION : v3.3
 *********************************************************/

include_once __DIR__ . '/../../dist/koneksi.php';
// Pastikan session dimulai untuk mengambil ID Admin yang login
if (session_id() == '') session_start();

// Cek apakah user sudah login (Optional, sesuaikan dengan sistem login Anda)
// $admin_login = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'System'; 
// Asumsi: Variabel session login Anda adalah 'id_user' atau 'ses_id'
// Jika error undefined index, ganti 'id_user' dengan nama session yang benar di file login.php Anda
$admin_login = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'Admin'; 

$status_aksi = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $mode       = $_POST['mode']; 
    $id_user    = mysqli_real_escape_string($conn, $_POST['id_user']);
    $nama_user  = mysqli_real_escape_string($conn, $_POST['nama_user']);
    $password_raw = $_POST['password']; 
    $hak_akses  = mysqli_real_escape_string($conn, $_POST['hak_akses']);
    $id_pegawai = !empty($_POST['id_pegawai']) ? mysqli_real_escape_string($conn, $_POST['id_pegawai']) : NULL;
    
    // Variabel ini sekarang berisi TEXT (Contoh: "Kepala Cabang")
    $jabatan    = !empty($_POST['jabatan']) ? mysqli_real_escape_string($conn, $_POST['jabatan']) : NULL;
    
    $status_aktif = isset($_POST['status_aktif']) ? 'Y' : 'N';

    // --- INSERT ---
    if ($mode == 'create') {
        $cek = mysqli_query($conn, "SELECT id_user FROM tb_user WHERE id_user = '$id_user'");
        if (mysqli_num_rows($cek) > 0) {
            $status_aksi = 'duplikat';
        } else {
            $password_hash = md5($password_raw); 

            // Tambahkan created_by & created_at
            $query = "INSERT INTO tb_user (
                        id_user, nama_user, password, hak_akses, id_pegawai, jabatan, status_aktif, 
                        created_at, created_by
                      ) VALUES (
                        '$id_user', '$nama_user', '$password_hash', '$hak_akses', '$id_pegawai', '$jabatan', '$status_aktif', 
                        NOW(), '$admin_login'
                      )";
            
            if (mysqli_query($conn, $query)) {
                $status_aksi = 'sukses_tambah';
            } else {
                $status_aksi = 'gagal';
                $pesan_error = mysqli_error($conn);
            }
        }
    }
    
    // --- UPDATE ---
    elseif ($mode == 'edit') {
        $id_user_lama = mysqli_real_escape_string($conn, $_POST['id_user_lama']);
        $sql_pass = "";
        if (!empty($password_raw)) {
            $password_hash = md5($password_raw);
            $sql_pass = ", password = '$password_hash'";
        }

        // Tambahkan updated_by & updated_at
        $query = "UPDATE tb_user SET 
                  nama_user    = '$nama_user',
                  hak_akses    = '$hak_akses',
                  id_pegawai   = '$id_pegawai',
                  jabatan      = '$jabatan',
                  status_aktif = '$status_aktif',
                  updated_at   = NOW(),
                  updated_by   = '$admin_login'
                  $sql_pass
                  WHERE id_user = '$id_user_lama'";

        if (mysqli_query($conn, $query)) {
            $status_aksi = 'sukses_edit';
        } else {
            $status_aksi = 'gagal';
            $pesan_error = mysqli_error($conn);
        }
    }
}

// --- DELETE ---
if (isset($_GET['act']) && $_GET['act'] == 'hapus' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    if (isset($_SESSION['id_user']) && $id == $_SESSION['id_user']) {
        $status_aksi = 'gagal';
        $pesan_error = "Tidak bisa menghapus akun sendiri!";
    } else {
        $query = "DELETE FROM tb_user WHERE id_user = '$id'";
        if (mysqli_query($conn, $query)) {
            $status_aksi = 'sukses_hapus';
        } else {
            $status_aksi = 'gagal';
            $pesan_error = mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proses User</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
      body { background-color: #f4f6f9; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; overflow: hidden; }
      .loading-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; width: 300px; }
      .loading-icon { color: #007bff; animation: spin 1s linear infinite; margin-bottom: 20px; }
      h4 { margin: 0; font-size: 18px; color: #333; }
      @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
  </style>
</head>
<body>
    <div class="loading-card">
        <i class="fas fa-circle-notch fa-3x loading-icon"></i>
        <h4>Memproses User...</h4>
    </div>
    <script>
        var status = "<?= $status_aksi ?>";
        var errorMsg = "<?= $pesan_error ?>";
        var redirectUrl = '../../home-admin.php?page=daftar-user';

        if (status == 'sukses_tambah') {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'User baru ditambahkan.', showConfirmButton: false, timer: 1500 }).then(() => { window.location.href = redirectUrl; });
        } else if (status == 'sukses_edit') {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data user diperbarui.', showConfirmButton: false, timer: 1500 }).then(() => { window.location.href = redirectUrl; });
        } else if (status == 'sukses_hapus') {
            Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'User berhasil dihapus.', showConfirmButton: false, timer: 1500 }).then(() => { window.location.href = redirectUrl; });
        } else if (status == 'duplikat') {
            Swal.fire({ icon: 'warning', title: 'Gagal!', text: 'Username sudah terdaftar.' }).then(() => { window.history.back(); });
        } else if (status == 'gagal') {
            Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Error: ' + errorMsg }).then(() => { window.history.back(); });
        }
    </script>
</body>
</html>