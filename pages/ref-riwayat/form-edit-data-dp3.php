<?php
	if (isset($_GET['id_dp3'])) {
	$id_dp3 = $_GET['id_dp3'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$ambilData=mysql_query("SELECT * FROM tb_dp3 WHERE id_dp3='$id_dp3'");
	$hasil=mysql_fetch_array($ambilData);
		$id_dp3	= $hasil['id_dp3'];
		$id_peg	= $hasil['id_peg'];
	
	$ambilPeg=mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
	$peg=mysql_fetch_array($ambilPeg);
		$nip	= $peg['nip'];
?>
<section class="content-header">
    <h1>Edit<small>Data SKP <b>#<?=$nip?></b></small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<form action="home-admin.php?page=edit-data-dp3&id_dp3=<?=$id_dp3?>" class="form-horizontal" method="POST" enctype="multipart/form-data">
					<div class="box-body">
						<div class="form-group">
							<label class="col-sm-3 control-label">Periode Penilaian</label>
							<div class="col-sm-3">
								<div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
									<input type="text" name="periode_awal" value="<?=$hasil['periode_awal'];?>" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
							<label class="col-sm-1 control-label">Sampai</label>
							<div class="col-sm-3">
								<div class="input-group date form_date" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
									<input type="text" name="periode_akhir" value="<?=$hasil['periode_akhir'];?>" class="form-control"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Nama Pejabat Penilai</label>
							<div class="col-sm-7">
								<input type="text" name="pejabat_penilai" value="<?=$hasil['pejabat_penilai'];?>" class="form-control" maxlength="64">
							</div>
						</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nama Atasan Pejabat Penilai</label>
								<div class="col-sm-7">
									<input type="text" name="atasan_pejabat_penilai" value="<?=$hasil['atasan_pejabat_penilai'];?>" class="form-control" maxlength="64">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Kesetiaan</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_kesetiaan" value="<?=$hasil['nilai_kesetiaan'];?>" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Prestasi</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_prestasi" value="<?=$hasil['nilai_prestasi'];?>" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Tanggung Jawab</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_tgjwb" value="<?=$hasil['nilai_tgjwb'];?>" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Ketaatan</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_ketaatan" value="<?=$hasil['nilai_ketaatan'];?>" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Kejujuran</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_kejujuran" value="<?=$hasil['nilai_kejujuran'];?>" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Kerjasama</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_kerjasama" value="<?=$hasil['nilai_kerjasama'];?>" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Prakarsa</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_prakarsa" value="<?=$hasil['nilai_prakarsa'];?>" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nilai Kepemimpinan</label>
								<div class="col-sm-3">
									<input type="text" name="nilai_kepemimpinan" value="<?=$hasil['nilai_kepemimpinan'];?>" class="form-control" maxlength="3">
								</div>
								<div class="col-sm-3">
									<p class="description">* Angka</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Hasil Penilian</label>
								<div class="col-sm-7">
									<select name="hasil_penilaian" class="form-control">
										<option value="Sangat Baik" <?php echo ($hasil['hasil_penilaian']=='Sangat Baik')?"selected":""; ?>>Sangat Baik
										<option value="Baik" <?php echo ($hasil['hasil_penilaian']=='Baik')?"selected":""; ?>>Baik
										<option value="Cukup Baik" <?php echo ($hasil['hasil_penilaian']=='Cukup Baik')?"selected":""; ?>>Cukup Baik
										<option value="Kurang Baik" <?php echo ($hasil['hasil_penilaian']=='Kurang Baik')?"selected":""; ?>>Kurang Baik
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