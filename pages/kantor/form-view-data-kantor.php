<section class="content-header">
	<h1>Data<small>Kantor</small></h1>
	<ol class="breadcrumb">
		<li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
		<li class="active">Kantor</li>
	</ol>
</section>
<?php
include "dist/koneksi.php";
$tampilOffice=mysql_query("SELECT
	a.*,
	(select count(id_peg) peg from tb_jabatan WHERE unit_kerja=a.kode_kantor_detail) peg
FROM
	tb_kantor a
WHERE
	(
SELECT RIGHT
	( kode_kantor_detail, 3 )= '000')");
?>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">				
				<div class="box-body">							
					<a href="home-admin.php?page=form-master-data-pegawai" type="button" class="btn btn-primary"><i class="fa fa-plus"></i> Tambah Data Kantor</a><a href="home-admin.php" type="button" class="btn btn-default pull-right"><i class="fa fa-step-backward"></i> Back</a><br /><br />					
					<table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>Kode Kantor</th>
								<th>Nama Kantor</th>
								<th>Alamat</th>
								<th>Jumlah Pegawai</th>
								<th>Status Kantor</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							while($office=mysql_fetch_array($tampilOffice)){
								?>	
								<tr>
									<td><a href="home-admin.php?page=view-detail-data-kantor&kode_kantor_detail=<?=$office['kode_kantor_detail'];?>" title="detail"><?php echo $office['kode_cabang'];?></a></td>
									<td><?php echo $office['nama_kantor'];?></td>
									<td><?php echo $office['alamat'];?></td>
									<td align="center"><?php echo $office['peg'];?></td>
									<td><?php echo $office['status_kantor'];?></td>
									<td class="tools" align="center"><a href="home-admin.php?page=view-detail-data-kantor&kode_kantor_detail=<?=$office['kode_kantor_detail'];?>" title="detail"><i class="fa fa-folder-open"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=form-edit-data-kantor&kode_kantor_detail=<?=$office['kode_kantor_detail'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-kantor&kode_kantor_detail=<?php echo $office['kode_kantor_detail'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
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
	<script>
		$(function () {
			$("#example1").DataTable();
			$('#example2').DataTable({
				"paging": true,
				"lengthChange": false,
				"searching": false,
				"ordering": true,
				"info": true,
				"autoWidth": false
			});
		});
	</script>