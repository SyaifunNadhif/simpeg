<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit<small> Data Anak </small></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item">Home</li>
          <li class="breadcrumb-item active">Edit Data Anak</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_anak'])) {
	$id_anak = $_GET['id_anak'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilAnak	= mysql_query("SELECT * FROM tb_anak WHERE id_anak='$id_anak'");
	$hasil	= mysql_fetch_array ($tampilAnak);
		$notnik	=$hasil['nik'];
		$id_peg	=$hasil['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$nik					=$_POST['nik'];
		$nama					=$_POST['nama'];
		$tmp_lhr			=$_POST['tmp_lhr'];
		$tgl_lhr			=date('Y-m-d', strtotime($_POST['tgl_lhr']));
		$pendidikan		=$_POST['pendidikan'];
		$id_pekerjaan	=$_POST['id_pekerjaan'];
		$pekerjaan		=$_POST['pekerjaan'];	
		$status_hub		=$_POST['status_hub'];
		$anak_ke			=$_POST['anak_ke'];	
		
	
		$ceknik	=mysql_num_rows (mysql_query("SELECT nik FROM tb_anak WHERE nik='$_POST[nik]' AND nik!='$notnik'"));
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
		$update= mysql_query ("UPDATE tb_anak SET nik='$nik', nama='$nama', tmp_lhr='$tmp_lhr', tgl_lhr='$tgl_lhr', pendidikan='$pendidikan', id_pekerjaan=$id_pekerjaan, pekerjaan='$pekerjaan', status_hub='$status_hub', anak_ke='$anak_ke' WHERE id_anak='$id_anak'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data Anak ".$id_anak." Berhasil</p>
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