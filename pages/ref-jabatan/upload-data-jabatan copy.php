<?php 
// menghubungkan dengan koneksi
include "dist/koneksi.php";
// menghubungkan dengan library excel reader
include "dist/excel_reader2.php";
?>

<?php
// upload file xls
$target = basename($_FILES['FileJabatan']['name']) ;
move_uploaded_file($_FILES['FileJabatan']['tmp_name'], $target);

// beri permisi agar file xls dapat di baca
chmod($_FILES['FileJabatan']['name'],0777);

// mengambil isi file xls
$data = new Spreadsheet_Excel_Reader($_FILES['FileJabatan']['name'],false);
// menghitung jumlah baris data yang ada
$jumlah_baris = $data->rowcount($sheet_index=0);

// jumlah default data yang berhasil di import
$berhasil = 0;
for ($i=2; $i<=$jumlah_baris; $i++){

	// menangkap data dan memasukkan ke variabel sesuai dengan kolumnya masing-masing
	$id_peg					= $data->val($i, 1);
	$jabatan				= $data->val($i, 2);
	$unit_kerja				= $data->val($i, 3);
	$tmt_jabatan			= $data->val($i, 4);
	$sampai_tgl				= $data->val($i, 5);
	$status_jab				= $data->val($i, 6);
	$no_sk					= $data->val($i, 7);
	$tgl_sk					= $data->val($i, 8);
	$date_reg				= $data->val($i, 9);

	
	if($id_peg != "" && $jabatan != "" && $unit_kerja != "" && $tmt_jabatan != "" && $no_sk != "" && $tgl_sk != ""){
		// input data ke database (table tb_diklat)
		$insert = "INSERT INTO tb_jabatan (id_jab, id_peg, jabatan, unit_kerja, tmt_jabatan, sampai_tgl, status_jab, no_sk, tgl_sk, date_reg) VALUES ('', '$id_peg', '$jabatan', '$unit_kerja', '$tmt_jabatan', '$sampai_tgl', '$status_jab	', '$no_sk', '$tgl_sk', '$date_reg')";
		$query = mysql_query ($insert);
		$berhasil++;
	}
}



// hapus kembali file .xls yang di upload tadi
unlink($_FILES['FileJabatan']['name']);

// alihkan halaman ke index.php
echo "<div class='register-logo'><b>Input Data</b> Successful!</div>
				<div class='box box-primary'>
					<div class='register-box-body'>
						<p>Upload Data Jabatan Berhasil</p>
						<div class='row'>
							<div class='col-xs-8'></div>
							<div class='col-xs-4'>
								<button type='button' onclick=location.href='home-admin.php?page=form-view-data-jabatan' class='btn btn-danger btn-block'>Next >></button>
							</div>
						</div>
					</div>
				</div>";
?>