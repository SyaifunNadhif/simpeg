<?php
	if (isset($_GET['id_peg'])) {
	$id_peg = $_GET['id_peg'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilPeg	=mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
	$peg		= mysql_fetch_array ($tampilPeg);
		$nama	=$peg['nama'];
?>
<section class="content-header">
    <h1>Change<small>Foto <b>#<?=$nama?></b></small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Foto</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
			<div class="box box-primary">				
				<div class="box-body">
					<div class="panel-body">
						<form action="home-admin.php?page=ganti-foto&id_peg=<?=$id_peg?>" class="form-horizontal" method="POST" enctype="multipart/form-data">
							<div class="form-group">
								<label class="col-sm-3 control-label">Foto</label>
								<div class="col-sm-7">
									<input type="file" name="foto" class="form-control" maxlength="255">
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-7">
									<button type="submit" name="edit" value="edit" class="btn btn-danger">Change</button>
									<a href="home-admin.php?page=form-view-data-pegawai" type="button" class="btn btn-default">Cancel</a>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
        </div>
	</div>
</section>