<section class="content-header">
    <h1>Data<small>Pegawai</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Pegawai</li>
    </ol>
</section>
<?php
	include "dist/koneksi.php";
	$tampilPeg=mysql_query("SELECT * FROM tb_pegawai");
?>
<section class="content">
    <div class="row">
        <div class="col-md-12">
			<div class="box box-primary">				
				<div class="box-body">							
					<a href="home-admin.php?page=form-master-data-pegawai" type="button" class="btn btn-primary"><i class="fa fa-plus"></i> Add Pegawai</a><br /><br />					
					<table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>Foto</th>
								<th>ID Pegawai</th>
								<th>Nama</th>
								<th>Tempat, Tgl Lahir</th>
								<th>Jenis Kelamin</th>
								<th>No. Telp</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
						<?php
							while($peg=mysql_fetch_array($tampilPeg)){
						?>	
							<tr>
								<td><a href="home-admin.php?page=form-ganti-foto&id_peg=<?=$peg['id_peg'];?>">
									<?php
									if (empty($peg['foto']))
										if ($peg['jk'] == "Laki-laki"){
											echo "<img class='profile-user-img img-responsive' src='pages/asset/foto/no-foto-male.png' title='$peg[nip]'>";
										}
										else{
											echo "<img class='profile-user-img img-responsive' src='pages/asset/foto/no-foto-female.png' title='$peg[nip]'>";
										}
										else
										echo "<img class='profile-user-img img-responsive' src='pages/asset/foto/$peg[foto]' title='$peg[nip]'>";
									?>
								</a></td>
								<td><a href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?=$peg['id_peg'];?>" title="detail"><?php echo $peg['id_peg'];?></a></td>
								<td><?php echo $peg['nama'];?></td>
								<td><?php echo $peg['tempat_lhr'];?>, <?php echo $peg['tgl_lhr'];?></td>
								<td><?php echo $peg['jk'];?></td>
								<td><?php echo $peg['telp'];?></td>
								<td class="tools" align="center"><a href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?=$peg['id_peg'];?>" title="detail"><i class="fa fa-folder-open"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=form-edit-data-pegawai&id_peg=<?=$peg['id_peg'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-pegawai&id_peg=<?php echo $peg['id_peg'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
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