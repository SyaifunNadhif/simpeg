<?php
// FILE: pages/report/ajax-detail.php
if (session_id() == '') session_start();
include '../../dist/koneksi.php'; // Naik 2 tingkat

if (isset($_POST['diklat'])) {
    $diklat = mysqli_real_escape_string($conn, $_POST['diklat']);
    $tahun  = mysqli_real_escape_string($conn, $_POST['tahun']);
    $peny   = mysqli_real_escape_string($conn, $_POST['penyelenggara']);

    $sql = "SELECT d.biaya, p.nama, p.id_peg, k.nama_kantor 
            FROM tb_diklat d
            JOIN tb_pegawai p ON d.id_peg = p.id_peg
            LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
            LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
            WHERE d.diklat = '$diklat' AND d.tahun = '$tahun' AND d.penyelenggara = '$peny'
            ORDER BY p.nama ASC";

    $q = mysqli_query($conn, $sql);
    
    // Output HTML Table sederhana
    echo '<table class="table table-sm table-bordered">';
    echo '<thead><tr><th>No</th><th>Nama</th><th>Unit Kerja</th><th class="text-right">Biaya</th></tr></thead>';
    echo '<tbody>';
    $no=1; 
    while($r = mysqli_fetch_assoc($q)){
        echo "<tr>
                <td>".$no++."</td>
                <td>".$r['nama']."<br><small>".$r['id_peg']."</small></td>
                <td>".$r['nama_kantor']."</td>
                <td class='text-right'>".number_format($r['biaya'])."</td>
              </tr>";
    }
    echo '</tbody></table>';
}
?>