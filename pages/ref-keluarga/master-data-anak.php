<section class="content-header">
    <h1>Master<small>Data Anak</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Master Data</li>
    </ol>
</section>
<div class="register-box">
<?php	
	if ($_POST['save'] == "save") {
	$id_peg					=$_POST['id_peg'];
	$nik						=$_POST['nik'];
	$nama						=strtoupper($_POST['nama']);
	$tmp_lhr				=$_POST['tmp_lhr'];
	$tgl_lhr				=date('Y-m-d', strtotime($_POST['tgl_lhr']));
	$pendidikan			=$_POST['pendidikan'];
	$id_pekerjaan		=$_POST['id_pekerjaan'];	
	$pekerjaan			=$_POST['pekerjaan'];	
	$status_hub			=$_POST['status_hub'];
	$anak_ke				=$_POST['anak_ke'];
	
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
	$id_anak	=kdauto("tb_anak","");
	$date_reg	=date("Ymd");
	
	$ceknik	=mysql_num_rows (mysql_query("SELECT nik FROM tb_anak WHERE nik='$_POST[nik]'"));
	$cekanak	=mysql_num_rows (mysql_query("SELECT anak_ke FROM tb_anak WHERE (id_peg='$_POST[id_peg]' AND anak_ke='$_POST[anak_ke]')"));
	
		if (empty($_POST['id_peg']) || empty($_POST['nik']) || empty($_POST['nama']) || empty($_POST['tmp_lhr']) || empty($_POST['tgl_lhr']) || empty($_POST['pendidikan']) || empty($_POST['pekerjaan']) || empty($_POST['status_hub'])) {
		echo "<div class='register-logo'><b>Oops!</b> Data Tidak Lengkap.</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. Pastikan data yang Anda masukan lengkap dan benar</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-anak' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else if($ceknik > 0) {
		echo "<div class='register-logo'><b>Oops!</b> Duplikat Data</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. NIK yang Anda masukan telah terpakai</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-anak' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else if($cekanak > 0) {
		echo "<div class='register-logo'><b>Oops!</b> Duplikat Data</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. Data Anak yang Anda masukan telah terisi sebelumnya</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-anak' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else{
		$insert = "INSERT INTO tb_anak (id_anak, id_peg, nik, nama, tmp_lhr, tgl_lhr, pendidikan, id_pekerjaan, pekerjaan, status_hub, anak_ke, date_reg) VALUES ('$id_anak', '$id_peg', '$nik', '$nama', '$tmp_lhr', '$tgl_lhr', '$pendidikan', '$id_pekerjaan', '$pekerjaan', '$status_hub', '$anak_ke', '$date_reg')";
		$query = mysql_query ($insert);
		
		if($query){
			echo "<div class='register-logo'><b>Input Data</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Input Data Anak Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-view-data-anak' class='btn btn-danger btn-block'>Next >></button>
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