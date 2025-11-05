<section class="content-header">
    <h1>Detail<small>Sasaran Kerja Pegawai</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">SKP</li>
    </ol>
</section>
<?php
	if (isset($_GET['id_dp3'])) {
	$id_dp3 = $_GET['id_dp3'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilDp3	=mysql_query("SELECT * FROM tb_dp3 WHERE id_dp3='$id_dp3'");
	$dp3		= mysql_fetch_array ($tampilDp3);
		$id_peg				=$dp3['id_peg'];
		$kesetiaan		=$dp3['nilai_kesetiaan'];
		$prestasi			=$dp3['nilai_prestasi'];
		$tgjwb				=$dp3['nilai_tgjwb'];
		$ketaatan			=$dp3['nilai_ketaatan'];
		$kejujuran		=$dp3['nilai_kejujuran'];
		$kerjasama		=$dp3['nilai_kerjasama'];
		$prakarsa			=$dp3['nilai_prakarsa'];
		$kepemimpinan	=$dp3['nilai_kepemimpinan'];
		$jml_nilai		=$kesetiaan+$prestasi+$tgjwb+$ketaatan+$kejujuran+$kerjasama+$prakarsa+$kepemimpinan;
		$rata					=$jml_nilai/8;
	
	$tampilPeg	=mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
	$peg		= mysql_fetch_array ($tampilPeg);
		$nip	= $peg['nip'];
?>
<section class="content">
    <div class="box box-primary">
		<div class="box-body">
			<div class="row">
				<div class="col-md-12">
					<div class="box-body">
						<div class="col-md-3">																									
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
						?><br />
						<h4 class="profile-username text-center"><?php echo $peg['nama']; ?></h4>
						<p class="text-muted text-center"><?php echo $peg['nip']; ?></p>
						</div>
						<div class="col-md-9">
							<div class="box-body no-padding">
								<table class="col-sm-12 table-condensed">
									<tr>
										<td class="col-sm-3">NIP</td>
										<td class="col-sm-9">: <b><?php echo $peg['nip']; ?></b></td>
									</tr>
									<tr>
										<td class="col-sm-3">Nama</td>
										<td class="col-sm-9">: <b><?php echo $peg['nama']; ?></b></td>
									</tr>
									<tr>
										<td class="col-sm-3">Tempat, Tanggal Lahir</td>
										<td class="col-sm-9">: <?php echo $peg['tempat_lhr']; ?>, <?php echo $peg['tgl_lhr']; ?></td>
									</tr>
									<tr>
										<td class="col-sm-3">No. Telp</td>
										<td class="col-sm-9">: <?php echo $peg['telp']; ?></td>
									</tr>
									<tr>
										<td class="col-sm-3">Email</td>
										<td class="col-sm-9">: <?php echo $peg['email']; ?></td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="box-body">
						<div class="col-md-3">
							<div align="center">
								<a type="button" href="home-admin.php?page=form-edit-data-dp3&id_dp3=<?=$id_dp3?>" class="btn btn-warning"><i class="fa fa-edit"></i> Edit</a>
								<a type="button" href="home-admin.php?page=delete-data-dp3&id_dp3=<?=$id_dp3?>" class="btn btn-default"><i class="fa fa-trash-o"></i> Hapus</a>
							</div>
						</div>
						<div class="col-md-9">
							<div class="box-body no-padding">
								<table class="col-sm-12 table-condensed">
									<tr>
										<td class="col-sm-3"><b>Periode Penilaian</b></td>
										<td class="col-sm-9">: <b><?php echo $dp3['periode_awal']; ?> sampai <?php echo $dp3['periode_akhir']; ?></b></td>
									</tr>
									<tr>
										<td class="col-sm-3"><b>Hasil Penilaian</b></td>
										<td class="col-sm-9">: <b><?php echo $dp3['hasil_penilaian']; ?></b></td>
									</tr>
									<tr>
										<td class="col-sm-3"><b>Pejabat Penilai</b></td>
										<td class="col-sm-9">: <b><?php echo $dp3['pejabat_penilai']; ?></b></td>
									</tr>
									<tr>
										<td class="col-sm-3"><b>Atasan Pejabat Penilai</b></td>
										<td class="col-sm-9">: <b><?php echo $dp3['atasan_pejabat_penilai']; ?></b></td>
									</tr>
								</table>
							</div><br /><br />
							<p class="description"><strong>Penilaian</strong></p>
							<table class="table table-bordered table-striped">
								<thead>
									<tr>
										<th>No</th>
										<th>Aspek Penilaian SKP</th>
										<th>Nilai Perolehan</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>1</td>
										<td>Kesetiaan</td>
										<td><?php echo $dp3['nilai_kesetiaan'];?></td>
									</tr>
									<tr>
										<td>2</td>
										<td>Prestasi</td>
										<td><?php echo $dp3['nilai_prestasi'];?></td>
									</tr>
									<tr>
										<td>3</td>
										<td>Tanggung Jawab</td>
										<td><?php echo $dp3['nilai_tgjwb'];?></td>
									</tr>
									<tr>
										<td>4</td>
										<td>Ketaatan</td>
										<td><?php echo $dp3['nilai_ketaatan'];?></td>
									</tr>
									<tr>
										<td>5</td>
										<td>Kejujuran</td>
										<td><?php echo $dp3['nilai_kejujuran'];?></td>
									</tr>
									<tr>
										<td>6</td>
										<td>Kerjasama</td>
										<td><?php echo $dp3['nilai_kerjasama'];?></td>
									</tr>
									<tr>
										<td>7</td>
										<td>Prakarsa</td>
										<td><?php echo $dp3['nilai_prakarsa'];?></td>
									</tr>
									<tr>
										<td>8</td>
										<td>Kepemimpinan</td>
										<td><?php echo $dp3['nilai_kepemimpinan'];?></td>
									</tr>
								</tbody>
								<tfoot>
									<tr>
									  <th colspan="2">Nilai Total</th>
									  <th><?=$jml_nilai?></th>
									</tr>
									<tr>
									  <th colspan="2">Rata-rata</th>
									  <th><?=$rata?></th>
									</tr>
								</tfoot>
							</table>
							<a type="button" href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?=$id_peg?>" class="btn btn-danger"><i class="fa fa-step-backward"></i> Back</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>