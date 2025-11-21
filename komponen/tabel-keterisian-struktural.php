<?php
include "dist/koneksi.php";

// Ambil data jabatan struktural (selain PE)
$query = mysqli_query($conn, "
  SELECT 
    r.kode_jabatan,
    r.jabatan,
    r.kuota,
    COUNT(j.id_jab) AS jml
  FROM tb_ref_jabatan r
  LEFT JOIN tb_jabatan j 
    ON j.jabatan = r.jabatan AND j.status_jab = 'Aktif'
  LEFT JOIN tb_pegawai p 
    ON j.id_peg = p.id_peg AND p.status_aktif = 1
  WHERE r.`group` = 'PS'
  GROUP BY r.kode_jabatan, r.jabatan, r.kuota
  ORDER BY r.kode_jabatan ASC
");
?>

<div class="card card-warning">
  <div class="card-header">
    <h3 class="card-title">Keterisian Jabatan Struktural</h3>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="tabelStruktural" class="table table-bordered table-sm table-striped">
        <thead>
          <tr>
            <th>Kode Jabatan</th>
            <th>Deskripsi Jabatan</th>
            <th>Jumlah</th>
            <th>Kuota</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($query)) {
            $kuota = $row['kuota'];
            $terisi = $row['jml'];
            $badge = ($terisi < $kuota)
              ? "<span class='badge badge-danger'>Vacant</span>"
              : "<span class='badge badge-success'>Terpenuhi</span>";
          ?>
            <tr>
              <td><?= $row['kode_jabatan'] ?></td>
              <td><?= $row['jabatan'] ?></td>
              <td><?= $terisi ?></td>
              <td><?= $kuota ?></td>
              <td><?= $badge ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Inisialisasi DataTables -->
<script>
  $(document).ready(function () {
    $('#tabelStruktural').DataTable({
      paging: true,
      pageLength: 10,
      responsive: true,
      autoWidth: false,
      ordering: true,
      lengthChange: false,
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
      }
    });
  });
</script>
