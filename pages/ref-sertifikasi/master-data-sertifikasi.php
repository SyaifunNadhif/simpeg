<section class="content-header">
    <h1>Master<small>Data Sertifikasi Pegawai</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Master Data</li>
    </ol>
</section>
<div class="register-box">
<?php	
	if ($_POST['save'] == "save") {
	$id_peg				=$_POST['id_peg'];
	$sertifikasi		=$_POST['sertifikasi'];
	$penyelenggara		=$_POST['penyelenggara'];
	$tgl_sertifikat		=date('Y-m-d', strtotime($_POST['tgl_sertifikat']));
	$tgl_expired		=date('Y-m-d', strtotime($_POST['tgl_expired']));
	$sertifikat		=$_FILES['sertifikat']['name'];	

	
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
	$id_sertif	=kdauto("tb_sertifikasi","");
	$date_reg		=date("Y-m-d H:i:s");
	$id_user		=$_SESSION['id_user'];
	
	if (strlen($sertifikat)>0) {
		if (is_uploaded_file($_FILES['sertifikat']['tmp_name'])) {
			move_uploaded_file ($_FILES['sertifikat']['tmp_name'], "pages/asset/sertifikat/".$sertifikat);
		}
	}

		if (empty($_POST['id_peg']) || empty($_POST['sertifikasi'])) {
			
		echo "<div class='register-logo'><b>Oops!</b> Data Tidak Lengkap.</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. Pastikan data yang Anda masukan lengkap dan benar</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-sertifikasi' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else{
			
			$insert = "INSERT INTO tb_sertifikasi (id_sertif, id_peg, sertifikasi, penyelenggara, tgl_sertifikat, tgl_expired, sertifikat, date_reg) VALUES ('', '$id_peg', '$sertifikasi', '$penyelenggara', '$tgl_sertifikat', '$tgl_expired','$sertifikat', '$date_reg')";
			$query_insert = mysql_query ($insert);				

			if($query_insert){
				echo "<div class='register-logo'><b>Input Data</b> Successful!</div>
				<div class='box box-primary'>
				<div class='register-box-body'>
				<p>Input Data Sertifikasi Berhasil</p>
				<div class='row'>
				<div class='col-xs-8'></div>
				<div class='col-xs-4'>
				<button type='button' onclick=location.href='home-admin.php?page=form-master-data-sertifikasi' class='btn btn-danger btn-block'>Next >></button>
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