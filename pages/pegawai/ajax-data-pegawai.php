<?php
/*********************************************************
 * FILE     : pages/pegawai/ajax-data-pegawai.php
 * MODULE   : SIMPEG — DataTables Server-side
 * UPDATE   : Filter 3 Level (Kantor -> Divisi -> Jabatan)
 *********************************************************/

ini_set('display_errors', 0);
error_reporting(0);

if (session_id() === '') session_start();
include "../../dist/koneksi.php";

// Helper
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// --- 1. RESOLVE FOTO ---
function resolve_foto_url($filename, $jk){
    $baseDir      = __DIR__ . "/../.."; 
    $newFs        = $baseDir . "/uploads/foto/";       
    $oldFs        = $baseDir . "/pages/assets/foto/";  
    $newUrlBase   = "uploads/foto/";
    $oldUrlBase   = "pages/assets/foto/";

    if ($filename && trim($filename) !== '') {
        if (file_exists($newFs . $filename)) return $newUrlBase . $filename;
        if (file_exists($oldFs . $filename)) return $oldUrlBase . $filename;
    }
    $fallback = ($jk === 'Laki-laki') ? 'no-foto-male.png' : 'no-foto-female.png';
    if (file_exists($oldFs . $fallback)) return $oldUrlBase . $fallback;
    return "https://ui-avatars.com/api/?name=User&background=random&color=fff";
}

// --- 2. AMBIL PARAMETER ---
$columns = ['nama', 'ttl', 'unit_kerja', 'jabatan', 'tgl_masuk', 'no_telp', 'action'];

// Filter Custom
$filterType    = isset($_GET['filter_type']) ? $_GET['filter_type'] : ''; 
$filterKantor  = isset($_GET['kantor']) ? mysqli_real_escape_string($conn, $_GET['kantor']) : '';  // Filter Kantor
$filterDivisi  = isset($_GET['divisi']) ? mysqli_real_escape_string($conn, $_GET['divisi']) : '';  // Filter Divisi (nama_unit_kerja)
$filterJabatan = isset($_GET['jabatan']) ? mysqli_real_escape_string($conn, $_GET['jabatan']) : ''; // Filter Jabatan

// Param DataTables
$limit  = isset($_GET['length']) ? intval($_GET['length']) : 10;
$offset = isset($_GET['start']) ? intval($_GET['start']) : 0;
$search = isset($_GET['search']['value']) ? mysqli_real_escape_string($conn, $_GET['search']['value']) : '';

// Sorting
$orderColumnIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
$orderDir         = (isset($_GET['order'][0]['dir']) && strtolower($_GET['order'][0]['dir']) === 'desc') ? 'DESC' : 'ASC';
$columnsDB = [ 0 => 'p.nama', 1 => 'p.tgl_lhr', 2 => 'k.nama_kantor', 3 => 'j.jabatan', 4 => 'p.tmt_kerja', 5 => 'p.telp' ];
$orderColumn = isset($columnsDB[$orderColumnIndex]) ? $columnsDB[$orderColumnIndex] : 'p.nama';

// Akses Kepala
$isKepala    = isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) === 'kepala';
$kode_kantor = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';

// --- 3. QUERY BUILDER ---
// Kita JOIN ke tb_master_jabatan (m) berdasarkan nama jabatan untuk dapat info Divisi/Lingkup
$sqlBase = "
    FROM tb_pegawai p
    LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
    LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
    LEFT JOIN tb_master_jabatan m ON j.jabatan = m.nama_jabatan 
    WHERE p.status_aktif = 1
";

// --- FILTER LOGIC ---
if ($filterType === 'nonjob') {
    // Tab Non-Job
    $sqlBase .= " AND j.id_jab IS NULL";
} else {
    // Tab Aktif
    $sqlBase .= " AND j.id_jab IS NOT NULL"; 

    // 1. Filter Kantor (Unit Kerja Fisik)
    if ($isKepala) {
        $sqlBase .= " AND j.unit_kerja = '{$kode_kantor}'";
    } elseif ($filterKantor !== '') {
        $sqlBase .= " AND j.unit_kerja = '{$filterKantor}'";
    }

    // 2. Filter Divisi (nama_unit_kerja dari master)
    if ($filterDivisi !== '') {
        $sqlBase .= " AND m.nama_unit_kerja = '{$filterDivisi}'";
    }

    // 3. Filter Jabatan Spesifik
    if ($filterJabatan !== '') {
        $sqlBase .= " AND j.jabatan = '{$filterJabatan}'";
    }
}

