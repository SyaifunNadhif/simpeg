<section class="content-header">
    <h1>Master<small>Data Pegawai</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Master Data</li>
    </ol>
</section>
<div class="register-box">
<?php	
	if ($_POST['save'] == "save") {
	$nip			=$_POST['nip'];
	$nama			=$_POST['nama'];
	$tempat_lhr		=$_POST['tempat_lhr'];
	$tgl_lhr		=$_POST['tgl_lhr'];
	$agama			=$_POST['agama'];
	$jk				=$_POST['jk'];	
	$gol_darah		=$_POST['gol_darah'];
	$status_nikah	=$_POST['status_nikah'];	
	$status_kepeg	=$_POST['status_kepeg'];	
	$tgl_naikpangkat=$_POST['tgl_naikpangkat'];	
	$tgl_naikgaji	=$_POST['tgl_naikgaji'];	
	$alamat			=$_POST['alamat'];
	$telp			=$_POST['telp'];
	$email			=$_POST['email'];
	$foto			=$_FILES['foto']['name'];
	
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
	$id_peg		=kdauto("tb_pegawai","");
	$date_reg	=date("Ymd");
	
	$pensiun = new DateTime($tgl_lhr);
	$pensiun->modify('+58 year');
	$pensiun->format('Y-m-d');
	$tgl_pensiun=$pensiun->format('Y-m-d');
	
	if (strlen($foto)>0) {
		if (is_uploaded_file($_FILES['foto']['tmp_name'])) {
			move_uploaded_file ($_FILES['foto']['tmp_name'], "pages/asset/foto/".$foto);
		}
	}
	
	$ceknip	=mysql_num_rows (mysql_query("SELECT nip FROM tb_pegawai WHERE nip='$_POST[nip]'"));
	
		if (empty($_POST['nip']) || empty($_POST['nama']) || empty($_POST['tempat_lhr']) || empty($_POST['tgl_lhr']) || empty($_POST['agama']) || empty($_POST['jk']) || empty($_POST['gol_darah']) || empty($_POST['status_nikah']) || empty($_POST['status_kepeg']) || empty($_POST['tgl_naikpangkat']) || empty($_POST['tgl_naikgaji'])) {
		echo "<div class='register-logo'><b>Oops!</b> Data Tidak Lengkap.</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali data pegawai. Pastikan NIP, Nama, TTL, Agama, Jenis Kelamin, Golongan Darah, dan Status Pernikahan telah Anda masukan dengan lengkap dan benar</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-pegawai' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else if($ceknip > 0) {
		echo "<div class='register-logo'><b>Oops!</b> NIP Not Available</div>
			<div class='box box-primary'>
				<div class='register-box-body'>
					<p>Harap periksa kembali dan pastikan NIP Pegawai yang Anda masukan benar.</p>
					<div class='row'>
						<div class='col-xs-8'></div>
						<div class='col-xs-4'>
							<button type='button' onclick=location.href='home-admin.php?page=form-master-data-pegawai' class='btn btn-block btn-warning'>Back</button>
						</div>
					</div>
				</div>
			</div>";
		}
		else{
		$insert = "INSERT INTO tb_pegawai (id_peg, nip, nama, tempat_lhr, tgl_lhr, agama, jk, gol_darah, status_nikah, status_kepeg, tgl_naikpangkat, tgl_naikgaji, alamat, telp, email, foto, tgl_pensiun, date_reg) VALUES ('$id_peg', '$nip', '$nama', '$tempat_lhr', '$tgl_lhr', '$agama', '$jk', '$gol_darah', '$status_nikah', '$status_kepeg', '$tgl_naikpangkat', '$tgl_naikgaji', '$alamat', '$telp', '$email', '$foto', '$tgl_pensiun', '$date_reg')";
		$query = mysql_query ($insert);
		
		if($query){
			echo "<div class='register-logo'><b>Input Data</b> Successful!</div>	
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Input Data Pegawai Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-view-data-pegawai' class='btn btn-danger btn-block'>Next >></button>
							</div>
						</div>
					</div>
				</div>";
		}
			else {
				echo "<div class='register-logo'><b>Oops!</b> 404 Error Server.</div>";
			}
		}
	}
?>
</div>