<?php
/*********************************************************
 * FILE     : pages/user/ajax-data-user.php
 * MODULE   : Backend JSON User (Direct Jabatan)
 *********************************************************/

if (session_id() == '') session_start();
ini_set('display_errors', 0);
while(ob_get_level()){ ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');

@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function esc($s){ global $conn; return mysqli_real_escape_string($conn, trim($s)); }

$draw   = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$len    = isset($_GET['length']) ? (int)$_GET['length'] : 10;
$search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
$f_role = isset($_GET['role']) ? esc($_GET['role']) : '';

// --- 1. BASE WHERE ---
$where = " WHERE 1=1 ";
if($f_role !== '') { $where .= " AND u.hak_akses = '$f_role' "; }

if($search !== '') {
    $s = esc($search);
    $where .= " AND (u.nama_user LIKE '%$s%' OR u.id_user LIKE '%$s%' OR u.jabatan LIKE '%$s%') ";
}

// --- 2. HITUNG TOTAL DATA ---
$qCount = mysqli_query($conn, "SELECT COUNT(*) AS c FROM tb_user u $where");
$total = ($qCount) ? (int)mysqli_fetch_assoc($qCount)['c'] : 0;

// --- 3. QUERY UTAMA (LANGSUNG KE TB_JABATAN) ---
// Perubahan: Tidak lagi join ke tb_master_jabatan.
// Langsung ambil kolom 'jabatan' dari 'tb_jabatan'.
$sql = "SELECT u.*,
        (
            SELECT j.jabatan 
            FROM tb_jabatan j 
            WHERE j.id_peg = u.id_pegawai AND j.status_jab = 'Aktif' 
            ORDER BY j.tmt_jabatan DESC LIMIT 1
        ) as jabatan_live
        FROM tb_user u 
        $where 
        ORDER BY u.created_at DESC 
        LIMIT $start, $len";

$q = mysqli_query($conn, $sql);
$data = array();
$no = $start + 1;

if($q){
    while($r = mysqli_fetch_assoc($q)){
        
        // Info User
        $user_info = '<div><b>'.h($r['nama_user']).'</b></div><small class="text-muted">@'.h($r['id_user']).'</small>';
        
        // Badge Role
        $role_cls = 'badge-secondary';
        if(strtolower($r['hak_akses'])=='admin') $role_cls = 'badge-primary';
        if(strtolower($r['hak_akses'])=='kepala') $role_cls = 'badge-info';
        $role_html = '<span class="badge '.$role_cls.' px-2 py-1">'.h(ucfirst($r['hak_akses'])).'</span>';

        // Badge Status
        $status_html = ($r['status_aktif']=='Y') ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Non-Aktif</span>';

        // --- LOGIC JABATAN ---
        // Prioritas 1: Jabatan Live dari Tabel Jabatan (Langsung kolom jabatan)
        // Prioritas 2: Jabatan Manual dari Tabel User (u.jabatan)
        // Default: "-"
        $jabatan_tampil = '-';
        if (!empty($r['jabatan_live'])) {
            $jabatan_tampil = h($r['jabatan_live']); // Ambil dari tb_jabatan
        } elseif (!empty($r['jabatan'])) {
            $jabatan_tampil = h($r['jabatan']);      // Ambil Manual (Backup)
        }

        // Tombol Aksi
        $aksi = '<div class="btn-group">
                    <a href="home-admin.php?page=form-master-data-user&mode=edit&id='.h($r['id_user']).'" class="btn btn-sm btn-light border shadow-sm text-warning rounded-circle" title="Edit"><i class="fas fa-pen"></i></a>
                    <button class="btn btn-sm btn-light border shadow-sm text-danger rounded-circle btn-delete" data-id="'.h($r['id_user']).'" title="Hapus"><i class="fas fa-trash"></i></button>
                 </div>';

        $data[] = array(
            'no' => $no++,
            'user_info' => $user_info,
            'jabatan' => $jabatan_tampil,
            'role' => $role_html,
            'status' => $status_html,
            'aksi' => $aksi
        );
    }
}

echo json_encode(array('draw'=>$draw, 'recordsTotal'=>$total, 'recordsFiltered'=>$total, 'data'=>$data));
?>