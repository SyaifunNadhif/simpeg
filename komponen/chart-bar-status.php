<?php
include "dist/koneksi.php";

$status_peg = "";
$jmlstat = "";
$sql = "SELECT status_kepeg, COUNT(*) as 'total' FROM tb_pegawai 
        WHERE status_aktif=1 
        GROUP BY status_kepeg 
        ORDER BY FIELD(status_kepeg, 'Kontrak', 'Calon Pegawai','Tetap')";
$hasil = mysqli_query($conn, $sql) or die(mysqli_error($conn));

while ($data = mysqli_fetch_array($hasil)) {
  $jk = $data['status_kepeg'];
  $status_peg .= "'$jk', ";
  $jum = $data['total'];
  $jmlstat .= "$jum, ";
}

$status_peg = rtrim($status_peg, ", ");
$jmlstat = rtrim($jmlstat, ", ");
?>

<!-- Card Container -->
<div class="card card-info">
  <div class="card-header">
    <h3 class="card-title">Berdasarkan Status Kepegawaian</h3>
  </div>
  <div class="card-body">
    <canvas id="StatusPeg" style="min-width: 250px; height: 250px; margin: 0 auto"></canvas>
  </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Chart Script -->
<script>
  var ctx = document.getElementById('StatusPeg').getContext('2d');
  var chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [<?= $status_peg; ?>],
      datasets: [{
        label: 'Jumlah Pegawai',
        backgroundColor: ['Tomato', 'Orange', 'Teal'],
        borderColor: 'rgba(0,0,0,0.1)',
        borderWidth: 1,
        data: [<?= $jmlstat; ?>]
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
</script>
