<?php
// Mencegah error tampil di output JSON (penting untuk security dan validitas JSON)
ini_set('display_errors', 0);
error_reporting(0);

// Memulai session jika belum ada
if (session_id() === '') session_start();

// Include koneksi database (sesuaikan path jika perlu)
include "../../dist/koneksi.php";

// Fungsi Helper untuk mencegah XSS (Output Encoding)
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/**
 * Fungsi Resolusi URL Foto (Tanpa CDN)
 * Fallback menggunakan gambar lokal 'no-foto-male.png' atau 'no-foto-female.png'
 * Pastikan gambar placeholder ini ada di folder 'pages/assets/foto/'
 */
function resolve_foto_url($filename, $jk){
    $baseDir = __DIR__ . "/../.."; 
    
    // Path sistem file (untuk pengecekan file_exists)
    $newFs = $baseDir . "/uploads/foto/"; 
    $oldFs = $baseDir . "/pages/assets/foto/";  
    
    // URL relatif (untuk output ke browser)
    $newUrlBase = "uploads/foto/"; 
    $oldUrlBase = "pages/assets/foto/";

    // Cek jika ada filename spesifik di database
    if ($filename && trim($filename) !== '') {
        if (file_exists($newFs . $filename)) return $newUrlBase . $filename;
        if (file_exists($oldFs . $filename)) return $oldUrlBase . $filename;
    }
    
    // Logika Fallback Lokal (HAPUS CDN ui-avatars.com)
    // Pastikan file 'no-foto-male.png' dan 'no-foto-female.png' tersedia di folder assets.
    $fallback = ($jk === 'Laki-laki') ? 'no-foto-male.png' : 'no-foto-female.png';
    
    // Jika file fallback ada, gunakan. Jika tidak, gunakan string kosong atau path default lainnya.
    if (file_exists($oldFs . $fallback)) {
        return $oldUrlBase . $fallback;
    } else {
        // Opsi darurat jika gambar lokal hilang: return path kosong atau icon default lain
        return $oldUrlBase . 'default-user.png'; 
    }
}

// --- MENANGKAP & SANITASI PARAMETER INPUT ---

// 1. Validasi Kolom Pengurutan (Whitelisting untuk mencegah SQL Injection di ORDER BY)
$columnsDB = [ 
    0 => 'p.nama', 
    1 => 'p.tgl_lhr', 
    2 => 'j.jabatan', 
    3 => 'p.tmt_kerja', 
    4 => 'p.telp' 
];
$orderColumnIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
// Default ke 'p.nama' jika index tidak valid
$orderColumn = isset($columnsDB[$orderColumnIndex]) ? $columnsDB[$orderColumnIndex] : 'p.nama';

// 2. Validasi Arah Pengurutan
$orderDir = (isset($_GET['order'][0]['dir']) && strtolower($_GET['order'][0]['dir']) === 'desc') ? 'DESC' : 'ASC';

// 3. Sanitasi Input Teks (Filter & Search)
$filterType    = isset($_GET['filter_type']) ? $_GET['filter_type'] : ''; 
$filterKantor  = isset($_GET['kantor']) ? mysqli_real_escape_string($conn, $_GET['kantor']) : '';
$filterDivisi  = isset($_GET['divisi']) ? mysqli_real_escape_string($conn, $_GET['divisi']) : '';
$filterJabatan = isset($_GET['jabatan']) ? mysqli_real_escape_string($conn, $_GET['jabatan']) : '';
$search        = isset($_GET['search']['value']) ? mysqli_real_escape_string($conn, $_GET['search']['value']) : '';

// 4. Pagination
$limit  = isset($_GET['length']) ? intval($_GET['length']) : 10;
$offset = isset($_GET['start']) ? intval($_GET['start']) : 0;


// --- QUERY BUILDER ---

// Base Query (Bagian FROM dan JOIN)
// Menggunakan 1=1 di WHERE agar memudahkan appending kondisi AND
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

    // A. FILTER KANTOR (PARENT)
    if ($filterKantor !== '') {
        // Cek level kantor untuk logika hierarki
        $cekK = mysqli_query($conn, "SELECT level, kode_cabang FROM tb_kantor WHERE kode_kantor_detail='$filterKantor'");
        if ($cekK && mysqli_num_rows($cekK) > 0) {
            $dK = mysqli_fetch_assoc($cekK);
            if ($dK['level'] == 'KC') {
                // Jika KC, ambil semua di bawah cabang tersebut
                $kode_cabang = mysqli_real_escape_string($conn, $dK['kode_cabang']);
                $sqlBase .= " AND k.kode_cabang = '$kode_cabang'";
            } else {
                // Jika KP/KANWIL, exact match
                $sqlBase .= " AND j.unit_kerja = '$filterKantor'";
            }
        } else {
             // Fallback jika kantor tidak ditemukan, exact match aman
             $sqlBase .= " AND j.unit_kerja = '$filterKantor'";
        }
    }

    // B. FILTER DIVISI / UNIT DETAIL (CHILD)
    if ($filterDivisi !== '') {
        if (is_numeric($filterDivisi)) {
            // Asumsi input angka adalah ID Unit Kerja
            $sqlBase .= " AND j.unit_kerja = '$filterDivisi'";
        } else {
            // Input string adalah Nama Unit Kerja
            $sqlBase .= " AND m.nama_unit_kerja = '$filterDivisi'";
        }
    }

    // C. FILTER JABATAN
    if ($filterJabatan !== '') {
        $sqlBase .= " AND j.jabatan = '$filterJabatan'";
    }
}

