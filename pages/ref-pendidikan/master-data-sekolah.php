<section class="content-header">
    <h1>Master<small>Data Sekolah</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Master Data</li>
    </ol>
</section>
<div class="register-box">
<?php	
	if ($_POST['save'] == "save") {
	$id_peg				=$_POST['id_peg'];
	$jenjang			=$_POST['jenjang'];
	$nama_sekolah	=$_POST['nama_sekolah'];
	$lokasi				=$_POST['lokasi'];
	$jurusan			=$_POST['jurusan'];
	$no_ijazah		=$_POST['no_ijazah'];
	$tgl_ijazah		=date('Y-m-d', strtotime($_POST['tgl_ijazah']));
	$kepala				=$_POST['kepala'];
	
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
			$angka = substr($row[0], strlen($inisial));
		}
		$angka++;
		$angka  	= strval($angka);
		$tmp  		= "";
		for($i=1; $i<=($panjang-strlen($inisial)-strlen($angka)); $i++) {
			$tmp=$tmp."0";
		}
		return $inisial.$tmp.$angka;
	}
	$id_sekolah	=kdauto("tb_sekolah","");
	
		if (empty($_POST['id_peg']) || empty($_POST['jenjang']) || empty($_POST['nama_sekolah']) || empty($_POST['lokasi']) || empty($_POST['no_ijazah']) || empty($_POST['tgl_ijazah']) ) {
		echo "<div class='register-logo'><b>Oops!</b> Data Tidak Lengkap.</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. Pastikan data yang Anda masukan lengkap dan benar</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-sekolah' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else{
		$insert = "INSERT INTO tb_sekolah (id_sekolah, id_peg, jenjang, nama_sekolah, lokasi, jurusan, no_ijazah, tgl_ijazah, kepala) VALUES ('$id_sekolah', '$id_peg', '$jenjang', '$nama_sekolah', '$lokasi', '$jurusan', '$no_ijazah', '$tgl_ijazah', '$kepala')";
		$query = mysql_query ($insert);
		
		if($query){
			echo "<div class='register-logo'><b>Input Data</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Input Data Sekolah Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-master-data-sekolah' class='btn btn-danger btn-block'>Next >></button>
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