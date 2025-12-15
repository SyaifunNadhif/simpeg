<?php
/*********************************************************
 * FILE    : pages/ref-sertifikasi/ajax-data-sertifikasi.php
 * MODULE  : Backend JSON Sertifikasi (Fix ID & Status)
 *********************************************************/

// 1. PEMBERSIH OUTPUT (WAJIB ADA: Anti Error JSON)
if (session_id() == '') session_start();
ini_set('display_errors', 0);
while(ob_get_level()){ ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');

// 2. KONEKSI
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }

function esc($s){ return mysqli_real_escape_string($GLOBALS['conn'], trim($s)); }

// 3. PARAMETER DATATABLES
$draw   = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$len    = isset($_GET['length']) ? (int)$_GET['length'] : 10;
$search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';

// 4. FILTER CUSTOM
$f_tahun   = isset($_GET['tahun']) ? esc($_GET['tahun']) : date('Y');
$f_sertif  = isset($_GET['sertifikasi']) ? esc($_GET['sertifikasi']) : '';
$f_kantor  = isset($_GET['kantor']) ? esc($_GET['kantor']) : '';

// 5. QUERY DASAR
$baseQuery = " FROM tb_sertifikasi s 
               JOIN tb_pegawai p ON s.id_peg = p.id_peg 
               LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
               LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail ";

// 6. FILTER LOGIC
$where = " WHERE 1=1 ";

// Filter Tahun
if($f_tahun !== ''){ $where .= " AND YEAR(s.tgl_sertifikat) = '$f_tahun' "; }
// Filter Nama Sertifikasi
if($f_sertif !== ''){ $where .= " AND s.sertifikasi = '$f_sertif' "; }
// Filter Unit Kerja
if($f_kantor !== ''){ $where .= " AND j.unit_kerja = '$f_kantor' "; }

// Global Search
if($search !== ''){
    $s = esc($search);
    $where .= " AND (
        p.nama LIKE '%$s%' OR 
        s.sertifikasi LIKE '%$s%' OR 
        s.penyelenggara LIKE '%$s%' OR
        s.no_sertifikat LIKE '%$s%'
    ) ";
}

// 7. HITUNG TOTAL
$total = 0;
$qCount = mysqli_query($conn, "SELECT COUNT(*) AS c $baseQuery $where");
if($qCount){ $r = mysqli_fetch_assoc($qCount); $total = (int)$r['c']; }

// 8. AMBIL DATA
$sql = "SELECT s.*, p.nama AS nama_peg, p.nip, k.nama_kantor
        $baseQuery $where
        ORDER BY s.tgl_sertifikat DESC
        LIMIT $start, $len";

$q = mysqli_query($conn, $sql);
$data = array();
$no = $start + 1;

if($q){
    while($r = mysqli_fetch_assoc($q)){
        
        // --- FORMAT TAMPILAN ---
        
        // Nama Pegawai
        $nama_html = '<div class="font-weight-bold text-dark">'.$r['nama_peg'].'</div>
                      <small class="text-muted">'.$r['nip'].'</small>';

        // Sertifikasi
        $sertif_html = '<div class="font-weight-bold text-primary">'.$r['sertifikasi'].'</div>
                        <small class="text-muted">No: '.$r['no_sertifikat'].'</small>';

        // Penyelenggara
        $tgl_sert = ($r['tgl_sertifikat'] != '0000-00-00') ? date('d M Y', strtotime($r['tgl_sertifikat'])) : '-';
        $lokasi_html = '<div>'.$r['penyelenggara'].'</div>
                        <small class="text-muted"><i class="fa fa-calendar-alt mr-1"></i> '.$tgl_sert.'</small>';

        // Status Expired
        $tgl_exp = isset($r['tgl_expired']) ? $r['tgl_expired'] : '';
        if($tgl_exp == '' || $tgl_exp == '0000-00-00'){
            $status_html = '<span class="badge badge-success">Seumur Hidup</span>';
        } else {
            if($tgl_exp < date('Y-m-d')){
                $status_html = '<span class="badge badge-danger">Expired</span><br><small class="text-danger" style="font-size:11px;">'.date('d-m-Y', strtotime($tgl_exp)).'</small>';
            } else {
                $status_html = '<span class="badge badge-success">Aktif</span><br><small class="text-muted" style="font-size:11px;">Exp: '.date('d-m-Y', strtotime($tgl_exp)).'</small>';
            }
        }

        // --- TOMBOL AKSI (FIX ID DISINI) ---
        // Kita pakai $r['id_sertif'] sesuai database kamu
        $id_val = $r['id_sertif']; 

        $aksi_html = '<a href="home-admin.php?page=form-master-data-sertifikasi&id='.$id_val.'" class="btn btn-sm btn-light border shadow-sm rounded-circle text-primary" title="Edit">
                        <i class="fa fa-pen"></i>
                      </a>';

        $data[] = array(
            'no'            => $no++,
            'nama_peg'      => $nama_html,
            'sertifikasi'   => $sertif_html,
            'penyelenggara' => $lokasi_html,
            'unit_kerja'    => $r['nama_kantor'] ?: '-',
            'status'        => $status_html,
            'aksi'          => $aksi_html
        );
    }
}

// 9. OUTPUT JSON
echo json_encode(array(
    'draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data
)); 
exit;
?>