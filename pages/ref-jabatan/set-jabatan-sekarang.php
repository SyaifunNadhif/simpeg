<section class="content-header">
    <h1>Setup<small>Jabatan Sekarang</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Setup</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_jab']) AND ($_GET['id_peg'])) {
	$id_jab = $_GET['id_jab'];
	$id_peg = $_GET['id_peg'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tP=mysql_query("SElECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
	$jkP=mysql_fetch_array($tP);
	$jk=$jkP['jk'];
		
	$update1= mysql_query ("UPDATE tb_jabatan SET status_jab='' WHERE id_peg='$id_peg'");
	$update2= mysql_query ("UPDATE tb_jabatan SET status_jab='Aktif'WHERE id_jab='$id_jab'");
		if($update2){
			echo "<div class='register-logo'><b>Setup</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Setup jabatan sekarang pegawai ".$id_peg." Berhasil</p>
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