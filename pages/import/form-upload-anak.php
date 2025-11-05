<?php 

// Get status message 
if(!empty($_GET['status'])){ 
    switch($_GET['status']){ 
        case 'succ': 
            $statusType = 'alert-success'; 
            $statusMsg = 'Data Anak berhasil Diupload.'; 
            break; 
        case 'err': 
            $statusType = 'alert-danger'; 
            $statusMsg = 'Upload Data Gagal, Silakan Coba Lagi.'; 
            break; 
        case 'invalid_file': 
            $statusType = 'alert-danger'; 
            $statusMsg = 'Silakan Upload File Excel yang Valid.'; 
            break; 
        default: 
            $statusType = ''; 
            $statusMsg = ''; 
    } 
} 
?>

<?php if(!empty($statusMsg)){ ?>
<div class="col-xs-12 p-3">
    <div class="alert <?php echo $statusType; ?>"><?php echo $statusMsg; ?></div>
</div>
<?php } ?>

<section class="content-header">
    <h1>Upload<small>Data Anak</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Data Anak</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
		<div class="box-body">
			<div class="row">
				<div class="col-md-12">	
					<div class="panel-body">
						<form action="home-admin.php?page=upload-anak" class="form-horizontal" method="POST" enctype="multipart/form-data">
							<div class="form-group">
								<label class="col-sm-3 control-label">Pilih File: </label>
								<div class="col-sm-7">								<input class="form-control" name="uploadanak" type="file" required="required"> 
								</div>	
								</br>
								</br>
								<div class="col-sm-offset-3 col-sm-7">
									<button type="submit" name="save" value="save" class="btn btn-primary">Upload</button>
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