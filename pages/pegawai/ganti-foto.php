<?php
// pages/pegawai/ganti-foto.php
include __DIR__ . '/../../dist/koneksi.php';

if (!isset($_GET['id_peg'])) die("Error. No Kode Selected!");
$id_peg = mysqli_real_escape_string($conn, $_GET['id_peg']);

// ambil nama user untuk membuat nama file
$qn = mysqli_query($conn, "SELECT nama, foto FROM tb_pegawai WHERE id_peg = '$id_peg'");
if (!$qn || mysqli_num_rows($qn) == 0) {
    echo "<script>alert('Pegawai tidak ditemukan.'); window.location='home-admin.php?page=form-view-data-pegawai';</script>";
    exit;
}
$row = mysqli_fetch_assoc($qn);
$nama_full = $row['nama'];
$oldFile = trim($row['foto']); // nama file lama yang tersimpan di DB (jika ada)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: home-admin.php?page=form-ganti-foto&id_peg=" . urlencode($id_peg));
    exit;
}

if (empty($_POST['cropped_image'])) {
    echo "<script>alert('Tidak ada gambar dikirim.'); history.back();</script>";
    exit;
}

$data = $_POST['cropped_image'];
if (!preg_match('/^data:image\/(\w+);base64,/', $data, $m)) {
    echo "<script>alert('Format gambar tidak dikenali.'); history.back();</script>";
    exit;
}
$type = strtolower($m[1]);
$data = substr($data, strpos($data, ',') + 1);
$imgData = base64_decode($data);
if ($imgData === false) { echo "<script>alert('Decode gambar gagal.'); history.back();</script>"; exit; }

$allowed = ['png','jpg','jpeg','gif'];
$ext = ($type === 'jpeg') ? 'jpg' : $type;
if (!in_array($ext, $allowed)) { echo "<script>alert('Tipe gambar tidak diperbolehkan.'); history.back();</script>"; exit; }

// buat nama file berdasarkan nama user (sanitasi) + timestamp
function safe_name($s) {
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9\-_\s]/', '', $s);
    $s = preg_replace('/\s+/', '_', trim($s));
    return $s;
}
$safe = safe_name($nama_full);
$filename = $safe . '_' . time() . '.' . $ext;

// simpan ke pages/assets/foto/
$uploadRel = 'pages/assets/foto/';
$uploadDir = __DIR__ . '/../../' . $uploadRel;
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$filepath = $uploadDir . $filename;
if (file_put_contents($filepath, $imgData) === false) {
    echo "<script>alert('Gagal menyimpan file.'); history.back();</script>";
    exit;
}

// update DB: simpan hanya nama file (seperti permintaan)
$stmt = mysqli_prepare($conn, "UPDATE tb_pegawai SET foto = ? WHERE id_peg = ?");
mysqli_stmt_bind_param($stmt, 'ss', $filename, $id_peg);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($ok) {
    // hapus file lama jika ada (oldFile menyimpan nama file saja)
    if (!empty($oldFile)) {
        $oldPath = $uploadDir . $oldFile;
        if (file_exists($oldPath)) @unlink($oldPath);
    }
    // redirect ke profil
    echo "<script>window.location='home-admin.php?page=profil-pegawai&id_peg=" . urlencode($id_peg) . "';</script>";
    exit;
} else {
    @unlink($filepath);
    echo "<script>alert('Gagal update database.'); history.back();</script>";
    exit;
}
