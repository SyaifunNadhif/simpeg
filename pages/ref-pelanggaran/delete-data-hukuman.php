<section class="content-header">
    <h1>Delete<small>Data Hukuman</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Delete Data</li>
    </ol>
</section>
<div class="register-box">
<?php
include "dist/koneksi.php";
if (isset($_GET['id_hukum'])) {
	$id_hukum = $_GET['id_hukum'];
	$query   = "SELECT * FROM tb_hukuman WHERE id_hukum='$id_hukum'";
	$hasil   = mysql_query($query);
	$data    = mysql_fetch_array($hasil);
		$id_peg	=$data['id_peg'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	
	if (!empty($id_hukum) && $id_hukum != "") {
		$delete = "DELETE FROM tb_hukuman WHERE id_hukum='$id_hukum'";
		$sqldel = mysql_query ($delete);
		
		if ($sqldel) {		
			echo "<div class='register-logo'><b>Delete</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Data Hukuman ".$id_hukum." Berhasil di Hapus</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=view-detail-data-pegawai&id_peg=$id_peg' class='btn btn-danger btn-block'>Next >></button>
							</div>
						</div>
					</div>
				</div>";		
		}
		else{
			echo "<div class='register-logo'><b>Oops!</b> 404 Error Server.</div>";	
		}
	}
	mysql_close($Open);
?>
</div>