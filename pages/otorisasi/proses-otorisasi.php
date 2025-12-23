<?php
/*********************************************************
 * FILE    : pages/otorisasi/proses-otorisasi.php
 * MODULE  : SIMPEG — Proses Otorisasi (batch per poin)
 * VERSION : v1.9 (Security Hardened)
 * DATE    : 2025-12-18
 *********************************************************/

if (session_id()==='') session_start();

/* ==== Robust include koneksi + normalisasi variabel ==== */
$__paths = array(
  __DIR__ . '/../../dist/koneksi.php',
  __DIR__ . '/../../../dist/koneksi.php',
  dirname(__DIR__) . '/dist/koneksi.php',
  __DIR__ . '/../dist/koneksi.php'
);
foreach ($__paths as $__p) { if (is_file($__p)) { include_once $__p; } } // Removed @ for safer inclusion
if (!isset($koneksi)) { if (isset($conn)) { $koneksi = $conn; } }

// --- SECURITY: ACCESS CONTROL CHECK ---
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
if ($hak_akses !== 'kepala' && $hak_akses !== 'admin') {
    // Log the unauthorized attempt
    error_log("Unauthorized access attempt to proses-otorisasi.php by user ID: " . (isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'unknown'));
    die("Access denied.");
}
// --------------------------------------

if (!isset($_POST['id_edit']) || !is_array($_POST['id_edit'])) {
  header("Location: ../../home-admin.php?page=otorisasi-approval&msg=invalid");
  exit;
}

$otorisator = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : '0';
$tanggal    = date('Y-m-d H:i:s');

/* ===== Whitelist kolom tb_pegawai saat approve ===== */
$allowed_map = array(
  'gol_darah'    => 'gol_darah',
  'status_nikah' => 'status_nikah',
  'alamat'       => 'alamat',
  'no_hp'        => 'no_hp',
  'email'        => 'email',
  // tambahkan sesuai kebutuhan...
);

/* ===== Penampung untuk notifikasi distinct per pemohon ===== */
$notif_users = array(); // key: id_user, val: array('id_peg'=>last, 'nama'=>last)

/* ===== Proses batch dalam transaksi ===== */
mysqli_autocommit($koneksi, false);
$jumlah_ok = 0;

