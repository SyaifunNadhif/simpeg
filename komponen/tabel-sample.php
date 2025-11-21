<?php
// Mulai halaman
include "dist/koneksi.php"; // jika perlu koneksi database
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Sample Tabel DataTables</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
</head>

<body class="hold-transition sidebar-mini text-sm">
<div class="wrapper">

  <!-- Main Content -->
  <div class="content-wrapper p-4">
    <section class="content">
      <div class="container-fluid">

        <!-- Card Tabel -->
        <div class="card card-info">
          <div class="card-header">
            <h3 class="card-title">Contoh Tabel DataTables (Pagination Aktif)</h3>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="tabelSample" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Posisi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 1; $i <= 20; $i++) { ?>
                    <tr>
                      <td><?= $i ?></td>
                      <td>Nama Pegawai <?= $i ?></td>
                      <td>Jabatan <?= ($i % 5 == 0 ? "Manager" : "Staff") ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </section>
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

<!-- DataTables Init -->
<script>
  $(function () {
    $('#tabelSample').DataTable({
      paging: true,
      pageLength: 5,
      responsive: true,
      autoWidth: false,
      lengthChange: false,
      ordering: true,
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
      }
    });
  });
</script>

</body>
</html>
