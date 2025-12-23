<?php
/*********************************************************
 * FILE   : pages/report/view-keadaan-pegawai.php
 * MODULE : Laporan Keadaan Pegawai (Modern UI - Stabil)
 *********************************************************/

if (session_id() == '') session_start();

// --- 1. HELPER FUNCTIONS ---
function usia_range($usia) {
    if ($usia <= 25) return '0-25';
    if ($usia <= 35) return '26-35';
    if ($usia <= 45) return '36-45';
    if ($usia <= 55) return '46-55';
    return '>55';
}

function hitung_usia($tgl_lahir) {
    if (empty($tgl_lahir) || $tgl_lahir == '0000-00-00') return 0;
    try {
        $lahir = new DateTime($tgl_lahir);
        $today = new DateTime(); 
        return $lahir->diff($today)->y;
    } catch (Exception $e) {
        return 0;
    }
}

// --- 2. SETUP DATA ARRAYS ---
$kelompok_usia = ['0-25', '26-35', '36-45', '46-55', '>55'];
$rekap = [];
foreach ($kelompok_usia as $u) {
    $rekap[$u] = [
        'jk' => [], 'pend' => [], 'status' => [], 'jabatan' => [], 'hukuman' => []
    ];
}
$rekap['JML'] = $rekap[$kelompok_usia[0]]; 

// --- 3. FILTER LOGIC ---
$kode_cabang = isset($_GET['kode_cabang']) ? mysqli_real_escape_string($conn, $_GET['kode_cabang']) : '';
$where_condition = " WHERE p.status_aktif = '1' ";

if ($kode_cabang != '') {
    $qK = mysqli_query($conn, "SELECT level, kode_cabang FROM tb_kantor WHERE kode_kantor_detail = '$kode_cabang'");
    $dK = mysqli_fetch_assoc($qK);
    if ($dK && $dK['level'] == 'KC') {
        $kd_cab = $dK['kode_cabang'];
        $where_condition .= " AND k.kode_cabang = '$kd_cab' ";
    } else {
        $where_condition .= " AND j.unit_kerja = '$kode_cabang' ";
    }
}

// --- 4. MAIN QUERY ---
$sql = "SELECT 
            p.id_peg, p.nama, p.tgl_lhr, p.jk, p.status_kepeg,
            j.jabatan,
            s.jenjang
        FROM tb_pegawai p
        LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
        LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
        LEFT JOIN (
            SELECT t1.id_peg, t1.jenjang
            FROM tb_pendidikan t1
            JOIN (
                SELECT id_peg, MAX(tgl_ijazah) as max_tgl
                FROM tb_pendidikan
                GROUP BY id_peg
            ) t2 ON t1.id_peg = t2.id_peg AND t1.tgl_ijazah = t2.max_tgl
        ) s ON p.id_peg = s.id_peg
        $where_condition
        GROUP BY p.id_peg";

$query = mysqli_query($conn, $sql);
if (!$query) { echo "<div class='alert alert-danger'>Main Query Error: " . mysqli_error($conn) . "</div>"; exit; }

$list_jabatan = [];
$list_status  = [];

while ($p = mysqli_fetch_assoc($query)) {
    $usia  = hitung_usia($p['tgl_lhr']);
    $range = usia_range($usia);

    // JK
    $jk = (strtolower($p['jk']) == 'perempuan') ? 'Perempuan' : 'Laki-laki';
    @$rekap[$range]['jk'][$jk]++; @$rekap['JML']['jk'][$jk]++;

    // PENDIDIKAN
    $pend = strtoupper(trim($p['jenjang']));
    if (empty($pend)) $pend = 'LAIN-LAIN';
    if ($pend == 'D2' || $pend == 'D3') $pend = 'D2/D3';
    if ($pend == 'S2' || $pend == 'S3') $pend = 'S2/S3';
    @$rekap[$range]['pend'][$pend]++; @$rekap['JML']['pend'][$pend]++;

    // STATUS
    $status = strtoupper(trim($p['status_kepeg']));
    if (empty($status)) $status = '-';
    if (!in_array($status, $list_status)) $list_status[] = $status;
    @$rekap[$range]['status'][$status]++; @$rekap['JML']['status'][$status]++;

    // JABATAN
    $jab = trim($p['jabatan']);
    if (empty($jab)) $jab = 'Non-Job';
    if (!in_array($jab, $list_jabatan)) $list_jabatan[] = $jab;
    @$rekap[$range]['jabatan'][$jab]++; @$rekap['JML']['jabatan'][$jab]++;
}
sort($list_status);
sort($list_jabatan);

// --- 5. HUKUMAN QUERY ---
$tahun_skr = date('Y');
$list_hukuman = [];

$sqlHukuman = "SELECT h.hukuman, p.tgl_lhr 
               FROM tb_hukuman h 
               JOIN tb_pegawai p ON h.id_peg = p.id_peg 
               LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
               LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
               $where_condition 
               AND YEAR(h.tgl_sk) = '$tahun_skr'";

$qh = mysqli_query($conn, $sqlHukuman);
if ($qh) {
    while ($h = mysqli_fetch_assoc($qh)) {
        $usia  = hitung_usia($h['tgl_lhr']);
        $range = usia_range($usia);
        $jenis = strtoupper($h['hukuman']);
        if (!in_array($jenis, $list_hukuman)) $list_hukuman[] = $jenis;
        @$rekap[$range]['hukuman'][$jenis]++; @$rekap['JML']['hukuman'][$jenis]++;
    }
}
sort($list_hukuman);

