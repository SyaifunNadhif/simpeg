<section class="content-header">
    <h1>Master<small>Data Hukuman</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Master Data</li>
    </ol>
</section>
<div class="register-box">
<?php	
	if ($_POST['save'] == "save") {
	$id_peg			=$_POST['id_peg'];
	$hukuman		=$_POST['hukuman'];
	$keterangan	=$_POST['keterangan'];
	$pejabat_sk	=$_POST['pejabat_sk'];
	$jabatan_sk	=$_POST['jabatan_sk'];
	$no_sk			=$_POST['no_sk'];
	$tgl_sk			=date('Y-m-d', strtotime($_POST['tgl_sk']));
	$pejabat_pulih	=$_POST['pejabat_pulih'];
	$jabatan_pulih	=$_POST['jabatan_pulih'];	
	$no_pulih		=$_POST['no_pulih'];
	$tgl_pulih		=date('Y-m-d', strtotime($_POST['tgl_pulih']));
	
	include "dist/koneksi.php";
	function kdauto($tabel, $inisial){
		$struktur   = mysql_query("SELECT * FROM $tabel");
		$field      = mysql_field_name($struktur,0);
		$panjang    = mysql_field_len($struktur,0);
		$qry  = mysql_query("SELECT max(".$field.") FROM ".$tabel);
		$row  = mysql_fetch_array($qry);
		if ($row[0]=="") {
		$angka=0;
		}
		else {
		$angka= substr($row[0], strlen($inisial));
		}
		$angka++;
		$angka      =strval($angka);
		$tmp  ="";
		for($i=1; $i<=($panjang-strlen($inisial)-strlen($angka)); $i++) {
		$tmp=$tmp."0";
		}
		return $inisial.$tmp.$angka;
		}
	$id_hukum	=kdauto("tb_hukuman","");
	
	$tP=mysql_query("SElECT * FROM tb_pangkat WHERE id_peg='$id_peg' AND status_pan='Aktif'");
	$gp=mysql_fetch_array($tP);
	$gol		=$gp['gol'];
	$pangkat	=$gp['pangkat'];
	
	$tJ=mysql_query("SElECT * FROM tb_jabatan WHERE id_peg='$id_peg' AND status_jab='Aktif'");
	$esl=mysql_fetch_array($tJ);
	$eselon		=$esl['eselon'];
	
		if (empty($_POST['id_peg']) || empty($_POST['hukuman']) || empty($_POST['pejabat_sk']) || empty($_POST['no_sk']) || empty($_POST['tgl_sk'])) {
		echo "<div class='register-logo'><b>Oops!</b> Data Tidak Lengkap.</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. Pastikan data yang Anda masukan lengkap dan benar</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-hukuman' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else{
		$insert = "INSERT INTO tb_hukuman (id_hukum, id_peg, hukuman, pejabat_sk, jabatan_sk, no_sk, tgl_sk, pejabat_pulih, jabatan_pulih, no_pulih, tgl_pulih, gol, pangkat, eselon) VALUES ('$id_hukum', '$id_peg', '$hukuman', '$pejabat_sk', '$jabatan_sk', '$no_sk', '$tgl_sk', '$pejabat_pulih', '$jabatan_pulih', '$no_pulih', '$tgl_pulih', '$gol', '$pangkat', '$eselon')";
		$query = mysql_query ($insert);
		
		if($query){
			echo "<div class='register-logo'><b>Input Data</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Input Data Hukuman Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-master-data-hukuman' class='btn btn-danger btn-block'>Next >></button>
							</div>
						</div>
					</div>
				</div>";
		}
			else {
				echo "<div class='register-logo'><b>Oops!</b> 404 Error Server.</div>";
			}
		}
	}
?>
</div>