<?php
/*********************************************************
 * FILE    : pages/pegawai/simpan-data-pegawai.php
 * MODULE  : SIMPEG — Pegawai (Tambah/Edit + Otorisasi + Kolektif)
 * VERSION : v1.3 (PHP 5.6 compatible)
 * DATE    : 2025-09-05
 * AUTHOR  : EWS/SIMPEG BKK Jateng — Refactor by ChatGPT
 *
 * CHANGELOG
 * - v1.3 (2025-09-05)
 *   - Rapi & konsistenkan gaya kode, helper input, dan komentar.
 *   - Tambah flow SUKSES (mode=tambah): SweetAlert ➜ pilih "Isi Jabatan Sekarang" atau "Lewati".
 *   - Validasi upload foto (ekstensi & ukuran sederhana), nama file unik, auto-dir ensure.
 *   - Hardening kecil: trim/sanitize input, default fallback, dan cek hasil query.
 *   - Sinkron user juga dipanggil ulang pasca insert sukses (idempotent).
 * - v1.2 (sebelumnya)
 *   - Penambahan mode kolektif (JSON) untuk import batch pegawai.
 *   - Alur edit oleh user biasa: ajukan perubahan (tb_edit_pending) + notifikasi kepala.
 * - v1.1 (sebelumnya)
 *   - Penambahan SweetAlert hasil simpan + auto-redirect by role (kepala/admin/user).
 * - v1.0 (awal)
 *   - Simpan tambah/edit pegawai dasar.
 *********************************************************/

if (session_id()=='') session_start();
include('../../dist/koneksi.php');   // pastikan mendefinisikan $conn (mysqli)
include('../../dist/functions.php'); // berisi helper termasuk sinkron_user_dari_pegawai()

/* ========================= Helper ========================= */
function postv($key, $def='') { return isset($_POST[$key]) ? trim($_POST[$key]) : $def; }
function clean_str($conn, $s){ return mysqli_real_escape_string($conn, trim($s)); }
function ensure_dir($path){ if (!is_dir($path)) { @mkdir($path, 0775, true); } }

$user       = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'admin';
$hak_akses  = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$tanggal    = date('Y-m-d');

/*
 * Catatan variabel DB:
 *  - File ini memakai $conn (mysqli) sesuai include('../../dist/koneksi.php').
 *  - Jika project Anda memakai $koneksi, sesuaikan nama variabel koneksi di seluruh file.
 */

$status = 'gagal'; // default

/* ==========================================================
 * 1) MODE KOLEKTIF (JSON) — import massal pegawai
 *    Request: header Content-Type: application/json, body {kolektif:1, data:[ {...}, ... ]}
 * ========================================================== */
