<?php
/***********************
 * FILE    : pages/ref-pendidikan/ajax-data-pendidikan.php
 * VERSION : v2.0 (Modern, Clean & Fix Search Error)
 * DATE    : 2025-12-10
 ***********************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php'; // Jika perlu
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }

ini_set('display_errors',0); while(ob_get_level()){ob_end_clean();}
header('Content-Type: application/json; charset=utf-8');

function esc($s){ return mysqli_real_escape_string($GLOBALS['conn'], trim($s)); }

$uid    = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$draw   = isset($_GET['draw'])? (int)$_GET['draw']:1;
$start  = isset($_GET['start'])?(int)$_GET['start']:0;
$len    = isset($_GET['length'])?(int)$_GET['length']:10;
$search = isset($_GET['search']['value'])? trim($_GET['search']['value']):'';

// 1. Definisikan Base Query (Join Table)
$baseQuery = " FROM tb_pendidikan p 
               LEFT JOIN tb_pegawai pgw ON pgw.id_peg = p.id_peg ";

// 2. Filter Kondisi (Where)
$where = " WHERE 1=1 ";
if($uid !== ''){ 
    $where .= " AND p.id_peg='".esc($uid)."' "; 
}

// 3. Logic Pencarian (Search)
if($search !== ''){
    $s = esc($search);
    // Kita cari di Nama Sekolah, Jurusan, Jenjang, DAN Nama Pegawai
    $where .= " AND (
        p.nama_sekolah LIKE '%$s%' OR 
        p.jurusan LIKE '%$s%' OR 
        p.jenjang LIKE '%$s%' OR 
        pgw.nama LIKE '%$s%'
    ) ";
}

// 4. Hitung Total Data (Filtered) - Penting agar pagination tidak error
$total = 0;
$qCount = mysqli_query($conn, "SELECT COUNT(*) AS c $baseQuery $where");
if($qCount){ 
    $r = mysqli_fetch_assoc($qCount); 
    $total = (int)$r['c']; 
}

// 5. Ambil Data (Select) - Kolom yang tidak perlu sudah dihapus
$sql = "SELECT p.id_pendidikan, p.id_peg, p.jenjang, p.nama_sekolah, p.jurusan, 
               p.th_lulus, p.no_ijazah, p.tgl_ijazah, 
               pgw.nama AS nama_peg
        $baseQuery
        $where
        ORDER BY COALESCE(p.th_lulus,'0000') DESC, p.id_pendidikan DESC
        LIMIT $start, $len";

$q = mysqli_query($conn, $sql);
$data = array();
$no = $start + 1;

if($q){
    while($r = mysqli_fetch_assoc($q)){
        $data[] = array(
            'no'            => $no++,
            // Data ID untuk Link Edit
            'id_pendidikan' => $r['id_pendidikan'],
            'id_peg'        => $r['id_peg'],
            
            // Tampilan Nama (Gabungan ID & Nama Pegawai)
            'idpeg_nama'    => '<div>'.($r['nama_peg']?:'-').'</div><small class="text-muted">'.$r['id_peg'].'</small>',
            
            'jenjang'       => $r['jenjang'],
            'nama_sekolah'  => $r['nama_sekolah'],
            'jurusan'       => $r['jurusan'],
            'th_lulus'      => $r['th_lulus'],
            'no_ijazah'     => $r['no_ijazah'],
            'tgl_ijazah'    => ($r['tgl_ijazah'] && $r['tgl_ijazah']!='0000-00-00') ? date('d-m-Y', strtotime($r['tgl_ijazah'])) : '-'
        );
    }
}

// Output JSON DataTables
echo json_encode(array(
    'draw'            => $draw,
    'recordsTotal'    => $total, // Menggunakan total filtered agar paging akurat
    'recordsFiltered' => $total,
    'data'            => $data
), JSON_UNESCAPED_UNICODE); 
exit;
?>