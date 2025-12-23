<?php
// FILE: pages/report/ajax-nominatif-pegawai.php
if (session_id() == '') session_start();
ini_set('display_errors', 0);
while(ob_get_level()){ ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');

@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }

function esc($conn, $str){ return mysqli_real_escape_string($conn, trim($str)); }
function h($str){ return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

// PARAMETER DATATABLES
$draw   = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$len    = isset($_GET['length']) ? (int)$_GET['length'] : 10;
$search = isset($_GET['search']['value']) ? esc($conn, $_GET['search']['value']) : '';

// PARAMETER FILTER
$unit_kerja   = isset($_GET['unit_kerja']) ? esc($conn, $_GET['unit_kerja']) : '';
$jabatan      = isset($_GET['jabatan']) ? esc($conn, $_GET['jabatan']) : '';
$status_kepeg = isset($_GET['status_kepeg']) ? esc($conn, $_GET['status_kepeg']) : '';

// HAK AKSES KEPALA
$hak_akses = isset($_SESSION['hak_akses']) ? $_SESSION['hak_akses'] : '';
$kode_kantor_user = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
if ($hak_akses == 'kepala') { $unit_kerja = $kode_kantor_user; }

// --- QUERY BUILDER ---
$sqlJoin = "FROM tb_pegawai p
            LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
            LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
            LEFT JOIN tb_pendidikan s ON p.id_peg = s.id_peg AND s.status = 'Akhir'";

$where = "WHERE p.status_aktif = 1";

// 1. SMART FILTER KANTOR
if ($unit_kerja != '') {
    $qK = mysqli_query($conn, "SELECT level, kode_cabang FROM tb_kantor WHERE kode_kantor_detail = '$unit_kerja'");
    $dK = mysqli_fetch_assoc($qK);

    if ($dK && $dK['level'] == 'KC') {
        // Jika KC, ambil Induk + semua KK (berdasarkan kode cabang)
        $kode_cabang = $dK['kode_cabang'];
        $where .= " AND k.kode_cabang = '$kode_cabang'";
    } else {
        // Exact match untuk KP atau Unit lain
        $where .= " AND j.unit_kerja = '$unit_kerja'";
    }
}

// 2. FILTER LAIN
if ($jabatan != '') { $where .= " AND j.jabatan = '$jabatan'"; }
if ($status_kepeg != '') { $where .= " AND p.status_kepeg = '$status_kepeg'"; }

// 3. SEARCH GLOBAL
if ($search != '') {
    $where .= " AND (p.nama LIKE '%$search%' OR p.nip LIKE '%$search%' OR j.jabatan LIKE '%$search%' OR k.nama_kantor LIKE '%$search%')";
}

// EKSEKUSI DATA
$qCount = mysqli_query($conn, "SELECT COUNT(*) AS total $sqlJoin $where");
$totalData = ($qCount) ? mysqli_fetch_assoc($qCount)['total'] : 0;

$query = "SELECT p.id_peg, p.nama, p.nip, p.status_kepeg, j.jabatan, j.tmt_jabatan, k.nama_kantor, s.nama_sekolah, s.jenjang 
          $sqlJoin $where 
          ORDER BY p.nama ASC LIMIT $start, $len";
$q = mysqli_query($conn, $query);

$data = [];
$no = $start + 1;
if($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        // Badge Status (Logic diperbaiki biar nangkep 'Pegawai Tetap' & 'Tetap')
        $st = strtolower($row['status_kepeg']);
        $cls = 'badge-secondary';
        if(strpos($st,'tetap')!==false) $cls='badge-primary';
        elseif(strpos($st,'calon')!==false || strpos($st,'capeg')!==false) $cls='badge-info';
        elseif(strpos($st,'kontrak')!==false || strpos($st,'pkwt')!==false) $cls='badge-warning';
        elseif(strpos($st,'thl')!==false || strpos($st,'outsource')!==false) $cls='badge-dark';
        
        $status = "<span class='badge $cls px-3 py-2 rounded-pill'>".h($row['status_kepeg'])."</span>";
        
        // Data Formatting
        $pend = empty($row['jenjang']) ? '-' : "<b>".h($row['jenjang'])."</b><br><small class='text-muted'>".h($row['nama_sekolah'])."</small>";
        $tmt = ($row['tmt_jabatan'] && $row['tmt_jabatan']!='0000-00-00') ? date('d-m-Y', strtotime($row['tmt_jabatan'])) : '-';

        $data[] = [
            "no" => $no++,
            "nama" => "<div style='font-weight:700; color:#334155;'>".h($row['nama'])."</div>",
            "nip" => "<div class='text-muted small font-monospace'>".h($row['nip'])."</div>",
            "jabatan" => h($row['jabatan']?:'-'),
            "unit_kerja" => "<span class='text-primary font-weight-bold'>".h($row['nama_kantor']?:'-')."</span>",
            "status" => $status,
            "tmt" => $tmt,
            "pendidikan" => $pend
        ];
    }
}

echo json_encode(["draw" => $draw, "recordsTotal" => $totalData, "recordsFiltered" => $totalData, "data" => $data]);
?>