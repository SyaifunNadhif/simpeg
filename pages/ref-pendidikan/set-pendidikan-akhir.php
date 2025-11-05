<section class="content-header">
    <h1>Setup<small>Pendidikan Akhir</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Setup</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_sekolah']) AND ($_GET['id_peg'])) {
	$id_sekolah = $_GET['id_sekolah'];
	$id_peg = $_GET['id_peg'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tP=mysql_query("SElECT * FROM tb_pangkat WHERE id_peg='$id_peg' AND status_pan='Aktif'");
	$gp=mysql_fetch_array($tP);
	$gol		=$gp['gol'];
	$pangkat	=$gp['pangkat'];
	
	$tJ=mysql_query("SElECT * FROM tb_jabatan WHERE id_peg='$id_peg' AND status_jab='Aktif'");
	$esl=mysql_fetch_array($tJ);
	$eselon		=$esl['eselon'];
		
	$update1= mysql_query ("UPDATE tb_sekolah SET status='' WHERE id_peg='$id_peg'");
	$update2= mysql_query ("UPDATE tb_sekolah SET status='Akhir', gol='$gol', pangkat='$pangkat', eselon='$eselon' WHERE id_sekolah='$id_sekolah'");
		if($update2){
			echo "<div class='register-logo'><b>Setup</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Setup pendidikan akhir pegawai ".$id_peg." Berhasil</p>
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