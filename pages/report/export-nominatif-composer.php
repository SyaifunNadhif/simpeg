<?php
// FILE: pages/laporan/export-nominatif-composer.php
include "../../dist/koneksi.php";

// Ambil Filter dari URL
$f_status  = isset($_GET['status']) ? $_GET['status'] : '';
$f_unit    = isset($_GET['unit']) ? $_GET['unit'] : '';
$f_jabatan = isset($_GET['jabatan']) ? $_GET['jabatan'] : '';

// Query Data (Tanpa Limit)
$where = " WHERE p.status_aktif = 1 ";
if (!empty($f_status))  $where .= " AND p.status_kepeg = '$f_status' ";
if (!empty($f_unit))    $where .= " AND j.unit_kerja = '$f_unit' ";
if (!empty($f_jabatan)) $where .= " AND j.jabatan = '$f_jabatan' ";

$sql = "SELECT p.nama, p.nip, p.status_kepeg,
               j.jabatan, j.tmt_jabatan, k.nama_kantor,
               s.jenjang, s.nama_sekolah
        FROM tb_pegawai p
        LEFT JOIN (
            SELECT j1.* FROM tb_jabatan j1
            INNER JOIN (SELECT id_peg, MAX(tmt_jabatan) AS max_tmt FROM tb_jabatan GROUP BY id_peg) j2 
            ON j1.id_peg = j2.id_peg AND j1.tmt_jabatan = j2.max_tmt
        ) j ON p.id_peg = j.id_peg
        LEFT JOIN tb_pendidikan s ON p.id_peg = s.id_peg AND s.status = 'Akhir'
        LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
        $where ORDER BY p.nama ASC";

$result = mysqli_query($conn, $sql);

// Header Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Nominatif_".date('Ymd').".xls");
?>

<table border="1" style="border-collapse: collapse;">
    <thead>
        <tr style="background-color: #4e73df; color: white;">
            <th>No</th>
            <th>Nama Pegawai</th>
            <th>NIP</th>
            <th>Jabatan</th>
            <th>Unit Kerja</th>
            <th>Status</th>
            <th>TMT Jabatan</th>
            <th>Pendidikan</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while($r = mysqli_fetch_assoc($result)){
            $pend = $r['jenjang'] ? $r['jenjang']." - ".$r['nama_sekolah'] : "-";
            echo "<tr>
                <td align='center'>{$no}</td>
                <td>{$r['nama']}</td>
                <td style='mso-number-format:\"\@\";'>{$r['nip']}</td>
                <td>{$r['jabatan']}</td>
                <td>{$r['nama_kantor']}</td>
                <td>{$r['status_kepeg']}</td>
                <td>{$r['tmt_jabatan']}</td>
                <td>{$pend}</td>
            </tr>";
            $no++;
        } 
        ?>
    </tbody>
</table>