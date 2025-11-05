<section class="content-header">
    <h1>Edit<small>Data Latihan Jabatan</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_lat_jabatan'])) {
	$id_lat_jabatan = $_GET['id_lat_jabatan'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilJab	= mysql_query("SELECT * FROM tb_lat_jabatan WHERE id_lat_jabatan='$id_lat_jabatan'");
	$jab	= mysql_fetch_array ($tampilJab);
		$id_peg	=$jab['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$nama_pelatih	=$_POST['nama_pelatih'];
		$tahun_lat		=$_POST['tahun_lat'];
		$jml_jam		=$_POST['jml_jam'];
		
		$update= mysql_query ("UPDATE tb_lat_jabatan SET nama_pelatih='$nama_pelatih', tahun_lat='$tahun_lat', jml_jam='$jml_jam' WHERE id_lat_jabatan='$id_lat_jabatan'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Pelatihan Jabatan ".$id_lat_jabatan." Berhasil</p>
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