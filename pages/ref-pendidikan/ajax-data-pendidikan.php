<?php
/*********************************************************
 * FILE     : pages/ref-pendidikan/ajax-data-pendidikan.php
 * MODULE   : Backend JSON Pendidikan (Secure & Sanitized)
 * VERSION  : v2.1 (Added Action Buttons & Security)
 *********************************************************/

if (session_id() == '') session_start();

// Matikan error display agar JSON valid
ini_set('display_errors', 0);
while(ob_get_level()){ ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');

// --- 1. KONEKSI ---
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn)) { 
    @include_once __DIR__ . '/../../config/koneksi.php'; 
    $conn = isset($koneksi) ? $koneksi : null; 
}

if (!$conn) {
    echo json_encode(['draw'=>0, 'recordsTotal'=>0, 'recordsFiltered'=>0, 'data'=>[], 'error'=>'Koneksi DB Gagal']);
    exit;
}

// --- 2. HELPER SECURITY ---
function esc($s){ global $conn; return mysqli_real_escape_string($conn, trim($s)); }
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// --- 3. PARAMETER DATATABLES ---
$draw   = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$len    = isset($_GET['length']) ? (int)$_GET['length'] : 10;
$search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
$uid    = isset($_GET['uid']) ? esc($_GET['uid']) : ''; // Filter ID Pegawai (opsional)

// --- 4. PARAMETER FILTER CUSTOM (Tambahan dari View Pendidikan) ---
$f_tahun   = isset($_GET['tahun']) ? esc($_GET['tahun']) : '';
$f_jenjang = isset($_GET['jenjang']) ? esc($_GET['jenjang']) : '';
$f_kantor  = isset($_GET['kantor']) ? esc($_GET['kantor']) : '';

// --- 5. QUERY BUILDER ---
$baseQuery = " FROM tb_pendidikan p 
               LEFT JOIN tb_pegawai pgw ON pgw.id_peg = p.id_peg 
               LEFT JOIN tb_jabatan j ON pgw.id_peg = j.id_peg AND j.status_jab = 'Aktif'
               LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail ";

$where = " WHERE 1=1 ";

// Filter UID (Jika dipanggil dari halaman Detail Pegawai)
if($uid !== ''){ 
    $where .= " AND p.id_peg='$uid' "; 
}

// Filter Custom Dropdown
if($f_tahun !== ''){ 
    $where .= " AND p.th_lulus = '$f_tahun' "; 
}
if($f_jenjang !== ''){ 
    $where .= " AND p.jenjang = '$f_jenjang' "; 
}
if($f_kantor !== ''){ 
    $where .= " AND j.unit_kerja = '$f_kantor' "; 
}

// Global Search
if($search !== ''){
    $s = esc($search);
    $where .= " AND (
        p.nama_sekolah LIKE '%$s%' OR 
        p.jurusan LIKE '%$s%' OR 
        p.jenjang LIKE '%$s%' OR 
        pgw.nama LIKE '%$s%'
    ) ";
}

// --- 6. HITUNG TOTAL ---
$total = 0;
$qCount = mysqli_query($conn, "SELECT COUNT(*) AS c $baseQuery $where");
if($qCount){ $r = mysqli_fetch_assoc($qCount); $total = (int)$r['c']; }

// --- 7. AMBIL DATA ---
$sql = "SELECT p.*, pgw.nama AS nama_peg
        $baseQuery $where
        ORDER BY COALESCE(p.th_lulus,'0000') DESC, p.id_pendidikan DESC
        LIMIT $start, $len";

$q = mysqli_query($conn, $sql);
$data = array();
$no = $start + 1;

if($q){
    while($r = mysqli_fetch_assoc($q)){
        
        // --- FORMAT DATA (Secure Output) ---
        
        // 1. Pegawai (Gabungan Nama & NIP) - Sesuai request 'idpeg_nama'
        $idpeg_nama = '<div class="font-weight-bold text-dark">'.h($r['nama_peg'] ?: '-').'</div>
                       <small class="text-muted">'.h($r['id_peg']).'</small>';

        // 2. Format Tanggal Ijazah
        $tgl_ijazah = ($r['tgl_ijazah'] && $r['tgl_ijazah']!='0000-00-00') ? date('d-m-Y', strtotime($r['tgl_ijazah'])) : '-';

        // 3. Tombol Aksi (Edit & Delete) - Style Modern
        $id_val = $r['id_pendidikan'];
        $aksi_html = '<div class="btn-group">
                        <a href="home-admin.php?page=form-master-data-pendidikan&id='.h($id_val).'" class="btn btn-sm btn-light border shadow-sm rounded-circle text-primary" title="Edit">
                            <i class="fa fa-pen"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-circle text-danger btn-delete" data-id="'.h($id_val).'" title="Hapus">
                            <i class="fa fa-trash"></i>
                        </button>
                      </div>';

        // Menyusun Array Data
        $data[] = array(
            'no'            => $no++,
            
            // Kolom Data Sesuai Request
            'id_pendidikan' => $r['id_pendidikan'],
            'id_peg'        => h($r['id_peg']),
            'idpeg_nama'    => $idpeg_nama, // HTML Nama Pegawai
            
            'jenjang'       => h($r['jenjang']),
            'nama_sekolah'  => h($r['nama_sekolah']),
            'jurusan'       => h($r['jurusan']),
            'th_lulus'      => h($r['th_lulus']),
            'no_ijazah'     => h($r['no_ijazah']),
            'tgl_ijazah'    => $tgl_ijazah,
            
            // Tambahan Kolom Aksi
            'aksi'          => $aksi_html
        );
    }
}

// Output JSON
echo json_encode(array(
    'draw'            => $draw,
    'recordsTotal'    => $total,
    'recordsFiltered' => $total,
    'data'            => $data
), JSON_UNESCAPED_UNICODE); 
exit;
?>