// Search Global
if ($search !== '') {
    $sqlBase .= " AND (
        p.id_peg LIKE '%{$search}%' OR 
        p.nama LIKE '%{$search}%' OR 
        j.jabatan LIKE '%{$search}%' OR
        k.nama_kantor LIKE '%{$search}%' OR
        m.nama_unit_kerja LIKE '%{$search}%'
    )";
}

// Hitung Filtered
$queryCount = mysqli_query($conn, "SELECT COUNT(*) as jum " . $sqlBase);
$rowCount   = mysqli_fetch_assoc($queryCount);
$totalFiltered = $rowCount['jum'];

// Hitung Total Data (Tanpa Search/Filter Detail)
$sqlTotalRaw = "SELECT COUNT(*) as jum FROM tb_pegawai p LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif' WHERE p.status_aktif = 1";
if ($filterType === 'nonjob') $sqlTotalRaw .= " AND j.id_jab IS NULL";
else $sqlTotalRaw .= " AND j.id_jab IS NOT NULL";

$queryTotal = mysqli_query($conn, $sqlTotalRaw);
$rowTotal   = mysqli_fetch_assoc($queryTotal);
$totalAll   = $rowTotal['jum'];

// Ambil Data Final
// Kita ambil juga m.nama_unit_kerja untuk ditampilkan kalau perlu
$sqlData = "SELECT 
    p.id_peg, p.nama, p.tempat_lhr, p.tgl_lhr, p.tmt_kerja, p.telp, p.foto, p.jk, p.status_kepeg, 
    j.jabatan, 
    k.nama_kantor,
    m.nama_unit_kerja AS divisi
" . $sqlBase;

$sqlData .= " ORDER BY {$orderColumn} {$orderDir} LIMIT {$offset}, {$limit}";

$result = mysqli_query($conn, $sqlData);
$data   = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Foto
        $fotoUrl  = resolve_foto_url($row['foto'], $row['jk']);
        $fotoHtml = '<div class="avatar-wrapper"><img src="'.h($fotoUrl).'?cb='.time().'" class="avatar-img" loading="lazy"></div>';
        
        $ttl = $row['tempat_lhr'] . ', ' . date('d-m-Y', strtotime($row['tgl_lhr']));

        // Render Kolom Unit Kerja (Gabung Kantor & Divisi)
        $unitDisplay = '<div style="line-height:1.3;">';
        $unitDisplay .= '<div class="text-primary font-weight-bold" style="font-size:0.9rem;">'.h($row['nama_kantor']).'</div>';
        if(!empty($row['divisi'])) {
            $unitDisplay .= '<div class="text-muted small"><i class="fa fa-sitemap mr-1"></i> '.h($row['divisi']).'</div>';
        }
        $unitDisplay .= '</div>';

        // Action Buttons
        $action = '<div class="btn-group">';
        if ($filterType === 'nonjob') {
            $cleanID = preg_replace('/[^a-zA-Z0-9-]/', '', $row['id_peg']);
            $action .= '<a href="home-admin.php?page=form-master-data-jabatan&uid=' . $cleanID . '" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm font-weight-bold"><i class="fa fa-plus-circle mr-1"></i> Set Jabatan</a>';
        } else {
            $action .= '<a href="home-admin.php?page=view-detail-data-pegawai&id_peg=' . h($row['id_peg']) . '" class="btn btn-sm btn-light text-info shadow-sm"><i class="fa fa-folder-open"></i></a>';
            if (!$isKepala) {
                $action .= '<a href="home-admin.php?page=form-master-data-pegawai&mode=edit&id=' . h($row['id_peg']) . '" class="btn btn-sm btn-light text-warning shadow-sm ml-1"><i class="fa fa-edit"></i></a>';
            }
        }
        $action .= '</div>';

        $data[] = [
            'nama'         => $fotoHtml, 
            'raw_nama'     => h($row['nama']), 
            'raw_id'       => h($row['id_peg']),
            'ttl'          => '<span class="small text-muted">'.$ttl.'</span>',
            'unit_kerja'   => $unitDisplay, // Tampilkan Kantor + Divisi
            'jabatan'      => h($row['jabatan']),
            'status_kepeg' => h($row['status_kepeg']),
            'tgl_masuk'    => date('d-m-Y', strtotime($row['tmt_kerja'])),
            'no_telp'      => h($row['telp']),
            'action'       => $action
        ];
    }
}

header('Content-Type: application/json');
echo json_encode([
    "draw" => intval($_GET['draw']),
    "recordsTotal" => intval($totalAll),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
]);
?>