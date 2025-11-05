<section class="content-header">
    <h1>Edit<small>Data Angkat</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_angkat'])) {
		$id_angkat = $_GET['id_angkat'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilMut	= mysql_query("SELECT * FROM tb_angkat WHERE id_angkat='$id_angkat'");
	$mut	= mysql_fetch_array ($tampilMut);
		$id_angkat	=$mut['id_angkat'];
				
	if ($_POST['edit'] == "edit") {
	$jns_mutasi	=$_POST['jns_mutasi'];
	$id_peg_baru=$_POST['id_peg_baru'];	
	$tgl_mutasi	=date('Y-m-d', strtotime($_POST['tgl_mutasi']));
	$no_mutasi	=$_POST['no_mutasi'];
	$tmt 				=date('Y-m-d', strtotime($_POST['tmt']));
	$sk_mutasi	=$_FILES['sk_mutasi']['name'];
		
		$update= mysql_query ("UPDATE tb_angkat SET tmt='$tmt' WHERE id_angkat='$id_angkat'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Mutasi ".$id_angkat." Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-view-data-angkat' class='btn btn-danger btn-block'>Next >></button>
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