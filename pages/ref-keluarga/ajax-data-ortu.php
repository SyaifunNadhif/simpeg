<?php
/*********************************************************
 * FILE     : pages/ref-keluarga/ajax-data-ortu.php
 * MODULE   : Backend JSON Data Orang Tua (Secure & Sanitized)
 * VERSION  : v2.0 (Standardized with Sertifikasi & Pendidikan)
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

// --- 4. QUERY BUILDER ---
// Asumsi tabel 'tb_ortu' dan 'tb_pegawai'. Sesuaikan jika beda.
$baseQuery = " FROM tb_ortu o 
               LEFT JOIN tb_pegawai p ON o.id_peg = p.id_peg 
               LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
               LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail ";

$where = " WHERE 1=1 ";

// Filter UID (Jika dipanggil dari halaman Detail Pegawai)
if($uid !== ''){ 
    $where .= " AND o.id_peg='$uid' "; 
}

// Global Search
if($search !== ''){
    $s = esc($search);
    $where .= " AND (
        o.nama LIKE '%$s%' OR 
        o.status_hub LIKE '%$s%' OR 
        p.nama LIKE '%$s%' OR
        o.nik LIKE '%$s%' OR
        o.pekerjaan LIKE '%$s%'
    ) ";
}

// --- 6. HITUNG TOTAL ---
$total = 0;
$qCount = mysqli_query($conn, "SELECT COUNT(*) AS c $baseQuery $where");
if($qCount){ $r = mysqli_fetch_assoc($qCount); $total = (int)$r['c']; }

// --- 7. AMBIL DATA ---
// Pastikan kolom yang diambil ada di tabel Anda.
$sql = "SELECT o.*, p.nama AS nama_peg, p.nip
        $baseQuery $where
        ORDER BY o.id_ortu DESC
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

        // 2. Nama Orang Tua
        $nama_ortu = '<div class="font-weight-bold text-primary">'.h($r['nama']).'</div>';
        if(!empty($r['nik'])){
             $nama_ortu .= '<small class="text-muted"><i class="fa fa-id-card mr-1"></i> '.h($r['nik']).'</small>';
        }

        // 3. Status Hubungan (Ayah/Ibu)
        $status_hub = h($r['status_hub']);
        $status_badge = '<span class="badge badge-light border">'.$status_hub.'</span>';
        if (stripos($status_hub, 'ayah') !== false) {
            $status_badge = '<span class="badge badge-info text-white">Ayah</span>';
        } elseif (stripos($status_hub, 'ibu') !== false) {
            $status_badge = '<span class="badge badge-danger">Ibu</span>';
        }

        // 4. TTL
        $ttl = h($r['tmp_lhr']) . ', ' . ($r['tgl_lhr'] ? date('d-m-Y', strtotime($r['tgl_lhr'])) : '-');

        // 5. Tombol Aksi (Edit & Delete) - Style Modern
        $id_val = $r['id_ortu'];
        // Jika ada UID, kirim juga UID-nya agar tombol back di form edit bisa kembali ke profil pegawai
        $link_edit = "home-admin.php?page=form-master-data-ortu&mode=edit&id_ortu=".h($id_val).($uid ? "&uid=".h($uid) : "");
        
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
            'nama_ortu'     => $nama_ortu,
            'status_hub'    => $status_badge,
            'ttl'           => $ttl,
            'pekerjaan'     => h($r['pekerjaan']) ?: '-',
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