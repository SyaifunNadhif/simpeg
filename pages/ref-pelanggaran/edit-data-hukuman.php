<section class="content-header">
    <h1>Edit<small>Data Hukuman</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_hukum'])) {
	$id_hukum = $_GET['id_hukum'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilHk	= mysql_query("SELECT * FROM tb_hukuman WHERE id_hukum='$id_hukum'");
	$huk	= mysql_fetch_array ($tampilHk);
		$id_peg	=$huk['id_peg'];
				
	if ($_POST['edit'] == "edit") {
	$hukuman		=$_POST['hukuman'];
	$keterangan	=$_POST['keterangan'];
	$pejabat_sk	=$_POST['pejabat_sk'];
	$jabatan_sk	=$_POST['jabatan_sk'];
	$no_sk			=$_POST['no_sk'];
	$tgl_sk			=date('Y-m-d', strtotime($_POST['tgl_sk']));
	$pejabat_pulih	=$_POST['pejabat_pulih'];
	$jabatan_pulih	=$_POST['jabatan_pulih'];	
	$no_pulih		=$_POST['no_pulih'];
	$tgl_pulih		=date('Y-m-d', strtotime($_POST['tgl_pulih']));
		
		$update= mysql_query ("UPDATE tb_hukuman SET hukuman='$hukuman', keterangan='$keterangan',pejabat_sk='$pejabat_sk', jabatan_sk='$jabatan_sk', no_sk='$no_sk', tgl_sk='$tgl_sk', pejabat_pulih='$pejabat_pulih', jabatan_pulih='$jabatan_pulih', no_pulih='$no_pulih', tgl_pulih='$tgl_pulih' WHERE id_hukum='$id_hukum'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Hukuman ".$id_hukum." Berhasil</p>
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