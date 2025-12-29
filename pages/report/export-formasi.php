<?php
ob_end_clean(); 
include "../../dist/koneksi.php";

$kode_cabang = isset($_GET['kode_cabang']) ? mysqli_real_escape_string($conn, $_GET['kode_cabang']) : '';
$nama_file = "Laporan_Formasi_Pegawai_" . date('Ymd_His') . ".xls";

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=$nama_file");
header("Pragma: no-cache");
header("Expires: 0");

$where_master_lingkup = "";
$join_filter_cabang = "";
$label_filter = "";

// LOGIC 3 TINGKATAN
if ($kode_cabang == 'PUSAT') {
    $where_master_lingkup = "WHERE m.lingkup IN ('KP', 'KANWIL')";
    $join_filter_cabang   = ""; 
    $label_filter         = "Filter Unit: KANTOR PUSAT & KANWIL";
} 
elseif ($kode_cabang != '') {
    $where_master_lingkup = "WHERE m.lingkup IN ('KC', 'KK')";
    $join_filter_cabang   = "AND j.unit_kerja = '$kode_cabang'";
    $label_filter         = "Filter Unit: CABANG " . $kode_cabang;
} 
else {
    $where_master_lingkup = ""; 
    $join_filter_cabang   = ""; 
    $label_filter         = "Filter Unit: SEMUA KANTOR / GLOBAL (GABUNGAN)";
}

$query = "
    SELECT * FROM (
        SELECT m.nama_jabatan, m.kuota, COUNT(DISTINCT p.id_peg) AS terisi, (m.kuota - COUNT(DISTINCT p.id_peg)) AS kosong, 'Master' as sumber
        FROM tb_master_jabatan m
        LEFT JOIN tb_jabatan j ON j.jabatan = m.nama_jabatan AND j.status_jab = 'Aktif' $join_filter_cabang
        LEFT JOIN tb_pegawai p ON p.id_peg = j.id_peg AND p.status_aktif = '1'
        $where_master_lingkup
        GROUP BY m.nama_jabatan, m.kuota

        UNION ALL

        SELECT j.jabatan as nama_jabatan, 0 as kuota, COUNT(DISTINCT p.id_peg) AS terisi, (0 - COUNT(DISTINCT p.id_peg)) AS kosong, 'NonMaster' as sumber
        FROM tb_jabatan j
        JOIN tb_pegawai p ON p.id_peg = j.id_peg AND p.status_aktif = '1'
        LEFT JOIN tb_master_jabatan m ON m.nama_jabatan = j.jabatan
        WHERE m.nama_jabatan IS NULL AND j.status_jab = 'Aktif'
        $join_filter_cabang
        GROUP BY j.jabatan
    ) AS gabungan
    ORDER BY nama_jabatan ASC
";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h3 style="text-align: center;">LAPORAN FORMASI PEGAWAI</h3>
    <p style="text-align: center;"><?= $label_filter ?> | Per Tanggal: <?= date('d-m-Y') ?></p>
    <table>
        <thead>
            <tr>
                <th>NO</th>
                <th>JABATAN</th>
                <th>KUOTA</th>
                <th>TERISI</th>
                <th>SISA</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $t_kuota = 0; $t_terisi = 0; $t_kosong = 0;
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $t_kuota += $row['kuota']; $t_terisi += $row['terisi']; $t_kosong += $row['kosong'];
                    $kosong = $row['kosong'];
                    $status = ($kosong > 0) ? "KURANG $kosong" : (($kosong < 0) ? "OVER ".abs($kosong) : "TERPENUHI");
                    $nm = ($row['sumber'] == 'NonMaster') ? ' *' : '';
                    echo "<tr><td class='center'>".$no++."</td><td>".$row['nama_jabatan'].$nm."</td><td class='center'>".$row['kuota']."</td><td class='center'>".$row['terisi']."</td><td class='center'>".(($kosong < 0) ? '+' . abs($kosong) : $kosong)."</td><td class='center'>$status</td></tr>";
                }
            }
            ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #eee; font-weight: bold;">
                <td colspan="2" style="text-align: right;">GRAND TOTAL</td>
                <td class="center"><?= $t_kuota ?></td>
                <td class="center"><?= $t_terisi ?></td>
                <td class="center"><?= ($t_kosong < 0) ? '+' . abs($t_kosong) : $t_kosong ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>