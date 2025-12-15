<?php
// FILE: pages/ref-diklat/ajax-get-jenis.php
include "../../dist/koneksi.php";

$tahun = isset($_POST['tahun']) ? $_POST['tahun'] : date('Y');

$sql = "SELECT DISTINCT diklat FROM tb_diklat WHERE tahun = '$tahun' ORDER BY diklat ASC";
$query = mysqli_query($conn, $sql);

echo '<option value="">- Semua Jenis -</option>';
while($row = mysqli_fetch_assoc($query)){
    echo '<option value="'.$row['diklat'].'">'.$row['diklat'].'</option>';
}
?>