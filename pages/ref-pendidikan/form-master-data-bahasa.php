<section class="content-header">
    <h1>Master<small>Data Bahasa</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Data Bahasa</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
		<div class="box-body">
			<div class="row">
				<div class="col-md-12">	
					<div class="panel-body">
						<form action="home-admin.php?page=master-data-bahasa" class="form-horizontal" method="POST" enctype="multipart/form-data">
							<div class="form-group">
								<label class="col-sm-3 control-label">Pegawai</label>
								<div class="col-sm-7">
									<?php
									include "dist/koneksi.php";
									$data = mysql_query("SELECT * FROM tb_pegawai");        
									echo '<select name="id_peg" class="form-control select2" style="width: 100%;">';    
									echo '<option value="">Pilih Pegawai</option>';    
									while ($row = mysql_fetch_array($data)) {    
										echo '<option value="'.$row['id_peg'].'">'. $row['nip'].' - '.$row['nama'].'</option>';    
									}    
									echo '</select>';
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Jenis Bahasa</label>
								<div class="col-sm-7">
									<input type="text" name="jns_bhs" class="form-control" maxlength="32">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Bahasa</label>
								<div class="col-sm-7">
									<input type="text" name="bahasa" class="form-control" maxlength="32">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Kemampuan Bicara</label>
								<div class="col-sm-7">
									<select name="kemampuan" class="form-control select2" style="width: 100%;">
										<option value="Aktif" selected="selected">Aktif</option>
										<option value="Pasif">Pasif</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-7">
									<button type="submit" name="save" value="save" class="btn btn-danger">Save</button>
									<a href="home-admin.php" type="button" class="btn btn-default">Cancel</a>
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
	$(function () {
		//Initialize Select2 Elements
		$(".select2").select2();
	});
</script>