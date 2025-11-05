<section class="content-header">
    <h1>Edit<small>Data SKP</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Edit Data</li>
    </ol>
</section>
<div class="register-box">
<?php
	if (isset($_GET['id_dp3'])) {
	$id_dp3 = $_GET['id_dp3'];
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	include "dist/koneksi.php";
	$tampilDp3	= mysql_query("SELECT * FROM tb_dp3 WHERE id_dp3='$id_dp3'");
	$dp3	= mysql_fetch_array ($tampilDp3);
		$id_peg	=$dp3['id_peg'];
				
	if ($_POST['edit'] == "edit") {
		$periode_awal			=$_POST['periode_awal'];
		$periode_akhir			=$_POST['periode_akhir'];
		$pejabat_penilai		=$_POST['pejabat_penilai'];
		$atasan_pejabat_penilai	=$_POST['atasan_pejabat_penilai'];
		$nilai_kesetiaan		=$_POST['nilai_kesetiaan'];
		$nilai_prestasi			=$_POST['nilai_prestasi'];
		$nilai_tgjwb			=$_POST['nilai_tgjwb'];
		$nilai_ketaatan			=$_POST['nilai_ketaatan'];
		$nilai_kejujuran		=$_POST['nilai_kejujuran'];
		$nilai_kerjasama		=$_POST['nilai_kerjasama'];
		$nilai_prakarsa			=$_POST['nilai_prakarsa'];
		$nilai_kepemimpinan		=$_POST['nilai_kepemimpinan'];
		$hasil_penilaian		=$_POST['hasil_penilaian'];
		
		$update= mysql_query ("UPDATE tb_dp3 SET periode_awal='$periode_awal', periode_akhir='$periode_akhir', pejabat_penilai='$pejabat_penilai', atasan_pejabat_penilai='$atasan_pejabat_penilai', nilai_kesetiaan='$nilai_kesetiaan', nilai_prestasi='$nilai_prestasi', nilai_tgjwb='$nilai_tgjwb', nilai_ketaatan='$nilai_ketaatan', nilai_kejujuran='$nilai_kejujuran', nilai_kerjasama='$nilai_kerjasama', nilai_prakarsa='$nilai_prakarsa', nilai_kepemimpinan='$nilai_kepemimpinan', hasil_penilaian='$hasil_penilaian' WHERE id_dp3='$id_dp3'");
		if($update){
			echo "<div class='register-logo'><b>Edit</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Edit Data SKP ".$id_dp3." Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=view-detail-data-pegawai&id_peg=$id_peg' class='btn btn-danger btn-block'>Next >></button>
							</div>
						</div>
					</div>
				</div>";
		}
		else {
			echo "<div class='register-logo'><b>Oops!</b> 404 Error Server.</div>";
		}
	}
?>
</div>