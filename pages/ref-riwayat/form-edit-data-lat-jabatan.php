<?php
	if (isset($_GET['id_lat_jabatan'])) {
	$id_lat_jabatan = $_GET['id_lat_jabatan'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$ambilData=mysql_query("SELECT * FROM tb_lat_jabatan WHERE id_lat_jabatan='$id_lat_jabatan'");
	$hasil=mysql_fetch_array($ambilData);
		$id_lat_jabatan	= $hasil['id_lat_jabatan'];
		$id_peg	= $hasil['id_peg'];
	
	$ambilPeg=mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
	$peg=mysql_fetch_array($ambilPeg);
		$nip	= $peg['nip'];
?>
<section class="content-header">
    <h1>Edit<small>Data Latihan Jabatan <b>#<?=$nip?></b></small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<form action="home-admin.php?page=edit-data-lat-jabatan&id_lat_jabatan=<?=$id_lat_jabatan?>" class="form-horizontal" method="POST" enctype="multipart/form-data">
					<div class="box-body">
						<div class="form-group">
							<label class="col-sm-3 control-label">Nama Pelatih</label>
							<div class="col-sm-7">
								<input type="text" name="nama_pelatih" class="form-control" value="<?=$hasil['nama_pelatih'];?>" maxlength="64">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Tahun</label>
							<div class="col-sm-4">
								<input type="text" name="tahun_lat" class="form-control" value="<?=$hasil['tahun_lat'];?>" maxlength="4">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Jumlah Jam</label>
							<div class="col-sm-4">
								<input type="text" name="jml_jam" class="form-control" value="<?=$hasil['jml_jam'];?>" maxlength="3">
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
<!-- datepicker -->
<script type="text/javascript" src="plugins/datepicker/jquery/jquery-1.8.3.min.js" charset="UTF-8"></script>
<script type="text/javascript" src="plugins/datepicker/js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript" src="plugins/datepicker/js/locales/bootstrap-datetimepicker.id.js" charset="UTF-8"></script>
<script type="text/javascript">
	 $('.form_date').datetimepicker({
			language:  'id',
			weekStart: 1,
			todayBtn:  1,
	  autoclose: 1,
	  todayHighlight: 1,
	  startView: 2,
	  minView: 2,
	  forceParse: 0
		});
	$(function () {
		//Initialize Select2 Elements
		$(".select2").select2();
	});
</script>