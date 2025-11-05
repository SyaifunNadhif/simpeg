<section class="content-header">
    <h1>Master<small>Data Suami Istri</small></h1>
    <ol class="breadcrumb">
        <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Master Data</li>
    </ol>
</section>
<div class="register-box">
<?php	
	if ($_POST['save'] == "save") {
	$id_peg				=$_POST['id_peg'];
	$nik					=$_POST['nik'];
	$nama					=$_POST['nama'];
	$tmp_lhr			=$_POST['tmp_lhr'];
	$tgl_lhr			=$_POST['tgl_lhr'];
	$pendidikan		=$_POST['pendidikan'];
	$id_pekerjaan	=$_POST['id_pekerjaan'];
	$status_hub		=$_POST['status_hub'];
	
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
	$id_si		=kdauto("tb_suamiistri","");
	$date_reg	=date("Ymd");
	
	$ceknik	=mysql_num_rows (mysql_query("SELECT nik FROM tb_suamiistri WHERE nik='$_POST[nik]'"));
	$ceksi	=mysql_num_rows (mysql_query("SELECT status_hub FROM tb_suamiistri WHERE id_peg='$_POST[id_peg]' AND (status_hub='Suami' OR status_hub='Istri')"));
	
		if (empty($_POST['id_peg']) || empty($_POST['nik']) || empty($_POST['nama']) || empty($_POST['tmp_lhr']) || empty($_POST['tgl_lhr']) || empty($_POST['status_hub'])) {
		echo "<div class='register-logo'><b>Oops!</b> Data Tidak Lengkap.</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. Pastikan data yang Anda masukan lengkap dan benar</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-suami-istri' class='btn btn-block btn-warning'>Back</button>
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
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-suami-istri' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else if($ceksi > 0) {
		echo "<div class='register-logo'><b>Oops!</b> Duplikat Data</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. Data Suami / Istri yang Anda masukan telah terisi sebelumnya</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-suami-istri' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else{
		$insert = "INSERT INTO tb_suamiistri (id_si, id_peg, nik, nama, tmp_lhr, tgl_lhr, pendidikan, id_pekerjaan, status_hub, date_reg) VALUES ('$id_si', '$id_peg', '$nik', '$nama', '$tmp_lhr', '$tgl_lhr', '$pendidikan', '$id_pekerjaan', '$status_hub', '$date_reg')";
		$query = mysql_query ($insert);
		
		if($query){
			echo "<div class='register-logo'><b>Input Data</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Input Data Suami / Istri Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-master-data-suami-istri' class='btn btn-success btn-block'>Next >></button>
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