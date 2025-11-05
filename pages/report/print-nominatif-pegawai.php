<?php
// File: print-nominatif-pegawai-mpdf.php
ini_set('memory_limit', '512M');
require_once '../../plugins/mpdf/mpdf.php';

include '../../dist/koneksi.php';

$status_kepeg = isset($_GET['status_kepeg']) ? $_GET['status_kepeg'] : '';
$unit_kerja   = isset($_GET['unit_kerja']) ? $_GET['unit_kerja'] : '';
$jabatan      = isset($_GET['jabatan']) ? $_GET['jabatan'] : '';

$where = "WHERE p.status_aktif = 1";
if ($status_kepeg != '') $where .= " AND p.status_kepeg = '$status_kepeg'";
if ($unit_kerja != '')   $where .= " AND j.unit_kerja = '$unit_kerja'";
if ($jabatan != '')      $where .= " AND j.jabatan = '$jabatan'";

$keterangan = "<br><div style='font-size:10pt;'>Filter: ";
$keterangan .= $status_kepeg ? "Status = $status_kepeg; " : "";
$keterangan .= $unit_kerja   ? "Unit = $unit_kerja; " : "";
$keterangan .= $jabatan      ? "Jabatan = $jabatan; " : "";
$keterangan .= "</div>";

$html = '<html><head><style>
  table { border-collapse: collapse; font-size: 9pt; }
  th, td { border: 1px solid #000; padding: 4px; }
  .title { text-align: center; font-size: 14pt; font-weight: bold; }
  .sub-title { text-align: center; font-size: 10pt; margin-bottom: 10px; }
</style></head><body>';

$html .= '<div class="title">DAFTAR NOMINATIF PEGAWAI</div>';
$html .= '<div class="sub-title">PT BPR BKK JATENG TAHUN ' . date('Y') . '</div>';
$html .= $keterangan;

$html .= '<table width="100%">
<tr align="center">
  <th rowspan="2">NO</th>
  <th colspan="2">NIP</th>
  <th rowspan="2">NIK</th>
  <th colspan="2">JABATAN</th>
  <th rowspan="2">UNIT KERJA</th>
  <th colspan="2">STATUS KEPEGAWAIAN</th>
  <th colspan="3">PEND AKHIR</th>
</tr>
<tr align="center">
  <th colspan="2">NAMA</th>
  <th>Deskripsi</th>
  <th>TMT</th>
  <th>Deskripsi</th>
  <th>TMT</th>
  <th>Sekolah/Univ.</th>
  <th>Th Lulus</th>
  <th>Jenjang</th>
</tr>
<tr align="center">
  <th>1</th><th colspan="2">2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th><th>10</th><th>11</th>
</tr>';

$no = 1;
$query = "SELECT
            p.id_peg,
            p.nama,
            p.nip,
            j.jabatan,
            j.tmt_jabatan,
            (SELECT nama_kantor FROM tb_kantor WHERE kode_kantor_detail = j.unit_kerja) AS unit_kerja,
            p.status_kepeg,
            s.nama_sekolah,
            s.tgl_ijazah,
            s.jenjang
          FROM tb_pegawai p
          LEFT JOIN (
            SELECT j1.*
            FROM tb_jabatan j1
            INNER JOIN (
              SELECT id_peg, MAX(tmt_jabatan) AS tmt_max
              FROM tb_jabatan
              GROUP BY id_peg
            ) j2 ON j1.id_peg = j2.id_peg AND j1.tmt_jabatan = j2.tmt_max
          ) j ON p.id_peg = j.id_peg
          LEFT JOIN tb_pendidikan s ON p.id_peg = s.id_peg AND s.status = 'Akhir'
          $where
          ORDER BY p.nama ASC";

$result = mysqli_query($conn, $query) or die(mysqli_error($conn));
while ($peg = mysqli_fetch_array($result)) {
  $html .= '<tr>
    <td align="center">' . $no++ . '</td>
    <td colspan="2">' . $peg['id_peg'] . '<br>' . $peg['nama'] . '</td>
    <td>' . $peg['nip'] . '</td>
    <td>' . $peg['jabatan'] . '</td>
    <td>' . $peg['tmt_jabatan'] . '</td>
    <td>' . $peg['unit_kerja'] . '</td>
    <td>' . $peg['status_kepeg'] . '</td>
    <td></td>
    <td>' . $peg['nama_sekolah'] . '</td>
    <td>' . $peg['tgl_ijazah'] . '</td>
    <td>' . $peg['jenjang'] . '</td>
  </tr>';
}

$html .= '</table><br><br>';
$html .= '<div style="text-align:right; font-size:10pt;">Dibuat di Semarang. Pada Tanggal, ' . date('d - m - Y') . '</div>';
$html .= '<br><br><div style="text-align:right; font-size:10pt;"><b>KEPALA DIVISI SDM DAN UMUM</b><br>PT BPR BKK JATENG<br><br><br><u>......................</u><br>Kepala Divisi<br><b>..........</b></div>';
$html .= '</body></html>';

$mpdf = new mPDF('utf-8', 'Legal-L');
$mpdf->WriteHTML($html);
$mpdf->Output('Daftar_Nominatif_Pegawai_' . date('dmY') . '.pdf', 'I');
