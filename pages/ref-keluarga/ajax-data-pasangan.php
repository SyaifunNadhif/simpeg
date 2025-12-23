<?php
/*********************************************************
 * FILE     : pages/ref-keluarga/ajax-data-pasangan.php
 * MODULE   : Backend JSON Data Pasangan (Secure & Sanitized)
 * VERSION  : v2.0 (Standardized)
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
$uid    = isset($_GET['uid']) ? esc($_GET['uid']) : ''; // Filter ID Pegawai

// --- 4. QUERY BUILDER ---
$baseQuery = " FROM tb_suamiistri si 
               LEFT JOIN tb_pegawai p ON si.id_peg = p.id_peg 
               LEFT JOIN tb_master_pekerjaan mp ON mp.id_pekerjaan = si.pekerjaan ";

$where = " WHERE 1=1 ";

// Filter UID (Jika dipanggil dari halaman Detail Pegawai)
if($uid !== ''){ 
    $where .= " AND si.id_peg='$uid' "; 
}

// Global Search
if($search !== ''){
    $s = esc($search);
    $where .= " AND (
        si.nama LIKE '%$s%' OR 
        p.nama LIKE '%$s%' OR
        si.nik LIKE '%$s%' OR
        si.pendidikan LIKE '%$s%' OR
        COALESCE(mp.desc_pekerjaan, '') LIKE '%$s%'
    ) ";
}

// --- 5. HITUNG TOTAL ---
$total = 0;
$qCount = mysqli_query($conn, "SELECT COUNT(*) AS c $baseQuery $where");
if($qCount){ $r = mysqli_fetch_assoc($qCount); $total = (int)$r['c']; }

// --- 6. AMBIL DATA ---
$sql = "SELECT si.*, p.nama AS nama_peg, p.nip, 
               COALESCE(mp.desc_pekerjaan, si.pekerjaan) AS pekerjaan_desc
        $baseQuery $where
        ORDER BY si.id_si DESC
        LIMIT $start, $len";

$q = mysqli_query($conn, $sql);
$data = array();
$no = $start + 1;

if($q){
    while($r = mysqli_fetch_assoc($q)){
        
        // --- FORMAT DATA (Secure Output) ---
        
        // 1. Pegawai (Gabungan Nama & NIP)
        $idpeg_nama = '<div class="font-weight-bold text-dark">'.h($r['nama_peg'] ?: '-').'</div>
                       <small class="text-muted">'.h($r['id_peg']).'</small>';

        // 2. Nama Pasangan
        $nama_si = '<div class="font-weight-bold text-primary">'.h($r['nama']).'</div>';
        if(!empty($r['nik'])){
             $nama_si .= '<small class="text-muted"><i class="fa fa-id-card mr-1"></i> '.h($r['nik']).'</small>';
        }

        // 3. Status Hubungan
        $status_hub = h($r['status_hub']);
        $status_badge = '<span class="badge badge-light border">'.$status_hub.'</span>';
        if (stripos($status_hub, 'suami') !== false) {
            $status_badge = '<span class="badge badge-info text-white">Suami</span>';
        } elseif (stripos($status_hub, 'istri') !== false) {
            $status_badge = '<span class="badge badge-danger">Istri</span>';
        }

        // 4. Tombol Aksi (Edit & Delete)
        $id_val = $r['id_si']; // Sesuaikan PK tabel tb_suamiistri
        $link_edit = "home-admin.php?page=form-master-data-suami-istri&mode=edit&id_si=".h($id_val).($uid ? "&uid=".h($uid) : "");
        
        $aksi_html = '<div class="btn-group">
                        <a href="'.$link_edit.'" class="btn btn-sm btn-light border shadow-sm rounded-circle text-primary" title="Edit">
                            <i class="fa fa-pen"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-circle text-danger btn-delete" data-id="'.h($id_val).'" title="Hapus">
                            <i class="fa fa-trash"></i>
                        </button>
                      </div>';

        // Menyusun Array Data
        $data[] = array(
            'no'            => $no++,
            'idpeg_nama'    => $idpeg_nama,
            'nama_pasangan' => $nama_si,
            'nik'           => h($r['nik']),
            'pendidikan'    => h($r['pendidikan']) ?: '-',
            'pekerjaan'     => h($r['pekerjaan_desc']) ?: '-',
            'status_hub'    => $status_badge,
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