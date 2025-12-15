<?php
include "dist/koneksi.php";
include "dist/library.php";

$tahun = date('Y');

$query = mysqli_query($conn, "
  SELECT 
    id_peg, nama, jk, foto,
    (SELECT jabatan 
     FROM tb_jabatan 
     WHERE id_peg=a.id_peg AND status_jab='Aktif' 
     LIMIT 1) AS jabatan,
    tempat_lhr, tgl_pensiun,
    DATEDIFF(tgl_pensiun, CURDATE()) AS selisih_hari
  FROM tb_pegawai a
  WHERE 
    id_peg NOT IN ('101-001','101-002','101-003','101-004','101-005','101-007','101-008')
    AND YEAR(tgl_pensiun) = YEAR(NOW())
  ORDER BY
    CASE
      WHEN DATEDIFF(tgl_pensiun, CURDATE()) BETWEEN 0 AND 30 THEN 1
      ELSE 2
    END,
    tgl_pensiun ASC
");
?>



<style>
.avatar,
.avatar img{
  width:42px;
  height:42px;
  border-radius:50%;
  object-fit:cover;
}
.avatar-text{
  background:#17a2b8;
  color:#fff;
  font-weight:bold;
  display:flex;
  align-items:center;
  justify-content:center;
}
.table td{vertical-align:middle;}
.badge{
  padding:6px 12px;
  border-radius:12px;
  font-size:12px;
}
</style>
</head>




<div class="card card-info">
  <div class="card-header">
    <h3 class="card-title">
      Daftar Pegawai Memasuki Usia Pensiun Tahun <?= $tahun ?>
    </h3>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table id="tabelPensiun" class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th>Pegawai</th>
            <th>Jabatan</th>
            <th>Status</th>
            <th>Tgl Pensiun</th>
            <th>Countdown</th>
          </tr>
        </thead>
        <tbody>

        <?php while($row=mysqli_fetch_assoc($query)){ 
          $hari = (int)$row['selisih_hari'];

          if($hari > 0){
            $status = 'Segera Pensiun';
            $badge  = 'badge-danger';
            $count  = $hari.' Hari Lagi';
            $countClass = 'text-danger font-weight-bold';
          }else{
            $status = 'Sudah Lewat';
            $badge  = 'badge-success';
            $count  = 'Selesai';
            $countClass = 'text-muted';
          }

          // FOTO
          $fotoPath = 'pages/assets/foto/'.$row['foto'];
          $fotoAda  = (!empty($row['foto']) && file_exists($fotoPath));
        ?>

        <tr>
          <td>
            <div class="d-flex align-items-center">
              <?php if($fotoAda){ ?>
                <img src="<?= $fotoPath ?>" class="avatar mr-2">
              <?php }else{ ?>
                <div class="avatar avatar-text mr-2">
                  <?= strtoupper(substr($row['nama'],0,1)); ?>
                </div>
              <?php } ?>

              <div>
                <div class="font-weight-bold"><?= $row['nama'] ?></div>
                <small class="text-muted">ID: <?= $row['id_peg'] ?></small>
              </div>
            </div>
          </td>

          <td><?= $row['jabatan'] ?></td>

          <td>
            <span class="badge <?= $badge ?>">
              <?= $status ?>
            </span>
          </td>

          <td>
            <strong><?= Indonesia2Tgl($row['tgl_pensiun']) ?></strong><br>
            <small class="text-muted"><?= $row['tempat_lhr'] ?></small>
          </td>

          <td class="<?= $countClass ?>">
            <?= $count ?>
          </td>
        </tr>

        <?php } ?>

        </tbody>
      </table>
    </div>
  </div>
</div>



<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function () {
  $('#tabelPensiun').DataTable({
    paging: true,
    searching: true,
    ordering: true,
    pageLength: 4,
    lengthChange: false,

    // PENTING: jangan override urutan SQL
    order: [],

    columnDefs: [
      { orderable: false, targets: [2,4] }
    ],

    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
    }
  });
});
</script>


