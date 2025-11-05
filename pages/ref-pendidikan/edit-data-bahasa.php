<section class="content-header">
    <h1>Edit<small>Data Bahasa</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_bhs'])) {
	$id_bhs = $_GET['id_bhs'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilBhs	= mysql_query("SELECT * FROM tb_bahasa WHERE id_bhs='$id_bhs'");
	$bhs	= mysql_fetch_array ($tampilBhs);
		$id_peg	=$bhs['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$jns_bhs	=$_POST['jns_bhs'];
		$bahasa		=$_POST['bahasa'];
		$kemampuan	=$_POST['kemampuan'];
		
		$update= mysql_query ("UPDATE tb_bahasa SET jns_bhs='$jns_bhs', bahasa='$bahasa', kemampuan='$kemampuan' WHERE id_bhs='$id_bhs'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Bahasa ".$id_bhs." Berhasil</p>
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