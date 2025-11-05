<?php
	if (isset($_GET['id_sekolah'])) {
	$id_sekolah = $_GET['id_sekolah'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$ambilData=mysql_query("SELECT * FROM tb_sekolah WHERE id_sekolah='$id_sekolah'");
	$hasil=mysql_fetch_array($ambilData);
		$id_sekolah	= $hasil['id_sekolah'];
		$id_peg		= $hasil['id_peg'];
	
	$ambilPeg=mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
	$peg=mysql_fetch_array($ambilPeg);
		$nip	= $peg['nip'];
?>
<section class="content-header">
    <h1>Edit<small>Data Sekolah <b>#<?=$nip?></b></small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<form action="home-admin.php?page=edit-data-sekolah&id_sekolah=<?=$id_sekolah?>" class="form-horizontal" method="POST" enctype="multipart/form-data">
					<div class="box-body">
						<div class="form-group">
							<label class="col-sm-3 control-label">Tingkat</label>
							<div class="col-sm-7">
								<input type="text" name="tingkat" class="form-control" value="<?=$hasil['tingkat'];?>" maxlength="16">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Nama Sekolah / Universitas</label>
							<div class="col-sm-7">
								<input type="text" name="nama_sekolah" class="form-control" value="<?=$hasil['nama_sekolah'];?>" maxlength="64">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Lokasi</label>
							<div class="col-sm-7">
								<input type="text" name="lokasi" class="form-control" value="<?=$hasil['lokasi'];?>" maxlength="32">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Jurusan</label>
							<div class="col-sm-7">
								<input type="text" name="jurusan" class="form-control" value="<?=$hasil['jurusan'];?>" maxlength="32">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Tanggal Ijazah</label>
							<div class="col-sm-7">
								<div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
									<input type="text" name="tgl_ijazah" value="<?=$hasil['tgl_ijazah'];?>" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">No. Ijazah</label>
							<div class="col-sm-7">
								<input type="text" name="no_ijazah" class="form-control" value="<?=$hasil['no_ijazah'];?>" maxlength="32">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Nama KepSek / Rektor</label>
							<div class="col-sm-7">
								<input type="text" name="kepala" class="form-control" value="<?=$hasil['kepala'];?>" maxlength="64">
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