foreach ($_POST['id_edit'] as $id_edit_raw) {
  $id_edit  = mysqli_real_escape_string($koneksi, $id_edit_raw);
  
  // Validate action exists for this ID
  if (!isset($_POST['aksi'][$id_edit_raw])) { continue; }
  
  $aksi_raw = strtolower(trim($_POST['aksi'][$id_edit_raw]));
  
  // Validate comment exists
  $komentar_raw = isset($_POST['komentar'][$id_edit_raw]) ? $_POST['komentar'][$id_edit_raw] : '';
  $komentar = mysqli_real_escape_string($koneksi, $komentar_raw);

  // Map aksi ke status final
  $statusBaru = '';
  if ($aksi_raw === 'approve' || $aksi_raw === 'setuju') $statusBaru = 'Disetujui';
  if ($aksi_raw === 'reject'  || $aksi_raw === 'tolak')  $statusBaru = 'Ditolak';
  if ($statusBaru === '') { continue; }

  // Ambil pengajuan (dan pastikan masih Menunggu)
  $q = mysqli_query($koneksi, "
    SELECT id_edit,id_peg,jenis_data,data_baru,id_user,status_otorisasi
      FROM tb_edit_pending
     WHERE id_edit = '".$id_edit."'
     LIMIT 1
  ");
  if (!$q || mysqli_num_rows($q) === 0) { continue; }
  $r = mysqli_fetch_assoc($q);

  if ($r['status_otorisasi'] !== 'Menunggu') { continue; }

  $idPeg     = mysqli_real_escape_string($koneksi, $r['id_peg']);
  $jenisData = $r['jenis_data'];
  $dataBaru  = $r['data_baru']; // bisa plain text
  $idPemohon = $r['id_user'];

  $ok_item = true;

  // Jika Disetujui → apply ke tb_pegawai (whitelist kolom)
  if ($statusBaru === 'Disetujui') {
    if (isset($allowed_map[$jenisData])) {
      $kolomPegawai = $allowed_map[$jenisData];
      $nilaiBaru    = mysqli_real_escape_string($koneksi, $dataBaru);
      
      // Update data pegawai
      $sqlUpdPeg = "
        UPDATE tb_pegawai
           SET $kolomPegawai = '".$nilaiBaru."',
               updated_by    = '".mysqli_real_escape_string($koneksi, $otorisator)."',
               updated_at    = NOW()
         WHERE id_peg = '".$idPeg."'
         LIMIT 1";
      $ok_item = $ok_item && mysqli_query($koneksi, $sqlUpdPeg);
      
      if (!$ok_item) { error_log('FAILED upd tb_pegawai id_edit='.$id_edit.' : '.mysqli_error($koneksi)); }
    }
    // jika jenisData tidak di whitelist, tetap lanjut mengubah status permintaan (logika bisnis)
  }

  // Update status pengajuan
  $sqlUpdReq = "
    UPDATE tb_edit_pending
       SET status_otorisasi  = '".$statusBaru."',
           tanggal_otorisasi = '".$tanggal."',
           otorisator        = '".mysqli_real_escape_string($koneksi, $otorisator)."',
           komentar_otorisasi= '".$komentar."'
     WHERE id_edit = '".$id_edit."'
       AND status_otorisasi = 'Menunggu'
     LIMIT 1";
  $ok_item = $ok_item && mysqli_query($koneksi, $sqlUpdReq);
  
  if (!$ok_item) { error_log('FAILED upd tb_edit_pending id_edit='.$id_edit.' : '.mysqli_error($koneksi)); }

  // Log history
  // Using explicit columns is safer
  $sqlHist = "
    INSERT INTO tb_edit_history (id_edit, action, actor, komentar, acted_at)
    VALUES ('".$id_edit."', '".$statusBaru."', '".mysqli_real_escape_string($koneksi, $otorisator)."', '".$komentar."', NOW())
  ";
  // Suppress error if table doesn't exist, but good to know
  @mysqli_query($koneksi, $sqlHist);

  if ($ok_item) {
    $jumlah_ok++;
    // catat pemohon untuk dikirim notifikasi nanti (distinct)
    $notif_users[$idPemohon] = array('id_peg' => $idPeg);
  }
}

/* ===== Commit/Rollback batch ===== */
if ($jumlah_ok > 0) {
  mysqli_commit($koneksi);
} else {
  mysqli_rollback($koneksi);
}
mysqli_autocommit($koneksi, true);

/* ===== Kirim notifikasi ringkas per pemohon ===== */
if (!empty($notif_users) && $jumlah_ok > 0) {
  foreach ($notif_users as $id_user_pemohon => $payload) {
    // Ambil nama pegawai terakhir
    $namaPeg = '';
    $idPegX  = mysqli_real_escape_string($koneksi, $payload['id_peg']);
    $rp = mysqli_query($koneksi, "SELECT nama FROM tb_pegawai WHERE id_peg='".$idPegX."' LIMIT 1");
    if ($rp && mysqli_num_rows($rp)>0) {
      $np = mysqli_fetch_assoc($rp);
      $namaPeg = $np['nama'];
    }

    $judul = "Status Permintaan Edit";
    $pesan = "Beberapa perubahan data atas nama ".$namaPeg." telah diproses oleh atasan.";
    // Ensure URL is correct relative to user view
    $link  = "home-admin.php?page=notifikasi-user"; 

    $sqlNotif = "
      INSERT INTO tb_notifikasi (id_user, judul, pesan, ref_type, ref_id, link_aksi, status_baca, created_at)
      VALUES ('".mysqli_real_escape_string($koneksi, $id_user_pemohon)."',
              '".$judul."',
              '".$pesan."',
              'edit',
              NULL,
              '".$link."',
              'Belum',
              NOW())
    ";
    @mysqli_query($koneksi, $sqlNotif);
  }
}

/* ===== Redirect ===== */
// Use urlencode for safety in GET params
header("Location: ../../home-admin.php?page=otorisasi-approval&msg=processed&jumlah=".urlencode($jumlah_ok));
exit;
?>