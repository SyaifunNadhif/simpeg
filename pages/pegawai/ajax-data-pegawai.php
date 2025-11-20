<?php
/*********************************************************
 * FILE    : pages/pegawai/ajax-data-pegawai.php
 * MODULE  : SIMPEG — DataTables Server-side (Pegawai Aktif)
 * VERSION : v1.4 (PHP 5.6 compatible)
 * DATE    : 2025-09-06
 * AUTHOR  : EWS/SIMPEG BKK Jateng — by ChatGPT
 *
 * PURPOSE :
 *   - Menyediakan data pegawai aktif untuk DataTables (serverSide:true).
 *   - Mendukung filter unit kerja, pencarian global, sorting, dan paging.
 *   - Menampilkan foto pegawai dengan prioritas folder baru (uploads/foto)
 *     lalu fallback ke folder lama (pages/assets/foto).
 *
 * CHANGELOG :
 * - v1.4 (2025-09-06): Support dua lokasi foto: uploads/foto (baru) dan pages/assets/foto (lama).
 *                      Tambah cache-buster agar foto baru tidak ke-cache.
 * - v1.3 (2025-09-06): Rapikan kode, perbaiki render foto & fallback.
 * - v1.2 (2025-08-xx): Tambah filter unit kerja sesuai hak akses Kepala/Admin.
 * - v1.1 (2025-08-xx): Struktur awal DataTables server-side.
 *********************************************************/

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_id() === '') session_start();
include "../../dist/koneksi.php";

// ---------------- Helper ----------------
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/**
 * Resolve foto ke URL publik dengan prioritas:
 * 1) uploads/foto/<filename>     (baru)
 * 2) pages/assets/foto/<filename> (lama)
 * Jika tak ada → fallback default berdasar jk.
 *
 * @param string $filename  Nama file di DB (tanpa path)
 * @param string $jk        'Laki-laki' atau lainnya (untuk default)
 * @return string           URL relatif untuk <img src="...">
 */
function resolve_foto_url($filename, $jk){
  // Path filesystem absolut (berdasarkan posisi file ini: pages/pegawai/…)
  $baseDir      = __DIR__ . "/../.."; // -> /simpeg
  $newFs        = $baseDir . "/uploads/foto/";        // fs path baru
  $oldFs        = $baseDir . "/pages/assets/foto/";   // fs path lama

  // URL relatif dari root web (home-admin.php di root /simpeg/)
  $newUrlBase   = "uploads/foto/";
  $oldUrlBase   = "pages/assets/foto/";

  if ($filename) {
    if (file_exists($newFs . $filename)) {
      return $newUrlBase . $filename;
    }
    if (file_exists($oldFs . $filename)) {
      return $oldUrlBase . $filename;
    }
  }

  // fallback default
  $fallback = ($jk === 'Laki-laki') ? 'no-foto-male.png' : 'no-foto-female.png';
  // Cari fallback di mana pun yang ada
  if (file_exists($oldFs . $fallback)) return $oldUrlBase . $fallback;
  if (file_exists($newFs . $fallback)) return $newUrlBase . $fallback;

  // fallback terakhir: gunakan satu default generik jika Anda punya
  return $oldUrlBase . 'no-foto.png';
}
// ----------------------------------------

$columns = ['foto', 'id_peg', 'nama', 'ttl', 'unit_kerja', 'jabatan', 'tgl_masuk', 'no_telp', 'action'];

$filterUnit       = isset($_GET['unit_kerja']) ? mysqli_real_escape_string($conn, $_GET['unit_kerja']) : '';
$limit            = isset($_GET['length']) ? intval($_GET['length']) : 10;
$offset           = isset($_GET['start']) ? intval($_GET['start']) : 0;
$search           = isset($_GET['search']['value']) ? mysqli_real_escape_string($conn, $_GET['search']['value']) : '';
$orderColumnIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
$orderColumn      = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'id_peg';
$orderDir         = (isset($_GET['order'][0]['dir']) && strtolower($_GET['order'][0]['dir']) === 'desc') ? 'DESC' : 'ASC';
$draw             = isset($_GET['draw']) ? intval($_GET['draw']) : 1;

// Cek hak akses dan kode kantor jika kepala
$isKepala   = isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) === 'kepala';
$kode_kantor = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';

// Base SQL
$sql = "SELECT
  p.id_peg,
  p.nama,
  p.tempat_lhr,
  p.tgl_lhr,
  p.tmt_kerja,
  p.telp,
  p.foto,
  p.jk,
  j.jabatan,
  j.tmt_jabatan,
  p.status_kepeg,
  k.nama_kantor AS unit_kerja
FROM
  tb_pegawai p
  LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
  LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
WHERE
  p.status_aktif = 1";

// Filter berdasarkan akses
if ($isKepala) {
  $sql .= " AND j.unit_kerja = '{$kode_kantor}'";
} elseif ($filterUnit !== '') {
  $sql .= " AND j.unit_kerja = '{$filterUnit}'";
}

// Filter pencarian
if ($search !== '') {
  $sql .= " AND (
    p.id_peg LIKE '%{$search}%' OR 
    p.nama LIKE '%{$search}%' OR 
    j.jabatan LIKE '%{$search}%' OR
    k.nama_kantor LIKE '%{$search}%' OR
    p.telp LIKE '%{$search}%'
  )";
}

// Hitung total filtered
$sqlFiltered     = $sql;
$resultFiltered  = mysqli_query($conn, $sqlFiltered);
$totalFiltered   = $resultFiltered ? mysqli_num_rows($resultFiltered) : 0;

// Final query + limit
$sql .= " ORDER BY {$orderColumn} {$orderDir} LIMIT {$offset}, {$limit}";
$result = mysqli_query($conn, $sql);

$data = [];
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $ttl      = $row['tempat_lhr'] . ', ' . date('d-m-Y', strtotime($row['tgl_lhr']));
    $fotoUrl  = resolve_foto_url($row['foto'], $row['jk']);
    // cache-buster agar foto baru tidak ke-cache browser
    $foto     = '<img src="'.h($fotoUrl).'?cb='.time().'" class="rounded-circle" width="40" loading="lazy">';

    $action = '<a href="home-admin.php?page=view-detail-data-pegawai&id_peg=' . h($row['id_peg']) . '" 
      class="btn btn-sm btn-outline-info" title="Detail"><i class="fa fa-folder-open"></i></a>';
    if (!$isKepala) {
      $action .= ' <a href="home-admin.php?page=form-master-data-pegawai&mode=edit&id=' . h($row['id_peg']) . '" 
      class="btn btn-sm btn-outline-warning" title="Edit"><i class="fa fa-edit"></i></a>';
    }

    $data[] = [
      'foto'       => $foto,
      'id_peg'     => h($row['id_peg']),
      'nama'       => h($row['nama']),
      'ttl'        => $ttl,
      'unit_kerja' => h($row['unit_kerja']),
      'jabatan'    => h($row['jabatan']),
      'tgl_masuk'  => date('d-m-Y', strtotime($row['tmt_kerja'])),
      'no_telp'    => h($row['telp']),
      'action'     => $action
    ];
  }
}

// Total seluruh data
$resultTotal = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tb_pegawai WHERE status_aktif = 1");
$rowTotal    = $resultTotal ? mysqli_fetch_assoc($resultTotal) : ['total' => 0];
$totalAll    = (int)$rowTotal['total'];

header('Content-Type: application/json');
echo json_encode([
  "draw"            => $draw,
  "recordsTotal"    => $totalAll,
  "recordsFiltered" => $totalFiltered,
  "data"            => $data
]);