// --- LOGIC PENCARIAN (SEARCH) ---
if ($search !== '') {
    $sqlBase .= " AND (
        p.id_peg LIKE '%$search%' OR 
        p.nama LIKE '%$search%' OR 
        j.jabatan LIKE '%$search%' OR 
        k.nama_kantor LIKE '%$search%'
    )";
}

// --- EKSEKUSI QUERY TOTAL FILTERED ---
$queryCount = mysqli_query($conn, "SELECT COUNT(*) as jum " . $sqlBase);
$rowCount   = mysqli_fetch_assoc($queryCount);
$totalFiltered = $rowCount['jum'];

// --- EKSEKUSI QUERY TOTAL ALL (Tanpa Filter Search, tapi tetap membedakan nonjob/job) ---
$sqlTotalRaw = "SELECT COUNT(*) as jum FROM tb_pegawai p LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif' WHERE p.status_aktif = 1";
if ($filterType === 'nonjob') {
    $sqlTotalRaw .= " AND j.id_jab IS NULL"; 
} else {
    $sqlTotalRaw .= " AND j.id_jab IS NOT NULL";
}
$qTotalAll = mysqli_query($conn, $sqlTotalRaw);
$totalAll  = mysqli_fetch_assoc($qTotalAll)['jum'];

// --- EKSEKUSI QUERY DATA UTAMA ---
$sqlData = "SELECT p.id_peg, p.nama, p.tempat_lhr, p.tgl_lhr, p.tmt_kerja, p.telp, p.foto, p.jk, p.status_kepeg, j.jabatan, k.nama_kantor, m.nama_unit_kerja AS divisi " 
         . $sqlBase 
         . " ORDER BY $orderColumn $orderDir LIMIT $offset, $limit";

$result = mysqli_query($conn, $sqlData);
$data = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Proses Foto
        $fotoUrl  = resolve_foto_url($row['foto'], $row['jk']);
        // Tambahkan timestamp ?cb= agar browser tidak cache foto lama jika baru diupdate
        $fotoHtml = '<div class="avatar-wrapper"><img src="'.h($fotoUrl).'?cb='.time().'" class="avatar-img" loading="lazy"></div>';
        
        // Format TTL
        $tgl_lhr = ($row['tgl_lhr']) ? date('d-m-Y', strtotime($row['tgl_lhr'])) : '-';
        $ttl = h($row['tempat_lhr']) . ', ' . $tgl_lhr;
        
        // Tombol Aksi
        $action = '<div class="btn-group">';
        if ($filterType === 'nonjob') {
            // Bersihkan ID untuk URL
            $cleanID = preg_replace('/[^a-zA-Z0-9-]/', '', $row['id_peg']);
            $action .= '<a href="home-admin.php?page=form-master-data-jabatan&uid='.$cleanID.'" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm font-weight-bold"><i class="fa fa-plus-circle mr-1"></i> Set Jabatan</a>';
        } else {
            $action .= '<a href="home-admin.php?page=view-detail-data-pegawai&id_peg='.h($row['id_peg']).'" class="btn btn-sm btn-action-blue"><i class="fa fa-folder-open"></i></a>';
            // Cek Hak Akses Session untuk tombol edit
            if (isset($_SESSION['hak_akses']) && ($_SESSION['hak_akses']=='admin' || $_SESSION['hak_akses']=='kepala')) {
                $action .= '<a href="home-admin.php?page=form-master-data-pegawai&mode=edit&id='.h($row['id_peg']).'" class="btn btn-sm btn-action-orange ml-1"><i class="fa fa-edit"></i></a>';
            }
        }
        $action .= '</div>';

        // Format Tanggal Masuk
        $tgl_masuk = ($row['tmt_kerja']) ? date('d-m-Y', strtotime($row['tmt_kerja'])) : '-';

        // Susun Data JSON
        $data[] = [
            'nama_teks' => h($row['nama']), 
            'nama_foto' => $fotoHtml, 
            'id_peg'    => h($row['id_peg']),
            'ttl'       => $ttl, 
            'jabatan'   => h($row['jabatan']), 
            'kantor'    => h($row['nama_kantor']), 
            'divisi'    => h($row['divisi']),
            'tgl_masuk' => $tgl_masuk, 
            'no_telp'   => h($row['telp']), 
            'action'    => $action
        ];
    }
}

// Return JSON Output
header('Content-Type: application/json');
echo json_encode([
    "draw"            => intval($_GET['draw']), 
    "recordsTotal"    => intval($totalAll), 
    "recordsFiltered" => intval($totalFiltered), 
    "data"            => $data
]);
?>