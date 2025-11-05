<section class="content-header">
    <h1>Edit<small>Data Pegawai</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_peg'])) {
	$id_peg = $_GET['id_peg'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilPeg	= mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
	$hasil	= mysql_fetch_array ($tampilPeg);
		$notnip	=$hasil['nip'];
				
	if ($_POST['edit'] == "edit") {
		$nip			=$_POST['nip'];
		$nama			=$_POST['nama'];
		$tempat_lhr		=$_POST['tempat_lhr'];
		$tgl_lhr		=$_POST['tgl_lhr'];
		$agama			=$_POST['agama'];
		$jk				=$_POST['jk'];	
		$gol_darah		=$_POST['gol_darah'];
		$status_nikah	=$_POST['status_nikah'];	
		$status_kepeg	=$_POST['status_kepeg'];	
		$tgl_naikpangkat=$_POST['tgl_naikpangkat'];	
		$tgl_naikgaji	=$_POST['tgl_naikgaji'];	
		$alamat			=$_POST['alamat'];
		$telp			=$_POST['telp'];
		$email			=$_POST['email'];
		
		$pensiun = new DateTime($tgl_lhr);
		$pensiun->modify('+58 year');
		$pensiun->format('Y-m-d');
		$tgl_pensiun=$pensiun->format('Y-m-d');
		
		$ceknip	=mysql_num_rows (mysql_query("SELECT nip FROM tb_pegawai WHERE nip='$_POST[nip]' AND nip!='$notnip'"));
		if($ceknip > 0) {
		echo "<div class='register-logo'><b>Oops!</b> NIP Not Available</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali dan pastikan NIP Pegawai yang Anda masukan benar.</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-view-data-pegawai' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else{
		$update= mysql_query ("UPDATE tb_pegawai SET nip='$nip', nama='$nama', tempat_lhr='$tempat_lhr', tgl_lhr='$tgl_lhr', agama='$agama', jk='$jk', gol_darah='$gol_darah', status_nikah='$status_nikah', status_kepeg='$status_kepeg', tgl_naikpangkat='$tgl_naikpangkat', tgl_naikgaji='$tgl_naikgaji', alamat='$alamat', telp='$telp', email='$email', tgl_pensiun='$tgl_pensiun' WHERE id_peg='$id_peg'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Pegawai ".$id_peg." Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-view-data-pegawai' class='btn btn-danger btn-block'>Next >></button>
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