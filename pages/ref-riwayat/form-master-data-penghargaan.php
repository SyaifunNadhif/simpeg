<section class="content-header">
    <h1>Master<small>Data Penghargaan</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Data Penghargaan</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
		<div class="box-body">
			<div class="row">
				<div class="col-md-12">	
					<div class="panel-body">
						<form action="home-admin.php?page=master-data-penghargaan" class="form-horizontal" method="POST" enctype="multipart/form-data">
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
								<label class="col-sm-3 control-label">Nama Penghargaan</label>
								<div class="col-sm-7">
									<input type="text" name="penghargaan" class="form-control" maxlength="64">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Tahun</label>
								<div class="col-sm-7">
									<input type="text" name="tahun" class="form-control" maxlength="4">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Negara / Instansi Pemberi</label>
								<div class="col-sm-7">
									<input type="text" name="pemberi" class="form-control" maxlength="64">
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