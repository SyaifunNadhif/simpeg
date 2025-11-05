<?php
	if (isset($_GET['id_sertif'])) {
	$id_sertif = $_GET['id_sertif'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$ambilData=mysql_query("SELECT a.*, nama FROM tb_sertifikasi a, tb_pegawai b WHERE a.id_peg=b.id_peg AND id_sertif='$id_sertif'");
	$hasil=mysql_fetch_array($ambilData);
		$id_sertif	= $hasil['id_sertif'];
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Referensi<small> Data Sertifikasi</small></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
          <li class="breadcrumb-item active">Edit Data Sertifikasi</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card card-warning">	
					<div class="card-header">
						<h3 class="card-title">Form Edit Data Sertifikasi</h3>
					</div>			
					<div class="card-body">
						<form role="form" id="formSertifikasi" action="home-admin.php?page=edit-data-sertifikasi&id_sertif=<?=$id_sertif?>" class="form-horizontal" method="POST" enctype="multipart/form-data">
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label>Nama Pegawai</label>
										<input type="text" name="id_peg" value="<?=$hasil['id_peg'];?> - <?=$hasil['nama'];?>" class="form-control" maxlength="10" readonly>
									</div>
								</div>
							</div>	
							<div class="row">	
								<div class="col-sm-6">	
									<div class="form-group">
										<label>Nama Sertifikasi</label>
										<input type="text" style="text-transform:uppercase" name="sertifikasi" value="<?=$hasil['sertifikasi'];?>" class="form-control" required="required" maxlength="255">
									</div>
								</div>
								<div class="col-sm-6">	
									<div class="form-group">
										<label>Penyelenggara</label>
										<input type="text" style="text-transform:uppercase" name="penyelenggara" value="<?=$hasil['penyelenggara'];?>" class="form-control" required="required" maxlength="128">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label>Tanggal Sertifikat</label>
										<div class="col-sm-6 input-group date" id="tgl_sertifikat" data-target-input="nearest">
											<input type="text" name="tgl_sertifikat" value="<?php echo date('d-m-Y',strtotime($hasil['tgl_sertifikat']));?>" placeholder="dd-mm-yyyy" class="form-control datetimepicker-input" data-target="#tgl_sertifikat"/>
											<div class="input-group-append" data-target="#tgl_sertifikat" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label>Tanggal Expired</label>
										<div class="col-sm-6 input-group date" id="tgl_expired" data-target-input="nearest">
											<input type="text" name="tgl_expired" value="<?php echo date('d-m-Y',strtotime($hasil['tgl_expired']));?>" placeholder="dd-mm-yyyy" class="form-control datetimepicker-input" data-target="#tgl_expired"/>
											<div class="input-group-append" data-target="#tgl_expired" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
										</div>
									</div>										
								</div>	
							</div>	
              <div class="row">
                <div class="col-sm-6">   
                  <div class="form-group">
                    <label>File Sertifikat</label>
                    <input type="file" name="sertifikat" value="<?=$hasil['sertifikat'];?>" class="form-control" maxlength="255">
                  </div>
                </div>
              </div> 
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-7">
									<button type="submit" name="edit" value="edit" class="btn btn-danger">Edit</button>
									<a href="javascript:history.back()" type="button" class="btn btn-default">Cancel</a>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrbootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="plugins/selecselect2.full.min.js"></script>
<!-- Bootstrap4 Duallistbox -->
<script src="plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
<!-- InputMask -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/inputmask/jquery.inputmask.min.js"></script>
<!-- date-range-picker -->
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- bootstrap color picker -->
<script src="plugins/bootstrap-colorpickbootstrap-colorpicker.min.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstraptempusdominus-bootstrap-4.min.js"></script>
<!-- bs-custom-file-input -->
<script src="../../plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<!-- Bootstrap Switch -->
<script src="plugins/bootstrap-switbootstrap-switch.min.js"></script>
<!-- BS-Stepper -->
<script src="plugins/bs-steppbs-stepper.min.js"></script>
<!-- dropzonejs -->
<script src="plugins/dropzone/min/dropzone.min.js"></script>
<!-- AdminLTE App -->
<script src="diadminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="didemo.js"></script>
<!-- jquery-validation -->
<script src="plugins/jquery-validation/jquery.validate.min.js"></script>
<script src="plugins/jquery-validation/additional-methods.min.js"></script>
<!-- Page specific script -->
<script>
	$(function () {
		bsCustomFileInput.init();
	});

  $(function () {
    //Initialize Select2 Elements
    $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })

    //Datemask yyyy
    $('#datemask').inputmask('yyyy', { 'placeholder': 'yyyy' })
    //Datemask2 yyyy
    $('#datemask2').inputmask('yyyy', { 'placeholder': 'yyyy' })
    //Money Euro
    $('[data-mask]').inputmask()

    //Date picker
    $('#tgl_kegiatan').datetimepicker({
      format: 'L',
      language:  'id',
      format: 'DD-MM-yyyy'
    });
        //Date picker
    $('#tgl_sertifikat').datetimepicker({
      format: 'L',
      language:  'id',
      format: 'DD-MM-yyyy'
    });
        //Date picker
    $('#tgl_expired').datetimepicker({
      format: 'L',
      language:  'id',
      format: 'DD-MM-yyyy'
    });

    //Date and time picker
    //$('#tgl_lahirtime').datetimepicker({ icons: { time: 'far fa-clock' } });

    //Date range picker
    $('#reservation').daterangepicker()
    //Date range picker with time picker
    $('#reservationtime').daterangepicker({
      timePicker: true,
      timePickerIncrement: 30,
      locale: {
        format: 'YYYY hh:mm A'
      }
    })
    //Date range as a button
    $('#daterange-btn').daterangepicker(
    {
      ranges   : {
        'Today'       : [moment(), moment()],
        'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month'  : [moment().startOf('month'), moment().endOf('month')],
        'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      },
      startDate: moment().subtract(29, 'days'),
      endDate  : moment()
    },
    function (start, end) {
      $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
    }
    )

    //Timepicker
    $('#timepicker').datetimepicker({
      format: 'LT'
    })

    //Bootstrap Duallistbox
    $('.duallistbox').bootstrapDualListbox()

    //Colorpicker
    $('.my-colorpicker1').colorpicker()
    //color picker with addon
    $('.my-colorpicker2').colorpicker()

    $('.my-colorpicker2').on('colorpickerChange', function(event) {
      $('.my-colorpicker2 .fa-square').css('color', event.color.toString());
    })

    $("input[data-bootstrap-switch]").each(function(){
      $(this).bootstrapSwitch('state', $(this).prop('checked'));
    })

  })
  // BS-Stepper Init
  document.addEventListener('DOMContentLoaded', function () {
    window.stepper = new Stepper(document.querySelector('.bs-stepper'))
  })

  // DropzoneJS Demo Code Start
  Dropzone.autoDiscover = false

  // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
  var previewNode = document.querySelector("#template")
  previewNode.id = ""
  var previewTemplate = previewNode.parentNode.innerHTML
  previewNode.parentNode.removeChild(previewNode)

  var myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
    url: "/target-url", // Set the url
    thumbnailWidth: 80,
    thumbnailHeight: 80,
    parallelUploads: 20,
    previewTemplate: previewTemplate,
    autoQueue: false, // Make sure the files aren't queued until manually added
    previewsContainer: "#previews", // Define the container to display the previews
    clickable: ".fileinput-button" // Define the element that should be used as click trigger to select files.
  })

  myDropzone.on("addedfile", function(file) {
    // Hookup the start button
    file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file) }
  })

  // Update the total progress bar
  myDropzone.on("totaluploadprogress", function(progress) {
    document.querySelector("#total-progress .progress-bar").style.width = progress + "%"
  })

  myDropzone.on("sending", function(file) {
    // Show the total progress bar when upload starts
    document.querySelector("#total-progress").style.opacity = "1"
    // And disable the start button
    file.previewElement.querySelector(".start").setAttribute("disabled", "disabled")
  })

  // Hide the total progress bar when nothing's uploading anymore
  myDropzone.on("queuecomplete", function(progress) {
    document.querySelector("#total-progress").style.opacity = "0"
  })

  // Setup the buttons for all transfers
  // The "add files" button doesn't need to be setup because the config
  // `clickable` has already been specified.
  document.querySelector("#actions .start").onclick = function() {
    myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED))
  }
  document.querySelector("#actions .cancel").onclick = function() {
    myDropzone.removeAllFiles(true)
  }
  // DropzoneJS Demo Code End
</script>
