<?php
// File: export-formasi.php
// Versi: 2.0 - Ekspor Excel Berdasarkan tb_ref_jabatan dan Filter Kantor

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Formasi_Pegawai_".date('dmY').".xls");

include "../../dist/koneksi.php";

$kode_cabang = isset($_GET['kode_cabang']) ? mysqli_real_escape_string($conn, $_GET['kode_cabang']) : '';
$filter_cabang = ($kode_cabang != '') ? "AND j.unit_kerja = '$kode_cabang'" : '';
$filter_lingkup = ($kode_cabang != '') ? "WHERE r.lingkup = 'KC'" : "";

$query = "SELECT 
            r.jabatan,
            r.kuota,
            COUNT(p.id_peg) AS terisi,
            (r.kuota - COUNT(p.id_peg)) AS kosong
          FROM tb_ref_jabatan r
          LEFT JOIN tb_jabatan j ON j.jabatan = r.jabatan $filter_cabang
          LEFT JOIN tb_pegawai p ON p.id_peg = j.id_peg AND p.status_aktif = 1
          $filter_lingkup
          GROUP BY r.jabatan, r.kuota
          ORDER BY r.jabatan";

$result = mysqli_query($conn, $query);

echo "<table border='1'>";
echo "<thead>
<tr>
  <th>No</th>
  <th>Jabatan</th>
  <th>Kuota</th>
  <th>Terisi</th>
  <th>Kosong</th>
  <th>Keterangan</th>
</tr>
</thead>
<tbody>";

$no = 1;
while ($row = mysqli_fetch_array($result)) {
  $keterangan = ($row['kosong'] == 0) ? 'Terpenuhi' : 'Perlu Penempatan';
  echo "<tr>
    <td align='center'>".$no++."</td>
    <td>".$row['jabatan']."</td>
    <td align='center'>".$row['kuota']."</td>
    <td align='center'>".$row['terisi']."</td>
    <td align='center'>".$row['kosong']."</td>
    <td>".$keterangan."</td>
  </tr>";
}
echo "</tbody></table>";
