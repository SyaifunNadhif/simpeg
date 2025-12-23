<?php
// pages/pegawai/ganti-foto.php
include __DIR__ . '/../../dist/koneksi.php';

// 1. SECURITY: Pastikan Session Aktif & User Login
if (session_id() == '') session_start();
if (empty($_SESSION['id_user']) && empty($_SESSION['id_pegawai'])) {
    die("<script>alert('Akses ditolak. Silakan login.'); window.location='../../login.php';</script>");
}

if (!isset($_GET['id_peg'])) die("Error. No Kode Selected!");
$id_peg = mysqli_real_escape_string($conn, $_GET['id_peg']);

// Simpan URL Asal (Referer) sebelum diproses
// Ini trik agar tahu harus kembali ke 'profil' atau 'view-detail'
$url_asal = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

// 2. DATA USER: Ambil data untuk penamaan file
$qn = mysqli_query($conn, "SELECT nama, foto FROM tb_pegawai WHERE id_peg = '$id_peg'");
if (!$qn || mysqli_num_rows($qn) == 0) {
    echo "<script>alert('Pegawai tidak ditemukan.'); window.location='home-admin.php?page=form-view-data-pegawai';</script>";
    exit;
}
$row = mysqli_fetch_assoc($qn);
$nama_full = $row['nama'];
$oldFile   = trim($row['foto']); 

// 3. VALIDASI METHOD
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika akses langsung via URL (GET), kembalikan
    echo "<script>history.back();</script>";
    exit;
}

if (empty($_POST['cropped_image'])) {
    echo "<script>alert('Tidak ada gambar dikirim.'); history.back();</script>";
    exit;
}

// 4. DECODE & SECURITY CHECK GAMBAR
$data = $_POST['cropped_image'];

// Cek Header Base64
if (!preg_match('/^data:image\/(\w+);base64,/', $data, $m)) {
    echo "<script>alert('Format data gambar tidak valid.'); history.back();</script>";
    exit;
}

$type = strtolower($m[1]);
$data = substr($data, strpos($data, ',') + 1);
$imgData = base64_decode($data);

if ($imgData === false) { 
    echo "<script>alert('Gagal memproses gambar (Decode Error).'); history.back();</script>"; 
    exit; 
}

// Validasi Ekstensi
$allowed = ['jpg', 'jpeg', 'png', 'gif'];
$ext = ($type === 'jpeg') ? 'jpg' : $type;
if (!in_array($ext, $allowed)) {
    echo "<script>alert('Hanya diperbolehkan format JPG, JPEG, PNG, GIF.'); history.back();</script>";
    exit;
}

// Validasi MIME Type (Mencegah upload script manipulasi)
$finfo = finfo_open();
$mime_type = finfo_buffer($finfo, $imgData, FILEINFO_MIME_TYPE);
finfo_close($finfo);
$allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($mime_type, $allowed_mimes)) {
    echo "<script>alert('File corrupt atau bukan gambar valid.'); history.back();</script>";
    exit;
}

// 5. PROSES SIMPAN FILE
function safe_name($s) {
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9\-_\s]/', '', $s);
    $s = preg_replace('/\s+/', '_', trim($s));
    return $s;
}
$safe = safe_name($nama_full);
$filename = $safe . '_' . time() . '.' . $ext;

$uploadRel = 'pages/assets/foto/';
$uploadDir = __DIR__ . '/../../' . $uploadRel;

// Auto create folder
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
    file_put_contents($uploadDir . '/index.php', ''); 
}

$filepath = $uploadDir . $filename;

if (file_put_contents($filepath, $imgData) === false) {
    echo "<script>alert('Gagal menyimpan file ke server (Permission Denied).'); history.back();</script>";
    exit;
}

// 6. UPDATE DATABASE
$stmt = mysqli_prepare($conn, "UPDATE tb_pegawai SET foto = ? WHERE id_peg = ?");
mysqli_stmt_bind_param($stmt, 'ss', $filename, $id_peg);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($ok) {
    
    // --- [CONFIG LOCAL] JANGAN HAPUS FILE LAMA ---
    // Uncomment baris di bawah saat deploy ke server asli
    /* if (!empty($oldFile) && $oldFile != 'default.png') {
        $oldPath = $uploadDir . $oldFile;
        if (file_exists($oldPath) && is_file($oldPath)) {
            @unlink($oldPath); 
        }
    }
    */
    
    // --- LOGIKA REDIRECT CERDAS ---
    $id_url = urlencode($id_peg);
    
    // 1. Cek dari mana User datang (History URL)
    if (strpos($url_asal, 'page=profil-pegawai') !== false) {
        // Jika asalnya dari Profil -> Balik ke Profil
        $redirect_to = "home-admin.php?page=profil-pegawai&id_peg=" . $id_url;
    } 
    elseif (strpos($url_asal, 'page=view-detail-data-pegawai') !== false) {
        // Jika asalnya dari Detail -> Balik ke Detail
        $redirect_to = "home-admin.php?page=view-detail-data-pegawai&id_peg=" . $id_url;
    } 
    else {
        // 2. Fallback Logic (Jika URL asal tidak terdeteksi)
        $id_login = isset($_SESSION['id_pegawai']) ? $_SESSION['id_pegawai'] : '';
        
        // Jika yang diedit adalah diri sendiri -> Ke Profil
        if ($id_peg == $id_login) {
            $redirect_to = "home-admin.php?page=profil-pegawai&id_peg=" . $id_url;
        } else {
            // Jika diedit orang lain (Admin) -> Ke Detail
            $redirect_to = "home-admin.php?page=view-detail-data-pegawai&id_peg=" . $id_url;
        }
    }

    echo "<script>window.location='" . $redirect_to . "';</script>";
    exit;

} else {
    if (file_exists($filepath)) @unlink($filepath);
    echo "<script>alert('Gagal update database. Silakan coba lagi.'); history.back();</script>";
    exit;
}
?>