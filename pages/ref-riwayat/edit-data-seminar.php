<section class="content-header">
    <h1>Edit<small>Data Seminar</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_seminar'])) {
	$id_seminar = $_GET['id_seminar'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilDik	= mysql_query("SELECT * FROM tb_seminar WHERE id_seminar='$id_seminar'");
	$dik	= mysql_fetch_array ($tampilDik);
		$id_peg	=$dik['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$seminar		=$_POST['seminar'];
		$tempat			=$_POST['tempat'];
		$penyelenggara	=$_POST['penyelenggara'];	
		$tgl_mulai		=$_POST['tgl_mulai'];
		$tgl_selesai	=$_POST['tgl_selesai'];
		$no_piagam		=$_POST['no_piagam'];
		$tgl_piagam		=$_POST['tgl_piagam'];
		
		$update= mysql_query ("UPDATE tb_seminar SET seminar='$seminar', tempat='$tempat', penyelenggara='$penyelenggara', tgl_mulai='$tgl_mulai', tgl_selesai='$tgl_selesai', no_piagam='$no_piagam', tgl_piagam='$tgl_piagam' WHERE id_seminar='$id_seminar'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Seminar ".$id_seminar." Berhasil</p>
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