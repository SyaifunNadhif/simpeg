<?php
/*********************************************************
 * FILE    : pages/user/proses-user.php
 * MODULE  : Backend CRUD User (Standalone & Secure)
 * VERSION : v2.0
 *********************************************************/

include_once __DIR__ . '/../../dist/koneksi.php';
if (session_id() == '') session_start();

$status_aksi = '';
$pesan_error = '';

// ==========================================
// 1. PROSES SIMPAN (CREATE & UPDATE)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $mode           = $_POST['mode']; // 'create' atau 'edit'
    $id_user        = mysqli_real_escape_string($conn, $_POST['id_user']);
    $nama_user      = mysqli_real_escape_string($conn, $_POST['nama_user']);
    $password_raw   = $_POST['password']; 
    $hak_akses      = mysqli_real_escape_string($conn, $_POST['hak_akses']);
    $id_pegawai     = !empty($_POST['id_pegawai']) ? mysqli_real_escape_string($conn, $_POST['id_pegawai']) : NULL;
    $unit_kerja     = !empty($_POST['unit_kerja']) ? mysqli_real_escape_string($conn, $_POST['unit_kerja']) : NULL;
    $status_aktif   = isset($_POST['status_aktif']) ? 'Y' : 'N';

    // --- LOGIKA TAMBAH BARU ---
    if ($mode == 'create') {
        // Cek Duplikat Username
        $cek = mysqli_query($conn, "SELECT id_user FROM tb_user WHERE id_user = '$id_user'");
        if (mysqli_num_rows($cek) > 0) {
            $status_aksi = 'duplikat';
        } else {
            // Enkripsi Password (MD5 standar legacy, ganti password_hash jika perlu)
            $password_hash = md5($password_raw); 

            $query = "INSERT INTO tb_user (id_user, nama_user, password, hak_akses, id_pegawai, unit_kerja, status_aktif, created_at) 
                      VALUES ('$id_user', '$nama_user', '$password_hash', '$hak_akses', '$id_pegawai', '$unit_kerja', '$status_aktif', NOW())";
            
            if (mysqli_query($conn, $query)) {
                $status_aksi = 'sukses_tambah';
            } else {
                $status_aksi = 'gagal';
                $pesan_error = mysqli_error($conn);
            }
        }
    }
    
    // --- LOGIKA UPDATE ---
    elseif ($mode == 'edit') {
        $id_user_lama = mysqli_real_escape_string($conn, $_POST['id_user_lama']);

        // Cek apakah password diubah?
        $sql_pass = "";
        if (!empty($password_raw)) {
            $password_hash = md5($password_raw);
            $sql_pass = ", password = '$password_hash'";
        }

        $query = "UPDATE tb_user SET 
                  nama_user = '$nama_user',
                  hak_akses = '$hak_akses',
                  id_pegawai = '$id_pegawai',
                  unit_kerja = '$unit_kerja',
                  status_aktif = '$status_aktif'
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

// ==========================================
// 2. PROSES HAPUS (DELETE)
// ==========================================
if (isset($_GET['act']) && $_GET['act'] == 'hapus' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Cegah hapus diri sendiri (Opsional, tapi bagus)
    if ($id == $_SESSION['id_user']) {
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
        
        // Redirect ke halaman data user (Mundur 2 folder)
        var redirectUrl = '../../home-admin.php?page=view-data-user';

        if (status == 'sukses_tambah') {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'User baru ditambahkan.', showConfirmButton: false, timer: 1500 })
            .then(() => { window.location.href = redirectUrl; });
        } 
        else if (status == 'sukses_edit') {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data user diperbarui.', showConfirmButton: false, timer: 1500 })
            .then(() => { window.location.href = redirectUrl; });
        } 
        else if (status == 'sukses_hapus') {
            Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'User berhasil dihapus.', showConfirmButton: false, timer: 1500 })
            .then(() => { window.location.href = redirectUrl; });
        } 
        else if (status == 'duplikat') {
            Swal.fire({ icon: 'warning', title: 'Gagal!', text: 'Username sudah terdaftar.' })
            .then(() => { window.history.back(); });
        } 
        else if (status == 'gagal') {
            Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Error: ' + errorMsg })
            .then(() => { window.history.back(); });
        }
    </script>

</body>
</html>