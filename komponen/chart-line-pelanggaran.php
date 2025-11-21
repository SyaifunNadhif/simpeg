<?php
include "dist/koneksi.php";

$namaBulan = [1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"Mei",6=>"Jun",7=>"Jul",8=>"Ags",9=>"Sep",10=>"Okt",11=>"Nov",12=>"Des"];

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$labels = [];
$dataJumlah = [];

$sql = "SELECT MONTH(tgl_sk) AS bulan, COUNT(id_peg) AS total 
        FROM tb_hukuman 
        WHERE YEAR(tgl_sk) = $tahun
        GROUP BY MONTH(tgl_sk)
        ORDER BY MONTH(tgl_sk)";
$hasil = mysqli_query($conn, $sql);

while ($data = mysqli_fetch_array($hasil)) {
  $labels[] = $namaBulan[(int)$data['bulan']];
  $dataJumlah[] = (int)$data['total'];
}

// Ambil daftar tahun unik dari data hukuman
$tahunList = [];
$tahunQuery = mysqli_query($conn, "SELECT DISTINCT YEAR(tgl_sk) AS tahun FROM tb_hukuman ORDER BY tahun DESC");
while ($row = mysqli_fetch_assoc($tahunQuery)) {
  $tahunList[] = $row['tahun'];
}
?>

<div class="card card-danger">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Daftar Pelanggaran Pegawai Tahun <?= $tahun ?></h3>
    <form method="get" class="form-inline">
      <label for="tahun" class="mr-2 mb-0">Tampilkan tahun:</label>
      <select name="tahun" id="tahun" class="form-control form-control-sm" onchange="this.form.submit()">
        <?php foreach ($tahunList as $t): ?>
          <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
  <div class="card-body">
    <canvas id="lineChartPelanggaran" style="min-height: 250px; height: 250px; width: 100%;"></canvas>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const ctx = document.getElementById('lineChartPelanggaran').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'Jumlah Pelanggaran',
        data: <?= json_encode($dataJumlah) ?>,
        fill: false,
        borderColor: '#e74c3c',
        backgroundColor: '#f1948a',
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
});
</script>
