<?php
include "dist/koneksi.php";

$jabatan = [];
$jmlJabatan = [];

$sql = "SELECT jabatan, COUNT(*) as total FROM tb_jabatan GROUP BY jabatan ORDER BY jabatan";
$hasil = mysqli_query($conn, $sql);

while ($data = mysqli_fetch_array($hasil)) {
  $jabatan[] = $data['jabatan'];
  $jmlJabatan[] = (int)$data['total'];
}
?>

<div class="card card-info">
  <div class="card-header">
    <h3 class="card-title">Berdasarkan Jabatan</h3>
  </div>
  <div class="card-body">
    <canvas id="barChartJabatan" style="min-height: 300px; height: 300px; width: 100%;"></canvas>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const ctx = document.getElementById('barChartJabatan').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($jabatan) ?>,
      datasets: [{
        label: 'Jumlah Pegawai',
        data: <?= json_encode($jmlJabatan) ?>,
        backgroundColor: '#F7DC6F',
        borderColor: '#B7950B',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: 'x',
      scales: {
        y: {
          beginAtZero: true
        },
        x: {
          ticks: {
            autoSkip: false,
            maxRotation: 90,
            minRotation: 45
          }
        }
      }
    }
  });
});
</script>
