<section class="content-header">
    <h1>Edit<small>Data Penghargaan</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_penghargaan'])) {
	$id_penghargaan = $_GET['id_penghargaan'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilHar	= mysql_query("SELECT * FROM tb_penghargaan WHERE id_penghargaan='$id_penghargaan'");
	$har	= mysql_fetch_array ($tampilHar);
		$id_peg	=$har['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$penghargaan	=$_POST['penghargaan'];
		$tahun			=$_POST['tahun'];
		$pemberi		=$_POST['pemberi'];
		
		$update= mysql_query ("UPDATE tb_penghargaan SET penghargaan='$penghargaan', tahun='$tahun', pemberi='$pemberi' WHERE id_penghargaan='$id_penghargaan'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Penghargaan ".$id_penghargaan." Berhasil</p>
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