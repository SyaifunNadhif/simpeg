<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Data<small> Pelanggaran Pegawai</small></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
          <li class="breadcrumb-item active">Pelanggaran Pegawai</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="card card-primary">
	  	<div class="card-header">
	  		<h3 class="card-title">Form Input Data Pelanggaran</h3>
	  	</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-12">	
					<div class="panel-body">
						<form action="home-admin.php?page=master-data-hukuman" class="form-horizontal" method="POST" enctype="multipart/form-data">
							<div class="col-sm-4">
                <div class="form-group">
                  <label>ID Pegawai</label>
                  <?php
                  include "dist/koneksi.php";
                  $data = mysql_query("SELECT * FROM tb_pegawai");        
                  echo '<select name="id_peg" required="required" class="form-control select2bs4" style="width: 100%;">';    
                  echo '<option value="" selected="selected">Pilih Pegawai</option>';    
                  while ($row = mysql_fetch_array($data)) {    
                    echo '<option value="'.$row['id_peg'].'">'. $row['id_peg'].' - '.$row['nama'].'</option>';    
                  }    
                  echo '</select>';
                  ?>
                </div>
              </div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Jenis Sanksi Pelanggaran</label>
								<div class="col-sm-4">
									<select name="hukuman" class="form-control select2" style="width: 100%;">
										<option value="-" selected="selected">Pilih</option>
										<option value="Surat Peringatan I">Surat Peringatan I</option>
										<option value="Surat Peringatan II">Surat Peringatan II</option>
										<option value="Surat Peringatan III">Surat Peringatan III</option>
										<option value="Skorsing">Skorsing</option>
										<option value="PTDH">PTDH</option>
									</select>
								</div>
							</div>
              <div class="form-group">
                <label class="col-sm-3 control-label">Keterangan</label>
                <div class="col-sm-6">
                  <textarea class="form-control" rows="3" name="keterangan" class="form-control"></textarea>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-4">
                  <div class="form-group">
                    <label class="control-label">Pejabat Pengesah SK</label>
                    <input type="text" name="pejabat_sk" class="form-control" maxlength="64">
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="form-group">
                    <label class="control-label">Jabatan Pengesah SK</label>
                    <input type="text" name="jabatan_sk" class="form-control" maxlength="64">
                  </div> 
                </div>               
              </div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nomor SK</label>
								<div class="col-sm-7">
									<input type="text" name="no_sk" class="form-control" maxlength="32">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Tanggal Pengesahan SK</label>
									<div class="col-sm-4 input-group date" id="tgl_sk" data-target-input="nearest">
                      <input type="text" name="tgl_sk" placeholder="dd-mm-yyyy" class="form-control datetimepicker-input" data-target="#tgl_sk" data-inputmask-alias="datetime" data-inputmask-inputformat="dd-mm-yyyy" data-mask/>
                      <div class="input-group-append" data-target="#tgl_sk" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                      </div>
                  </div>
							</div>
              <div class="row">
                <div class="col-sm-4">
    							<div class="form-group">
    								<label class="control-label">Pejabat Pemulih Hukuman</label>
    								<input type="text" name="pejabat_pulih" class="form-control" maxlength="64">
    							</div>
                </div>
                <div class="col-sm-4">
                  <div class="form-group">
                    <label class="control-label">Jabatan Pemulih Hukuman</label>
                    <input type="text" name="jabatan_pulih" class="form-control" maxlength="64">
                  </div> 
                </div>               
              </div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Nomor SK Pemulihan</label>
								<div class="col-sm-3">
									<input type="text" name="no_pulih" class="form-control" maxlength="32">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Tanggal Pemulihan Hukuman</label>
								<div class="col-sm-4 input-group date" id="tgl_pulih" data-target-input="nearest">
                      <input type="text" name="tgl_pulih" placeholder="dd-mm-yyyy" class="form-control datetimepicker-input" data-target="#tgl_pulih" data-inputmask-alias="datetime" data-inputmask-inputformat="dd-mm-yyyy" data-mask/>
                      <div class="input-group-append" data-target="#tgl_pulih" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                      </div>
                  </div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-7">
									<button type="submit" name="save" value="save" class="btn btn-danger">Save</button>
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
    $('#tgl_sk').datetimepicker({
      format: 'L',
      language:  'id',
      format: 'DD-MM-yyyy'
    });
    $('#tgl_pulih').datetimepicker({
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
