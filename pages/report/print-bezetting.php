<?php
ob_start();
include'../../plugins/tcpdf/tcpdf.php';
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
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'    '.'*** '.date ("d-m-Y").' ***', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF('P', 'mm', 'Legal', true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Andi Hatmoko');
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
include "../../dist/koneksi.php";
$header = '<p align="center"><font size="14"><b>DAFTAR BEZETTING PNS</b></font><br /><font size="10">PADA DINAS KEPENDUDUKAN KOTA CILACAP PERIODE BULAN '.date ('m').' TAHUN '.date ('Y').'<font></p>';
$pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = 10, $header, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);

$html ='<table border="1" cellspacing="0" cellpadding="3">
			<tr align="center">
				<th width="30">No</th>
				<th width="150">NAMA<br />TEMPAT TANGGAL LAHIR</th>
				<th width="110">NIP</th>
				<th width="120">PANGKAT<br />GOL/RUANG</th>
				<th width="100">JABATAN</th>
				<th width="70">PEND<br />TERAKHIR</th>
				<th width="40">UMUR (THN)</th>
				<th width="40">KET</th>
			</tr>
			<tr align="center">
				<th width="30">1</th>
				<th width="150">2</th>
				<th width="110">3</th>
				<th width="120">4</th>
				<th width="100">5</th>
				<th width="70">6</th>
				<th width="40">7</th>
				<th width="40">8</th>
			</tr>';
			$no=1;
			$idPeg=mysql_query("SELECT * FROM tb_pegawai WHERE status_kepeg='PNS' ORDER BY urut_pangkat DESC");
			while($peg=mysql_fetch_array($idPeg)) { 
				$html .='<tr>
					<td align="center">'.$no++.'</td>
					<td>'.$peg['nama'].'<br />'.$peg['tempat_lhr'].','.$peg['tgl_lhr'].'</td>
					<td>'.$peg['nip'].'</td>';
					$idPan=mysql_query("SELECT * FROM tb_pangkat WHERE (id_peg='$peg[id_peg]' AND status_pan='Aktif')");
					$hpan=mysql_fetch_array($idPan);
					$html .='<td align="center">'.$hpan['pangkat'].'<br />'.$hpan['gol'].'</td>';
					$idJab=mysql_query("SELECT * FROM tb_jabatan WHERE (id_peg='$peg[id_peg]' AND status_jab='Aktif')");
					$hjab=mysql_fetch_array($idJab);
					$html .='<td>'.$hjab['jabatan'].'</td>';
					$idSek=mysql_query("SELECT * FROM tb_sekolah WHERE (id_peg='$peg[id_peg]' AND status='Akhir')");
					$hsek=mysql_fetch_array($idSek);								
					$html .='<td align="center">'.$hsek['tingkat'].'</td>';
					$lhr	= new DateTime($peg['tgl_lhr']);
						$today	= new DateTime();										
						$selisih	= $today->diff($lhr);
					$html .='<td align="center">'.$selisih->y.'</td>
					<td align="center">'.$peg['status_kepeg'].'</td>
				</tr>';
			} 
$html .= '</table><br /><br />';
$html .= '<table cellpadding="1" border="0">
			<tr>
				<td width="200"></td>
				<td width="200"></td>
				<td width="260"></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td align="center"><font size="9">Dibuat di Cilacap. Pada Tanggal, '.date ("d - m - Y").'</font></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td align="center"><font size="10"><b>KEPALA DINAS KEPENDUDUKAN</b></font></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td align="center"><font size="9">KOTA CILACAP</font></td>
			</tr>
			<tr>
				<td height="60"></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td align="center"><font size="10"><b><u>MUHAMMAD RAJASA H, SH.MM</u></b></font></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td align="center"><font size="9">Pembina</font></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td align="center"><font size="10"><b>NIP. 3301023006860001 1 002</b></font></td>
			</tr>
		</table>';
$pdf->writeHTML($html, true, false, false, false, '');
//Close and output PDF document
$pdf->Output('Daftar_Bezetting_'.date("Ymd").'.pdf', 'I');
?>