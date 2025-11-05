<section class="content-header">
    <h1>Delete<small>Data Pegawai</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Delete Data Pegawai</li>
    </ol>
</section>
<div class="register-box">
<?php
include "dist/koneksi.php";
if (isset($_GET['id_peg'])) {
	$id_peg = $_GET['id_peg'];
	$query   = "SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'";
	$hasil   = mysql_query($query);
	$data    = mysql_fetch_array($hasil);
	}
	else {
		die ("Error. No Kode Selected! ");	
	}
	
	if (!empty($id_peg) && $id_peg != "") {
		$insert = "INSERT INTO tb_recycle_bin_pegawai (id_peg, nip, nama, tempat_lhr, tgl_lhr, agama, jk, gol_darah, status_nikah, status_kepeg, alamat, telp, email, foto, tgl_pensiun, date_reg, bpjstk, bpjskes, status_aktif) 
		SELECT 'id_peg', 'nip', 'nama', 'tempat_lhr', 'tgl_lhr', 'agama', 'jk', 'gol_darah', 'status_nikah', 'status_kepeg', 'alamat', 'telp', 'email', 'foto', 'tgl_pensiun', 'date_reg', 'bpjstk', 'bpjskes', 'status_aktif' FROM tb_pegawai WHERE id_peg=$id_peg;
		$query = mysql_query ($insert);

		$delete = "DELETE FROM tb_pegawai WHERE id_peg='$id_peg'";
		$sqldel = mysql_query ($delete);
		$delanak = mysql_query("DELETE FROM tb_anak WHERE id_peg='$id_peg'");
		$delbhs = mysql_query("DELETE FROM tb_bahasa WHERE id_peg='$id_peg'");
		$delcuti = mysql_query("DELETE FROM tb_cuti WHERE id_peg='$id_peg'");
		$deldik = mysql_query("DELETE FROM tb_diklat WHERE id_peg='$id_peg'");
		$deldp3 = mysql_query("DELETE FROM tb_dp3 WHERE id_peg='$id_peg'");
		$delhuk = mysql_query("DELETE FROM tb_hukuman WHERE id_peg='$id_peg'");
		$deljab = mysql_query("DELETE FROM tb_jabatan WHERE id_peg='$id_peg'");
		$delortu = mysql_query("DELETE FROM tb_ortu WHERE id_peg='$id_peg'");
		$delpan = mysql_query("DELETE FROM tb_pangkat WHERE id_peg='$id_peg'");
		$delpen = mysql_query("DELETE FROM tb_penghargaan WHERE id_peg='$id_peg'");
		$deltug = mysql_query("DELETE FROM tb_penugasan WHERE id_peg='$id_peg'");
		$delsek = mysql_query("DELETE FROM tb_sekolah WHERE id_peg='$id_peg'");
		$delsem = mysql_query("DELETE FROM tb_seminar WHERE id_peg='$id_peg'");
		$delsi = mysql_query("DELETE FROM tb_suamiistri WHERE id_peg='$id_peg'");
		$dellatjab = mysql_query("DELETE FROM tb_lat_jabatan WHERE id_peg='$id_peg'");
		$delmut = mysql_query("DELETE FROM tb_mutasi WHERE id_peg='$id_peg'");
		
		if ($sqldel) {		
			echo "<div class='register-logo'><b>Delete</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Data Pegawai ".$id_peg." Berhasil di Hapus</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-view-data-pegawai' class='btn btn-danger btn-block'>Next >></button>
							</div>
						</div>
					</div>
				</div>";		
		}
		else{
			echo "<div class='register-logo'><b>Oops!</b> 404 Error Server.</div>";	
		}
	}
	mysql_close($Open);
?>
</div>