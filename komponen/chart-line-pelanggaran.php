<?php
// Bagian atas cukup ambil TAHUN UNIK saja untuk dropdown
// Data chart diambil via AJAX nanti
$tahunList = [];
$tahunQuery = mysqli_query($conn, "SELECT DISTINCT YEAR(tgl_sk) AS tahun FROM tb_hukuman ORDER BY tahun DESC");
while ($row = mysqli_fetch_assoc($tahunQuery)) {
  $tahunList[] = $row['tahun'];
}
// Default tahun sekarang
$tahun_sekarang = date('Y');
?>

<div class="card card-modern h-100 mb-4" style="min-height: 450px;">
  <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
    <h6 class="fw-bold text-soft mb-0">
      <i class="fas fa-chart-line text-pastel-red me-2"></i> Tren Pelanggaran
    </h6>
    
    <div class="card-tools">
      <select id="filter_tahun_pelanggaran" class="form-control form-control-sm border-0 bg-light text-muted fw-bold" style="width: 100px;">
        <?php foreach ($tahunList as $t): ?>
          <option value="<?= $t ?>" <?= $t == $tahun_sekarang ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="card-body d-flex flex-column justify-content-center">
    <div style="height: 300px; width: 100%;">
      <canvas id="lineChartPelanggaran"></canvas>
    </div>
    <div class="text-center mt-3">
       <small class="text-muted fw-bold ls-1" style="font-size: 10px;">DATA PELANGGARAN PER BULAN</small>
    </div>
  </div>
</div>

<style>
  .text-pastel-red { color: #E57373 !important; } /* Merah Soft */
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const ctxLine = document.getElementById('lineChartPelanggaran').getContext('2d');
  
  // 1. Inisialisasi Chart Kosong Dulu
  let pelanggaranChart = new Chart(ctxLine, {
    type: 'line',
    data: {
      labels: [], // Nanti diisi AJAX
      datasets: [{
        label: 'Jumlah Pelanggaran',
        data: [], // Nanti diisi AJAX
        borderColor: '#E57373', // Warna Garis (Pastel Red)
        backgroundColor: 'rgba(229, 115, 115, 0.1)', // Warna Arsiran bawah garis
        borderWidth: 2,
        pointBackgroundColor: '#fff',
        pointBorderColor: '#E57373',
        pointRadius: 4,
        pointHoverRadius: 6,
        fill: true,
        tension: 0.4 // Garis melengkung (smooth)
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#fff', titleColor: '#555', bodyColor: '#666',
            borderColor: '#f0f0f0', borderWidth: 1, padding: 10,
            callbacks: {
                label: function(ctx) { return ' Total: ' + ctx.raw + ' Kasus'; }
            }
        }
      },
      scales: {
        y: { 
           beginAtZero: true, 
           grid: { borderDash: [5,5], drawBorder: false },
           ticks: { stepSize: 1, font:{size:10} } // Step 1 biar gak ada koma (kasus orang masak 0.5)
        },
        x: { 
           grid: { display: false },
           ticks: { font:{size:10} }
        }
      }
    }
  });

  // 2. Fungsi Load Data via AJAX
  function loadDataPelanggaran(tahun) {
    // Ganti path sesuai letak file ajax kamu
    fetch(`komponen/get_data_pelanggaran.php?tahun=${tahun}`)
      .then(response => response.json())
      .then(result => {
        // Update Data Chart
        pelanggaranChart.data.labels = result.labels;
        pelanggaranChart.data.datasets[0].data = result.data;
        pelanggaranChart.update(); // Render ulang chart dengan animasi
      })
      .catch(error => console.error('Error fetching data:', error));
  }

  // 3. Load Data Pertama Kali (Tahun Sekarang)
  const selectTahun = document.getElementById('filter_tahun_pelanggaran');
  loadDataPelanggaran(selectTahun.value);

  // 4. Event Listener saat Ganti Tahun
  selectTahun.addEventListener('change', function() {
    loadDataPelanggaran(this.value);
  });
});
</script>