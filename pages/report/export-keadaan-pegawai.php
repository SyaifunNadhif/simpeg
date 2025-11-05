<?php
// File: export-keadaan-pegawai.php
// Versi: 2.0 - Export Excel Keadaan Pegawai

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Keadaan_Pegawai_".date('Ym').".xls");

include "../../dist/koneksi.php";

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

ob_start();
include "view-keadaan-pegawai.php";
$html = ob_get_clean();

echo "<h3>LAPORAN KEADAAN PEGAWAI - PT BPR BKK JATENG</h3>";
echo "<h5>BULAN ".date('F', mktime(0,0,0,$bulan,1))." $tahun</h5>";
echo $html;
