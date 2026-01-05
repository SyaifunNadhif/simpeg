<?php
/***********************
 * FILE   : pages/biaya_pendidikan/ajax-data-biaya-pendidikan.php
 * MODULE : Backend JSON DataTables Biaya Pendidikan
 ***********************/

// --- 1. CLEAN OUTPUT & SESSION START ---
if (session_id() === '') session_start();
ini_set('display_errors', 0);
while(ob_get_level()){ ob_end_clean(); } 
header('Content-Type: application/json; charset=utf-8');

// --- 2. SECURITY CHECK ---
if (empty($_SESSION['id_user'])) {
    echo json_encode(['error' => 'Akses ditolak. Silakan login.']);
    exit;
}

// --- 3. KONEKSI DATABASE ---
// Sesuaikan path ini dengan struktur foldermu
$path_koneksi = __DIR__ . '/../../dist/koneksi.php';
if (file_exists($path_koneksi)) {
    include $path_koneksi;
} else {
    echo json_encode(['error' => 'Koneksi database gagal (File not found).']);
    exit;
}

// Helper: Escape SQL
function esc($conn, $s){ 
    return mysqli_real_escape_string($conn, trim($s)); 
}

// Helper: Escape HTML & Format Rupiah
function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function formatRupiah($angka){ return "Rp " . number_format($angka, 0, ',', '.'); }
function formatTgl($tgl){ return ($tgl && $tgl != '0000-00-00') ? date('d-m-Y', strtotime($tgl)) : '-'; }

// --- 4. PARAMETER DATATABLES ---
$draw   = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$len    = isset($_GET['length']) ? (int)$_GET['length'] : 10;
$search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';

// --- 5. PARAMETER FILTER (Tahun & Kuartal) ---
$f_tahun   = isset($_GET['tahun']) ? esc($conn, $_GET['tahun']) : ''; // Default kosong = semua tahun (atau bisa set date('Y'))
$f_kuartal = isset($_GET['kuartal']) ? esc($conn, $_GET['kuartal']) : ''; // 1, 2, 3, atau 4

// --- 6. QUERY BUILDER ---
// Kita JOIN ke tabel referensi biar bisa cari berdasarkan Nama Kategori juga
$baseQuery = " FROM tb_biaya_pendidikan b
               LEFT JOIN tb_ref_pengembangan rp ON b.kode_pengembangan = rp.kode_sandi
               LEFT JOIN tb_ref_pelaksana rpl ON b.kode_pihak = rpl.kode_pihak ";

$where = " WHERE 1=1 ";

// A. Filter Tahun (Berdasarkan tgl_pengembangan_sdm)
if($f_tahun !== '') { 
    $where .= " AND YEAR(b.tgl_pengembangan_sdm) = '$f_tahun' "; 
}

// B. Filter Kuartal (1-4)
if($f_kuartal !== '') {
    if($f_kuartal == '1') {
        $where .= " AND MONTH(b.tgl_pengembangan_sdm) BETWEEN 1 AND 3 "; // Jan - Mar
    } elseif($f_kuartal == '2') {
        $where .= " AND MONTH(b.tgl_pengembangan_sdm) BETWEEN 4 AND 6 "; // Apr - Jun
    } elseif($f_kuartal == '3') {
        $where .= " AND MONTH(b.tgl_pengembangan_sdm) BETWEEN 7 AND 9 "; // Jul - Sep
    } elseif($f_kuartal == '4') {
        $where .= " AND MONTH(b.tgl_pengembangan_sdm) BETWEEN 10 AND 12 "; // Okt - Des
    }
}

// C. Pencarian Global
if($search !== ''){
    $s = esc($conn, $search);
    $where .= " AND (
        b.pengembangan_sdm LIKE '%$s%' OR 
        b.pihak_pelaksana LIKE '%$s%' OR
        rp.kategori LIKE '%$s%' OR
        rpl.nama_pihak LIKE '%$s%'
    ) ";
}

