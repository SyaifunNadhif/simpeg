<?php
	if (isset($_GET['id_pangkat'])) {
	$id_pangkat = $_GET['id_pangkat'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$ambilData=mysql_query("SELECT * FROM tb_pangkat WHERE id_pangkat='$id_pangkat'");
	$hasil=mysql_fetch_array($ambilData);
		$id_pangkat	= $hasil['id_pangkat'];
		$id_peg	= $hasil['id_peg'];
	
	$ambilPeg=mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
	$peg=mysql_fetch_array($ambilPeg);
		$nip	= $peg['nip'];
?>
<section class="content-header">
    <h1>Edit<small>Data Pangkat <b>#<?=$nip?></b></small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<form action="home-admin.php?page=edit-data-pangkat&id_pangkat=<?=$id_pangkat?>" class="form-horizontal" method="POST" enctype="multipart/form-data">
					<div class="box-body">
						<div class="form-group">
							<label class="col-sm-3 control-label">Pangkat</label>
							<div class="col-sm-7">
								<input type="text" name="pangkat" class="form-control" value="<?=$hasil['pangkat'];?>" maxlength="64">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Golongan</label>
							<div class="col-sm-7">
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
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Jenis Pangkat</label>
							<div class="col-sm-7">
								<input type="text" name="jns_pangkat" class="form-control" value="<?=$hasil['jns_pangkat'];?>" maxlength="32">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">TMT</label>
							<div class="col-sm-4">
								<div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
									<input type="text" name="tmt_pangkat" value="<?=$hasil['tmt_pangkat'];?>" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Tanggal Pengesahan SK</label>
							<div class="col-sm-4">
								<div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
									<input type="text" name="tgl_sk" value="<?=$hasil['tgl_sk'];?>" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Pejabat Pengesah SK</label>
							<div class="col-sm-7">
								<input type="text" name="pejabat_sk" class="form-control" value="<?=$hasil['pejabat_sk'];?>" maxlength="32">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Nomor SK</label>
							<div class="col-sm-7">
								<input type="text" name="no_sk" class="form-control" value="<?=$hasil['no_sk'];?>" maxlength="32">
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