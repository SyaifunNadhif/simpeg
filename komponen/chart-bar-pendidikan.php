<?php
include_once "dist/koneksi.php";

// Filter Unit Kerja
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$kode_cabang_session = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$where_unit = '';

if ($hak_akses === 'kepala') {
    $unit = mysqli_real_escape_string($conn, $kode_cabang_session);
    $where_unit = "AND j.unit_kerja = '$unit'";
}

// Logic Pendidikan Tertinggi
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
$label_pend = ""; $data_pend = "";

while ($d = mysqli_fetch_array($hasil_pend)) {
    $label_pend .= "'$d[jenjang_fix]', ";
    $data_pend .= "$d[total], ";
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
    // Render Chart Pendidikan
    const ctx = document.getElementById('barChartPendidikan');
    if(ctx){
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?= $label_pend ?>],
                datasets: [{
                    data: [<?= $data_pend ?>],
                    backgroundColor: [
                        '#BA68C8', '#CE93D8', '#90CAF9', '#4DD0E1', '#4DB6AC', 
                        '#FFF176', '#FFB74D', '#A1887F', '#E0E0E0'
                    ],
                    borderRadius: 6, barPercentage: 0.5, borderWidth: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff', titleColor: '#555', bodyColor: '#666',
                        borderColor: '#f0f0f0', borderWidth: 1, padding: 10,
                        callbacks: { label: function(ctx) { return ' Total: ' + ctx.raw + ' Pegawai'; } }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5,5], drawBorder: false }, ticks: { precision:0, font:{size:10} } },
                    x: { grid: { display: false }, ticks: { font:{size:10} } }
                }
            }
        });
    }
});
</script>