if (isset($_POST['kolektif']) && $_POST['kolektif'] == '1') {
  $json = file_get_contents('php://input');
  $post = json_decode($json, true);

  $data     = isset($post['data']) && is_array($post['data']) ? $post['data'] : array();
  $berhasil = 0; $gagal = 0;

  foreach ($data as $row) {
    $id_peg = isset($row['id_peg']) ? clean_str($conn, $row['id_peg']) : '';
    $nip    = isset($row['nip'])    ? clean_str($conn, $row['nip'])    : '';
    $nama   = isset($row['nama'])   ? clean_str($conn, $row['nama'])   : '';

    if ($id_peg==='' && $nip==='') { $gagal++; continue; }

    $cek = mysqli_query($conn, "SELECT 1 FROM tb_pegawai WHERE nip = '$nip' OR id_peg = '$id_peg' LIMIT 1");
    if ($cek && mysqli_num_rows($cek) == 0) {
      $sql = mysqli_query($conn, "INSERT INTO tb_pegawai (
        id_peg, id_peg_old, nip, nama, tempat_lhr, tgl_lhr, agama, jk, gol_darah, status_nikah,
        status_kepeg, alamat, telp, email, tmt_kerja, tgl_pensiun, bpjstk, bpjskes,
        status_aktif, date_reg, created_by
      ) VALUES (
        '".$id_peg."', '".clean_str($conn, (isset($row['id_peg_old'])?$row['id_peg_old']:'')) ."',
        '".$nip."', '".$nama."',
        '".clean_str($conn, (isset($row['tempat_lhr'])?$row['tempat_lhr']:'')) ."',
        '".clean_str($conn, (isset($row['tgl_lhr'])?$row['tgl_lhr']:'')) ."',
        '".clean_str($conn, (isset($row['agama'])?$row['agama']:'')) ."',
        '".clean_str($conn, (isset($row['jk'])?$row['jk']:'')) ."',
        '".clean_str($conn, (isset($row['gol_darah'])?$row['gol_darah']:'')) ."',
        '".clean_str($conn, (isset($row['status_nikah'])?$row['status_nikah']:'')) ."',
        '".clean_str($conn, (isset($row['status_kepeg'])?$row['status_kepeg']:'')) ."',
        '".clean_str($conn, (isset($row['alamat'])?$row['alamat']:'')) ."',
        '".clean_str($conn, (isset($row['telp'])?$row['telp']:'')) ."',
        '".clean_str($conn, (isset($row['email'])?$row['email']:'')) ."',
        '".clean_str($conn, (isset($row['tmt_kerja'])?$row['tmt_kerja']:'')) ."',
        '".clean_str($conn, (isset($row['tgl_pensiun'])?$row['tgl_pensiun']:'')) ."',
        '".clean_str($conn, (isset($row['bpjstk'])?$row['bpjstk']:'')) ."',
        '".clean_str($conn, (isset($row['bpjskes'])?$row['bpjskes']:'')) ."',
        '".clean_str($conn, (isset($row['status_aktif'])?$row['status_aktif']:'1')) ."',
        '".$tanggal."', '".$user."'
      )");
      if ($sql) { $berhasil++; } else { $gagal++; }
    } else { $gagal++; }
  }

  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array(
    'status'   => 'success',
    'berhasil' => $berhasil,
    'gagal'    => $gagal
  ));
  exit;
}

/* ==========================================================
 * 2) MODE FORM (tambah/edit)
 * ========================================================== */
$id_peg        = postv('id_peg');
$nip           = postv('nip');
$nama          = postv('nama');
$tempat_lhr    = postv('tempat_lhr');
$tgl_lhr       = postv('tgl_lhr');
$agama         = postv('agama');
$jk            = postv('jk');
$gol_darah     = postv('gol_darah');
$status_nikah  = postv('status_nikah');
$status_kepeg  = postv('status_kepeg');
$alamat        = postv('alamat');
$telp          = postv('telp');
$email         = postv('email');
$bpjstk        = postv('bpjstk');
$bpjskes       = postv('bpjskes');
$mode          = postv('mode', 'tambah');

// Sanitasi untuk query raw (tetap disarankan pakai prepared statement jika memungkinkan)
$id_peg        = clean_str($conn, $id_peg);
$nip           = clean_str($conn, $nip);
$nama          = clean_str($conn, $nama);
$tempat_lhr    = clean_str($conn, $tempat_lhr);
$tgl_lhr       = clean_str($conn, $tgl_lhr);
$agama         = clean_str($conn, $agama);
$jk            = clean_str($conn, $jk);
$gol_darah     = clean_str($conn, $gol_darah);
$status_nikah  = clean_str($conn, $status_nikah);
$status_kepeg  = clean_str($conn, $status_kepeg);
$alamat        = clean_str($conn, $alamat);
$telp          = clean_str($conn, $telp);
$email         = clean_str($conn, $email);
$bpjstk        = clean_str($conn, $bpjstk);
$bpjskes       = clean_str($conn, $bpjskes);

