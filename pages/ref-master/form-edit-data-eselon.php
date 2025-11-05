<?php
	if (isset($_GET['id_masteresl'])) {
	$id_masteresl = $_GET['id_masteresl'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$ambilData=mysql_query("SELECT * FROM tb_masteresl WHERE id_masteresl='$id_masteresl'");
	$hasil=mysql_fetch_array($ambilData);
		$id_masteresl	= $hasil['id_masteresl'];
?>
<section class="content-header">
    <h1>Edit<small>Master Eselon <b>#<?=$id_masteresl?></b></small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<form action="home-admin.php?page=edit-data-eselon&id_masteresl=<?=$id_masteresl?>" class="form-horizontal" method="POST" enctype="multipart/form-data">
					<div class="box-body">
						<div class="form-group">
							<label class="col-sm-3 control-label">Eselon</label>
							<div class="col-sm-7">
								<input type="text" name="nama_masteresl" class="form-control" value="<?=$hasil['nama_masteresl'];?>" maxlength="6">
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-3 col-sm-7">
								<button type="submit" name="edit" value="edit" class="btn btn-danger">Edit</button>
								<a href="home-admin.php?page=form-master-data-jabatan" type="button" class="btn btn-default">Cancel</a>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>