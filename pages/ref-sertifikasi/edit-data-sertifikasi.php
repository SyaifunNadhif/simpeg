<section class="content-header">
    <h1>Edit<small>Data Sertifikasi</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_sertif'])) {
	$id_sertif = $_GET['id_sertif'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilDik	= mysql_query("SELECT * FROM tb_sertifikasi WHERE id_sertif='$id_sertif'");
	$dik	= mysql_fetch_array ($tampilDik);
		$id_sertif	=$dik['id_sertif'];
				
	if ($_POST['edit'] == "edit") {
	$sertifikasi		=$_POST['sertifikasi'];
	$penyelenggara	=$_POST['penyelenggara'];
	$tgl_sertifikat	=date('Y-m-d', strtotime($_POST['tgl_sertifikat']));
	$tgl_expired		=date('Y-m-d', strtotime($_POST['tgl_expired']));
	$sertifikat			=$_FILES['sertifikat']['name'];	
		
		$update= mysql_query ("UPDATE tb_sertifikasi SET sertifikasi='$sertifikasi', penyelenggara='$penyelenggara', tgl_sertifikat='$tgl_sertifikat', tgl_expired='$tgl_expired', sertifikat='$sertifikat' WHERE id_sertif='$id_sertif'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data sertifikasi ".$id_sertif." Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-view-data-sertifikasi' class='btn btn-danger btn-block'>Next >></button>
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