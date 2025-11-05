<section class="content-header">
    <h1>Master<small>Data Pengangkatan Pegawai</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Master Data</li>
    </ol>
</section>
<div class="register-box">
<?php	
	if ($_POST['save'] == "save") {
	$id_peg			=$_POST['id_peg'];
	$jns_mutasi	=$_POST['jns_mutasi'];
	$id_peg_baru=$_POST['id_peg_baru'];	
	$tgl_mutasi	=date('Y-m-d', strtotime($_POST['tgl_mutasi']));
	$no_mutasi	=$_POST['no_mutasi'];
	$tmt 				=date('Y-m-d', strtotime($_POST['tmt']));
	$sk_mutasi	=$_FILES['sk_mutasi']['name'];
	
	include "dist/koneksi.php";
	function kdauto($tabel, $inisial){
		$struktur   = mysql_query("SELECT * FROM $tabel");
		$field      = mysql_field_name($struktur,0);
		$panjang    = mysql_field_len($struktur,0);
		$qry  = mysql_query("SELECT max(".$field.") FROM ".$tabel);
		$row  = mysql_fetch_array($qry);
		if ($row[0]=="") {
		$angka=0;
		}
		else {
		$angka= substr($row[0], strlen($inisial));
		}
		$angka++;
		$angka      =strval($angka);
		$tmp  ="";
		for($i=1; $i<=($panjang-strlen($inisial)-strlen($angka)); $i++) {
		$tmp=$tmp."0";
		}
		return $inisial.$tmp.$angka;
		}
	$id_angkat = kdauto("tb_angkat","");
	$date_reg  = date('Y-m-d');
	
	if (strlen($sk_mutasi)>0) {
		if (is_uploaded_file($_FILES['sk_mutasi']['tmp_name'])) {
			move_uploaded_file ($_FILES['sk_mutasi']['tmp_name'], "pages/asset/sk_mutasi/".$sk_mutasi);
		}
	}

	$cekid_peg	=mysql_num_rows (mysql_query("SELECT id_peg FROM tb_pegawai WHERE id_peg='$_POST[id_peg_baru]'"));	
	
		if (empty($_POST['id_peg']) || empty($_POST['jns_mutasi']) || empty($_POST['id_peg_baru']) || empty($_POST['tgl_mutasi']) || empty($_POST['no_mutasi'])) {
		echo "<div class='register-logo'><b>Oops!</b> Data Tidak Lengkap.</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali. Pastikan data yang Anda masukan lengkap dan benar</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-angkat' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else if($cekid_peg > 0) {
		echo "<div class='register-logo'><b>Oops!</b> id_peg Not Available</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali dan pastikan ID Pegawai yang Anda masukan benar.</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-angkat' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}		
		else{
			
			$insert = "INSERT INTO tb_angkat (id_angkat, id_peg, jns_mutasi, id_peg_baru, tgl_mutasi, tmt, no_mutasi, sk_mutasi, date_reg) VALUES ('$id_angkat', '$id_peg', '$jns_mutasi', '$id_peg_baru', '$tgl_mutasi', '$tmt', '$no_mutasi', '$sk_mutasi', '$date_reg')";
			$query_insert = mysql_query ($insert);

			$update_peg = "UPDATE tb_pegawai SET id_peg_old='$id_peg', id_peg='$id_peg_baru', status_kepeg='$jns_mutasi' WHERE id_peg='$id_peg'";

			$update_diklat = "UPDATE tb_diklat SET id_peg_old='$id_peg', id_peg='$id_peg_baru' WHERE id_peg='$id_peg'";		

			$update_hukuman = "UPDATE tb_hukuman SET id_peg_old='$id_peg', id_peg='$id_peg_baru' WHERE id_peg='$id_peg'";

			$update_jabatan = "UPDATE tb_jabatan SET id_peg_old='$id_peg', id_peg='$id_peg_baru' WHERE id_peg='$id_peg'";			

			$update_ortu = "UPDATE tb_ortu SET id_peg_old='$id_peg', id_peg='$id_peg_baru' WHERE id_peg='$id_peg'";			

			$update_sekolah = "UPDATE tb_sekolah SET id_peg_old='$id_peg', id_peg='$id_peg_baru' WHERE id_peg='$id_peg'";

			$update_anak = "UPDATE tb_anak SET id_peg_old='$id_peg', id_peg='$id_peg_baru' WHERE id_peg='$id_peg'";

			$update_pasangan = "UPDATE tb_suamiistri SET id_peg_old='$id_peg', id_peg='$id_peg_baru' WHERE id_peg='$id_peg'";		

			$update_mutasi = "UPDATE tb_mutasi SET id_peg_old='$id_peg', id_peg='$id_peg_baru' WHERE id_peg='$id_peg'";					

			if($query_insert){
				echo "<div class='register-logo'><b>Input Data</b> Successful!</div>
				<div class='box box-primary'>
				<div class='register-box-body'>
				<p>Input Data Mutasi Berhasil</p>
				<div class='row'>
				<div class='col-xs-8'></div>
				<div class='col-xs-4'>
				<button type='button' onclick=location.href='home-admin.php?page=form-master-data-angkat' class='btn btn-danger btn-block'>Next >></button>
				</div>
				</div>
				</div>
				</div>";	

				$query_update = mysql_query ($update_peg);

				$query_update = mysql_query ($update_diklat);

				$query_update = mysql_query ($update_hukuman);

				$query_update = mysql_query ($update_jabatan);

				$query_update = mysql_query ($update_ortu);

				$query_update = mysql_query ($update_sekolah);

				$query_update = mysql_query ($update_anak);

				$query_update = mysql_query ($update_pasangan);

				$query_update = mysql_query ($update_mutasi);				

			}
			else {
				echo "<div class='register-logo'><b>Oops!</b> 404 Error Server.</div>";
			}
		}
	}
?>
</div>