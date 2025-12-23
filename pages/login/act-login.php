<?php
include "dist/koneksi.php";

// Ambil input (bisa berupa id_user atau id_pegawai)
$login_input = isset($_POST['id_user']) ? trim($_POST['id_user']) : '';
$password    = isset($_POST['password']) ? md5($_POST['password']) : '';
$op          = isset($_GET['op']) ? $_GET['op'] : 'in';

?>
<!DOCTYPE html>
<html>
<head>
  <script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>
  <style> body { font-family: sans-serif; background: #f4f6f9; } </style>
</head>
<body>

<?php
if ($op == "in") {
    
    // --- LOGIKA BARU: CEK ID_USER ATAU ID_PEGAWAI ---
    // Query: Cari di tabel tb_user dimana (id_user = input ATAU id_pegawai = input) DAN password cocok
    $stmt = mysqli_prepare($conn, "SELECT * FROM tb_user WHERE (id_user=? OR id_pegawai=?) AND password=?");
    
    // "sss" artinya 3 string: param1 (id_user), param2 (id_pegawai), param3 (password)
    // Kita masukkan $login_input dua kali karena dia mengecek ke dua kolom berbeda
    mysqli_stmt_bind_param($stmt, "sss", $login_input, $login_input, $password);
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Cek apakah user ditemukan
    if ($row = mysqli_fetch_assoc($result)) {
        
        // Cek Status Aktif
        if ($row['status_aktif'] == "N") {
            echo "<script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Ditolak',
                    text: 'Akun Anda dinonaktifkan. Hubungi Admin.',
                    confirmButtonText: 'Kembali',
                    confirmButtonColor: '#d33'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            </script>";
        } else {
            // Regenerate Session ID (Keamanan)
            session_regenerate_id(true);

            // Set Session
            $_SESSION['id_user']    = $row['id_user'];
            $_SESSION['nama_user']  = $row['nama_user'];
            $_SESSION['hak_akses']  = strtolower($row['hak_akses']);
            $_SESSION['id_pegawai'] = $row['id_pegawai'];

            // Logic Kepala Cabang (Ambil Kode Kantor)
            if ($_SESSION['hak_akses'] == 'kepala') {
                $id_peg = $row['id_pegawai'];
                
                $stmt2 = mysqli_prepare($conn, "SELECT unit_kerja FROM tb_jabatan WHERE id_peg=? AND status_jab='Aktif' LIMIT 1");
                mysqli_stmt_bind_param($stmt2, "s", $id_peg);
                mysqli_stmt_execute($stmt2);
                $res2 = mysqli_stmt_get_result($stmt2);
                
                if ($dKantor = mysqli_fetch_assoc($res2)) {
                    $_SESSION['kode_kantor'] = $dKantor['unit_kerja'];
                } else {
                    $_SESSION['kode_kantor'] = '-';
                }
                mysqli_stmt_close($stmt2);
            }

            // Redirect sesuai hak akses
            switch ($_SESSION['hak_akses']) {
                case 'admin':  $redirectPage = 'home-admin.php'; break;
                case 'kepala': $redirectPage = 'home-admin.php?page=dashboard-cabang'; break;
                case 'user':   $redirectPage = 'home-admin.php?page=profil-pegawai'; break;
                default:       $redirectPage = 'index.php'; break;
            }

            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil',
                    text: 'Selamat datang, " . htmlspecialchars($row['nama_user']) . "!',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = '$redirectPage';
                });
            </script>";
        }
    } else {
        // Login Gagal
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: 'Username/ID Pegawai atau Password salah!',
                confirmButtonText: 'Coba Lagi',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                window.location.href = 'index.php';
            });
        </script>";
    }
    
    mysqli_stmt_close($stmt);

} elseif ($op == "out") {
    // Logout Logic
    session_unset();
    session_destroy();
    
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Logout Berhasil',
            text: 'Sampai jumpa lagi!',
            showConfirmButton: false,
            timer: 2000
        }).then(() => {
            window.location.href = 'index.php';
        });
    </script>";
}
?>

</body>
</html>