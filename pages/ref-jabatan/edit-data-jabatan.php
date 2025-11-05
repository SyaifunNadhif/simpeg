<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit<small> Data Jabatan</small></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
          <li class="breadcrumb-item active">Edit Data Jabatan</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_jab'])) {
	$id_jab = $_GET['id_jab'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilJab	= mysql_query("SELECT * FROM tb_jabatan WHERE id_jab='$id_jab'");
	$jab	= mysql_fetch_array ($tampilJab);
		$id_peg	=$jab['id_peg'];
				
	if ($_POST['edit'] == "edit") {

		$jabatan			=$_POST['jabatan'];
		$unit_kerja		=$_POST['unit_kerja'];
		$tmt_jabatan	=date('Y-m-d', strtotime($_POST['tmt_jabatan']));
		$sampai_tgl		=date('Y-m-d', strtotime($_POST['sampai_tgl']));
		$no_sk				=$_POST['no_sk'];
		$tgl_sk				=date('Y-m-d', strtotime($_POST['tgl_sk']));

		
		$update= mysql_query ("UPDATE tb_jabatan SET jabatan='$jabatan', unit_kerja='$unit_kerja', tmt_jabatan='$tmt_jabatan', sampai_tgl='$sampai_tgl', no_sk='$no_sk', tgl_sk='$tgl_sk' WHERE id_jab='$id_jab'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Jabatan ".$id_jab." Berhasil</p>
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