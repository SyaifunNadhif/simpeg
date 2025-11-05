<section class="content-header">
    <h1>Edit<small>Data Diklat</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_diklat'])) {
	$id_diklat = $_GET['id_diklat'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilDik	= mysql_query("SELECT * FROM tb_diklat WHERE id_diklat='$id_diklat'");
	$dik	= mysql_fetch_array ($tampilDik);
		$id_peg	=$dik['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$diklat			=$_POST['diklat'];
		$jml_jam		=$_POST['jml_jam'];
		$penyelenggara	=$_POST['penyelenggara'];
		$tempat			=$_POST['tempat'];
		$angkatan		=$_POST['angkatan'];
		$tahun			=$_POST['tahun'];
		$no_sttpp		=$_POST['no_sttpp'];
		$tgl_sttpp		=$_POST['tgl_sttpp'];
		
		$update= mysql_query ("UPDATE tb_diklat SET diklat='$diklat', jml_jam='$jml_jam', penyelenggara='$penyelenggara', tempat='$tempat', angkatan='$angkatan', tahun='$tahun', no_sttpp='$no_sttpp', tgl_sttpp='$tgl_sttpp' WHERE id_diklat='$id_diklat'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Diklat ".$id_diklat." Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=view-detail-data-pegawai&id_peg=$id_peg' class='btn btn-danger btn-block'>Next >></button>
							</div>
						</div>
					</div>
				</div>";
		}
		else {
			echo "<div class='register-logo'><b>Oops!</b> 404 Error Server.</div>";
		}
	}
?>
</div>