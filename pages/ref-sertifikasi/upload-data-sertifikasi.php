<?php 
// menghubungkan dengan koneksi
include "dist/koneksi.php";
// menghubungkan dengan library excel reader
include "dist/excel_reader2.php";
?>

<?php
// upload file xls
$target = basename($_FILES['FileSertifikasi']['name']) ;
move_uploaded_file($_FILES['FileSertifikasi']['tmp_name'], $target);

// beri permisi agar file xls dapat di baca
chmod($_FILES['FileSertifikasi']['name'],0777);

// mengambil isi file xls
$data = new Spreadsheet_Excel_Reader($_FILES['FileSertifikasi']['name'],false);
// menghitung jumlah baris data yang ada
$jumlah_baris = $data->rowcount($sheet_index=0);

// jumlah default data yang berhasil di import
$berhasil = 0;
for ($i=2; $i<=$jumlah_baris; $i++){

	// menangkap data dan memasukkan ke variabel sesuai dengan kolumnya masing-masing
	$id_peg					= $data->val($i, 1);
	$sertifikasi		= $data->val($i, 2);
	$penyelenggara	= $data->val($i, 3);
	$tgl_sertifikat	= $data->val($i, 4);
	$tgl_expired		= $data->val($i, 5);
	$sertifikat			= $data->val($i, 6);
	$date_reg				= $data->val($i, 7);

	
	if($id_peg != "" && $sertifikasi != "" && $penyelenggara != ""){
		// input data ke database (table tb_diklat)
		$insert= "INSERT INTO tb_sertifikasi (id_sertif, id_peg, sertifikasi, penyelenggara, tgl_sertifikat, tgl_expired, sertifikat, date_reg) VALUES ('', '$id_peg', '$sertifikasi', '$penyelenggara', '$tgl_sertifikat', '$tgl_expired', '$sertifikat', '$date_reg')";
		$query = mysql_query ($insert);
		$berhasil++;
	}
}



// hapus kembali file .xls yang di upload tadi
unlink($_FILES['FileSertifikasi']['name']);

// alihkan halaman ke index.php
echo "<div class='register-logo'><b>Input Data</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Input Data Diklat Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-master-data-diklat' class='btn btn-danger btn-block'>Next >></button>
							</div>
						</div>
					</div>
				</div>";
?>