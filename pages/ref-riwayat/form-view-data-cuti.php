

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Data<small> Historis Cuti Pegawai</small></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
          <li class="breadcrumb-item active">Data Historis Cuti Pegawai</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<?php
	include "dist/koneksi.php";
	$tampilCuti=mysql_query("SELECT tb_cuti.*, (SELECT nama FROM tb_pegawai WHERE id_peg=tb_cuti.id_peg) nama_peg FROM tb_cuti");
?>
<section class="content">
    <div class="row">
        <div class="col-md-12">
			<div class="box box-primary">				
				<div class="box-body">							
					<a href="home-admin.php?page=form-master-data-cuti" type="button" class="btn btn-primary"><i class="fa fa-plus"></i> Tambah Data Cuti Pegawai</a><a href="home-admin.php" type="button" class="btn btn-info float-sm-right"><i class="fa fa-step-backward"></i> Back</a><br /><br />					
					<table id="Cutiatan" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>ID Pegawai</th>
								<th>Nama Pegawai</th>
								<th>Jenis Cuti</th>
								<th>No Persetujuan Cuti</th>
								<th>Tgl Persetujuan Cuti</th>
								<th>Tgl Mulai Cuti</th>
								<th>Tgl Berakhir Cuti</th>
								<th>Keterangan</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
						<?php
							while($Cuti=mysql_fetch_array($tampilCuti)){
						?>	
							<tr>
								<td><?php echo $Cuti['id_peg'];?></td>
								<td><?php echo $Cuti['nama_peg'];?></td>
								<td><?php echo $Cuti['jns_cuti'];?></td>
								<td><?php echo $Cuti['no_suratcuti'];?></td>
								<td><?php echo $Cuti['tgl_suratcuti'];?></td>
								<td><?php echo $Cuti['tgl_mulai'];?></td>
								<td><?php echo $Cuti['tgl_selesai'];?></td>
								<td><?php echo $Cuti['ket'];?></td>
								<td class="tools" align="center">
									<a class="btn btn-primary" href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?=$Cuti['id_peg'];?>" title="detail"><i class="fa fa-folder-open fa-lg"></i></a>
									<a class="btn btn-warning" href="home-admin.php?page=form-edit-data-Cutiatan&id_Cuti=<?=$Cuti['id_Cuti'];?>" title="edit"><i class="fa fa-edit fa-lg"></i></a></td>
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
<div id="modCutiatan" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h5 class="modal-title">Data Cutiatan</h5>
			</div>
			<div class="modal-body table-responsive p-0">
				<table class="table table-hover text-nowrap table-bordered" id="modalCutiatan">
					<thead>
						<tr>
							<th>No</th>
							<th>ID Pegawai</th>
							<th>Nama Pegawai</th>
							<th>Nama Cutiatan</th>
							<th>Unit Kerja</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$no=0;
						$tampilCuti	=mysql_query("SELECT
								id_Cuti,
								id_peg,
								(SELECT nama FROM tb_pegawai WHERE id_peg=tb_Cutiatan.id_peg) nama, Cutiatan,
								(SELECT nama_kantor FROM tb_kantor WHERE kode_kantor_detail=tb_Cutiatan.unit_kerja) kantor
							FROM
								tb_Cutiatan
							WHERE
								id_peg NOT IN (SELECT id_peg FROM tb_pegawai)");
						while($Cuti=mysql_fetch_array($tampilCuti)){
							$no++;
							?>	
							<tr>
								<td><?=$no?></td>
								<td><?php echo $Cuti['id_peg'];?></td>
								<td><?php echo $Cuti['nama'];?></td>
								<td><?php echo $Cuti['Cutiatan'];?></td>
								<td><?php echo $Cuti['kantor'];?></td>
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
    $("#Cutiatan").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#Cutiatan_wrapper .col-md-6:eq(0)');

    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });

    $('#modalCutiatan').DataTable({
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
