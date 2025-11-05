<?php
// File: export-nominatif-pegawai-excel.php
// Versi: 1.0 - Ekspor Data Nominatif Pegawai ke Excel

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Nominatif_Pegawai_".date('dmY').".xls");

include "../../dist/koneksi.php";

$status_kepeg = isset($_GET['status_kepeg']) ? mysqli_real_escape_string($conn, $_GET['status_kepeg']) : '';
$unit_kerja   = isset($_GET['unit_kerja']) ? mysqli_real_escape_string($conn, $_GET['unit_kerja']) : '';
$jabatan      = isset($_GET['jabatan']) ? mysqli_real_escape_string($conn, $_GET['jabatan']) : '';

$where = "WHERE tb_pegawai.status_aktif = 1";
if ($status_kepeg != '') $where .= " AND tb_pegawai.status_kepeg = '$status_kepeg'";
if ($unit_kerja != '')   $where .= " AND tb_jabatan.unit_kerja = '$unit_kerja'";
if ($jabatan != '')      $where .= " AND tb_jabatan.jabatan = '$jabatan'";

echo "<table border='1'>";
echo "<thead>
<tr>
  <th>No</th>
  <th>ID Pegawai</th>
  <th>Nama</th>
  <th>NIK</th>
  <th>Jabatan</th>
  <th>TMT Jabatan</th>
  <th>Unit Kerja</th>
  <th>Status Kepegawaian</th>
  <th>Sekolah/Univ.</th>
  <th>Tahun Lulus</th>
  <th>Jenjang</th>
</tr>
</thead>
<tbody>";

$no = 1;
$query = "SELECT
            tb_pegawai.id_peg,
            tb_pegawai.nama,
            tb_pegawai.nip,
            tb_jabatan.jabatan,
            tb_jabatan.tmt_jabatan,
            (SELECT nama_kantor FROM tb_kantor WHERE kode_kantor_detail = tb_jabatan.unit_kerja) AS unit_kerja,
            tb_pegawai.status_kepeg,
            tb_sekolah.nama_sekolah,
            tb_sekolah.tgl_ijazah,
            tb_sekolah.jenjang
          FROM tb_pegawai
          LEFT JOIN tb_jabatan ON tb_pegawai.id_peg = tb_jabatan.id_peg
          LEFT JOIN tb_sekolah ON tb_pegawai.id_peg = tb_sekolah.id_peg
          $where";

$result = mysqli_query($conn, $query);
while ($peg = mysqli_fetch_array($result)) {
  echo "<tr>
    <td align='center'>".$no++."</td>
    <td>".$peg['id_peg']."</td>
    <td>".$peg['nama']."</td>
    <td>".$peg['nip']."</td>
    <td>".$peg['jabatan']."</td>
    <td>".$peg['tmt_jabatan']."</td>
    <td>".$peg['unit_kerja']."</td>
    <td>".$peg['status_kepeg']."</td>
    <td>".$peg['nama_sekolah']."</td>
    <td>".$peg['tgl_ijazah']."</td>
    <td>".$peg['jenjang']."</td>
  </tr>";
}

echo "</tbody></table>";
exit;