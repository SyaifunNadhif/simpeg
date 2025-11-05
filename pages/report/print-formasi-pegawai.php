<?php
// File: print-formasi.php
// Versi: 2.0 - Formasi Berdasarkan tb_ref_jabatan dan filter kantor

include '../../plugins/mpdf/mpdf.php';
include '../../dist/koneksi.php';

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

$html = '<style>
  table { border-collapse: collapse; width: 100%; font-size: 10pt; }
  th, td { border: 1px solid #000; padding: 5px; }
  th { background-color: #eee; }
</style>';

$html .= '<h3 align="center">LAPORAN FORMASI PEGAWAI</h3>';
$html .= '<h5 align="center">PT BPR BKK JATENG TAHUN '.date('Y').'</h5>';

$html .= '<table>
<thead>
<tr>
  <th>No</th>
  <th>Jabatan</th>
  <th>Kuota</th>
  <th>Terisi</th>
  <th>Kosong</th>
  <th>Keterangan</th>
</tr>
</thead>
<tbody>';

$no = 1;
while ($row = mysqli_fetch_array($result)) {
  $keterangan = ($row['kosong'] == 0) ? 'Terpenuhi' : 'Perlu Penempatan';
  $html .= '<tr>
    <td align="center">'.$no++.'</td>
    <td>'.$row['jabatan'].'</td>
    <td align="center">'.$row['kuota'].'</td>
    <td align="center">'.$row['terisi'].'</td>
    <td align="center">'.$row['kosong'].'</td>
    <td>'.$keterangan.'</td>
  </tr>';
}
$html .= '</tbody></table>';

$html .= '<pagebreak /><div style="font-size:10pt; text-align:right;">
  Dibuat di Semarang, pada tanggal '.date('d - m - Y').'<br><br>
  <b>KEPALA DIVISI SDM DAN UMUM</b><br>
  PT BPR BKK JATENG
  <br><br><br><br>
  <b><u>....................................</u></b><br>
  Kepala Divisi
  <br><b>..........................</b>
</div>';

$mpdf = new mPDF('utf-8', 'Legal-L');
$mpdf->WriteHTML($html);
$mpdf->Output('Formasi_Pegawai_'.date('dmY').'.pdf', 'I');
