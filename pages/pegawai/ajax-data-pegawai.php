<?php
ini_set('display_errors', 0);
error_reporting(0);

if (session_id() === '') session_start();
include "../../dist/koneksi.php";

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function resolve_foto_url($filename, $jk){
    // ... (Kode foto sama seperti sebelumnya) ...
    $baseDir = __DIR__ . "/../.."; 
    $newFs = $baseDir . "/uploads/foto/"; $oldFs = $baseDir . "/pages/assets/foto/";  
    $newUrlBase = "uploads/foto/"; $oldUrlBase = "pages/assets/foto/";
    if ($filename && trim($filename) !== '') {
        if (file_exists($newFs . $filename)) return $newUrlBase . $filename;
        if (file_exists($oldFs . $filename)) return $oldUrlBase . $filename;
    }
    $fallback = ($jk === 'Laki-laki') ? 'no-foto-male.png' : 'no-foto-female.png';
    return (file_exists($oldFs . $fallback)) ? $oldUrlBase . $fallback : "https://ui-avatars.com/api/?name=User";
}

// PARAMETER
$columns = ['nama', 'ttl', 'unit_kerja', 'jabatan', 'tgl_masuk', 'no_telp', 'action'];
$filterType    = isset($_GET['filter_type']) ? $_GET['filter_type'] : ''; 
$filterKantor  = isset($_GET['kantor']) ? mysqli_real_escape_string($conn, $_GET['kantor']) : '';
$filterDivisi  = isset($_GET['divisi']) ? mysqli_real_escape_string($conn, $_GET['divisi']) : ''; // Bisa Kode KK atau Nama Divisi
$filterJabatan = isset($_GET['jabatan']) ? mysqli_real_escape_string($conn, $_GET['jabatan']) : '';

$limit  = isset($_GET['length']) ? intval($_GET['length']) : 10;
$offset = isset($_GET['start']) ? intval($_GET['start']) : 0;
$search = isset($_GET['search']['value']) ? mysqli_real_escape_string($conn, $_GET['search']['value']) : '';

$orderColumnIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
$orderDir         = (isset($_GET['order'][0]['dir']) && strtolower($_GET['order'][0]['dir']) === 'desc') ? 'DESC' : 'ASC';
$columnsDB        = [ 0 => 'p.nama', 1 => 'p.tgl_lhr', 2 => 'j.jabatan', 3 => 'p.tmt_kerja', 4 => 'p.telp' ];
$orderColumn      = isset($columnsDB[$orderColumnIndex]) ? $columnsDB[$orderColumnIndex] : 'p.nama';

// QUERY BUILDER UTAMA
$sqlBase = "
    FROM tb_pegawai p
    LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
    LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
    LEFT JOIN tb_master_jabatan m ON j.jabatan = m.nama_jabatan 
    WHERE p.status_aktif = 1
";

// --- LOGIC FILTER CASCADING ---
if ($filterType === 'nonjob') {
    $sqlBase .= " AND j.id_jab IS NULL";
} else {
    $sqlBase .= " AND j.id_jab IS NOT NULL"; 

    // 1. FILTER KANTOR (PARENT)
    if ($filterKantor !== '') {
        // Cek dulu ini kantor level apa?
        $cekK = mysqli_query($conn, "SELECT level, kode_cabang FROM tb_kantor WHERE kode_kantor_detail='$filterKantor'");
        $dK = mysqli_fetch_assoc($cekK);
        
        if ($dK['level'] == 'KC') {
            // MAGIC: Jika pilih KC, ambil semua data yang kode_cabang-nya sama (Termasuk KK)
            $kode_cabang = $dK['kode_cabang'];
            $sqlBase .= " AND k.kode_cabang = '$kode_cabang'";
        } else {
            // Jika KP/KANWIL, ambil exact match
            $sqlBase .= " AND j.unit_kerja = '$filterKantor'";
        }
    }

    // 2. FILTER DIVISI / UNIT DETAIL (CHILD)
    if ($filterDivisi !== '') {
        if (is_numeric($filterDivisi)) {
            // Jika isinya Angka (misal 002001), berarti user memilih KANTOR KAS spesifik
            $sqlBase .= " AND j.unit_kerja = '$filterDivisi'";
        } else {
            // Jika isinya Huruf, berarti user memilih NAMA BAGIAN/DIVISI
            $sqlBase .= " AND m.nama_unit_kerja = '$filterDivisi'";
        }
    }

    // 3. FILTER JABATAN
    if ($filterJabatan !== '') {
        $sqlBase .= " AND j.jabatan = '$filterJabatan'";
    }
}

