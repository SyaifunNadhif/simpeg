<?php
// File: view-keadaan-pegawai.php
// Versi: 1.6 - Tambah subtotal kolom usia & kategori Hukuman Disiplin

function usia_range($usia) {
  if ($usia <= 25) return '0-25';
  if ($usia <= 35) return '26-35';
  if ($usia <= 45) return '36-45';
  if ($usia <= 55) return '46-55';
  return '>55';
}

function usia_from_tgllahir($tgl_lahir, $tahun, $bulan) {
  $lahir = new DateTime($tgl_lahir);
  $per = new DateTime("$tahun-$bulan-01");
  return $lahir->diff($per)->y;
}

$rekap = [];
$kelompok = ['0-25', '26-35', '36-45', '46-55', '>55'];
foreach ($kelompok as $k) $rekap[$k] = [];
$rekap['JML'] = [];

$bulan = '01';
$tahun = date('Y');

$kode_cabang = isset($_GET['kode_cabang']) ? $_GET['kode_cabang'] : '';
$filter_kantor = $kode_cabang != '' ? "AND j.unit_kerja = '$kode_cabang'" : '';

$query = mysqli_query($conn, "
  SELECT 
    p.id_peg, p.nama, p.tgl_lhr, p.jk, p.status_kepeg,
    j.jabatan, s.jenjang
  FROM tb_pegawai p
  LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg
  LEFT JOIN tb_pendidikan s ON p.id_peg = s.id_peg AND s.status = 'Akhir'
  WHERE p.status_aktif = 1 $filter_kantor
");

if (!$query) {
  echo "Query Error: " . mysqli_error($conn);
  exit;
}

$daftar_jabatan = [];
$filter_lingkup = $kode_cabang != '' ? "WHERE lingkup = 'KC'" : "";
$qj = mysqli_query($conn, "SELECT DISTINCT jabatan FROM tb_ref_jabatan $filter_lingkup ORDER BY jabatan");
while ($j = mysqli_fetch_array($qj)) {
  $daftar_jabatan[] = $j['jabatan'];
}

while ($p = mysqli_fetch_assoc($query)) {
  $usia = usia_from_tgllahir($p['tgl_lhr'], $tahun, $bulan);
  $range = usia_range($usia);

  $jk = strtolower($p['jk']) == 'perempuan' ? 'Perempuan' : 'Laki-laki';
  @$rekap[$range]['jk'][$jk]++;
  @$rekap['JML']['jk'][$jk]++;

  $pend = strtoupper($p['jenjang']) ?: '-';
  @$rekap[$range]['pend'][$pend]++;
  @$rekap['JML']['pend'][$pend]++;

  $status = strtoupper($p['status_kepeg']) ?: '-';
  @$rekap[$range]['status'][$status]++;
  @$rekap['JML']['status'][$status]++;

  $jab = $p['jabatan'];
  if (in_array($jab, $daftar_jabatan)) {
    @$rekap[$range]['jabatan'][$jab]++;
    @$rekap['JML']['jabatan'][$jab]++;
  }
}

// HUKUMAN: berdasarkan tb_hukuman tahun berjalan
$sub_hukuman = [];
$qh = mysqli_query($conn, "SELECT h.id_peg, h.hukuman, p.tgl_lhr FROM tb_hukuman h LEFT JOIN tb_pegawai p ON h.id_peg = p.id_peg WHERE YEAR(h.tgl_sk) = '$tahun'");
while ($h = mysqli_fetch_assoc($qh)) {
  $usia = usia_from_tgllahir($h['tgl_lhr'], $tahun, $bulan);
  $range = usia_range($usia);
  $jenis = strtoupper($h['hukuman']);
  @$rekap[$range]['hukuman'][$jenis]++;
  @$rekap['JML']['hukuman'][$jenis]++;
  if (!in_array($jenis, $sub_hukuman)) $sub_hukuman[] = $jenis;
}

$kategori = [
  ['label' => 'JENIS KELAMIN', 'key' => 'jk', 'sub' => ['Laki-laki', 'Perempuan']],
  ['label' => 'JENIS PENDIDIKAN', 'key' => 'pend', 'sub' => ['SD', 'SMP', 'SMA', 'D2/D3', 'S1/D4', 'S2/S3']],
  ['label' => 'STATUS KEPEGAWAIAN', 'key' => 'status', 'sub' => ['OUTSOURCE', 'KONTRAK', 'CAPEG', 'TETAP']],
  ['label' => 'JABATAN', 'key' => 'jabatan', 'sub' => $daftar_jabatan],
  ['label' => 'HUKUMAN DISIPLIN', 'key' => 'hukuman', 'sub' => $sub_hukuman]
];


echo "<table border='1' cellpadding='4' cellspacing='0' width='100%'>";
echo "<tr align='center' bgcolor='#f2f2f2'>
<th rowspan='2'>No</th>
<th rowspan='2'>JENIS LAPORAN</th>
<th colspan='5'>USIA</th>
<th rowspan='2'>JML</th>
<th rowspan='2'>KET</th>
</tr>
<tr align='center' bgcolor='#f2f2f2'>
<th>0 s.d. 25</th><th>>25 s.d. 35</th><th>>35 s.d. 45</th><th>>45 s.d. 55</th><th>>55</th>
</tr>";

$no = 1;
foreach ($kategori as $kat) {
  echo "<tr><td colspan='9'><b>$no. {$kat['label']}</b></td></tr>";
  $col_total = array_fill_keys($kelompok, 0);
  foreach ($kat['sub'] as $sub) {
    echo "<tr><td></td><td>- $sub</td>";
    $subtotal = 0;
    foreach ($kelompok as $k) {
      $val = isset($rekap[$k][$kat['key']][$sub]) ? $rekap[$k][$kat['key']][$sub] : 0;
      echo "<td align='center'>$val</td>";
      $col_total[$k] += $val;
      $subtotal += $val;
    }
    echo "<td align='center'>$subtotal</td><td></td></tr>";
  }
  // SUBTOTAL per kolom usia
  echo "<tr><td></td><td><b>Subtotal</b></td>";
  $jml_semua = 0;
  foreach ($kelompok as $k) {
    echo "<td align='center'><b>{$col_total[$k]}</b></td>";
    $jml_semua += $col_total[$k];
  }
  echo "<td align='center'><b>$jml_semua</b></td><td></td></tr>";
  $no++;
}
echo "</table>";
