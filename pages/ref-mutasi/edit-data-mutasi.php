<section class="content-header">
    <h1>Edit<small>Data Mutasi</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_mutasi'])) {
		$id_mutasi = $_GET['id_mutasi'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilMut	= mysql_query("SELECT * FROM tb_mutasi WHERE id_mutasi='$id_mutasi'");
	$mut	= mysql_fetch_array ($tampilMut);
		$id_mutasi	=$mut['id_mutasi'];
				
	if ($_POST['edit'] == "edit") {
		$jns_mutasi	=$_POST['jns_mutasi'];
		$tgl_mutasi	=$_POST['tgl_mutasi'];
		$no_mutasi	=$_POST['no_mutasi'];
		$tmt 				=$_POST['tmt'];
		
		$update= mysql_query ("UPDATE tb_mutasi SET jns_mutasi='$jns_mutasi', tgl_mutasi='$tgl_mutasi', no_mutasi='$no_mutasi', tmt='$tmt' WHERE id_mutasi='$id_mutasi'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Mutasi ".$id_mutasi." Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-view-data-mutasi class='btn btn-danger btn-block'>Next >></button>
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