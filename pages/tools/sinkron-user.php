<?php
include '../dist/koneksi.php';
include '../dist/function.php';

$result = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE status_aktif = 'Y'");
$total = 0;

while ($r = mysqli_fetch_assoc($result)) {
  sinkron_user_dari_pegawai($r['id_peg']);
  $total++;
}
echo "Sinkronisasi user selesai: $total user dibuat/diperbarui.";
?>