/* ---------- Upload Foto (optional) ---------- */
$foto_name = '';
if (!empty($_FILES['foto']['name']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
  $allowed = array('jpg','jpeg','png','gif');
  $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
  $size_ok = (isset($_FILES['foto']['size']) ? (int)$_FILES['foto']['size'] : 0) <= (2 * 1024 * 1024); // <=2MB

  if (in_array($ext, $allowed) && $size_ok) {
    $safeId = preg_replace('~[^A-Za-z0-9_\-]~','', $id_peg);
    $foto_name = 'foto_' . $safeId . '_' . time() . '.' . $ext;
    $dest_dir  = realpath(__DIR__ . '/../../uploads');
    if ($dest_dir === false) { $dest_dir = __DIR__ . '/../../uploads'; }
    $dest_path = rtrim($dest_dir, '/\\') . '/foto/';
    ensure_dir($dest_path);
    @move_uploaded_file($_FILES['foto']['tmp_name'], $dest_path . $foto_name);
  }
}

/* ---------- Sinkron user berdasarkan id_peg (early call) ---------- */
if ($id_peg !== '') { @sinkron_user_dari_pegawai($id_peg); }

/* ---------- Proses Simpan ---------- */
if ($mode == 'tambah') {
  $cek = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg = '$id_peg' LIMIT 1");
  if ($cek && mysqli_num_rows($cek) > 0) {
    $status = 'duplikat';
  } else {
    $sql = "INSERT INTO tb_pegawai (
      id_peg, nip, nama, tempat_lhr, tgl_lhr, agama, jk, gol_darah, status_nikah,
      status_kepeg, alamat, telp, email, bpjstk, bpjskes, foto, status_aktif, date_reg, created_by
    ) VALUES (
      '$id_peg', '$nip', '$nama', '$tempat_lhr', '$tgl_lhr', '$agama', '$jk', '$gol_darah', '$status_nikah',
      '$status_kepeg', '$alamat', '$telp', '$email', '$bpjstk', '$bpjskes', '$foto_name', '1', '$tanggal', '$user'
    )";

    if (mysqli_query($conn, $sql)) {
      // Pastikan sinkron user juga dilakukan pasca insert (idempotent)
      @sinkron_user_dari_pegawai($id_peg);
      $status = 'sukses';
    } else {
      $status = 'gagal';
    }
  }
}
else if ($mode == 'edit') {
  // EDIT oleh USER biasa → ajukan perubahan ke kepala
  if ($hak_akses == 'user') {
    $qOld = mysqli_query($conn, "SELECT * FROM tb_pegawai WHERE id_peg = '$id_peg' LIMIT 1");
    $dataLama = $qOld ? mysqli_fetch_assoc($qOld) : null;

    if ($dataLama) {
      $fieldMap = array(
        'nip'=>$nip, 'nama'=>$nama, 'tempat_lhr'=>$tempat_lhr, 'tgl_lhr'=>$tgl_lhr,
        'agama'=>$agama, 'jk'=>$jk, 'gol_darah'=>$gol_darah, 'status_nikah'=>$status_nikah,
        'status_kepeg'=>$status_kepeg, 'alamat'=>$alamat, 'telp'=>$telp, 'email'=>$email,
        'bpjstk'=>$bpjstk, 'bpjskes'=>$bpjskes
      );
      foreach ($fieldMap as $field => $newVal) {
        $oldVal = isset($dataLama[$field]) ? $dataLama[$field] : '';
        if ($oldVal != $newVal) {
          mysqli_query($conn, "INSERT INTO tb_edit_pending (id_peg, jenis_data, data_lama, data_baru, status_otorisasi, tanggal_pengajuan, id_user)
                                VALUES ('$id_peg', '$field', '".clean_str($conn,$oldVal)."', '".clean_str($conn,$newVal)."', 'pending', NOW(), '$user')");
        }
      }
      if (!empty($foto_name)) {
        $oldFoto = isset($dataLama['foto']) ? clean_str($conn, $dataLama['foto']) : '';
        mysqli_query($conn, "INSERT INTO tb_edit_pending (id_peg, jenis_data, data_lama, data_baru, status_otorisasi, tanggal_pengajuan, id_user)
                              VALUES ('$id_peg', 'foto', '$oldFoto', '$foto_name', 'pending', NOW(), '$user')");
      }

      // Cari kepala unit dari jabatan aktif pegawai terkait
      $qKepala = mysqli_query($conn, "
        SELECT u.id_user 
        FROM tb_user u
        JOIN tb_jabatan j ON u.id_pegawai = j.id_peg
        WHERE u.hak_akses = 'kepala' AND j.status_jab = 'Aktif' AND j.unit_kerja = (
          SELECT unit_kerja FROM tb_jabatan WHERE id_peg = '$id_peg' AND status_jab = 'Aktif' LIMIT 1
        ) LIMIT 1");

      if ($qKepala && mysqli_num_rows($qKepala) > 0) {
        $kepala = mysqli_fetch_assoc($qKepala);
        $kepala_id = $kepala['id_user'];

        mysqli_query($conn, "INSERT INTO tb_notifikasi (id_user, judul, pesan, link_aksi, status_baca, waktu_notif)
                              VALUES ('".clean_str($conn,$kepala_id)."', 'Permintaan Edit', 'Permintaan edit data pegawai atas nama $nama', 'home-admin.php?page=otorisasi-approval', 'unread', NOW())");
        mysqli_query($conn, "INSERT INTO tb_notifikasi (id_user, judul, pesan, link_aksi, status_baca, waktu_notif)
                              VALUES ('".clean_str($conn,$user)."', 'Pengajuan Perubahan', 'Pengajuan Anda atas nama $nama sedang menunggu otorisasi.', 'home-admin.php?page=preview-edit&id_peg=$id_peg', 'unread', NOW())");
      }
      $status = 'ajukan';
    } else { $status = 'gagal'; }
  }
  // EDIT oleh ADMIN/Kepala → langsung update
  else {
    $sql = "UPDATE tb_pegawai SET
      nip='$nip', nama='$nama', tempat_lhr='$tempat_lhr', tgl_lhr='$tgl_lhr',
      agama='$agama', jk='$jk', gol_darah='$gol_darah', status_nikah='$status_nikah',
      status_kepeg='$status_kepeg', alamat='$alamat', telp='$telp', email='$email',
      bpjstk='$bpjstk', bpjskes='$bpjskes', updated_at=NOW(), updated_by='$user'";
    if (!empty($foto_name)) { $sql .= ", foto='$foto_name'"; }
    $sql .= " WHERE id_peg='$id_peg'";

    $status = mysqli_query($conn, $sql) ? 'sukses' : 'gagal';
  }
}

/* ========================= Tampilan hasil ========================= */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Proses Simpan Pegawai</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php
  // Fallback tujuan lama per-role (dipakai jika user memilih "Lewati" atau untuk mode edit)
  $tujuan_default = '/simpeg/home-admin.php';
  if (isset($_SESSION['hak_akses'])) {
    $akses = strtolower($_SESSION['hak_akses']);
    if ($akses == 'kepala') {
      $tujuan_default = '/simpeg/home-admin.php?page=dashboard-cabang';
    } elseif ($akses == 'admin') {
      $tujuan_default = '/simpeg/home-admin.php';
    } elseif ($akses == 'user') {
      $tujuan_default = '/simpeg/home-admin.php?page=profil-pegawai';
    }
  }
  ?>
  <style>html,body{background:#0b0b0b;color:#eaeaea} .swal2-popup{font-size:14px}</style>
</head>
<body>
<script>
(function(){
  var status  = <?php echo json_encode($status); ?>;
  var idPeg   = <?php echo json_encode($id_peg); ?>;
  var tujuan  = <?php echo json_encode($tujuan_default); ?>;
  var mode    = <?php echo json_encode($mode); ?>;

  // Hardcode base /simpeg/
  function go(path){
    window.location.href = window.location.origin + '/simpeg/' + path.replace(/^\//,'');
  }

  if(status === 'sukses'){
    if(mode === 'tambah'){
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data pegawai berhasil disimpan. Ingin mengisi Jabatan & Unit Kerja sekarang?',
        showCancelButton: true,
        confirmButtonText: 'Ya, isi sekarang',
        cancelButtonText: 'Lewati',
        allowOutsideClick: false
      }).then(function(res){
        if(res.isConfirmed){
          go('home-admin.php?page=form-master-data-jabatan&uid=' + encodeURIComponent(idPeg));
        } else {
          go('home-admin.php?page=form-view-data-pegawai');
        }
      });
    } else {
      Swal.fire({ icon:'success', title:'Berhasil!', text:'Perubahan data berhasil disimpan.'})
        .then(function(){ go(tujuan); });
    }
  }
  else if(status === 'duplikat'){
    Swal.fire({ icon: 'warning', title: 'Duplikat!', text: 'ID Pegawai sudah terdaftar.' })
      .then(function(){ history.back(); });
  }
  else if(status === 'ajukan'){
    Swal.fire({ icon: 'info', title: 'Perubahan Diajukan!', text: 'Menunggu persetujuan atasan.' })
      .then(function(){ go(tujuan); });
  }
  else {
    Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat menyimpan data.' })
      .then(function(){ history.back(); });
  }
})();
</script>


</body>
</html>