// --- 7. HITUNG TOTAL DATA ---
$total = 0;
$qCount = mysqli_query($conn, "SELECT COUNT(*) AS c $baseQuery $where");
if($qCount){ $r = mysqli_fetch_assoc($qCount); $total = (int)$r['c']; }

// --- 8. AMBIL DATA UTAMA ---
$sql = "SELECT b.*, 
               rp.kategori AS nama_kategori, 
               rpl.nama_pihak AS jenis_pihak
        $baseQuery $where
        ORDER BY b.tgl_pengembangan_sdm DESC, b.created_at DESC
        LIMIT $start, $len";

$q = mysqli_query($conn, $sql);
$data = array();
$no = $start + 1;

if($q){
    while($r = mysqli_fetch_assoc($q)){
        
        // Data Mentah
        $id          = h($r['biaya_id']);
        $judul       = h($r['pengembangan_sdm']);
        $tgl         = formatTgl($r['tgl_pengembangan_sdm']);
        $durasi      = h($r['waktu_pelaksanaan']);
        $jml_sdm     = (int)$r['jumlah_sdm'];
        $biaya       = (float)$r['total_biaya'];
        
        // Data Join (Ref) vs Manual
        // Kalau nama kategori di Ref ada, pakai itu. Kalau tidak, tampilkan kodenya.
        $kategori    = !empty($r['nama_kategori']) ? h($r['nama_kategori']) : '<span class="text-danger">'.h($r['kode_pengembangan']).'</span>';
        
        // Nama Pihak: Gabungan Jenis Pihak (Ref) & Nama Instansi (Manual)
        $jenis_pihak = !empty($r['jenis_pihak']) ? h($r['jenis_pihak']) : '-';
        $instansi    = h($r['pihak_pelaksana']);

        // --- FORMAT HTML UNTUK TABEL ---

        // Kolom 1: Detail Kegiatan
        $detail_html = '<div class="font-weight-bold text-dark">'.$judul.'</div>
                        <div class="small text-muted mt-1">
                            <i class="fa fa-tag text-info mr-1"></i> '.$kategori.'
                        </div>';

        // Kolom 2: Pihak Pelaksana
        $pihak_html = '<div class="text-dark">'.$instansi.'</div>
                       <small class="text-muted font-italic">('.$jenis_pihak.')</small>';

        // Kolom 3: Waktu
        $waktu_html = '<div><i class="fa fa-calendar-alt text-primary mr-1"></i> '.$tgl.'</div>
                       <small class="text-muted">Durasi: '.$durasi.'</small>';

        // Kolom 4: Biaya & SDM
        $biaya_html = '<div class="font-weight-bold text-success">'.formatRupiah($biaya).'</div>
                       <small class="text-muted">Peserta: '.$jml_sdm.' Org</small>';

        // Kolom 5: Aksi
        $aksi_html = '<div style="white-space:nowrap;">
                        <a href="home-admin.php?page=form-biaya-pendidikan&id='.$id.'" class="btn btn-sm btn-light border shadow-sm rounded-circle text-primary me-1" title="Edit">
                            <i class="fa fa-pen"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-circle text-danger btn-delete" data-id="'.$id.'" title="Hapus">
                            <i class="fa fa-trash"></i>
                        </button>
                      </div>';

        $data[] = array(
            'no'            => $no++,
            'kegiatan'      => $detail_html,
            'pelaksana'     => $pihak_html,
            'waktu'         => $waktu_html,
            'biaya'         => $biaya_html,
            'aksi'          => $aksi_html
        );
    }
}

// --- 9. OUTPUT JSON ---
echo json_encode(array(
    'draw'            => $draw,
    'recordsTotal'    => $total,
    'recordsFiltered' => $total, // Wajib sama dengan total jika filter server-side
    'data'            => $data
)); 
exit;
?>