<?php
	if (isset($_GET['id_bhs'])) {
	$id_bhs = $_GET['id_bhs'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$ambilData=mysql_query("SELECT * FROM tb_bahasa WHERE id_bhs='$id_bhs'");
	$hasil=mysql_fetch_array($ambilData);
		$id_bhs	= $hasil['id_bhs'];
		$id_peg	= $hasil['id_peg'];
	
	$ambilPeg=mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
	$peg=mysql_fetch_array($ambilPeg);
		$nip	= $peg['nip'];
?>
<section class="content-header">
    <h1>Edit<small>Data Bahasa <b>#<?=$nip?></b></small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<form action="home-admin.php?page=edit-data-bahasa&id_bhs=<?=$id_bhs?>" class="form-horizontal" method="POST" enctype="multipart/form-data">
					<div class="box-body">
						<div class="form-group">
							<label class="col-sm-3 control-label">Jenis Bahasa</label>
							<div class="col-sm-7">
								<input type="text" name="jns_bhs" class="form-control" value="<?=$hasil['jns_bhs'];?>" maxlength="32">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Bahasa</label>
							<div class="col-sm-7">
								<input type="text" name="bahasa" class="form-control" value="<?=$hasil['bahasa'];?>" maxlength="32">
							</div>
						</div>
						<div class="form-group has-feedback">
							<label class="col-sm-3 control-label">Kemampuan Bicara</label>
							<div class="col-sm-7">
								<select name="kemampuan" class="form-control">
									<option value="Aktif" <?php echo ($hasil['kemampuan']=='Aktif')?"selected":""; ?>>Aktif
									<option value="Pasif" <?php echo ($hasil['kemampuan']=='Pasif')?"selected":""; ?>>Pasif
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-3 col-sm-7">
								<button type="submit" name="edit" value="edit" class="btn btn-danger">Edit</button>
								<a href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?=$id_peg?>" type="button" class="btn btn-default">Cancel</a>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>