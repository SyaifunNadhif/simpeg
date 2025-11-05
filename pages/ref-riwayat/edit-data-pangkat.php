<section class="content-header">
    <h1>Edit<small>Data Pangkat</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_pangkat'])) {
	$id_pangkat = $_GET['id_pangkat'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilPan	= mysql_query("SELECT * FROM tb_pangkat WHERE id_pangkat='$id_pangkat'");
	$pan	= mysql_fetch_array ($tampilPan);
		$id_peg	=$pan['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$pangkat		=$_POST['pangkat'];
		$gol			=$_POST['gol'];
		$jns_pangkat	=$_POST['jns_pangkat'];
		$tmt_pangkat	=$_POST['tmt_pangkat'];
		$pejabat_sk		=$_POST['pejabat_sk'];
		$no_sk			=$_POST['no_sk'];
		$tgl_sk			=$_POST['tgl_sk'];
		
		$update= mysql_query ("UPDATE tb_pangkat SET pangkat='$pangkat', gol='$gol', jns_pangkat='$jns_pangkat', tmt_pangkat='$tmt_pangkat', pejabat_sk='$pejabat_sk', no_sk='$no_sk', tgl_sk='$tgl_sk' WHERE id_pangkat='$id_pangkat'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Pangkat ".$id_pangkat." Berhasil</p>
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