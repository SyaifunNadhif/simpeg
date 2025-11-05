<?php
/*********************************************************
 * FILE   : laporan-nominatif-pegawai.php
 * MODULE : Laporan Pegawai (Nominatif)
 * VERSION: v1.7 (PHP 5.6)
 * DATE   : 12 Oktober 2025
 * AUTHOR : SIMPEG BPR BKK Jateng
 * NOTES  :
 * - Format header mengikuti laporan keadaan pegawai
 * - Support filter Status, Unit, Jabatan
 * - Hak akses kepala hanya tampilkan unitnya
 * - DataTables: pagination, responsive, export
 *********************************************************/

include "dist/koneksi.php";
include "dist/library.php";

$status_kepeg = isset($_GET['status_kepeg']) ? mysqli_real_escape_string($conn, $_GET['status_kepeg']) : '';
$unit_kerja   = isset($_GET['unit_kerja']) ? mysqli_real_escape_string($conn, $_GET['unit_kerja']) : '';
$jabatan      = isset($_GET['jabatan']) ? mysqli_real_escape_string($conn, $_GET['jabatan']) : '';

$hak_akses = isset($_SESSION['hak_akses']) ? $_SESSION['hak_akses'] : '';
$kode_kantor_user = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$is_kepala = ($hak_akses == 'kepala');

if ($is_kepala) {
  $unit_kerja = $kode_kantor_user;
  $unit_filter_locked = true;
} else {
  $unit_filter_locked = false;
}

$where = "WHERE p.status_aktif = 1";
if ($status_kepeg != '') $where .= " AND p.status_kepeg = '$status_kepeg'";
if ($unit_kerja != '')   $where .= " AND j.unit_kerja = '$unit_kerja'";
if ($jabatan != '')      $where .= " AND j.jabatan = '$jabatan'";

$query = "SELECT
            p.id_peg,
            p.nama,
            p.nip,
            j.jabatan,
            j.tmt_jabatan,
            (SELECT nama_kantor FROM tb_kantor WHERE kode_kantor_detail = j.unit_kerja) AS unit_kerja,
            p.status_kepeg,
            s.nama_sekolah,
            s.tgl_ijazah,
            s.jenjang
          FROM tb_pegawai p
          LEFT JOIN (
            SELECT j1.*
            FROM tb_jabatan j1
            INNER JOIN (
              SELECT id_peg, MAX(tmt_jabatan) AS tmt_max
              FROM tb_jabatan
              GROUP BY id_peg
            ) j2 ON j1.id_peg = j2.id_peg AND j1.tmt_jabatan = j2.tmt_max
          ) j ON p.id_peg = j.id_peg
          LEFT JOIN tb_pendidikan s ON p.id_peg = s.id_peg AND s.status = 'Akhir'
          $where
          ORDER BY p.nama ASC";

$result = mysqli_query($conn, $query);
?>


<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>


<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Laporan Nominatif Pegawai</h1>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card">
    <div class="card-header">
      <form method="GET" action="">
        <input type="hidden" name="page" value="nominatif">
        <div class="form-row align-items-end">
          <div class="col-md-3">
            <label>Status Kepegawaian</label>
            <select name="status_kepeg" class="form-control">
              <option value="">-- Semua --</option>
              <option value="Tetap" <?= $status_kepeg == 'Tetap' ? 'selected' : '' ?>>Tetap</option>
              <option value="Kontrak" <?= $status_kepeg == 'Kontrak' ? 'selected' : '' ?>>Kontrak</option>
              <option value="Outsource" <?= $status_kepeg == 'Outsource' ? 'selected' : '' ?>>Outsource</option>
            </select>
          </div>
          <div class="col-md-3">
            <label>Unit Kerja</label>
            <select name="unit_kerja" class="form-control" <?= $unit_filter_locked ? 'disabled' : '' ?> >
              <option value="">-- Semua --</option>
              <?php
              $qKantor = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor ORDER BY nama_kantor");
              while ($k = mysqli_fetch_array($qKantor)) {
                $sel = ($unit_kerja == $k['kode_kantor_detail']) ? 'selected' : '';
                echo "<option value='".$k['kode_kantor_detail']."' $sel>".$k['nama_kantor']."</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-3">
            <label>Jabatan</label>
            <select name="jabatan" class="form-control">
              <option value="">-- Semua --</option>
              <?php
              $qJab = mysqli_query($conn, "SELECT DISTINCT jabatan FROM tb_jabatan ORDER BY jabatan");
              while ($j = mysqli_fetch_array($qJab)) {
                $sel = ($jabatan == $j['jabatan']) ? 'selected' : '';
                echo "<option value='".$j['jabatan']."' $sel>".$j['jabatan']."</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-3">
            <label>&nbsp;</label>
            <div class="btn-group d-flex">
              <button type="submit" class="btn btn-primary w-50">Terapkan</button>
              <a href="pages/report/print-nominatif-pegawai.php?<?= http_build_query($_GET); ?>" target="_blank" class="btn btn-success w-50">Cetak</a>
              <a href="pages/report/export-nominatif-excel.php?<?= http_build_query($_GET); ?>" class="btn btn-warning w-50">Export Excel</a>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="nominatifTable" class="table table-bordered table-striped table-hover">
          <thead>
            <tr>
              <th>No</th>
              <th>ID Pegawai</th>
              <th>Nama</th>
              <th>NIK</th>
              <th>Jabatan</th>
              <th>TMT</th>
              <th>Unit Kerja</th>
              <th>Status Kepeg.</th>
              <th>Sekolah</th>
              <th>Tahun</th>
              <th>Jenjang</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 0;
            while ($row = mysqli_fetch_array($result)) {
              $no++;
              echo "<tr>
                      <td>$no</td>
                      <td>$row[id_peg]</td>
                      <td>$row[nama]</td>
                      <td>$row[nip]</td>
                      <td>$row[jabatan]</td>
                      <td>$row[tmt_jabatan]</td>
                      <td>$row[unit_kerja]</td>
                      <td>$row[status_kepeg]</td>
                      <td>$row[nama_sekolah]</td>
                      <td>$row[tgl_ijazah]</td>
                      <td>$row[jenjang]</td>
                    </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<script>
  $(document).ready(function () {
    $('#nominatifTable').DataTable({
      "paging": true,
      "pageLength": 15,
      "lengthChange": true,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
      "language": {
        "search": "Cari:",
        "lengthMenu": "Tampilkan _MENU_ data per halaman",
        "zeroRecords": "Data tidak ditemukan",
        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        "infoEmpty": "Tidak ada data tersedia",
        "infoFiltered": "(difilter dari _MAX_ total data)"
      }
    });
  });
</script>
