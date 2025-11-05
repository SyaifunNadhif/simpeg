<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Data<small> Mutasi Pegawai</small></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
          <li class="breadcrumb-item active">Data Mutasi Pegawai</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<?php
	include "dist/koneksi.php";
	include "dist/library.php";
	$tampilMutasi=mysql_query("SELECT tb_mutasi.*,
	(SELECT nama FROM tb_pegawai WHERE id_peg=tb_mutasi.id_peg) nama_peg
FROM tb_mutasi WHERE YEAR(tgl_mutasi)=year(now())");
?>
<section class="content">
    <div class="row">
        <div class="col-md-12">
			<div class="box box-primary">				
				<div class="box-body">							
					<a href="home-admin.php?page=form-master-data-mutasi" type="button" class="btn btn-primary"><i class="fa fa-plus"></i> Tambah Data Mutasi Pegawai</a><a href="home-admin.php" type="button" class="btn btn-info float-sm-right"><i class="fa fa-step-backward"></i> Back</a><br /><br />					
					<table id="mutasi" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>Nama Pegawai</th>
								<th>Jabatan</th>
								<th>Jenis Mutasi</th>
								<th>Tanggal SK Mutasi</th>
								<th>No SK</th>
								<th>TMT</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
						<?php
							while($Mutasi=mysql_fetch_array($tampilMutasi)){
						?>	
							<tr>
								<td><?php echo $Mutasi['nama_peg'];?></td>
								<td><?php echo $Mutasi['jabatan'];?></td>
								<td><?php echo $Mutasi['jns_mutasi'];?></td>
								<td><?php echo Indonesia2Tgl($Mutasi['tgl_mutasi']);?></td>
								<td><?php echo $Mutasi['no_mutasi'];?></td>
								<td><?php echo Indonesia2Tgl($Mutasi['tmt']);?></td>
								<td class="tools" align="center">
									<a class="btn-sm btn-primary" href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?=$Mutasi['id_peg'];?>" title="detail"><i class="fa fa-folder-open fa-lg"></i></a>
									<a class="btn-sm btn-warning" href="home-admin.php?page=form-edit-data-mutasi&id_mutasi=<?=$Mutasi['id_mutasi'];?>" title="edit"><i class="fa fa-edit fa-lg"></i></a></td>
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
  $(function () {
    $("#mutasi").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
      "columnDefs": [ { type: 'date', 'targets': [5] } ],
      "order": [[5, 'desc']]
    }).buttons().container().appendTo('#anak_wrapper .col-md-6:eq(0)');
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
  });
</script>
