<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <a href="home-admin.php"><button class="btn-sm btn-info"><i class="fa fa-step-backward"></i> Back</button></a>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
          <li class="breadcrumb-item active">Data Pelanggaran Pegawai</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<style>
  select.col-form-label{
    display: inline;
    width: 200px;
    height: 30PX;
    margin-left: 25px;
  }
</style>
<?php
	include "dist/koneksi.php";
	include "dist/library.php";
	$tampilJudge=mysqli_query($conn, "SELECT tb_hukuman.*, (SELECT nama from tb_pegawai where id_peg=tb_hukuman.id_peg) nama FROM tb_hukuman");
?>
<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-12">	
				<div class="card">	
					<div class="card-body">	
						<div class="row">
							<div class="col-sm-6"><h4>Data Pelanggaran Pegawai</h4></div>
							<div class="col-sm-6">											
								<a href="home-admin.php?page=form-master-data-hukuman"><button class="btn-sm btn-info float-right"><i class="fa fa-plus"></i> Tambah Data</button></a><a href="#"><button class="btn-sm btn-danger float-right"><i class="fa fa-plus"></i> Tambah Data Kolektif</button></a>
							</div>
						</div>
						<div class="col-sm-6">
							<?php
							include "dist/koneksi.php";
							$data = mysqli_query($conn, "SELECT * FROM tb_hukuman GROUP BY hukuman");        
							echo '<div class="row">
										<select name="categoryFilter" id="categoryFilter" required="required" class="col-sm-3 col-form-label float-sm-right" style="width: 30%;">';    
							echo '<option value="" selected="selected">Pilih Semua</option>';    
							while ($row = mysqli_fetch_array($data)) {    
								echo '<option value="'.$row['hukuman'].'">'. $row['hukuman'].'</option>';    
							}    
							echo '</select></div>';
							?>
						</div>						
						<br>				
						<table id="pelanggaran" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>ID Pegawai</th>
									<th>Nama Pegawai</th>
									<th>Jenis Pelanggaran</th>
									<th>Keterangan</th>
									<th>Tgl Surat</th>
									<th>Dokumen</th>
									<th width="8%">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php
									while($peg=mysqli_fetch_array($tampilJudge)){
								?>	
								<tr>
									<td><?php echo $peg['id_peg'];?></td>
									<td><?php echo $peg['nama'];?></td>
									<td><?php echo $peg['hukuman'];?></td>
									<td><?php echo $peg['keterangan'];?></td>
									<td><?php echo $peg['tgl_sk'];?></td>
									<td></td>
									<td class="tools" align="center"><a class="btn btn-outline-primary btn-xs" href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?=$peg['id_peg'];?>" title="detail"><i class="fa fa-folder-open"></i></a>
										<a class="btn btn-outline-warning btn-xs" href="home-admin.php?page=form-edit-data-hukuman&id_hukum=<?=$peg['id_hukum'];?>" title="edit"><i class="fa fa-edit"></i></a>
										<a class="btn btn-outline-danger btn-xs" href="home-admin.php?page=delete-data-hukuman&id_hukum=<?php echo $peg['id_hukum'];?>" title="delete"><i class="fas fa-trash-alt"></i></a></td>
									</td>
								</tr>
								<?php
									}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="plugins/jszip/jszip.min.js"></script>
<script src="plugins/pdfmake/pdfmake.min.js"></script>
<script src="plugins/pdfmake/vfs_fonts.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<script>
$("document").ready(function () {

	$("#pelanggaran").dataTable({
		"searching": true,
		order: [[4, 'desc']]
	});

    //Get a reference to the new datatable
	var table = $('#pelanggaran').DataTable();

    //Take the category filter drop down and append it to the datatables_filter div. 
    //You can use this same idea to move the filter anywhere withing the datatable that you want.
	$("#pelanggaran_filter.dataTables_filter").append($("#categoryFilter"));

    //Get the column index for the Category column to be used in the method below ($.fn.dataTable.ext.search.push)
    //This tells datatables what column to filter on when a user selects a value from the dropdown.
    //It's important that the text used here (Category) is the same for used in the header of the column to filter
	var categoryIndex = 0;
	$("#pelanggaran th").each(function (i) {
		if ($($(this)).html() == "Jenis Pelanggaran") {
			categoryIndex = i; return false;
		}
	});

    //Use the built in datatables API to filter the existing rows by the Category column
	$.fn.dataTable.ext.search.push(
		function (settings, data, dataIndex) {
			var selectedItem = $('#categoryFilter').val()
			var category = data[categoryIndex];
			if (selectedItem === "" || category.includes(selectedItem)) {
				return true;
			}
			return false;
		}
		);

    //Set the change event for the Category Filter dropdown to redraw the datatable each time
    //a user selects a new filter.
	$("#categoryFilter").change(function (e) {
		table.draw();
	});

	table.draw();
});
</script>
