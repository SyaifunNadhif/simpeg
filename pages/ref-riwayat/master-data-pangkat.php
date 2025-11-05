<section class="content-header">
    <h1>Master<small>Data Pangkat</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Master Data</li>
    </ol>
</section>
<div class="register-box">
<?php	
	if ($_POST['save'] == "save") {
	$id_peg			=$_POST['id_peg'];
	$pangkat		=$_POST['pangkat'];
	$gol			=$_POST['gol'];
	$jns_pangkat	=$_POST['jns_pangkat'];
	$tmt_pangkat	=$_POST['tmt_pangkat'];
	$pejabat_sk		=$_POST['pejabat_sk'];
	$no_sk			=$_POST['no_sk'];
	$tgl_sk			=$_POST['tgl_sk'];
	
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
	$id_pangkat	=kdauto("tb_pangkat","");
	
		if (empty($_POST['id_peg']) || empty($_POST['pangkat']) || empty($_POST['gol']) || empty($_POST['jns_pangkat']) || empty($_POST['pejabat_sk']) || empty($_POST['no_sk']) || empty($_POST['tgl_sk'])) {
		echo "<div class='register-logo'><b>Oops!</b> Data Tidak Lengkap.</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. Pastikan data yang Anda masukan lengkap dan benar</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-pangkat' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else{
		$insert = "INSERT INTO tb_pangkat (id_pangkat, id_peg, pangkat, gol, jns_pangkat, tmt_pangkat, pejabat_sk, no_sk, tgl_sk) VALUES ('$id_pangkat', '$id_peg', '$pangkat', '$gol', '$jns_pangkat', '$tmt_pangkat', '$pejabat_sk', '$no_sk', '$tgl_sk')";
		$query = mysql_query ($insert);		
		
		if($query){
			echo "<div class='register-logo'><b>Input Data</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Input Data Pangkat Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-master-data-pangkat' class='btn btn-danger btn-block'>Next >></button>
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