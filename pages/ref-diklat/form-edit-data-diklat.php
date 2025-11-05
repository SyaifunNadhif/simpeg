<?php
	if (isset($_GET['id_diklat'])) {
	$id_diklat = $_GET['id_diklat'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$ambilData=mysql_query("SELECT * FROM tb_diklat WHERE id_diklat='$id_diklat'");
	$hasil=mysql_fetch_array($ambilData);
		$id_diklat	= $hasil['id_diklat'];
		$id_peg	= $hasil['id_peg'];
	
	$ambilPeg=mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
	$peg=mysql_fetch_array($ambilPeg);
		$nip	= $peg['nip'];
?>
<section class="content-header">
    <h1>Edit<small>Data Diklat <b>#<?=$nip?></b></small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<form action="home-admin.php?page=edit-data-diklat&id_diklat=<?=$id_diklat?>" class="form-horizontal" method="POST" enctype="multipart/form-data">
					<div class="box-body">
						<div class="form-group">
							<label class="col-sm-3 control-label">Nama Diklat</label>
							<div class="col-sm-7">
								<input type="text" name="diklat" class="form-control" value="<?=$hasil['diklat'];?>" maxlength="128">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Jumlah Jam</label>
							<div class="col-sm-7">
								<input type="text" name="jml_jam" class="form-control" value="<?=$hasil['jml_jam'];?>" maxlength="4">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Penyelenggara</label>
							<div class="col-sm-7">
								<input type="text" name="penyelenggara" class="form-control" value="<?=$hasil['penyelenggara'];?>" maxlength="64">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Tempat</label>
							<div class="col-sm-7">
								<input type="text" name="tempat" class="form-control" value="<?=$hasil['tempat'];?>" maxlength="32">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Angkatan</label>
							<div class="col-sm-7">
								<input type="text" name="angkatan" class="form-control" value="<?=$hasil['angkatan'];?>" maxlength="4">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Tahun</label>
							<div class="col-sm-7">
								<input type="text" name="tahun" class="form-control" value="<?=$hasil['tahun'];?>" maxlength="4">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Tanggal STTPP</label>
							<div class="col-sm-7">
								<div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
									<input type="text" name="tgl_sttpp" value="<?=$hasil['tgl_sttpp'];?>" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Nomor STTPP</label>
							<div class="col-sm-7">
								<input type="text" name="no_sttpp" class="form-control" value="<?=$hasil['no_sttpp'];?>" maxlength="32">
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