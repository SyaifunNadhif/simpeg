<section class="content-header">
    <h1>Setup<small>Pangkat Sekarang</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Setup</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_pangkat']) AND ($_GET['gol']) AND ($_GET['id_peg'])) {
	$id_pangkat = $_GET['id_pangkat'];
	$gol		= $_GET['gol'];
	$id_peg = $_GET['id_peg'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tP=mysql_query("SElECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
	$jkP=mysql_fetch_array($tP);
	$jk=$jkP['jk'];
		
	$update1= mysql_query ("UPDATE tb_pangkat SET status_pan='' WHERE id_peg='$id_peg'");
	$update2= mysql_query ("UPDATE tb_pangkat SET status_pan='Aktif', jk_pan='$jk' WHERE id_pangkat='$id_pangkat'");
	$update3= mysql_query ("UPDATE tb_pegawai SET urut_pangkat='$gol' WHERE id_peg='$id_peg'");
		if($update2){
			echo "<div class='register-logo'><b>Setup</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Setup pangkat sekarang pegawai ".$id_peg." Berhasil</p>
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
?>
</div>