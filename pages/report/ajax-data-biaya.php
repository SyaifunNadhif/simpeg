<?php
// FILE: pages/report/ajax-data-biaya.php

if (session_id() == '') session_start();
ini_set('display_errors', 0); // Matikan error agar tampilan rapi

// Auto Detect Koneksi
if (file_exists('../../dist/koneksi.php')) include '../../dist/koneksi.php';
elseif (file_exists('../../../dist/koneksi.php')) include '../../../dist/koneksi.php';
else die("Koneksi Error");

// Filter Data
$hak_akses   = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$kode_kantor = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$tahun_pilih = isset($_POST['tahun']) ? mysqli_real_escape_string($conn, $_POST['tahun']) : date('Y');

$where_akses = "WHERE 1=1";
if ($hak_akses !== 'admin') {
    $where_akses .= " AND id_peg IN (SELECT id_peg FROM tb_jabatan WHERE unit_kerja = '$kode_kantor' AND status_jab = 'Aktif')";
}
$where_tahun = ($tahun_pilih == 'Semua') ? "" : " AND tahun = '$tahun_pilih'";

// Query
$query = "SELECT diklat, penyelenggara, tahun,
            COUNT(id_peg) as jumlah_peserta, 
            SUM(biaya) as total_biaya_kegiatan
          FROM tb_diklat 
          $where_akses $where_tahun
          GROUP BY diklat, penyelenggara, tahun
          ORDER BY total_biaya_kegiatan DESC";

$result = mysqli_query($conn, $query);
$grand_total = 0;
$total_peserta = 0;
?>

<div class="table-responsive">
    <table class="table table-hover table-bordered mb-0" style="width: 100%;">
        <thead class="bg-light">
            <tr style="background: linear-gradient(45deg, #1e3c72, #2a5298); color: white;">
                <th width="5%" class="text-center align-middle">No</th>
                <th class="align-middle">Nama Kegiatan / Diklat</th>
                <th class="align-middle">Penyelenggara</th>
                <th width="10%" class="text-center align-middle">Tahun</th>
                <th width="10%" class="text-center align-middle">Peserta</th>
                <th width="20%" class="text-right align-middle">Realisasi Biaya</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (mysqli_num_rows($result) > 0) {
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                    $grand_total += $row['total_biaya_kegiatan'];
                    $total_peserta += $row['jumlah_peserta'];
            ?>
                <tr>
                    <td class="text-center font-weight-bold text-muted"><?= $no++ ?>.</td>
                    <td class="font-weight-bold text-dark"><?= $row['diklat'] ?></td>
                    <td class="small text-uppercase text-secondary font-weight-bold"><?= $row['penyelenggara'] ?></td>
                    <td class="text-center"><span class="badge badge-light border"><?= $row['tahun'] ?></span></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-info btn-detail font-weight-bold px-3 shadow-sm text-white"
                                data-diklat="<?= htmlspecialchars($row['diklat']) ?>"
                                data-penyelenggara="<?= htmlspecialchars($row['penyelenggara']) ?>"
                                data-tahun="<?= $row['tahun'] ?>">
                            <?= $row['jumlah_peserta'] ?>
                        </button>
                    </td>
                    <td class="text-right" style="font-family: 'Consolas', monospace; font-size: 1rem; color: #333;">
                        Rp <?= number_format($row['total_biaya_kegiatan'], 0, ',', '.') ?>
                    </td>
                </tr>
            <?php 
                } 
            } else { 
                echo '<tr><td colspan="6" class="text-center py-5 text-muted">Data tidak ditemukan.</td></tr>';
            } 
            ?>
        </tbody>
        <tfoot class="bg-light">
            <tr style="background-color: #eef2ff; color: #1e3c72; border-top: 2px solid #ccc;">
                <td colspan="4" class="text-right font-weight-bold text-uppercase p-3">Grand Total</td>
                <td class="text-center font-weight-bold p-3"><?= number_format($total_peserta) ?></td>
                <td class="text-right font-weight-bold p-3" style="font-size: 1.1rem;">
                    Rp <?= number_format($grand_total, 0, ',', '.') ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>