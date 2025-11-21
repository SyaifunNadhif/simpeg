<?php
include "dist/koneksi.php";

// Persiapan data chart
$jenis_kelamin = "";
$jmljk = "";

$sql = "SELECT jk, COUNT(*) as total FROM tb_pegawai WHERE status_aktif=1 GROUP BY jk";
$hasil = mysqli_query($conn, $sql);

while ($data = mysqli_fetch_array($hasil)) {
  $jk = $data['jk'];
  $jenis_kelamin .= "'$jk', ";
  $jmljk .= "$data[total], ";
}
?>

<!-- Card Container -->
<div class="card card-info">
  <div class="card-header">
    <h3 class="card-title">Berdasarkan Jenis Kelamin</h3>
  </div>
  <div class="card-body">
    <canvas id="pieChartJK" style="min-height: 250px; height: 250px; max-height: 300px; max-width: 100%;"></canvas>
  </div>
</div>

<!-- Script Chart -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('pieChartJK').getContext('2d');
    new Chart(ctx, {
      type: 'pie',
      data: {
        labels: [<?php echo $jenis_kelamin; ?>],
        datasets: [{
          label: 'Jenis Kelamin',
          data: [<?php echo $jmljk; ?>],
          backgroundColor: [
            'rgba(60,141,188,0.9)', // Laki-laki
            'rgba(210,214,222,1)'   // Perempuan
          ],
          borderColor: '#fff',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top'
          }
        }
      }
    });
  });
</script>

