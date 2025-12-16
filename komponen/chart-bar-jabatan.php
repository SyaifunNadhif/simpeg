<?php
include "dist/koneksi.php";

$jabatan = [];
$jmlJabatan = [];

// Query Data
$sql = "SELECT jabatan, COUNT(*) as total FROM tb_jabatan GROUP BY jabatan ORDER BY jabatan ASC";
$hasil = mysqli_query($conn, $sql);

while ($data = mysqli_fetch_array($hasil)) {
  // Potong nama jabatan jika terlalu panjang biar grafik ga berantakan
  $nama_jabatan = $data['jabatan'];
  if (strlen($nama_jabatan) > 20) {
      $nama_jabatan = substr($nama_jabatan, 0, 20) . '...';
  }
  
  $jabatan[] = $nama_jabatan;
  $jmlJabatan[] = (int)$data['total'];
}
?>

<style>
.card-modern {
  border: none;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 40px rgba(0,0,0,0.04);
  margin-bottom: 2rem;
  overflow: hidden;
}

.card-modern .card-header {
  background: #ffffff;
  border-bottom: 1px solid #f2f4f8;
  padding: 25px 35px;
}

.title-group h3 {
  font-size: 1.3rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 5px;
}
.title-group p {
  font-size: 0.9rem;
  color: #94a3b8;
  margin-bottom: 0;
}
</style>

<div class="card card-modern">
  <div class="card-header">
    <div class="title-group">
      <h3>Statistik Pegawai</h3>
      <p>Jumlah pegawai berdasarkan jabatan saat ini</p>
    </div>
  </div>
  
  <div class="card-body">
    <div style="position: relative; height: 400px; width: 100%;">
      <canvas id="barChartJabatan"></canvas>
    </div>
  </div>
</div>

<script src="plugins/chart.js/Chart.min.js"></script> 

<script>
document.addEventListener('DOMContentLoaded', function () {
  var ctx = document.getElementById('barChartJabatan').getContext('2d');

  // 1. Buat Gradient Warna (Supaya Modern)
  var gradient = ctx.createLinearGradient(0, 0, 0, 400);
  gradient.addColorStop(0, 'rgba(14, 165, 233, 0.9)'); // Sky Blue Tebal
  gradient.addColorStop(1, 'rgba(56, 189, 248, 0.4)'); // Sky Blue Transparan

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($jabatan) ?>,
      datasets: [{
        label: 'Jumlah Pegawai',
        data: <?= json_encode($jmlJabatan) ?>,
        backgroundColor: gradient, // Pakai Gradient
        borderColor: '#0ea5e9',    // Garis tepi biru
        borderWidth: 1,
        borderRadius: 5,           // Ujung batang melengkung (Modern)
        barPercentage: 0.6,        // Lebar batang tidak terlalu gemuk
        hoverBackgroundColor: '#0284c7' // Warna saat di-hover mouse
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false // Hide legend karena judul sudah jelas
        },
        tooltip: {
          backgroundColor: '#1e293b',
          titleFont: { size: 13 },
          bodyFont: { size: 13 },
          padding: 10,
          cornerRadius: 8,
          displayColors: false // Hilangkan kotak warna di tooltip
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: '#f1f5f9',      // Warna grid sangat halus
            borderDash: [5, 5],    // Grid putus-putus (Modern look)
            drawBorder: false      // Hilangkan garis batas kiri
          },
          ticks: {
            color: '#64748b',
            font: { size: 11, family: "'Inter', sans-serif" }
          }
        },
        x: {
          grid: {
            display: false,        // Hilangkan grid vertikal biar bersih
            drawBorder: false
          },
          ticks: {
            color: '#64748b',
            font: { size: 10, family: "'Inter', sans-serif" },
            maxRotation: 45,       // Miringkan text biar muat
            minRotation: 45,
            autoSkip: true,        // Skip label kalau terlalu padat
            maxTicksLimit: 20      // Batasi jumlah label yg tampil
          }
        }
      },
      animation: {
        duration: 2000,
        easing: 'easeOutQuart' // Animasi smooth saat load
      }
    }
  });
});
</script>