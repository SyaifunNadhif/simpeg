<?php
// =============================================================
// FILE: pages/report/print-biodata-pegawai.php
// MODULE: Cetak Biodata (TCPDF) - Fix Layout Foto
// =============================================================

ob_start(); // Buffer Output

// 1. LOAD LIBRARY TCPDF
// Pastikan path ini benar. Jika folder plugins ada di root, sesuaikan ../ nya
$path_tcpdf = '../../plugins/tcpdf/tcpdf.php';
if (!file_exists($path_tcpdf)) {
    die("Error: Library TCPDF tidak ditemukan di: " . $path_tcpdf);
}
require_once($path_tcpdf);

// 2. LOAD KONEKSI
include "../../dist/koneksi.php";
include "../../dist/library.php"; // Opsional

// 3. CEK ID
if (isset($_GET['id_peg'])) {
    $id_peg = mysqli_real_escape_string($conn, $_GET['id_peg']);
} else {
    die("Error: ID Pegawai tidak ditemukan.");
}

// 4. AMBIL DATA PEGAWAI
$qPeg = mysqli_query($conn, "SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
$peg  = mysqli_fetch_array($qPeg);

if (!$peg) die("Error: Data pegawai tidak ditemukan.");

// 5. SETUP FOTO
$fotoDb   = $peg['foto'];
$jk       = $peg['jk'];
$pathFoto = '../../pages/assets/foto/'; 

// Cek Ketersediaan File
if (!empty($fotoDb) && file_exists($pathFoto . $fotoDb)) {
    $fileFoto = $pathFoto . $fotoDb;
} else {
    // Default Avatar
    if ($jk == 'Laki-laki' || $jk == 'L') {
        $fileFoto = '../../pages/assets/foto/no-foto-male.png'; // Pastikan file default ini ada
    } else {
        $fileFoto = '../../pages/assets/foto/no-foto-female.png';
    }
    
    // Fallback jika file default pun tidak ada (biar pdf ga error)
    if(!file_exists($fileFoto)) $fileFoto = ''; 
}


// --- KONFIGURASI PDF ---
class MYPDF extends TCPDF {
    public function Header() {
        // Kosongkan header default
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Halaman '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().' | Dicetak: '.date("d-m-Y H:i"), 0, false, 'R');
    }
}

$pdf = new MYPDF('P', 'mm', 'Legal', true, 'UTF-8', false); // Ukuran Legal/F4 sesuai request awal
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('SIMPEG BKK');
$pdf->SetTitle('Biodata - ' . $peg['nama']);

// Margin (Kiri, Atas, Kanan)
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('helvetica', '', 10);

$pdf->AddPage();

// ==========================================================================
// ISI KONTEN PDF
// ==========================================================================

// 1. JUDUL HEADER
$htmlHeader = '
<div style="text-align:center;">
    <span style="font-size:14pt; font-weight:bold;">PT BPR BKK JATENG (PERSERODA)</span><br><br>
    <span style="font-size:11pt; text-decoration:underline; font-weight:bold;">BIODATA PEGAWAI</span>
</div><br>';
$pdf->writeHTML($htmlHeader, true, false, false, false, '');

// 2. DATA PRIBADI (LAYOUT FOTO DIPERBAIKI)
// Kita gunakan tabel HTML dengan lebar kolom terkunci agar foto punya ruang pas
// Kolom Kiri: Label & Data (80%)
// Kolom Kanan: Foto (20%)

$tgl_lahir = date('d-m-Y', strtotime($peg['tgl_lhr']));

$tblPribadi = '
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td width="75%">
            <table border="0" cellpadding="3">
                <tr><td colspan="3" style="font-weight:bold; font-size:10pt;">I. DATA PRIBADI</td></tr>
                <tr>
                    <td width="30">1.</td>
                    <td width="130">NIP</td>
                    <td width="300">: ' . $peg['nip'] . '</td>
                </tr>
                <tr>
                    <td>2.</td>
                    <td>Nama Lengkap</td>
                    <td>: <b>' . strtoupper($peg['nama']) . '</b></td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td>Tempat, Tgl Lahir</td>
                    <td>: ' . $peg['tempat_lhr'] . ', ' . $tgl_lahir . '</td>
                </tr>
                <tr>
                    <td>4.</td>
                    <td>Jenis Kelamin</td>
                    <td>: ' . $peg['jk'] . '</td>
                </tr>
                <tr>
                    <td>5.</td>
                    <td>Agama</td>
                    <td>: ' . $peg['agama'] . '</td>
                </tr>
                <tr>
                    <td>6.</td>
                    <td>Status Nikah</td>
                    <td>: ' . $peg['status_nikah'] . '</td>
                </tr>
                <tr>
                    <td>7.</td>
                    <td>Status Kepegawaian</td>
                    <td>: ' . $peg['status_kepeg'] . '</td>
                </tr>
                <tr>
                    <td>8.</td>
                    <td>Alamat</td>
                    <td>: ' . $peg['alamat'] . '</td>
                </tr>
                <tr>
                    <td>9.</td>
                    <td>No. Telepon / HP</td>
                    <td>: ' . $peg['telp'] . '</td>
                </tr>
                <tr>
                    <td>10.</td>
                    <td>Email</td>
                    <td>: ' . $peg['email'] . '</td>
                </tr>
            </table>
        </td>
        
        <td width="25%" align="center" valign="top" style="padding-top:10px;">
            <br>
            '. ($fileFoto ? '<img src="'.$fileFoto.'" width="110" height="140" border="0.5" style="object-fit:cover;">' : '<div style="border:1px solid #000; width:110px; height:140px; line-height:140px;">No Photo</div>') .'
            <br><span style="font-size:8pt;">' . $peg['id_peg'] . '</span>
        </td>
    </tr>
</table><br>';

$pdf->writeHTML($tblPribadi, true, false, false, false, '');


// 3. DATA KELUARGA
$tblKeluarga = '
<span style="font-weight:bold;">II. DATA KELUARGA</span><br>
<table border="1" cellspacing="0" cellpadding="4">
    <tr style="background-color:#EEE; font-weight:bold; text-align:center;">
        <th width="30">No</th>
        <th width="200">Nama</th>
        <th width="200">Tempat, Tgl Lahir</th>
        <th width="100">Status</th>
    </tr>';

$no = 1;
// Pasangan
$qPas = mysqli_query($conn, "SELECT * FROM tb_suamiistri WHERE id_peg='$id_peg'");
while ($r = mysqli_fetch_array($qPas)) {
    $tgl = date('d-m-Y', strtotime($r['tgl_lhr']));
    $tblKeluarga .= '<tr>
        <td align="center">' . $no++ . '</td>
        <td>' . $r['nama'] . '</td>
        <td>' . $r['tmp_lhr'] . ', ' . $tgl . '</td>
        <td align="center">' . $r['status_hub'] . '</td>
    </tr>';
}
// Anak
$qAnak = mysqli_query($conn, "SELECT * FROM tb_anak WHERE id_peg='$id_peg' ORDER BY tgl_lhr ASC");
while ($r = mysqli_fetch_array($qAnak)) {
    $tgl = date('d-m-Y', strtotime($r['tgl_lhr']));
    $tblKeluarga .= '<tr>
        <td align="center">' . $no++ . '</td>
        <td>' . $r['nama'] . '</td>
        <td>' . $r['tmp_lhr'] . ', ' . $tgl . '</td>
        <td align="center">Anak ke-' . $r['anak_ke'] . '</td>
    </tr>';
}
// Orang Tua
$qOrtu = mysqli_query($conn, "SELECT * FROM tb_ortu WHERE id_peg='$id_peg'");
while ($r = mysqli_fetch_array($qOrtu)) {
    $tgl = date('d-m-Y', strtotime($r['tgl_lhr']));
    $tblKeluarga .= '<tr>
        <td align="center">' . $no++ . '</td>
        <td>' . $r['nama'] . '</td>
        <td>' . $r['tmp_lhr'] . ', ' . $tgl . '</td>
        <td align="center">' . $r['status_hub'] . '</td>
    </tr>';
}
if ($no == 1) $tblKeluarga .= '<tr><td colspan="4" align="center">- Tidak ada data keluarga -</td></tr>';

$tblKeluarga .= '</table><br>';
$pdf->writeHTML($tblKeluarga, true, false, false, false, '');


// 4. RIWAYAT PENDIDIKAN
$tblPendidikan = '
<span style="font-weight:bold;">III. RIWAYAT PENDIDIKAN</span><br>
<table border="1" cellspacing="0" cellpadding="4">
    <tr style="background-color:#EEE; font-weight:bold; text-align:center;">
        <th width="30">No</th>
        <th width="50">Jenjang</th>
        <th width="180">Nama Sekolah / Universitas</th>
        <th width="100">Jurusan</th>
        <th width="60">Lulus</th>
        <th width="110">Kepala / Rektor</th>
    </tr>';

$no = 1;
$qSek = mysqli_query($conn, "SELECT * FROM tb_pendidikan WHERE id_peg='$id_peg' ORDER BY tgl_ijazah DESC");
while ($r = mysqli_fetch_array($qSek)) {
    $thn = date('Y', strtotime($r['tgl_ijazah']));
    $tblPendidikan .= '<tr>
        <td align="center">' . $no++ . '</td>
        <td align="center">' . $r['jenjang'] . '</td>
        <td>' . $r['nama_sekolah'] . '</td>
        <td>' . $r['jurusan'] . '</td>
        <td align="center">' . $thn . '</td>
        <td>' . $r['kepala'] . '</td>
    </tr>';
}
if ($no == 1) $tblPendidikan .= '<tr><td colspan="6" align="center">- Tidak ada data pendidikan -</td></tr>';
$tblPendidikan .= '</table><br>';
$pdf->writeHTML($tblPendidikan, true, false, false, false, '');


// 5. RIWAYAT JABATAN
$tblJabatan = '
<span style="font-weight:bold;">IV. RIWAYAT JABATAN</span><br>
<table border="1" cellspacing="0" cellpadding="4">
    <tr style="background-color:#EEE; font-weight:bold; text-align:center;">
        <th width="30">No</th>
        <th width="200">Nama Jabatan</th>
        <th width="160">Unit Kerja</th>
        <th width="70">TMT</th>
        <th width="70">Status</th>
    </tr>';

$no = 1;
$qJab = mysqli_query($conn, "SELECT j.*, m.nama_jabatan, k.nama_kantor 
                             FROM tb_jabatan j
                             LEFT JOIN tb_master_jabatan m ON j.kode_jabatan = m.kode_jabatan
                             LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
                             WHERE j.id_peg='$id_peg' 
                             ORDER BY j.tmt_jabatan DESC");

while ($r = mysqli_fetch_array($qJab)) {
    $namaJab = !empty($r['nama_jabatan']) ? $r['nama_jabatan'] : $r['jabatan'];
    $tmt = date('d-m-Y', strtotime($r['tmt_jabatan']));
    
    $tblJabatan .= '<tr>
        <td align="center">' . $no++ . '</td>
        <td>' . $namaJab . '</td>
        <td>' . $r['nama_kantor'] . ' (' . $r['unit_kerja'] . ')</td>
        <td align="center">' . $tmt . '</td>
        <td align="center">' . $r['status_jab'] . '</td>
    </tr>';
}
if ($no == 1) $tblJabatan .= '<tr><td colspan="5" align="center">- Tidak ada riwayat jabatan -</td></tr>';
$tblJabatan .= '</table><br><br>';
$pdf->writeHTML($tblJabatan, true, false, false, false, '');


// 6. TANDA TANGAN
$tglCetak = date("d F Y");
$tblTtd = '
<table border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td width="350"></td>
        <td width="200" align="center">
            Semarang, ' . $tglCetak . '<br>
            Pegawai Yang Bersangkutan,
            <br><br><br><br><br>
            <b><u>' . strtoupper($peg['nama']) . '</u></b><br>
            NIP. ' . $peg['id_peg'] . '
        </td>
    </tr>
</table>';
$pdf->writeHTML($tblTtd, true, false, false, false, '');


// OUTPUT
ob_end_clean(); 
$pdf->Output('Biodata_' . $peg['nama'] . '.pdf', 'I');
?>