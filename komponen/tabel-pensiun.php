<?php
include "dist/koneksi.php";
include "dist/library.php";

$tahun = date('Y');

// Query Pegawai Pensiun Tahun Ini
$query = mysqli_query($conn, "
  SELECT 
    id_peg, nama, jk,
    (SELECT jabatan FROM tb_jabatan WHERE id_peg=a.id_peg AND status_jab='Aktif') AS jabatan,
    tempat_lhr, tgl_lhr, tgl_pensiun,
    DATEDIFF(tgl_pensiun, CURDATE()) AS selisih_hari
  FROM tb_pegawai a
  WHERE 
    id_peg NOT IN ('101-001','101-002','101-003','101-004','101-005','101-007','101-008')
    AND YEAR(tgl_pensiun) = YEAR(NOW())
  ORDER BY 
    (DATEDIFF(tgl_pensiun, CURDATE()) <= 31 AND DATEDIFF(tgl_pensiun, CURDATE()) >= 0) DESC,
    tgl_pensiun ASC
");
?>

<!-- Card Container -->
<div class="card card-info">
  <div class="card-header">
    <h3 class="card-title">Daftar Pegawai Memasuki Usia Pensiun Tahun <?= $tahun; ?></h3>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="tabelPensiun" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>ID Pegawai</th>
            <th>Nama</th>
            <th>Jenis Kelamin</th>
            <th>Jabatan</th>
            <th>Tempat, Tgl Lahir</th>
            <th>Tgl Pensiun</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_array($query)) {
            $tglPensiun = $row['tgl_pensiun'];
            $selisihHari = $row['selisih_hari'];
            $badge = ($selisihHari >= 0 && $selisihHari <= 31)
            ? "<span class='badge badge-danger'>Segera</span>"
            : "";
            ?>
            <tr>
              <td><?= $row['id_peg']; ?></td>
              <td><?= $row['nama']; ?></td>
              <td><?= $row['jk']; ?></td>
              <td><?= $row['jabatan']; ?></td>
              <td><?= $row['tempat_lhr'] . ', ' . Indonesia2Tgl($row['tgl_lhr']); ?></td>
              <td><?= Indonesia2Tgl($tglPensiun) . " $badge"; ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- JS Libraries -->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>

<!-- DataTables JS -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<!-- DataTables Initialization -->
<script>
  $(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#tabelPensiun')) {
      $('#tabelPensiun').DataTable().destroy();
    }

    $('#tabelPensiun').DataTable({
      paging: true,
      pageLength: 5,
      lengthChange: false,
      responsive: true,
      autoWidth: false,
      order: [[5, 'asc']],
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
      }
    });
  });
</script>