// --- 6. SETUP TAMPILAN ---
$kategori = [
    ['label' => 'JENIS KELAMIN', 'key' => 'jk', 'sub' => ['Laki-laki', 'Perempuan']],
    ['label' => 'JENIS PENDIDIKAN', 'key' => 'pend', 'sub' => ['SD', 'SMP', 'SMA', 'SMK', 'D1', 'D2/D3', 'S1', 'S2/S3', 'LAIN-LAIN']],
    ['label' => 'STATUS KEPEGAWAIAN', 'key' => 'status', 'sub' => $list_status],
    ['label' => 'JABATAN', 'key' => 'jabatan', 'sub' => $list_jabatan],
    ['label' => 'HUKUMAN DISIPLIN (Tahun '.$tahun_skr.')', 'key' => 'hukuman', 'sub' => $list_hukuman]
];
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

    .report-container {
        font-family: 'Poppins', sans-serif;
        font-size: 0.9rem;
        color: #444;
        width: 100%;
        overflow-x: hidden; /* Mencegah scroll horizontal container utama */
    }
    
    .card-modern {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        background: #fff;
        margin-bottom: 20px;
    }

    /* Override Table Responsive agar tidak ada scrollbar bawah */
    .table-responsive-custom {
        width: 100%;
        overflow-x: hidden !important; 
    }

    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-modern thead th {
        background: linear-gradient(45deg, #1e3c72, #2a5298);
        color: white;
        padding: 15px 10px; /* Padding sedikit dikecilkan agar muat */
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
        vertical-align: middle !important;
        font-size: 0.85rem; /* Font header disesuaikan */
    }

    /* HAPUS TRANSFORM SCALE PENYEBAB KEDEP-KEDEP */
    .table-modern tbody tr:hover {
        background-color: #f0f7ff; /* Hanya ganti warna background */
        transition: background-color 0.2s ease;
    }

    .table-modern td {
        padding: 10px 12px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }

    .cat-header {
        background-color: #f8f9fa;
        color: #2c3e50;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.9rem;
    }
    
    .subtotal-row {
        background-color: #e8eaf6 !important;
        font-weight: 600;
        color: #1e3c72;
    }

    .badge-modern {
        display: inline-block;
        min-width: 30px;
        padding: 4px 8px;
        border-radius: 6px;
        background: #f1f3f5;
        color: #555;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .badge-modern.active {
        background: #e3f2fd;
        color: #1565c0;
    }
    
    .empty-data {
        color: #cfd8dc;
        font-size: 0.8rem;
    }
</style>

<div class="report-container">
    <div class="card-modern">
        <div class="table-responsive-custom">
            <table class="table-modern">
                <thead class="text-center">
                    <tr>
                        <th rowspan="2" width="5%">NO</th>
                        <th rowspan="2">JENIS LAPORAN</th>
                        <th colspan="5">RENTANG USIA (TAHUN)</th>
                        <th rowspan="2" width="10%">TOTAL</th>
                    </tr>
                    <tr>
                        <th width="10%">0-25</th>
                        <th width="10%">26-35</th>
                        <th width="10%">36-45</th>
                        <th width="10%">46-55</th>
                        <th width="10%">>55</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($kategori as $kat): 
                        $subtotal_usia = array_fill_keys($kelompok_usia, 0);
                        $subtotal_total = 0;
                    ?>
                        <tr class="cat-header">
                            <td align="center"><?= $no++ ?></td>
                            <td colspan="7"><?= $kat['label'] ?></td>
                        </tr>

                        <?php 
                        if (empty($kat['sub'])) {
                             echo "<tr><td></td><td class='pl-4 text-muted font-italic'>- Tidak ada data -</td><td colspan='6'></td></tr>";
                        } else {
                            foreach ($kat['sub'] as $sub): 
                        ?>
                            <tr>
                                <td></td>
                                <td class="pl-4">
                                    <span style="color: #bbb; margin-right:5px;">•</span> <?= $sub ?>
                                </td>
                                
                                <?php 
                                $total_baris = 0;
                                foreach ($kelompok_usia as $u): 
                                    $val = isset($rekap[$u][$kat['key']][$sub]) ? $rekap[$u][$kat['key']][$sub] : 0;
                                    $subtotal_usia[$u] += $val;
                                    $total_baris += $val;
                                    
                                    $display = ($val > 0) ? "<span class='badge-modern active'>$val</span>" : "<span class='empty-data'>-</span>";
                                ?>
                                    <td align="center"><?= $display ?></td>
                                <?php endforeach; ?>

                                <td align="center">
                                    <span class="badge-modern" style="background:#37474f; color:white;"><?= ($total_baris > 0) ? $total_baris : '-' ?></span>
                                </td>
                            </tr>
                        <?php 
                                $subtotal_total += $total_baris;
                            endforeach; 
                        }
                        ?>

                        <tr class="subtotal-row">
                            <td></td>
                            <td align="right" style="padding-right: 20px;">SUBTOTAL</td>
                            <?php foreach ($kelompok_usia as $u): ?>
                                <td align="center"><?= ($subtotal_usia[$u] > 0) ? number_format($subtotal_usia[$u]) : '-' ?></td>
                            <?php endforeach; ?>
                            <td align="center" style="font-size: 1rem; font-weight: 700; color: #c62828;">
                                <?= ($subtotal_total > 0) ? number_format($subtotal_total) : '-' ?>
                            </td>
                        </tr>
                        
                        <tr><td colspan="8" style="background:#fff; height:10px; border:none;"></td></tr>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>