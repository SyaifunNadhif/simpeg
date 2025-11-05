<section class="content-header">
    <h1>Edit<small>Data Cuti</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_cuti'])) {
	$id_cuti = $_GET['id_cuti'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilCut	= mysql_query("SELECT * FROM tb_cuti WHERE id_cuti='$id_cuti'");
	$cut	= mysql_fetch_array ($tampilCut);
		$id_peg	=$cut['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$jns_cuti		=$_POST['jns_cuti'];
		$no_suratcuti	=$_POST['no_suratcuti'];
		$tgl_suratcuti	=$_POST['tgl_suratcuti'];
		$tgl_mulai		=$_POST['tgl_mulai'];
		$tgl_selesai	=$_POST['tgl_selesai'];
		$ket			=$_POST['ket'];
		
		$update= mysql_query ("UPDATE tb_cuti SET jns_cuti='$jns_cuti', no_suratcuti='$no_suratcuti', tgl_suratcuti='$tgl_suratcuti', tgl_mulai='$tgl_mulai', tgl_selesai='$tgl_selesai', ket='$ket' WHERE id_cuti='$id_cuti'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Cuti ".$id_cuti." Berhasil</p>
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