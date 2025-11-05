<section class="content-header">
    <h1>Master<small>Data Jabatan</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Master Data</li>
    </ol>
</section>
<div class="register-box">
<?php	
	if ($_POST['save'] == "save") {
	$id_peg					=$_POST['id_peg'];
	$kode_jabatan		=$_POST['kode_jabatan'];
	$unit_kerja			=$_POST['unit_kerja'];
	$tmt_jabatan		=date('Y-m-d', strtotime($_POST['tmt_jabatan']));
	$sampai_tgl			=date('Y-m-d', strtotime($_POST['sampai_tgl']));
	$no_sk					=$_POST['no_sk'];
	$tgl_sk					=date('Y-m-d', strtotime($_POST['tgl_sk']));

	
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
	$id_jab	=kdauto("tb_jabatan","");
	
	$date_reg =date('Y-m-d');
	
		if (empty($_POST['id_peg']) || empty($_POST['kode_jabatan']) || empty($_POST['no_sk']) || empty(strtotime($_POST['tgl_sk'])) || empty($_POST['tmt_jabatan'])) {
		echo "<div class='register-logo'><b>Oops!</b> Data Tidak Lengkap.</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. Pastikan data yang Anda masukan lengkap dan benar</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-jabatan' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else{
		$insert = "INSERT INTO tb_jabatan (id_jab, id_peg, kode_jabatan, unit_kerja, tmt_jabatan, sampai_tgl, status_jab, no_sk, tgl_sk, date_reg) VALUES ('$id_jab', '$id_peg', '$kode_jabatan', '$unit_kerja', '$tmt_jabatan', '$sampai_tgl', '', '$no_sk', '$tgl_sk', '$date_reg')";
		$query = mysql_query ($insert);
		
		if($query){
			echo "<div class='register-logo'><b>Input Data</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Input Data Jabatan Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-master-data-jabatan' class='btn btn-danger btn-block'>Next >></button>
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