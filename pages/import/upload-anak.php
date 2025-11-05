<!-- import excel ke mysql -->

<?php 
// menghubungkan dengan koneksi
include 'dist/koneksi.php';
// menghubungkan dengan library excel reader
include "dist/excel_reader2.php";
?>

<?php
// upload file xls
$target = basename($_FILES['uploadanak']['name']) ;
move_uploaded_file($_FILES['uploadanak']['tmp_name'], $target);

// beri permisi agar file xls dapat di baca
chmod($_FILES['uploadanak']['name'],0777);

// mengambil isi file xls
$data = new Spreadsheet_Excel_Reader($_FILES['uploadanak']['name'],false);
// menghitung jumlah baris data yang ada
$jumlah_baris = $data->rowcount($sheet_index=0);

// jumlah default data yang berhasil di import
$berhasil = 0;
for ($i=2; $i<=$jumlah_baris; $i++){

	// menangkap data dan memasukkan ke variabel sesuai dengan kolumnya masing-masing
	$id_peg     = $data->val($i, 1);
	$nik 		= $data->val($i, 2);
	$nama  		= $data->val($i, 3);
	$tmp_lahir	= $data->val($i, 4);
	$tgl_lahir	= $data->val($i, 5);
	$pendidikan	= $data->val($i, 6);
	$pekerjaan	= $data->val($i, 7);
	$status_hub	= $data->val($i, 8);
	$date_reg	= $data->val($i, 9);

	if($id_peg != "" && $nik != "" && $nama != "" && $tmp_lahir != "" && $tgl_lahir != "" && $pendidikan != "" && $pekerjaan != "" && $status_hub != "" && $date_reg != ""){
		// input data ke database (table tb_anak)
		mysqli_query($koneksi,"INSERT into tb_anak values('','$id_peg','$nik','$nama','$tmp_lahir','$tgl_lahir','$pendidikan','$pekerjaan','$status_hub','$date_reg')");
		$berhasil++;
	}
}

// hapus kembali file .xls yang di upload tadi
unlink($_FILES['uploadanak']['name']);

// alihkan halaman ke index.php
header("location:pages/import/form-upload-anak.php?berhasil=$berhasil");
?>