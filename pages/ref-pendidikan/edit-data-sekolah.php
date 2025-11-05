<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit<small> Data Pendidikan</small></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
          <li class="breadcrumb-item active">Edit Data Pendidikan</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_sekolah'])) {
	$id_sekolah = $_GET['id_sekolah'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilSek	= mysql_query("SELECT * FROM tb_sekolah WHERE id_sekolah='$id_sekolah'");
	$sek	= mysql_fetch_array ($tampilSek);
		$id_sekolah	=$sek['id_sekolah'];
		$id_peg	=$sek['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$jenjang			=$_POST['jenjang'];
		$nama_sekolah	=$_POST['nama_sekolah'];
		$lokasi				=$_POST['lokasi'];
		$jurusan			=$_POST['jurusan'];
		$no_ijazah		=$_POST['no_ijazah'];
		$tgl_ijazah		=date('Y-m-d', strtotime($_POST['tgl_ijazah']));
		$kepala				=$_POST['kepala'];
		
		$update= mysql_query ("UPDATE tb_sekolah SET jenjang='$jenjang', nama_sekolah='$nama_sekolah', lokasi='$lokasi', jurusan='$jurusan', no_ijazah='$no_ijazah', tgl_ijazah='$tgl_ijazah', kepala='$kepala' WHERE id_sekolah='$id_sekolah'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Sekolah ".$id_sekolah." Berhasil</p>
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