// SEARCH
if ($search !== '') {
    $sqlBase .= " AND (p.id_peg LIKE '%$search%' OR p.nama LIKE '%$search%' OR j.jabatan LIKE '%$search%' OR k.nama_kantor LIKE '%$search%')";
}

// EKSEKUSI DATA
$queryCount = mysqli_query($conn, "SELECT COUNT(*) as jum " . $sqlBase);
$rowCount   = mysqli_fetch_assoc($queryCount);
$totalFiltered = $rowCount['jum'];

// Total Raw (Optimized)
$sqlTotalRaw = "SELECT COUNT(*) as jum FROM tb_pegawai p LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif' WHERE p.status_aktif = 1";
if ($filterType === 'nonjob') $sqlTotalRaw .= " AND j.id_jab IS NULL"; else $sqlTotalRaw .= " AND j.id_jab IS NOT NULL";
$totalAll = mysqli_fetch_assoc(mysqli_query($conn, $sqlTotalRaw))['jum'];

$sqlData = "SELECT p.id_peg, p.nama, p.tempat_lhr, p.tgl_lhr, p.tmt_kerja, p.telp, p.foto, p.jk, p.status_kepeg, j.jabatan, k.nama_kantor, m.nama_unit_kerja AS divisi " . $sqlBase . " ORDER BY $orderColumn $orderDir LIMIT $offset, $limit";
$result = mysqli_query($conn, $sqlData);
$data = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $fotoUrl  = resolve_foto_url($row['foto'], $row['jk']);
        $fotoHtml = '<div class="avatar-wrapper"><img src="'.h($fotoUrl).'?cb='.time().'" class="avatar-img" loading="lazy"></div>';
        $ttl = $row['tempat_lhr'] . ', ' . date('d-m-Y', strtotime($row['tgl_lhr']));
        
        $action = '<div class="btn-group">';
        if ($filterType === 'nonjob') {
            $cleanID = preg_replace('/[^a-zA-Z0-9-]/', '', $row['id_peg']);
            $action .= '<a href="home-admin.php?page=form-master-data-jabatan&uid='.$cleanID.'" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm font-weight-bold"><i class="fa fa-plus-circle mr-1"></i> Set Jabatan</a>';
        } else {
            $action .= '<a href="home-admin.php?page=view-detail-data-pegawai&id_peg='.h($row['id_peg']).'" class="btn btn-sm btn-action-blue"><i class="fa fa-folder-open"></i></a>';
            if (isset($_SESSION['hak_akses']) && ($_SESSION['hak_akses']=='admin' || $_SESSION['hak_akses']=='kepala')) {
                $action .= '<a href="home-admin.php?page=form-master-data-jabatan&mode=edit&uid='.h($row['id_peg']).'" class="btn btn-sm btn-action-orange ml-1"><i class="fa fa-edit"></i></a>';
            }
        }
        $action .= '</div>';

        $data[] = [
            'nama_teks' => h($row['nama']), 'nama_foto' => $fotoHtml, 'id_peg' => h($row['id_peg']),
            'ttl' => $ttl, 'jabatan' => h($row['jabatan']), 'kantor' => h($row['nama_kantor']), 'divisi' => h($row['divisi']),
            'tgl_masuk' => date('d-m-Y', strtotime($row['tmt_kerja'])), 'no_telp' => h($row['telp']), 'action' => $action
        ];
    }
}

header('Content-Type: application/json');
echo json_encode(["draw" => intval($_GET['draw']), "recordsTotal" => intval($totalAll), "recordsFiltered" => intval($totalFiltered), "data" => $data]);
?>