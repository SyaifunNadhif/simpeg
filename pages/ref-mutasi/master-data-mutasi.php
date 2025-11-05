<section class="content-header">
    <h1>Master<small>Data Mutasi</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Master Data</li>
    </ol>
</section>
<div class="register-box">
<?php	
	if ($_POST['save'] == "save") {
	$id_peg			=$_POST['id_peg'];
	$jns_mutasi	=$_POST['jns_mutasi'];
	$tgl_mutasi	=date('Y-m-d', strtotime($_POST['tgl_mutasi']));
	$no_mutasi	=$_POST['no_mutasi'];
	$tmt 				=date('Y-m-d', strtotime($_POST['tmt']));
	$sk_mutasi	=$_FILES['sk_mutasi']['name'];
	
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
	$id_mutasi	=kdauto("tb_mutasi","");
	
	$tJ=mysql_query("SElECT * FROM tb_jabatan WHERE id_peg='$id_peg' AND status_jab='Aktif'");
	$jab=mysql_fetch_array($tJ);
	$jabatan		=$jab['jabatan'];

	if (strlen($sk_mutasi)>0) {
		if (is_uploaded_file($_FILES['sk_mutasi']['tmp_name'])) {
			move_uploaded_file ($_FILES['sk_mutasi']['tmp_name'], "pages/asset/sk_mutasi/".$sk_mutasi);
		}
	}
	
		if (empty($_POST['id_peg']) || empty($_POST['jns_mutasi']) || empty(strtotime($_POST['tgl_mutasi'])) || empty($_POST['no_mutasi'])) {
		echo "<div class='register-logo'><b>Oops!</b> Data Tidak Lengkap.</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. Pastikan data yang Anda masukan lengkap dan benar</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-mutasi' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else{
			
			$insert = "INSERT INTO tb_mutasi (id_mutasi, id_peg, jns_mutasi, tgl_mutasi, tmt, no_mutasi, sk_mutasi, jabatan) VALUES ('$id_mutasi', '$id_peg', '$jns_mutasi', '$tgl_mutasi', '$tmt', '$no_mutasi', '$sk_mutasi', '$jabatan')";
			$query_insert = mysql_query ($insert);

			$update = "UPDATE tb_pegawai SET status_aktif='3' WHERE id_peg='$id_peg'";

			if($query_insert){
				echo "<div class='register-logo'><b>Input Data</b> Successful!</div>
				<div class='box box-primary'>
				<div class='register-box-body'>
				<p>Input Data Mutasi Berhasil</p>
				<div class='row'>
				<div class='col-xs-8'></div>
				<div class='col-xs-4'>
				<button type='button' onclick=location.href='home-admin.php?page=form-master-data-mutasi' class='btn btn-danger btn-block'>Next >></button>
				</div>
				</div>
				</div>
				</div>";	

				$query_update = mysql_query ($update);	
			}
			else {
				echo "<div class='register-logo'><b>Oops!</b> 404 Error Server.</div>";
			}
		}
	}
?>
</div>