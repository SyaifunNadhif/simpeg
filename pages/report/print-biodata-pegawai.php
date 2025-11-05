<?php
ob_start();
include'../../plugins/tcpdf/tcpdf.php';
if (isset($_GET['id_peg'])) {
	$id_peg = $_GET['id_peg'];
}
else {
	die ("Error. No ID Selected! ");	
}

class MYPDF extends TCPDF {
	public function Header() {
        // Logo
        //$image_file = K_PATH_IMAGES.'logo_example.jpg';
        //$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Header
        //$html = '<p align="center"></p>';
		//$this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = 10, $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
	}
	public function Footer() {
        // Position at 15 mm from bottom
		$this->SetY(-15);
        // Set font
		$this->SetFont('helvetica', 'I', 6);
        // Page number
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'    '.'Dicetak Tanggal: '.date ("d-m-Y").' ***', 0, false, 'R', 0, '', 0, false, 'T', 'M');
	}
}
$pdf = new MYPDF('P', 'mm', 'Legal', true, 'UTF-8', false);
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Riza Pahlevi');
$pdf->SetTitle('Report');
$pdf->SetSubject('TCPDF');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// set margins
$pdf->SetMargins(12, 20, 12);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 20);
// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}
// add a page
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 8);
$pdf->SetFillColor(100,100,100);

