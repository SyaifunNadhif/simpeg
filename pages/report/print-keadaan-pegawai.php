<?php
// File: print-keadaan-pegawai.php
// Versi: 2.0 - Cetak PDF Keadaan Pegawai Bulanan

include '../../plugins/mpdf/mpdf.php';
include '../../dist/koneksi.php';

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$periode = "$tahun-$bulan";

ob_start();
include "view-keadaan-pegawai.php";
$html = ob_get_clean();

$html_header = "<h3 align='center'>LAPORAN KEADAAN PEGAWAI</h3>";
$html_header .= "<h5 align='center'>PT BPR BKK JATENG BULAN ".date('F', mktime(0,0,0,$bulan,1))." $tahun</h5>";

$html_footer = "<pagebreak /><div style='font-size:10pt; text-align:right;'>
  Dibuat di Semarang, pada tanggal ".date('d - m - Y')."<br><br>
  <b>KEPALA DIVISI SDM DAN UMUM</b><br>
  PT BPR BKK JATENG
  <br><br><br><br>
  <b><u>....................................</u></b><br>
  Kepala Divisi
  <br><b>..........................</b>
</div>";

$mpdf = new mPDF('utf-8', 'A4-P');
$mpdf->WriteHTML($html_header);
$mpdf->WriteHTML($html);
$mpdf->WriteHTML($html_footer);
$mpdf->Output('Keadaan_Pegawai_'.$bulan.$tahun.'.pdf', 'I');
