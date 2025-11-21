<?php
include "dist/koneksi.php";

// Persiapan data chart
$pendidikan = "";
$jmlpend = "";

$sql = "SELECT jenjang, COUNT(*) as total FROM tb_pendidikan GROUP BY jenjang 
        ORDER BY FIELD(jenjang, 'SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3')";
$hasil = mysqli_query($conn, $sql);

while ($data = mysqli_fetch_array($hasil)) {
  $pend = $data['jenjang'];
  $pendidikan .= "'$pend', ";
  $jmlpend .= $data['total'] . ", ";
}
?>

<!-- Card Container -->
<div class="card card-info">
  <div class="card-header">
    <h3 class="card-title">Berdasarkan Jenjang Pendidikan</h3>
  </div>
  <div class="card-body">
    <canvas id="barChartPendidikan" style="min-height: 250px; height: 250px; max-height: 300px; max-width: 100%;"></canvas>
  </div>
</div>

<!-- Script Chart -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('barChartPendidikan').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: [<?php echo $pendidikan; ?>],
        datasets: [{
          label: 'Jumlah Pegawai',
          data: [<?php echo $jmlpend; ?>],
          backgroundColor: [
          '#A7C7E7', '#AED6F1', '#85C1E9', '#76D7C4', '#F7DC6F', '#F8C471', '#E59866', '#BB8FCE', '#F5B7B1', '#D2B4DE'
        ],
          borderColor: 'rgba(200,200,200,0.5)',
          borderWidth: 1
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