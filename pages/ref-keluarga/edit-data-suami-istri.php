<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit<small> Data Pasangan </small></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item">Home</li>
          <li class="breadcrumb-item active">Edit Data Pasangan</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_si'])) {
	$id_si = $_GET['id_si'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilSi	= mysql_query("SELECT * FROM tb_suamiistri WHERE id_si='$id_si'");
	$hasil	= mysql_fetch_array ($tampilSi);
		$notnik	=$hasil['nik'];
		$id_peg	=$hasil['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$id_peg				=$_POST['id_peg'];
		$nik					=$_POST['nik'];
		$nama					=$_POST['nama'];
		$tmp_lhr			=$_POST['tmp_lhr'];
		$tgl_lhr			=date('Y-m-d', strtotime($_POST['tgl_lhr']));
		$pendidikan		=$_POST['pendidikan'];
		$id_pekerjaan	=$_POST['id_pekerjaan'];	
		$status_hub		=$_POST['status_hub'];	
	
		$ceknik	=mysql_num_rows (mysql_query("SELECT nik FROM tb_suamiistri WHERE nik='$_POST[nik]' AND nik!='$notnik'"));
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
		$update= mysql_query ("UPDATE tb_suamiistri SET id_peg='$id_peg', nik='$nik', nama='$nama', tmp_lhr='$tmp_lhr', tgl_lhr='$tgl_lhr', pendidikan='$pendidikan', id_pekerjaan=$id_pekerjaan, status_hub='$status_hub' WHERE id_si='$id_si'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Suami /Istri ".$id_si." Berhasil</p>
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