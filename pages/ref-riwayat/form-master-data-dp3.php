<section class="content-header">
    <h1>Master<small>Data Sasaran Kerja Pegawai</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Data SKP</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
		<div class="box-body">
			<div class="row">
				<div class="col-md-12">	
					<div class="panel-body">
						<form action="home-admin.php?page=master-data-dp3" class="form-horizontal" method="POST" enctype="multipart/form-data">
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
								<label class="col-sm-3 control-label">Periode Penilaian</label>
								<div class="col-sm-3">
									<div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
										<input type="text" name="periode_awal" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
									</div>
								</div>
								<label class="col-sm-1 control-label">Sampai</label>
								<div class="col-sm-3">
									<div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
										<input type="text" name="periode_akhir" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nama Pejabat Penilai</label>
								<div class="col-sm-7">
									<input type="text" name="pejabat_penilai" class="form-control" maxlength="64">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nama Atasan Pejabat Penilai</label>
								<div class="col-sm-7">
									<input type="text" name="atasan_pejabat_penilai" class="form-control" maxlength="64">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Kesetiaan</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_kesetiaan" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Prestasi</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_prestasi" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Tanggung Jawab</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_tgjwb" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Ketaatan</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_ketaatan" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Kejujuran</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_kejujuran" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Kerjasama</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_kerjasama" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Prakarsa</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_prakarsa" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Kepemimpinan</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_kepemimpinan" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Hasil Penilian</label>
								<div class="col-sm-7">
									<select name="hasil_penilaian" class="form-control">
										<option value="">Pilih</option>
										<option value="Sangat Baik">Sangat Baik</option>
										<option value="Baik">Baik</option>
										<option value="Cukup Baik">Cukup Baik</option>
										<option value="Kurang Baik">Kurang Baik</option>
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