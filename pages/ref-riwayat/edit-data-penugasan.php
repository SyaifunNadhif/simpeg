<section class="content-header">
    <h1>Edit<small>Data Penugasan</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_penugasan'])) {
	$id_penugasan = $_GET['id_penugasan'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilTug	= mysql_query("SELECT * FROM tb_penugasan WHERE id_penugasan='$id_penugasan'");
	$tug	= mysql_fetch_array ($tampilTug);
		$id_peg	=$tug['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$tujuan	=$_POST['tujuan'];
		$tahun	=$_POST['tahun'];
		$lama	=$_POST['lama'];
		$alasan	=$_POST['alasan'];
		
		$update= mysql_query ("UPDATE tb_penugasan SET tujuan='$tujuan', tahun='$tahun', lama='$lama', alasan='$alasan' WHERE id_penugasan='$id_penugasan'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Penugasan ".$id_penugasan." Berhasil</p>
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