include "../../dist/koneksi.php";
include "../../dist/library.php";
$tampilPeg=mysqli_query($conn, "SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
$peg=mysqli_fetch_array($tampilPeg);
$header = '<p><font size="12"><b>PT BPR BKK JATENG (PERSERODA)</b></font>
<br /><br />
<font size="9" align="center"><u><b>BIODATA PEGAWAI</b></u><font></p>';
$pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = 10, $header, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
$dpri ='<p><font size="9"><b>I. DATA PRIBADI</b><font></p>';
$pdf->writeHTML($dpri, true, false, false, false, '');
$tblpri = '<table cellspacing="0" cellpadding="2" border="0">
<tr>
<td width="30">1.</td>
<td width="150">NIP</td>
<td width="360">: '.$peg['nip'].'</td>';
if (empty($peg['foto']))
	if ($peg['jk'] == "Laki-laki"){
		$tblpri .='<td rowspan="11" align="center" width="120"><img src="../../pages/asset/foto/no-foto-male.png" width="100" height="130"><br />'.$peg['id_peg'].'</td>';
	}
	else{
		$tblpri .='<td rowspan="11" align="center" width="120"><img src="../../pages/asset/foto/no-foto-female.png" width="100" height="130"><br />'.$peg['id_peg'].'</td>';
	}
	else
		$tblpri .='<td rowspan="11" align="center" width="120"><img src="../../pages/asset/foto/'.$peg['foto'].'" width="100" height="130"><br />'.$peg['id_peg'].'</td>';
	$tblpri .='</tr>
	<tr>
	<td width="30">2.</td>
	<td width="150">Nama</td>
	<td width="360">: '.$peg['nama'].'</td>
	</tr>
	<tr>
	<td width="30">3.</td>
	<td width="150">Tempat, Tanggal Lahir</td>
	<td width="360">: '.$peg['tempat_lhr'].', '.$peg['tgl_lhr'].'</td>
	</tr>
	<tr>
	<td width="30">4.</td>
	<td width="150">Agama</td>
	<td width="360">: '.$peg['agama'].'</td>
	</tr>
	<tr>
	<td width="30">5.</td>
	<td width="150">Jenis Kelamin</td>
	<td width="360">: '.$peg['jk'].'</td>
	</tr>
	<tr>
	<td width="30">6.</td>
	<td width="150">Golongan Darah</td>
	<td width="360">: '.$peg['gol_darah'].'</td>
	</tr>
	<tr>
	<td width="30">7.</td>
	<td width="150">Status Pernikahan</td>
	<td width="360">: '.$peg['status_nikah'].'</td>
	</tr>
	<tr>
	<td width="30">8.</td>
	<td width="150">Status Kepegawaian</td>
	<td width="360">: '.$peg['status_kepeg'].'</td>
	</tr>
	<tr>
	<td width="30">9.</td>
	<td width="150">Alamat</td>
	<td width="360">: '.$peg['alamat'].'</td>
	</tr>
	<tr>
	<td width="30">10.</td>
	<td width="150">No. Telepon</td>
	<td width="360">: '.$peg['telp'].'</td>
	</tr>
	<tr>
	<td width="30">11.</td>
	<td width="150">Email</td>
	<td width="360">: '.$peg['email'].'</td>
	</tr>
	</table>';
	$pdf->writeHTML($tblpri, true, false, false, false, '');
	$dkel ='<font size="9"><b>II. RIWAYAT KELUARGA</b><font></br>';
	$pdf->writeHTML($dkel, true, false, false, false, '');
	$tblkel ='<table cellspacing="0" cellpadding="2" border="0">
	<tr style="background-color:#7CBFC1;color:#FFFFFF;">
	<th width="30"><b>No.</b></th>
	<th width="170"><b>Nama</b></th>
	<th width="170"><b>Tempat, Tanggal Lahir</b></th>
	<th width="290"><b>Status</b></th>
	</tr>
	';
	$no=1;
	$tampilOrt	=mysqli_query($conn, "SELECT * FROM tb_ortu WHERE id_peg='$id_peg' ORDER BY status_hub DESC");
	while($ort=mysqli_fetch_array($tampilOrt)) { 
		$tblkel .='<tr>
		<td width="30">'.$no++.'.</td>
		<td width="170">'.$ort['nama'].'</td>
		<td width="170">'.$ort['tmp_lhr'].', '.$ort['tgl_lhr'].'</td>
		<td width="290">'.$ort['status_hub'].'</td>
		</tr>';
	}
	$tampilSi	=mysqli_query($conn, "SELECT * FROM tb_suamiistri WHERE id_peg='$id_peg'");
	while($si=mysqli_fetch_array($tampilSi)) { 
		$tblkel .='<tr>
		<td width="30">'.$no++.'.</td>
		<td width="170">'.$si['nama'].'</td>
		<td width="170">'.$si['tmp_lhr'].', '.$si['tgl_lhr'].'</td>
		<td width="290">'.$si['status_hub'].'</td>
		</tr>';
	} 
	$tampilAnk	=mysqli_query($conn, "SELECT * FROM tb_anak WHERE id_peg='$id_peg' ORDER BY tgl_lhr");
	while($ank=mysqli_fetch_array($tampilAnk)) { 
		$tblkel .='<tr>
		<td width="30">'.$no++.'.</td>
		<td width="170">'.$ank['nama'].'</td>
		<td width="170">'.$ank['tmp_lhr'].', '.$ank['tgl_lhr'].'</td>
		<td width="290">'.$ank['status_hub'].'</td>
		</tr>';
	}
	$tblkel .= '</table>';
	$pdf->writeHTML($tblkel, true, false, false, false, '');
	$dpen ='<font size="9"><b>III. PENDIDIKAN</b><font></br>';
	$pdf->writeHTML($dpen, true, false, false, false, '');
	$tblpen ='<table cellspacing="0" cellpadding="2" border="0">
	<tr style="background-color:#7CBFC1;color:#FFFFFF;">
	<th width="30"><b>No.</b></th>
	<th width="50"><b>Jenjang</b></th>
	<th width="120"><b>Sekolah / Universitas</b></th>
	<th width="60"><b>Lokasi</b></th>
	<th width="110"><b>Jurusan</b></th>
	<th width="100"><b>No. Ijazah</b></th>
	<th width="60"><b>Tgl. Ijazah</b></th>
	<th width="130"><b>Kepala / Rektor</b></th>
	</tr>
	';
	$no=1;
	$tampilSek	=mysqli_query($conn, "SELECT * FROM tb_sekolah WHERE id_peg='$id_peg' ORDER BY tgl_ijazah DESC");
	while($sek=mysqli_fetch_array($tampilSek)) { 
		$tblpen .='<tr>
		<td width="30">'.$no++.'.</td>
		<td width="50">'.$sek['jenjang'].'</td>
		<td width="120">'.$sek['nama_sekolah'].'</td>
		<td width="60">'.$sek['lokasi'].'</td>
		<td width="110">'.$sek['jurusan'].'</td>
		<td width="100">'.$sek['no_ijazah'].'</td>
		<td width="60">'.$sek['tgl_ijazah'].'</td>
		<td width="130">'.$sek['kepala'].'</td>
		</tr>';
	} 
	$tblpen .= '</table>';
	$pdf->writeHTML($tblpen, true, false, false, false, '');

	/*
	$dbhs ='<font size="9"><b>IV. KECAKAPAN BAHASA</b><font></br>';
	$pdf->writeHTML($dbhs, true, false, false, false, '');
	$tblbhs ='<table cellspacing="0" cellpadding="2" border="0">
	<tr style="background-color:#7CBFC1;color:#FFFFFF;">
	<th width="30"><b>No.</b></th>
	<th width="170"><b>Jenis Bahasa</b></th>
	<th width="170"><b>Bahasa</b></th>
	<th width="290"><b>Kemampuan Bicara</b></th>
	</tr>
	';
	$no=1;
	$tampilBhs	=mysqli_query($conn, "SELECT * FROM tb_bahasa WHERE id_peg='$id_peg'");
	while($bhs=mysqli_fetch_array($tampilBhs)) { 
		$tblbhs .='<tr>
		<td width="30">'.$no++.'.</td>
		<td width="170">'.$bhs['jns_bhs'].'</td>
		<td width="170">'.$bhs['bahasa'].'</td>
		<td width="290">'.$bhs['kemampuan'].'</td>
		</tr>';
	} 
	$tblbhs .= '</table>';
	$pdf->writeHTML($tblbhs, true, false, false, false, '');
	*/

	$djab ='<font size="9"><b>IV. RIWAYAT JABATAN</b><font></br>';
	$pdf->writeHTML($djab, true, false, false, false, '');
	$tbljab ='<table cellspacing="0" cellpadding="2" border="0">
	<tr style="background-color:#7CBFC1;color:#FFFFFF;">
	<th width="30"><b>No.</b></th>
	<th width="200"><b>Jabatan</b></th>
	<th width="190"><b>Unit Kerja</b></th>
	<th width="80"><b>TMT</b></th>
	<th width="80"><b>Selesai Tugas</b></th>
	<th width="80"><b>Status</b></th>
	</tr>
	';
	$no=1;
	$tampilJab	=mysqli_query($conn, "SELECT
															id_peg,
															tmt_jabatan,
															sampai_tgl,
															status_jab,
															kode_jabatan,
															(SELECT jabatan FROM tb_ref_jabatan WHERE kode_jabatan=tb_jabatan.kode_jabatan) nama_jabatan,
															(SELECT nama_kantor FROM tb_kantor WHERE kode_kantor_detail=tb_jabatan.unit_kerja) unit_kerja
														FROM
															tb_jabatan
														WHERE
															id_peg = '$id_peg'
														ORDER BY
															tmt_jabatan DESC");
	while($jab=mysqli_fetch_array($tampilJab)) { 
		$tbljab .='<tr>
		<td width="30">'.$no++.'.</td>
		<td width="200">'.$jab['nama_jabatan'].'</td>
		<td width="190">'.$jab['unit_kerja'].'</td>
		<td width="80">'.$jab['tmt_jabatan'].'</td>
		<td width="80">'.$jab['sampai_tgl'].'</td>
		<td width="80">'.$jab['status_jab'].'</td>
		</tr>';
	} 
	$tbljab .= '</table>';
	$pdf->writeHTML($tbljab, true, false, false, false, '');
	/*
	$dpan ='<font size="9"><b>VI. RIWAYAT KEPANGKATAN</b><font></br>';
	$pdf->writeHTML($dpan, true, false, false, false, '');
	$tblpan ='<table cellspacing="0" cellpadding="2" border="0">
	<tr style="background-color:#7CBFC1;color:#FFFFFF;">
	<th width="30"><b>No.</b></th>
	<th width="230"><b>Pangkat</b></th>
	<th width="110"><b>Golongan</b></th>
	<th width="110"><b>TMT</b></th>
	<th width="180"><b>Status</b></th>
	</tr>
	';
	$no=1;
	$tampilPan	=mysqli_query($conn, "SELECT * FROM tb_pangkat WHERE id_peg='$id_peg' ORDER BY tmt_pangkat DESC");
	while($pan=mysqli_fetch_array($tampilPan)) { 
		$tblpan .='<tr>
		<td width="30">'.$no++.'.</td>
		<td width="230">'.$pan['pangkat'].'</td>
		<td width="110">'.$pan['gol'].'</td>
		<td width="110">'.$pan['tmt_pangkat'].'</td>
		<td width="180">'.$pan['status_pan'].'</td>
		</tr>';
	} 
	$tblpan .= '</table>';
	$pdf->writeHTML($tblpan, true, false, false, false, '');
	$dhar ='<font size="9"><b>VII. RIWAYAT PENGHARGAAN</b><font></br>';
	$pdf->writeHTML($dhar, true, false, false, false, '');
	$tblhar ='<table cellspacing="0" cellpadding="2" border="0">
	<tr style="background-color:#7CBFC1;color:#FFFFFF;">
	<th width="30"><b>No.</b></th>
	<th width="230"><b>Nama Penghargaan</b></th>
	<th width="110"><b>Tahun</b></th>
	<th width="290"><b>Negara / Instansi Pemberi</b></th>
	</tr>
	';
	$no=1;
	$tampilHar	=mysqli_query($conn, "SELECT * FROM tb_penghargaan WHERE id_peg='$id_peg' ORDER BY tahun DESC");
	while($har=mysqli_fetch_array($tampilHar)) { 
		$tblhar .='<tr>
		<td width="30">'.$no++.'.</td>
		<td width="230">'.$har['penghargaan'].'</td>
		<td width="110">'.$har['tahun'].'</td>
		<td width="290">'.$har['pemberi'].'</td>
		</tr>';
	} 
	$tblhar .= '</table>';
	$pdf->writeHTML($tblhar, true, false, false, false, '');
	 */

	$dpln ='<font size="9"><b>V. RIWAYAT DIKLAT</b><font></br>';
	$pdf->writeHTML($dpln, true, false, false, false, '');
	$tbldkl ='<table cellspacing="0" cellpadding="2" border="0">
	<tr style="background-color:#7CBFC1;color:#FFFFFF;">
	<th width="30"><b>No.</b></th>
	<th width="290"><b>Nama Diklat</b></th>
	<th width="220"><b>Penyelenggara</b></th>
	<th width="60"><b>Tahun</b></th>
	<th width="60"><b>Angkatan</b></th>
	</tr>
	';
	$no=1;
	$tampilDkl	=mysqli_query($conn, "SELECT * FROM tb_diklat WHERE id_peg='$id_peg' ORDER BY tahun DESC");
	while($dkl=mysqli_fetch_array($tampilDkl)) { 
		$tbldkl .='<tr>
		<td width="30">'.$no++.'.</td>
		<td width="290">'.$dkl['diklat'].'</td>
		<td width="220">'.$dkl['penyelenggara'].'</td>
		<td width="60">'.$dkl['tahun'].'</td>
		<td width="60">'.$dkl['angkatan'].'</td>
		</tr>';
	} 
	$tbldkl .= '</table>';
	$pdf->writeHTML($tbldkl, true, false, false, false, '');

	$dsrt ='<font size="9"><b>VI. RIWAYAT SERTIFIKASI</b><font></br>';
	$pdf->writeHTML($dsrt, true, false, false, false, '');
	$tblsrt ='<table cellspacing="0" cellpadding="2" border="0">
	<tr style="background-color:#7CBFC1;color:#FFFFFF;">
	<th width="30"><b>No.</b></th>
	<th width="290"><b>Jenis Sertifikasi</b></th>
	<th width="180"><b>Penyelenggara</b></th>
	<th width="80"><b>Tgl Sertifikat</b></th>
	<th width="80"><b>Tgl Expired</b></th>
	</tr>
	';
	$no=1;
	$tampilSrt	=mysqli_query($conn, "SELECT * FROM tb_sertifikasi WHERE id_peg='$id_peg' ORDER BY tgl_sertifikat DESC");
	while($srt=mysqli_fetch_array($tampilSrt)) { 
		$tblsrt .='<tr>
		<td width="30">'.$no++.'.</td>
		<td width="290">'.$srt['sertifikasi'].'</td>
		<td width="180">'.$srt['penyelenggara'].'</td>
		<td width="80">'.$srt['tgl_sertifikat'].'</td>
		<td width="80">'.$srt['tgl_expired'].'</td>
		</tr>';
	} 
	$tblsrt .= '</table>';
	$pdf->writeHTML($tblsrt, true, false, false, false, '');


	$dhkm ='<font size="9"><b>VII. RIWAYAT PELANGGARAN</b><font></br>';
	$pdf->writeHTML($dhkm, true, false, false, false, '');
	$tblhkm ='<table cellspacing="0" cellpadding="2" border="0">
	<tr style="background-color:#7CBFC1;color:#FFFFFF;">
	<th width="30"><b>No.</b></th>
	<th width="170"><b>Jenis Hukuman</b></th>
	<th width="150"><b>No. SK</b></th>
	<th width="100"><b>Tanggal SK</b></th>
	<th width="110"><b>No. Pemulihan</b></th>
	<th width="100"><b>Tanggal Pemulihan</b></th>
	</tr>
	';
	$no=1;
	$tampilHkm	=mysqli_query($conn, "SELECT * FROM tb_hukuman WHERE id_peg='$id_peg' ORDER BY tgl_sk DESC");
	while($hkm=mysqli_fetch_array($tampilHkm)) { 
		$tblhkm .='<tr>
		<td width="30">'.$no++.'.</td>
		<td width="170">'.$hkm['hukuman'].'</td>
		<td width="150">'.$hkm['no_sk'].'</td>
		<td width="100">'.$hkm['tgl_sk'].'</td>
		<td width="110">'.$hkm['no_pulih'].'</td>
		<td width="100">'.$hkm['tgl_pulih'].'</td>
		</tr>';
	} 
	$tblhkm .= '</table><br /><br />';
	$pdf->writeHTML($tblhkm, true, false, false, false, '');
	$signa = '<table cellpadding="1">
	<tr>
	<td></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td></td>
	<td></td>
	<td align="center"><font size="8">Dibuat di Semarang</font></td>
	</tr>
	<tr>
	<td></td>
	<td></td>
	<td align="center"><font size="8">Tanggal, '.date ("d M Y").'</font></td>
	</tr>
	<tr>
	<td height="30"></td>
	<td></td>
	<td align="center"><font size="8"></font></td>
	</tr>
	<tr>
	<td height="40"></td>
	<td></td>
	<td align="center"></td>
	</tr>
	<tr>
	<td></td>
	<td></td>
	<td align="center"><font size="8"><b><u>'.$peg['nama'].'</u></b></font></td>
	</tr>
	<tr>
	<td></td>
	<td></td>
	<td align="center"><font size="8"><b>NIP. '.$peg['id_peg'].'</b></font></td>
	</tr>
	</table>';
	$pdf->writeHTML($signa, true, false, false, false, '');
//Close and output PDF document
	$pdf->Output('Biodata_Pegawai_'.$peg['id_peg'].'.pdf', 'I');
?>