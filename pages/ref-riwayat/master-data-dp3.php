<section class="content-header">
    <h1>Master<small>Data Sasaran Kerja Pegawai</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Master SKP</li>
    </ol>
</section>
<div class="register-box">
<?php	
	if ($_POST['save'] == "save") {
	$id_peg					=$_POST['id_peg'];
	$periode_awal			=$_POST['periode_awal'];
	$periode_akhir			=$_POST['periode_akhir'];
	$pejabat_penilai		=$_POST['pejabat_penilai'];
	$atasan_pejabat_penilai	=$_POST['atasan_pejabat_penilai'];
	$nilai_kesetiaan		=$_POST['nilai_kesetiaan'];
	$nilai_prestasi			=$_POST['nilai_prestasi'];
	$nilai_tgjwb			=$_POST['nilai_tgjwb'];
	$nilai_ketaatan			=$_POST['nilai_ketaatan'];
	$nilai_kejujuran		=$_POST['nilai_kejujuran'];
	$nilai_kerjasama		=$_POST['nilai_kerjasama'];
	$nilai_prakarsa			=$_POST['nilai_prakarsa'];
	$nilai_kepemimpinan		=$_POST['nilai_kepemimpinan'];
	$hasil_penilaian		=$_POST['hasil_penilaian'];
	
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
	$id_dp3	=kdauto("tb_dp3","");
	
		if (empty($_POST['id_peg']) || empty($_POST['periode_awal']) || empty($_POST['periode_akhir']) || empty($_POST['pejabat_penilai']) || empty($_POST['atasan_pejabat_penilai']) || empty($_POST['hasil_penilaian'])) {
		echo "<div class='register-logo'><b>Oops!</b> Data Tidak Lengkap.</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. Pastikan data yang Anda masukan lengkap dan benar</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-dp3' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else{
		$insert = "INSERT INTO tb_dp3 (id_dp3, id_peg, periode_awal, periode_akhir, pejabat_penilai, atasan_pejabat_penilai, nilai_kesetiaan, nilai_prestasi, nilai_tgjwb, nilai_ketaatan, nilai_kejujuran, nilai_kerjasama, nilai_prakarsa, nilai_kepemimpinan, hasil_penilaian)
					VALUES ('$id_dp3', '$id_peg', '$periode_awal', '$periode_akhir', '$pejabat_penilai', '$atasan_pejabat_penilai', '$nilai_kesetiaan', '$nilai_prestasi', '$nilai_tgjwb', '$nilai_ketaatan', '$nilai_kejujuran', '$nilai_kerjasama', '$nilai_prakarsa', '$nilai_kepemimpinan', '$hasil_penilaian')";
		$query = mysql_query ($insert);
		
		if($query){
			echo "<div class='register-logo'><b>Input Data</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Input Data SKP Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-master-data-dp3' class='btn btn-danger btn-block'>Next >></button>
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