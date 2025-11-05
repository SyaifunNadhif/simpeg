<section class="content-header">
    <h1>Biodata<small>Pegawai</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Biodata</li>
    </ol>
</section>
<?php
	if (isset($_GET['id_peg'])) {
	$id_peg = $_GET['id_peg'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilPeg	=mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
	$peg		= mysql_fetch_array ($tampilPeg);
?>
<section class="content">
    <div class="row">
        <div class="col-md-3">
			<div class="box box-primary">
				<div class="box-body box-profile">
					<a href="home-admin.php?page=form-ganti-foto&id_peg=<?=$peg['id_peg'];?>">
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
					</a><br />
					<h3 class="profile-username text-center"><?php echo $peg['nama']; ?></h3>
					<p class="text-muted text-center"><?php echo $peg['id_peg']; ?></p>
					<ul class="list-group list-group-unbordered">
						<li class="list-group-item">
							<i class="fa fa-phone"></i> <a class="pull-right"><?php echo $peg['telp']; ?></a>
						</li>
						<li class="list-group-item">
							<i class="fa fa-envelope"></i> <a class="pull-right"><?php echo $peg['email']; ?></a>
						</li>
					</ul>
				</div>
			</div>
			<div class="box box-primary">
				<div class="box-header with-border">
					<strong><i class="fa fa-calendar margin-r-5"></i> Schedule</strong>
				</div>
				<div class="box-body">
					<button type="button" class="btn bg-orange btn-sm" data-toggle="modal" data-target="#pensiun">Pensiun</button>
					<button type="button" class="btn bg-purple btn-sm" data-toggle="modal" data-target="#naikpkt">Pangkat</button>
					<button type="button" class="btn bg-navy btn-sm" data-toggle="modal" data-target="#naikgj">Gaji</button>
				</div>
			</div>
			<div class="box box-primary">
				<div class="box-header with-border">
					<strong><i class="fa fa-book margin-r-5"></i> Education</strong>
				</div>
				<div class="box-body">
					<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#bahasa">Bahasa</button>
					<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#pendidikan">Pendidikan</button>
				</div>
			</div>
			<div class="box box-primary">
				<div class="box-header with-border">
					<strong><i class="fa fa-list-ul margin-r-5"></i> Sasaran Kerja Pegawai</strong>
				</div>
				<div class="box-body">
					<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#dp3">SKP</button>
				</div>
			</div>
        </div>
        <div class="col-md-9">
			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<li class="active"><a href="#profile" data-toggle="tab">Profile</a></li>
					<li><a href="#suamiistri" data-toggle="tab">Suami Istri</a></li>
					<li><a href="#anak" data-toggle="tab">Anak</a></li>
					<li><a href="#ortu" data-toggle="tab">Ortu</a></li>
					<li style="float:right"><button onclick=window.open('./pages/report/print-biodata-pegawai.php?id_peg=<?=$id_peg?>','_blank'); class="btn btn-default btn-flat"><i class="fa fa-print"></i> Print</button></li>
					<li style="float:right"><button onclick=location.href="home-admin.php?page=form-view-data-pegawai" class="btn btn-default btn-flat"><i class="fa fa-step-backward"></i> Back</button></li>
				</ul>
				<div class="tab-content">
					<div class="active tab-pane" id="profile">
						<div class="box-body no-padding">
							<table class="col-sm-12 table-condensed">
								<tr>
									<td class="col-sm-3">NIK</td>
									<td class="col-sm-9">: <?php echo $peg['nip']; ?></td>
								</tr>
								<tr>
									<td class="col-sm-3">Nama</td>
									<td class="col-sm-9">: <?php echo $peg['nama']; ?></td>
								</tr>
								<tr>
									<td class="col-sm-3">Tempat, Tanggal Lahir</td>
									<td class="col-sm-9">: <?php echo $peg['tempat_lhr']; ?>, <?php echo $peg['tgl_lhr']; ?></td>
								</tr>
								<tr>
									<td class="col-sm-3">Agama</td>
									<td class="col-sm-9">: <?php echo $peg['agama']; ?></td>
								</tr>
								<tr>
									<td class="col-sm-3">Jenis Kelamin</td>
									<td class="col-sm-9">: <?php echo $peg['jk']; ?></td>
								</tr>
								<tr>
									<td class="col-sm-3">Golongan Darah</td>
									<td class="col-sm-9">: <?php echo $peg['gol_darah']; ?></td>
								</tr>
								<tr>
									<td class="col-sm-3">Status Pernikahan</td>
									<td class="col-sm-9">: <?php echo $peg['status_nikah']; ?></td>
								</tr>
								<tr>
									<td class="col-sm-3">Status Kepegawaian</td>
									<td class="col-sm-9">: <?php echo $peg['status_kepeg']; ?></td>
								</tr>
								<tr>
									<td class="col-sm-3">Alamat</td>
									<td class="col-sm-9">: <?php echo $peg['alamat']; ?></td>
								</tr>
								<tr>
									<td class="col-sm-3">No. Telp</td>
									<td class="col-sm-9">: <?php echo $peg['telp']; ?></td>
								</tr>
								<tr>
									<td class="col-sm-3">Email</td>
									<td class="col-sm-9">: <?php echo $peg['email']; ?></td>
								</tr>
								<tr>
									<td class="col-sm-3">No BPJS Tenaga Kerja</td>
									<td class="col-sm-9">: <?php echo $peg['']; ?></td>
								</tr>								
							</table>
						</div>
					</div>
					<div class="tab-pane" id="suamiistri">
						<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>NIK</th>
									<th>Nama</th>
									<th>TTL</th>
									<th>Pendidikan</th>
									<th>Pekerjaan</th>
									<th>Hubungan</th>
									<th>More</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$tampilSi	=mysql_query("SELECT * FROM tb_suamiistri WHERE id_peg='$id_peg'");
								while($si=mysql_fetch_array($tampilSi)){
							?>	
								<tr>
									<td><?php echo $si['nik'];?></td>
									<td><?php echo $si['nama'];?></td>
									<td><?php echo $si['tmp_lhr'];?>, <?php echo $si['tgl_lhr'];?></td>
									<td><?php echo $si['pendidikan'];?></td>
									<td><?php echo $si['pekerjaan'];?></td>
									<td><?php echo $si['status_hub'];?></td>
									<td class="tools" align="center"><a href="home-admin.php?page=form-edit-data-suami-istri&id_si=<?=$si['id_si'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-suami-istri&id_si=<?=$si['id_si'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
								</tr>
							<?php
								}
							?>
							</tbody>
						</table>
					</div>
					<div class="tab-pane" id="anak">
						<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No</th>
									<th>NIK</th>
									<th>Nama</th>
									<th>TTL</th>
									<th>Pendidikan</th>
									<th>Pekerjaan</th>
									<th>Hubungan</th>
									<th>Anak Ke</th>
									<th>No. BPJS</th>
									<th>More</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$no=0;
								$tampilAnak	=mysql_query("SELECT * FROM tb_anak WHERE id_peg='$id_peg'");
								while($anak=mysql_fetch_array($tampilAnak)){
								$no++;
							?>	
								<tr>
									<td><?=$no?></td>
									<td><?php echo $anak['nik'];?></td>
									<td><?php echo $anak['nama'];?></td>
									<td><?php echo $anak['tmp_lhr'];?>, <?php echo $anak['tgl_lhr'];?></td>
									<td><?php echo $anak['pendidikan'];?></td>
									<td><?php echo $anak['pekerjaan'];?></td>
									<td><?php echo $anak['status_hub'];?></td>
									<td><?php echo $anak['anak_ke'];?></td>
									<td><?php echo $anak['bpjs_anak'];?></td>
									<td class="tools" align="center"><a href="home-admin.php?page=form-edit-data-anak&id_anak=<?=$anak['id_anak'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-anak&id_anak=<?=$anak['id_anak'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
								</tr>
							<?php
								}
							?>
							</tbody>
						</table>
					</div>
					<div class="tab-pane" id="ortu">
						<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>NIK</th>
									<th>Nama</th>
									<th>TTL</th>
									<th>Pendidikan</th>
									<th>Pekerjaan</th>
									<th>Hubungan</th>
									<th>More</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$tampilOrtu	=mysql_query("SELECT * FROM tb_ortu WHERE id_peg='$id_peg'");
								while($ortu=mysql_fetch_array($tampilOrtu)){
							?>	
								<tr>
									<td><?php echo $ortu['nik'];?></td>
									<td><?php echo $ortu['nama'];?></td>
									<td><?php echo $ortu['tmp_lhr'];?>, <?php echo $ortu['tgl_lhr'];?></td>
									<td><?php echo $ortu['pendidikan'];?></td>
									<td><?php echo $ortu['pekerjaan'];?></td>
									<td><?php echo $ortu['status_hub'];?></td>
									<td class="tools" align="center"><a href="home-admin.php?page=form-edit-data-ortu&id_ortu=<?=$ortu['id_ortu'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-ortu&id_ortu=<?=$ortu['id_ortu'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
								</tr>
							<?php
								}
							?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<h5><i class="fa fa-bars margin-r-5"></i>Riwayat</h5>
			<button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#jabatan">Jabatan</button>
			<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#pangkat">Kepangkatan</button>
			<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#hukum">Hukuman</button>
			<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#diklat">Diklat</button>
			<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#harga">Penghargaan</button>
			<button type="button" class="btn bg-purple btn-sm" data-toggle="modal" data-target="#tugas">Penugasan Luar Negeri</button>
			<button type="button" class="btn bg-maroon btn-sm" data-toggle="modal" data-target="#seminar">Seminar</button>
			<button type="button" class="btn bg-olive btn-sm" data-toggle="modal" data-target="#cuti">Cuti</button>
			<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#latjab">Lat Jabatan</button>
			<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#mutasi">Mutasi</button>
			<!-- Modal -->
			<div id="bahasa" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Kemampuan Bahasa</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
								<thead>
									<tr>
										<th>No</th>
										<th>Jenis Bahasa</th>
										<th>Bahasa</th>
										<th>Kemampuan Bicara</th>
										<th>More</th>
									</tr>
								</thead>
								<tbody>
								<?php
									$no=0;
									$tampilBhs	=mysql_query("SELECT * FROM tb_bahasa WHERE id_peg='$id_peg'");
									while($bhs=mysql_fetch_array($tampilBhs)){
									$no++;
								?>	
									<tr>
										<td><?=$no?></td>
										<td><?php echo $bhs['jns_bhs'];?></td>
										<td><?php echo $bhs['bahasa'];?></td>
										<td><?php echo $bhs['kemampuan'];?></td>
										<td class="tools" align="center"><a href="home-admin.php?page=form-edit-data-bahasa&id_bhs=<?=$bhs['id_bhs'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-bahasa&id_bhs=<?=$bhs['id_bhs'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
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
			<div id="pendidikan" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Pendidikan</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>Tingkat</th>
									<th>Nama</th>
									<th>Lokasi</th>
									<th>Jurusan</th>
									<th>No. Ijazah</th>
									<th>Tgl. Ijazah</th>
									<th>Kepala / Rektor</th>
									<th>Status</th>
									<th>More</th>
									<th>Set Sbg Pend Akhir</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$tampilSek	=mysql_query("SELECT * FROM tb_sekolah WHERE id_peg='$id_peg' ORDER BY tgl_ijazah DESC");
								while($sek=mysql_fetch_array($tampilSek)){
							?>	
								<tr>
									<td><?php echo $sek['tingkat'];?></td>
									<td><?php echo $sek['nama_sekolah'];?></td>
									<td><?php echo $sek['lokasi'];?></td>
									<td><?php echo $sek['jurusan'];?></td>
									<td><?php echo $sek['no_ijazah'];?></td>
									<td><?php echo $sek['tgl_ijazah'];?></td>
									<td><?php echo $sek['kepala'];?></td>
									<td><?php 
										if ($sek['status'] ==""){
											echo "-";
										}
										else{
											echo "Pend $sek[status];";
										}
										?>
									</td>
									<td class="tools"><a href="home-admin.php?page=form-edit-data-sekolah&id_sekolah=<?=$sek['id_sekolah'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-sekolah&id_sekolah=<?=$sek['id_sekolah'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>	
									<td class="tools"><a href="home-admin.php?page=set-pendidikan-akhir&id_sekolah=<?=$sek['id_sekolah'];?>&id_peg=<?=$peg['id_peg'];?>" type="button" class="btn bg-orange btn-xs">Setup</a></td>
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
			<div id="jabatan" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Riwayat Jabatan</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No</th>
									<th>Jabatan</th>
									<th>Eselon</th>
									<th>TMT</th>
									<th>Sampai</th>
									<th>Status</th>
									<th>More</th>
									<th>Set Sbg Jabatan Sekarang</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$no=0;
								$tampilJab	=mysql_query("SELECT * FROM tb_jabatan WHERE id_peg='$id_peg' ORDER BY tmt_jabatan");
								while($jab=mysql_fetch_array($tampilJab)){
								$no++;
							?>	
								<tr>
									<td><?=$no?></td>
									<td><?php echo $jab['jabatan'];?></td>
									<td><?php echo $jab['eselon'];?></td>
									<td><?php echo $jab['tmt_jabatan'];?></td>
									<td><?php echo $jab['sampai_tgl'];?></td>
									<td><?php 
										if ($jab['status_jab'] ==""){
											echo "-";
										}
										else{
											echo "$jab[status_jab]";
										}
										?></td>
									<td class="tools"><a href="home-admin.php?page=form-edit-data-jabatan&id_jab=<?=$jab['id_jab'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-jabatan&id_jab=<?=$jab['id_jab'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
									<td class="tools"><a href="home-admin.php?page=set-jabatan-sekarang&id_jab=<?=$jab['id_jab'];?>&id_peg=<?=$peg['id_peg'];?>" type="button" class="btn bg-orange btn-xs">Setup</a></td>
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
			<div id="pangkat" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Riwayat Pangkat</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th rowspan="2">No</th>
									<th rowspan="2">Pangkat</th>
									<th rowspan="2">Gol</th>
									<th rowspan="2">Jenis</th>
									<th rowspan="2">TMT</th>
									<th colspan="3">Surat Keputusan</th>
									<th rowspan="2">Status</th>
									<th rowspan="2">More</th>
									<th rowspan="2">Set Sbg Pangkat Sekarang</th>
								</tr>
								<tr>
									<th>Pejabat Pengesah</th>
									<th>Nomor</th>
									<th>Tgl Pengesahan</th>																		
								</tr>								
							</thead>
							<tbody>
							<?php
								$no=0;
								$tampilPan	=mysql_query("SELECT * FROM tb_pangkat WHERE id_peg='$id_peg' ORDER BY tgl_sk");
								while($pangkat=mysql_fetch_array($tampilPan)){
								$no++;
							?>	
								<tr>
									<td><?=$no?></td>
									<td><?php echo $pangkat['pangkat'];?></td>
									<td><?php echo $pangkat['gol'];?></td>
									<td><?php echo $pangkat['jns_pangkat'];?></td>
									<td><?php echo $pangkat['tmt_pangkat'];?></td>
									<td><?php echo $pangkat['pejabat_sk'];?></td>
									<td><?php echo $pangkat['no_sk'];?></td>
									<td><?php echo $pangkat['tgl_sk'];?></td>
									<td><?php 
										if ($pangkat['status_pan'] ==""){
											echo "-";
										}
										else{
											echo "$pangkat[status_pan]";
										}
										?></td>
									<td class="tools"><a href="home-admin.php?page=form-edit-data-pangkat&id_pangkat=<?=$pangkat['id_pangkat'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-pangkat&id_pangkat=<?=$pangkat['id_pangkat'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
									<td class="tools"><a href="home-admin.php?page=set-pangkat-sekarang&id_pangkat=<?=$pangkat['id_pangkat'];?>&gol=<?=$pangkat['gol'];?>&id_peg=<?=$peg['id_peg'];?>" type="button" class="btn bg-orange btn-xs">Setup</a></td>
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
			<div id="hukum" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Riwayat Hukuman</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th rowspan="2">No</th>
									<th rowspan="2">Jenis Hukuman</th>
									<th colspan="3">Surat Keputusan</th>
									<th colspan="4">Pemulihan</th>
								</tr>
								<tr>
									<th scope="col">Pejabat Pengesah</th>
									<th scope="col">Nomor</th>
									<th scope="col">Tgl Pengesahan</th>
									<th scope="col">Pejabat Pemulih</th>
									<th scope="col">Nomor Pemulihan</th>
									<th scope="col">Tgl Pemulihan</th>											
									<th scope="col">More</th>									
								</tr>								
							</thead>
							<tbody>
							<?php
								$no=0;
								$tampilHuk	=mysql_query("SELECT * FROM tb_hukuman WHERE id_peg='$id_peg' ORDER BY tgl_sk");
								while($hukum=mysql_fetch_array($tampilHuk)){
								$no++;
							?>	
								<tr>
									<td><?=$no?></td>
									<td><?php echo $hukum['hukuman'];?></td>
									<td><?php echo $hukum['pejabat_sk'];?></td>
									<td><?php echo $hukum['no_sk'];?></td>
									<td><?php echo $hukum['tgl_sk'];?></td>
									<td><?php echo $hukum['pejabat_pulih'];?></td>
									<td><?php echo $hukum['no_pulih'];?></td>
									<td><?php echo $hukum['tgl_pulih'];?></td>
									<td class="tools"><a href="home-admin.php?page=form-edit-data-hukuman&id_hukum=<?=$hukum['id_hukum'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-hukuman&id_hukum=<?=$hukum['id_hukum'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
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
			<div id="diklat" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Riwayat Diklat</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No</th>
									<th>Nama Diklat</th>
									<th>Jumlah Jam</th>
									<th>Penyelenggara</th>
									<th>Tempat</th>
									<th>Angkatan</th>
									<th>Tahun</th>
									<th>No STTPP</th>
									<th>Tgl STTPP</th>
									<th>More</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$no=0;
								$tampilDik	=mysql_query("SELECT * FROM tb_diklat WHERE id_peg='$id_peg' ORDER BY tahun");
								while($dik=mysql_fetch_array($tampilDik)){
								$no++;
							?>	
								<tr>
									<td><?=$no?></td>
									<td><?php echo $dik['diklat'];?></td>
									<td><?php echo $dik['jml_jam'];?></td>
									<td><?php echo $dik['penyelenggara'];?></td>
									<td><?php echo $dik['tempat'];?></td>
									<td><?php echo $dik['angkatan'];?></td>
									<td><?php echo $dik['tahun'];?></td>
									<td><?php echo $dik['no_sttpp'];?></td>
									<td><?php echo $dik['tgl_sttpp'];?></td>
									<td class="tools"><a href="home-admin.php?page=form-edit-data-diklat&id_diklat=<?=$dik['id_diklat'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-diklat&id_diklat=<?=$dik['id_diklat'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
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
			<div id="harga" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Riwayat Penghargaan</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No</th>
									<th>Nama Penghargaan</th>
									<th>Tahun</th>
									<th>Negara / Instansi Pemberi</th>
									<th>More</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$no=0;
								$tampilHar	=mysql_query("SELECT * FROM tb_penghargaan WHERE id_peg='$id_peg' ORDER BY tahun");
								while($har=mysql_fetch_array($tampilHar)){
								$no++;
							?>	
								<tr>
									<td><?=$no?></td>
									<td><?php echo $har['penghargaan'];?></td>
									<td><?php echo $har['tahun'];?></td>
									<td><?php echo $har['pemberi'];?></td>
									<td class="tools"><a href="home-admin.php?page=form-edit-data-penghargaan&id_penghargaan=<?=$har['id_penghargaan'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-penghargaan&id_penghargaan=<?=$har['id_penghargaan'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
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
			<div id="tugas" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Riwayat Penugasan Luar Negeri</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No</th>
									<th>Negara Tujuan</th>
									<th>Tahun</th>
									<th>Lama Penugasan</th>
									<th>Alasan Penugasan</th>
									<th>More</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$no=0;
								$tampilTug	=mysql_query("SELECT * FROM tb_penugasan WHERE id_peg='$id_peg' ORDER BY tahun");
								while($tug=mysql_fetch_array($tampilTug)){
								$no++;
							?>	
								<tr>
									<td><?=$no?></td>
									<td><?php echo $tug['tujuan'];?></td>
									<td><?php echo $tug['tahun'];?></td>
									<td><?php echo $tug['lama'];?> Hari</td>
									<td><?php echo $tug['alasan'];?></td>
									<td class="tools"><a href="home-admin.php?page=form-edit-data-penugasan&id_penugasan=<?=$tug['id_penugasan'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-penugasan&id_penugasan=<?=$tug['id_penugasan'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
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
			<div id="seminar" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Riwayat Seminar</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No</th>
									<th>Nama Seminar</th>
									<th>Tempat</th>
									<th>Penyelenggara</th>
									<th>Tanggal Mulai</th>
									<th>Tanggal Selesai</th>
									<th>No. Piagam</th>
									<th>Tanggal Piagam</th>
									<th>More</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$no=0;
								$tampilSem	=mysql_query("SELECT * FROM tb_seminar WHERE id_peg='$id_peg' ORDER BY tgl_selesai");
								while($sem=mysql_fetch_array($tampilSem)){
								$no++;
							?>	
								<tr>
									<td><?=$no?></td>
									<td><?php echo $sem['seminar'];?></td>
									<td><?php echo $sem['tempat'];?></td>
									<td><?php echo $sem['penyelenggara'];?></td>
									<td><?php echo $sem['tgl_mulai'];?></td>
									<td><?php echo $sem['tgl_selesai'];?></td>
									<td><?php echo $sem['no_piagam'];?></td>
									<td><?php echo $sem['tgl_piagam'];?></td>
									<td class="tools"><a href="home-admin.php?page=form-edit-data-seminar&id_seminar=<?=$sem['id_seminar'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-seminar&id_seminar=<?=$sem['id_seminar'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
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
			<div id="cuti" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Riwayat Cuti</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No</th>
									<th>Jenis Cuti</th>
									<th>No. Surat Cuti</th>
									<th>Tgl Surat Cuti</th>
									<th>Tanggal Mulai</th>
									<th>Tanggal Selesai</th>
									<th>Keterangan</th>
									<th>More</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$no=0;
								$tampilCut	=mysql_query("SELECT * FROM tb_cuti WHERE id_peg='$id_peg' ORDER BY tgl_suratcuti");
								while($cut=mysql_fetch_array($tampilCut)){
								$no++;
							?>	
								<tr>
									<td><?=$no?></td>
									<td><?php echo $cut['jns_cuti'];?></td>
									<td><?php echo $cut['no_suratcuti'];?></td>
									<td><?php echo $cut['tgl_suratcuti'];?></td>
									<td><?php echo $cut['tgl_mulai'];?></td>
									<td><?php echo $cut['tgl_selesai'];?></td>
									<td><?php echo $cut['ket'];?></td>
									<td class="tools"><a href="home-admin.php?page=form-edit-data-cuti&id_cuti=<?=$cut['id_cuti'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-cuti&id_cuti=<?=$cut['id_cuti'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
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
			<div id="latjab" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Riwayat Pelatihan Jabatan</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No</th>
									<th>Nama Pelatih</th>
									<th>Tahun</th>
									<th>Jumlah Jam</th>
									<th>More</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$no=0;
								$tampilLatjab	=mysql_query("SELECT * FROM tb_lat_jabatan WHERE id_peg='$id_peg' ORDER BY tahun_lat");
								while($latjab=mysql_fetch_array($tampilLatjab)){
								$no++;
							?>	
								<tr>
									<td><?=$no?></td>
									<td><?php echo $latjab['nama_pelatih'];?></td>
									<td><?php echo $latjab['tahun_lat'];?></td>
									<td><?php echo $latjab['jml_jam'];?></td>
									<td class="tools"><a href="home-admin.php?page=form-edit-data-lat-jabatan&id_lat_jabatan=<?=$latjab['id_lat_jabatan'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-lat-jabatan&id_lat_jabatan=<?=$latjab['id_lat_jabatan'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
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
			<div id="mutasi" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Riwayat Mutasi</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No</th>
									<th>Jenis Mutasi</th>
									<th>Tanggal</th>
									<th>No. SK</th>
									<th>More</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$no=0;
								$tampilMut	=mysql_query("SELECT * FROM tb_mutasi WHERE id_peg='$id_peg'");
								while($mut=mysql_fetch_array($tampilMut)){
								$no++;
							?>	
								<tr>
									<td><?=$no?></td>
									<td><?php echo $mut['jns_mutasi'];?></td>
									<td><?php echo $mut['tgl_mutasi'];?></td>
									<td><?php echo $mut['no_mutasi'];?></td>
									<td class="tools"><a href="home-admin.php?page=form-edit-data-mutasi&id_mutasi=<?=$mut['id_mutasi'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-mutasi&id_mutasi=<?=$mut['id_mutasi'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
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
			<div id="dp3" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Sasaran Kerja Pegawai</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th rowspan="2">No</th>
									<th colspan="2">Periode Penilaian</th>
									<th colspan="2">Penilai</th>
									<th rowspan="2">Nilai Total</th>
									<th rowspan="2">Rata-Rata</th>
									<th rowspan="2">Mutu</th>
								</tr>
								<tr>
									<th scope="col">Awal</th>
									<th scope="col">Akhir</th>
									<th scope="col">Pejabat</th>
									<th scope="col">Atasan Pejabat</th>
									<th>More</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$no=0;
								$tampilDp3	=mysql_query("SELECT * FROM tb_dp3 WHERE id_peg='$id_peg' ORDER BY periode_akhir");
								while($dp3=mysql_fetch_array($tampilDp3)){
									$id_dp3	=$dp3['id_dp3'];									
								$no++;
							?>	
								<tr>
									<td><?=$no?></td>
									<td><?php echo $dp3['periode_awal'];?></td>
									<td><?php echo $dp3['periode_akhir'];?></td>
									<td><?php echo $dp3['pejabat_penilai'];?></td>
									<td><?php echo $dp3['atasan_pejabat_penilai'];?></td>
									<td><?php
										$nilai	=mysql_query("SELECT * FROM tb_dp3 WHERE id_dp3='$id_dp3'");
											while($ndp3=mysql_fetch_array($nilai)){
												$kesetiaan		=$ndp3['nilai_kesetiaan'];
												$prestasi		=$ndp3['nilai_prestasi'];
												$tgjwb			=$ndp3['nilai_tgjwb'];
												$ketaatan		=$ndp3['nilai_ketaatan'];
												$kejujuran		=$ndp3['nilai_kejujuran'];
												$kerjasama		=$ndp3['nilai_kerjasama'];
												$prakarsa		=$ndp3['nilai_prakarsa'];
												$kepemimpinan	=$ndp3['nilai_kepemimpinan'];
											}
											$jml_nilai	=$kesetiaan+$prestasi+$tgjwb+$ketaatan+$kejujuran+$kerjasama+$prakarsa+$kepemimpinan;
											$rata		=$jml_nilai/8;
										echo $jml_nilai;
										?>
									</td>
									<td><?=$rata?></td>
									<td><?php echo $dp3['hasil_penilaian'];?></td>
									<td class="tools"><a href="home-admin.php?page=view-detail-data-dp3&id_dp3=<?=$dp3['id_dp3'];?>" type="button" class="btn bg-orange btn-xs">Detail</a></td>
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
			<div id="pensiun" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Tanggal Pensiun</h4>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>Tanggal Kelahiran</th>
									<th>Tanggal Jatuh Tempo Pensiun</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$tampilPens	=mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
								$pens	=mysql_fetch_array($tampilPens);
									$lahir	=$pens['tgl_lhr'];
									$pensiun=$pens['tgl_pensiun'];							
							?>	
								<tr>
									<td><?=$lahir?></td>
									<td><?=$pensiun?></td>
								</tr>
							</tbody>
						</table>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<div id="naikpkt" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Periode Kenaikan Pangkat</h4>
						</div>
						<div class="modal-body">
							<?php
								$tampilNp	=mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
								$np	=mysql_fetch_array($tampilNp);
									$naikpangkat	=$np['tgl_naikpangkat'];																	
									$naikpensiun	=$np['tgl_pensiun'];																	
							?>
							<table class="table table-bordered table-striped">
							<thead>								
								<tr>
									<th>Periode</th>
									<th>Tanggal Kenaikan Pangkat</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$begin = new DateTime($naikpangkat);
								$end = new DateTime($naikpensiun);
								$no=0;
								for($i = $begin; $begin <= $end; $i->modify('+4 year')){	
								$no++;
							?>
								<tr>
									<td>Periode <?=$no?></td>
									<td><?php echo $i->format("Y-m-d");?></td>
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
			<div id="naikgj" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Periode Kenaikan Gaji</h4>
						</div>
						<div class="modal-body">
							<?php
								$tampilGj	=mysql_query("SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
								$ng	=mysql_fetch_array($tampilGj);
									$naikgaji	=$ng['tgl_naikgaji'];																	
									$naikpens	=$ng['tgl_pensiun'];																	
							?>
							<table class="table table-bordered table-striped">
							<thead>								
								<tr>
									<th>Periode</th>
									<th>Tanggal Kenaikan Gaji</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$beging = new DateTime($naikgaji);
								$endg = new DateTime($naikpens);
								$nog=0;
								for($ig = $beging; $beging <= $endg; $ig->modify('+2 year')){	
								$nog++;
							?>
								<tr>
									<td>Periode <?=$nog?></td>
									<td><?php echo $ig->format("Y-m-d");?></td>
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
		</div>
    </div>
</section>