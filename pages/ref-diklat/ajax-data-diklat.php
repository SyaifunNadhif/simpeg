<?php
/***********************
 * FILE    : pages/ref-diklat/ajax-data-diklat.php
 * MODULE  : Backend JSON DataTables (Struktur Anti-Error)
 ***********************/

// --- 1. PEMBERSIH OUTPUT (PENTING AGAR TIDAK ERROR JSON) ---
if (session_id() === '') session_start();
ini_set('display_errors', 0);
while(ob_get_level()){ ob_end_clean(); } // Hapus sisa-sisa spasi/warning PHP
header('Content-Type: application/json; charset=utf-8');

// --- 2. KONEKSI ---
@include_once __DIR__ . '/../../dist/koneksi.php';
// Fallback koneksi jika path beda
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }

function esc($s){ return mysqli_real_escape_string($GLOBALS['conn'], trim($s)); }

// --- 3. AMBIL PARAMETER DATATABLES & FILTER ---
$draw   = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$len    = isset($_GET['length']) ? (int)$_GET['length'] : 10;
$search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';

// Filter Custom
$f_tahun   = isset($_GET['tahun']) ? esc($_GET['tahun']) : date('Y'); // Default tahun ini
$f_diklat  = isset($_GET['diklat']) ? esc($_GET['diklat']) : '';
$f_kantor  = isset($_GET['kantor']) ? esc($_GET['kantor']) : '';

// --- 4. QUERY DASAR ---
$baseQuery = " FROM tb_diklat d 
               JOIN tb_pegawai p ON d.id_peg = p.id_peg 
               LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
               LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail ";

// --- 5. LOGIKA FILTER (WHERE) ---
$where = " WHERE 1=1 ";

if($f_tahun !== '') { $where .= " AND d.tahun = '$f_tahun' "; }
if($f_diklat !== ''){ $where .= " AND d.diklat = '$f_diklat' "; }
if($f_kantor !== ''){ $where .= " AND j.unit_kerja = '$f_kantor' "; }

// Logika Pencarian Global (Search Box kanan atas tabel)
if($search !== ''){
    $s = esc($search);
    $where .= " AND (
        p.nama LIKE '%$s%' OR 
        d.diklat LIKE '%$s%' OR 
        d.penyelenggara LIKE '%$s%'
    ) ";
}

// --- 6. HITUNG TOTAL DATA (Wajib untuk Pagination) ---
$total = 0;
$qCount = mysqli_query($conn, "SELECT COUNT(*) AS c $baseQuery $where");
if($qCount){ $r = mysqli_fetch_assoc($qCount); $total = (int)$r['c']; }

// --- 7. AMBIL DATA ---
$sql = "SELECT d.*, p.nama AS nama_peg, p.nip, k.nama_kantor
        $baseQuery $where
        ORDER BY d.date_reg DESC
        LIMIT $start, $len";

$q = mysqli_query($conn, $sql);
$data = array();
$no = $start + 1;

if($q){
    while($r = mysqli_fetch_assoc($q)){
        // Format Tampilan di PHP agar JS Ringan
        $nama_html = '<div class="font-weight-bold text-dark">'.$r['nama_peg'].'</div>
                      <small class="text-muted">'.$r['nip'].'</small>';
        
        $lokasi_html = '<div>'.$r['penyelenggara'].'</div>
                        <small class="text-muted"><i class="fa fa-map-marker-alt text-danger mr-1"></i> '.$r['tempat'].'</small>';

        $aksi_html = '<a href="home-admin.php?page=form-diklat&id='.$r['id_diklat'].'" class="btn btn-sm btn-light border shadow-sm rounded-circle text-primary" title="Edit">
                        <i class="fa fa-pen"></i>
                      </a>';

        $data[] = array(
            'no'            => $no++,
            'nama_peg'      => $nama_html,
            'diklat'        => $r['diklat'], // Kolom Jenis Diklat
            'penyelenggara' => $lokasi_html,
            'unit_kerja'    => $r['nama_kantor'] ?: '-',
            'aksi'          => $aksi_html
        );
    }
}

// --- 8. OUTPUT JSON ---
echo json_encode(array(
    'draw'            => $draw,
    'recordsTotal'    => $total,
    'recordsFiltered' => $total,
    'data'            => $data
)); 
exit;
?>