<section class="content-header">
    <h1>Master<small>Data Pangkat</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Data Pangkat</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
		<div class="box-body">
			<div class="row">
				<div class="col-md-12">	
					<div class="panel-body">
						<form action="home-admin.php?page=master-data-pangkat" class="form-horizontal" method="POST" enctype="multipart/form-data">
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
								<label class="col-sm-3 control-label">Pangkat</label>
								<div class="col-sm-7">
									<input type="text" name="pangkat" class="form-control" maxlength="64">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Golongan</label>
								<div class="col-sm-4">
									<?php
									$dataG = mysql_query("SELECT * FROM tb_mastergol ORDER BY nama_mastergol DESC");        
									echo '<select name="gol" class="form-control select2" style="width: 100%;">';    
									echo '<option value="">Pilih Golongan</option>';    
									while ($rowg = mysql_fetch_array($dataG)) {    
										echo '<option value="'.$rowg['nama_mastergol'].'">'. $rowg['nama_mastergol'].'</option>';    
									}    
									echo '</select>';
									?>
								</div>
								<div class="col-sm-3" align="right">
									<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#mastergol">Tambah Master Golongan</button>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Jenis Pangkat</label>
								<div class="col-sm-7">
									<input type="text" name="jns_pangkat" class="form-control" maxlength="32">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">TMT</label>
								<div class="col-sm-4">
									<div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
										<input type="text" name="tmt_pangkat" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Tanggal Pengesahan SK</label>
								<div class="col-sm-4">
									<div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
										<input type="text" name="tgl_sk" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Pejabat Pengesah SK</label>
								<div class="col-sm-7">
									<input type="text" name="pejabat_sk" class="form-control" maxlength="32">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nomor SK</label>
								<div class="col-sm-7">
									<input type="text" name="no_sk" class="form-control" maxlength="32">
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
<div id="mastergol" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Master Data Golongan</h4>
			</div>
			<div class="modal-body">
				<form action="home-admin.php?page=master-data-golongan" class="form-horizontal" method="POST" enctype="multipart/form-data">
					<div class="form-group">
						<label class="col-sm-2 control-label">Golongan</label>
						<div class="col-sm-2">
							<input type="text" name="nama_mastergol" class="form-control" maxlength="6">
						</div>
						<div class="col-sm-6">
							<p>* Gunakan tanda baca GARING " / " setelah Romawi. Ex: III/A</p>
						</div>
						<div class="col-sm-2">
							<button type="submit" name="save" value="save" class="btn btn-danger">Save</button>
						</div>
					</div>
				</form>
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>No</th>
							<th>Golongan</th>
							<th>More</th>
						</tr>
					</thead>
					<tbody>
					<?php
						$no=0;
						$tampilG	=mysql_query("SELECT * FROM tb_mastergol ORDER BY nama_mastergol DESC");
						while($gol=mysql_fetch_array($tampilG)){
						$no++;
					?>	
						<tr>
							<td><?=$no?></td>
							<td><?php echo $gol['nama_mastergol'];?></td>
							<td class="tools" align="center"><a href="home-admin.php?page=form-edit-data-golongan&id_mastergol=<?=$gol['id_mastergol'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-golongan&id_mastergol=<?=$gol['id_mastergol'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
						</tr>
					<?php
						}
					?>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
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