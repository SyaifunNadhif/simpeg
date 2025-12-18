<?php
include_once "dist/koneksi.php";

// --- 1. FILTER & SECURITY ---
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$kode_cabang_session = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$where_unit = '';

if ($hak_akses === 'kepala') {
    $unit = mysqli_real_escape_string($conn, $kode_cabang_session);
    $where_unit = "AND j.unit_kerja = '$unit'";
}

// --- 2. LOGIC DATA (PHP Array) ---
$labels = [];
$values = [];

$sql_pend = "
SELECT 
    CASE WHEN highest_edu IS NULL OR highest_edu = '' THEN 'Belum Input' ELSE highest_edu END as jenjang_fix,
    COUNT(*) as total
FROM (
    SELECT p.id_peg,
        (SELECT jenjang FROM tb_pendidikan edu WHERE edu.id_peg = p.id_peg 
         ORDER BY FIELD(jenjang, 'S3', 'S2', 'S1', 'D4', 'D3', 'D2', 'D1', 'SMA', 'SMK', 'SMP', 'SD') ASC LIMIT 1) as highest_edu
    FROM tb_pegawai p JOIN tb_jabatan j ON p.id_peg = j.id_peg
    WHERE p.status_aktif = 1 AND j.status_jab = 'Aktif' $where_unit
) as data_final
GROUP BY jenjang_fix
ORDER BY FIELD(jenjang_fix, 'S3', 'S2', 'S1', 'D4', 'D3', 'D2', 'D1', 'SMA', 'SMK', 'SMP', 'SD', 'Belum Input')";

$hasil_pend = mysqli_query($conn, $sql_pend);

if ($hasil_pend) {
    while ($d = mysqli_fetch_assoc($hasil_pend)) {
        $labels[] = $d['jenjang_fix'];
        $values[] = (int)$d['total'];
    }
}
?>

<div class="card card-modern h-100 mb-4" style="min-height: 450px;">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="fw-bold text-soft mb-0">
            <i class="fas fa-graduation-cap text-pastel-purple me-2"></i> Jenjang Pendidikan
        </h6>
    </div>
    
    <div class="card-body d-flex flex-column justify-content-center">
        <div style="height: 300px; width: 100%;">
            <canvas id="barChartPendidikan"></canvas>
        </div>
        <div class="text-center mt-3">
            <small class="text-muted fw-bold ls-1" style="font-size: 10px;">DATA PENDIDIKAN TERTINGGI PEGAWAI</small>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const ctxElement = document.getElementById('barChartPendidikan');
    
    if (ctxElement && typeof Chart !== 'undefined') {
        const ctx = ctxElement.getContext('2d');
        
        // Font Sistem Offline
        const systemFont = "'-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif";

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Jumlah Pegawai', // Tambah label biar aman
                    data: <?= json_encode($values) ?>,
                    backgroundColor: [
                        '#BA68C8', '#CE93D8', '#90CAF9', '#4DD0E1', '#4DB6AC', 
                        '#FFF176', '#FFB74D', '#A1887F', '#E0E0E0'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                // --- FIX CHART.JS V2 COMPATIBILITY ---
                legend: {
                    display: false // Ini cara hide legend di Chart.js v2
                },
                tooltips: { // Di v2 namanya 'tooltips' bukan 'plugins.tooltip'
                    backgroundColor: '#fff',
                    titleFontColor: '#555',
                    titleFontFamily: systemFont,
                    bodyFontColor: '#666',
                    bodyFontFamily: systemFont,
                    borderColor: '#f0f0f0',
                    borderWidth: 1,
                    xPadding: 10,
                    yPadding: 10,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            // Logic ambil data di v2 sedikit beda
                            var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                            return ' Total: ' + value + ' Pegawai';
                        }
                    }
                },
                scales: {
                    yAxes: [{ // Di v2 namanya 'yAxes' (array)
                        ticks: {
                            beginAtZero: true,
                            precision: 0,
                            fontFamily: systemFont,
                            fontSize: 11,
                            fontColor: '#888'
                        },
                        gridLines: {
                            borderDash: [5, 5],
                            drawBorder: false,
                            color: '#f2f2f2'
                        }
                    }],
                    xAxes: [{ // Di v2 namanya 'xAxes' (array)
                        gridLines: {
                            display: false
                        },
                        ticks: {
                            fontFamily: systemFont,
                            fontSize: 11,
                            fontColor: '#666'
                        }
                    }]
                }
            }
        });
    }
});
</script>