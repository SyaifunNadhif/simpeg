<section class="content-header">
    <h1>Master<small>Data Pegawai</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Data Pegawai</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
			<div class="box box-primary">				
				<div class="box-body">
					<div class="panel-body">
						<form action="home-admin.php?page=master-data-pegawai" class="form-horizontal" method="POST" enctype="multipart/form-data">
							<div class="form-group">
								<label class="col-sm-3 control-label">NIP</label>
								<div class="col-sm-7">
									<input type="text" name="nip" class="form-control" maxlength="24">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nama Pegawai</label>
								<div class="col-sm-7">
									<input type="text" name="nama" class="form-control" maxlength="64">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Tempat, Tanggal Lahir</label>
								<div class="col-sm-3">
									<input type="text" name="tempat_lhr" class="form-control" maxlength="64">
								</div>
								<div class="input-group date form_date col-sm-3" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
									<input type="text" name="tgl_lhr" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Foto</label>
								<div class="col-sm-7">
									<input type="file" name="foto" class="form-control" maxlength="255">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Agama</label>
								<div class="col-sm-7">
									<select name="agama" class="form-control">
										<option value="">Pilih</option>
										<option value="Islam">Islam</option>
										<option value="Protestan">Protestan</option>
										<option value="Katolik">Katolik</option>
										<option value="Hindu">Hindu</option>
										<option value="Budha">Budha</option>
										<option value="Kong Hu Cu">Kong Hu Cu</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Jenis Kelamin</label>
								<div class="col-sm-7">
									<select name="jk" class="form-control">
										<option value="">Pilih</option>
										<option value="Laki-laki">Laki-laki</option>
										<option value="Perempuan">Perempuan</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Golongan Darah</label>
								<div class="col-sm-7">
									<select name="gol_darah" class="form-control">
										<option value="">Pilih</option>
										<option value="A">A</option>
										<option value="AB">AB</option>
										<option value="B">B</option>
										<option value="O">O</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Status Pernikahan</label>
								<div class="col-sm-7">
									<select name="status_nikah" class="form-control">
										<option value="">Pilih</option>
										<option value="Nikah">Nikah</option>
										<option value="Belum Nikah">Belum Nikah</option>
										<option value="Cerai">Cerai</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Status Kepegawaian</label>
								<div class="col-sm-7">
									<select name="status_kepeg" class="form-control">
										<option value="">Pilih</option>
										<option value="PNS">PNS</option>
										<option value="PTT">PTT</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Tanggal Kenaikan Pangkat</label>
								<div class="col-sm-4">
									<div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
										<input type="text" name="tgl_naikpangkat" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Tanggal Kenaikan Gaji</label>
								<div class="col-sm-4">
									<div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
										<input type="text" name="tgl_naikgaji" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Alamat</label>
								<div class="col-sm-7">
									<textarea type="text" name="alamat" class="form-control" maxlength="255"></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">No. Telp</label>
								<div class="col-sm-7">
									<input type="text" name="telp" class="form-control" maxlength="12">
								</div>
							</div>							
							<div class="form-group">
								<label class="col-sm-3 control-label">Email</label>
								<div class="col-sm-7">
									<input type="text" name="email" class="form-control" maxlength="64">
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-7">
									<button type="submit" name="save" value="save" class="btn btn-danger">Save</button>
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
</script>