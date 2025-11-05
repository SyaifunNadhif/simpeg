<!-- Content Header (Page header) -->
<section class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1><small>Detail</small> Data Diklat</h1>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
					<li class="breadcrumb-item active">Detail Diklat</li>
				</ol>
			</div>
		</div>
	</div><!-- /.container-fluid -->
</section>
<?php
if (isset($_GET['diklat'])) {
	$diklat = $_GET['diklat'];
}
else {
	die ("Error. No Kode Selected! ");	
}
include "dist/koneksi.php";
$tampilPeg	=mysql_query("SELECT * FROM tb_diklat WHERE diklat='$diklat'");
$peg				=mysql_fetch_array ($tampilPeg);
$tampilPeserta 	= mysql_query("SELECT a.id_peg, b.nama, kode_jabatan, (select jabatan from tb_ref_jabatan where kode_jabatan=c.kode_jabatan) jabatan, (SELECT nama_kantor FROM tb_kantor WHERE kode_kantor_detail = c.unit_kerja) unit_kerja FROM tb_diklat a, tb_pegawai b, tb_jabatan c WHERE a.id_peg=b.id_peg AND b.id_peg=c.id_peg AND status_jab='Aktif' AND diklat='$diklat'");
?>
<!-- Main content -->
<section class="content">
	<div class="container-fluid">
		<div class="row">
			<!-- /.col -->
			<div class="col-md-12">
				<div class="card card-primary card-tabs">
					<div class="card-header p-0 pt-1">
						<ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
							<li class="nav-item"><a class="nav-link active" href="#profile" data-toggle="tab">Detail</a></li>
						</ul>
					</div>
					<!-- /.card-header -->
					<div class="card-body">	
						<div class="tab-content">
							<div class="active tab-pane" id="profile">
								<div class="box-body no-padding">
									<table class="col-sm-12 table-condensed">
										<tr>
											<td class="col-sm-1">Nama Kegiatan</td>
											<td class="col-sm-12">: <?php echo $peg['diklat']; ?></td>
										</tr>
										<tr>
											<td class="col-sm-3">Nama Penyelenggara</td>
											<td class="col-sm-9">: <?php echo $peg['penyelenggara']; ?></td>
										</tr>
										<tr>
											<td class="col-sm-3">Tempat Kegiatan</td>
											<td class="col-sm-9">: <?php echo $peg['tempat']; ?></td>
										</tr>
										<tr>
											<td class="col-sm-3">Tahun Pelaksanaan</td>
											<td class="col-sm-9">: <?php echo $peg['tahun']; ?></td>
										</tr>
										<tr>
											<td class="col-sm-3">Daftar Peserta</td>
											<td class="col-sm-9">:</td>
										</tr>								
									</table>
								</div>
							</div>
						</div>	
						<br>										
						<table id="diklat" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>ID Pegawai</th>
									<th>Nama Peserta</th>
									<th>Jabatan</th>
									<th>Unit Kerja</th>
								</tr>
							</thead>
							<tbody>
								<?php
								while($peserta=mysql_fetch_array ($tampilPeserta)){
									?>	
									<tr>
										<td><?php echo $peserta['id_peg'];?></td>
										<td><?php echo $peserta['nama'];?></td>
										<td><?php echo $peserta['jabatan'];?></td>
										<td><?php echo $peserta['unit_kerja'];?></td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
				<div>
					<a href="home-admin.php?page=form-view-data-diklat" type="button" class="btn btn-info float-sm-right"><i class="fa fa-step-backward"></i> Back</a><br /><br />
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
    $("#diklat").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#diklat_wrapper .col-md-6:eq(0)');
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