<section class="content-header">
    <h1>Edit<small>Data Orang Tua</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_ortu'])) {
	$id_ortu = $_GET['id_ortu'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilOrtu	= mysql_query("SELECT * FROM tb_ortu WHERE id_ortu='$id_ortu'");
	$hasil	= mysql_fetch_array ($tampilOrtu);
		$notnik	=$hasil['nik'];
		$id_peg	=$hasil['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$id_peg		=$_POST['id_peg'];
		$nik			=$_POST['nik'];
		$nama			=ucfirst($_POST['nama']);
		$tmp_lhr	=$_POST['tmp_lhr'];
		$tgl_lhr	=date('Y-m-d', strtotime($_POST['tgl_lhr']));
		$pendidikan	=$_POST['pendidikan'];
		$pekerjaan	=$_POST['pekerjaan'];	
		$status_hub	=$_POST['status_hub'];	
	
		$ceknik	=mysql_num_rows (mysql_query("SELECT nik FROM tb_ortu WHERE nik='$_POST[nik]' AND nik!='$notnik'"));
		if($ceknik > 0) {
		echo "<div class='register-logo'><b>Oops!</b> Duplikat Data</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. NIK yang Anda masukan telah terpakai</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=view-detail-data-pegawai&id_peg=$id_peg' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else{
		$update= mysql_query ("UPDATE tb_ortu SET id_peg='$id_peg', nik='$nik', nama='$nama', tmp_lhr='$tmp_lhr', tgl_lhr='$tgl_lhr', pendidikan='$pendidikan', pekerjaan='$pekerjaan', status_hub='$status_hub' WHERE id_ortu='$id_ortu'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Orang ".$id_ortu." Berhasil</p>
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
	}
?>
</div>