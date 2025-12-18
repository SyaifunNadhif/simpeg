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

// --- 2. QUERY RAW DATA ---
$sql = "SELECT p.status_kepeg, COUNT(DISTINCT p.id_peg) as total 
        FROM tb_pegawai p 
        JOIN tb_jabatan j ON p.id_peg = j.id_peg
        WHERE p.status_aktif=1 
        AND j.status_jab = 'Aktif' 
        $where_unit 
        GROUP BY p.status_kepeg";

$hasil = mysqli_query($conn, $sql);

// --- 3. LOGIKA MERGE DATA ---
$kategori_final = [
    'Pegawai Tetap' => 0,
    'Calon Pegawai' => 0,
    'Kontrak'       => 0,
    'Outsourcing'   => 0,
    'Lainnya'       => 0
];

if ($hasil) {
    while ($data = mysqli_fetch_array($hasil)) {
        // Sanitasi output dari DB biar aman XSS
        $status_db = strtoupper(trim(htmlspecialchars($data['status_kepeg'])));
        $jumlah = (int)$data['total'];

        if ($status_db == 'TETAP' || $status_db == 'PEGAWAI TETAP') {
            $kategori_final['Pegawai Tetap'] += $jumlah;
        } 
        elseif ($status_db == 'CAPEG' || $status_db == 'CALON PEGAWAI') {
            $kategori_final['Calon Pegawai'] += $jumlah;
        }
        elseif (strpos($status_db, 'KONTRAK') !== false || $status_db == 'PKWT') {
            $kategori_final['Kontrak'] += $jumlah;
        }
        elseif (strpos($status_db, 'SOURCE') !== false || $status_db == 'THL') {
            $kategori_final['Outsourcing'] += $jumlah;
        }
        else {
            $kategori_final['Lainnya'] += $jumlah;
        }
    }
}

// --- 4. SIAPKAN DATA CHART (ARRAY) ---
// Kita pakai Array PHP lalu di json_encode, JANGAN string manual biar aman.
$labels = [];
$values = [];

foreach ($kategori_final as $label => $nilai) {
    if ($nilai > 0) { 
        $labels[] = $label;
        $values[] = $nilai;
    }
}
?>

<div class="card card-modern h-100 mb-4" style="min-height: 400px;">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="fw-bold text-soft mb-0">
            <i class="fas fa-id-card-alt text-pastel-green me-2"></i> Status Kepegawaian
        </h6>
    </div>
    
    <div class="card-body d-flex flex-column justify-content-center">
        <div style="height: 300px; width: 100%;">
            <canvas id="chartStatusPeg"></canvas>
        </div>
        <div class="text-center mt-3">
            <small class="text-muted fw-bold ls-1" style="font-size: 10px;">DISTRIBUSI PEGAWAI AKTIF</small>
        </div>
    </div>
</div>

<style>
    .text-pastel-green { color: #81C784 !important; }
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const ctxElement = document.getElementById('chartStatusPeg');
    
    // Cek element & Library Chart.js
    if(ctxElement && typeof Chart !== 'undefined') {
        const ctx = ctxElement.getContext('2d');

        // Font System (Offline Friendly)
        const systemFont = "'-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif";

        new Chart(ctx, {
            type: 'bar',
            data: {
                // Gunakan JSON Encode agar data aman dari error syntax JS
                labels: <?= json_encode($labels); ?>,
                datasets: [{
                    label: 'Jumlah Pegawai',
                    data: <?= json_encode($values); ?>,
                    backgroundColor: [
                        '#81C784', // Tetap (Hijau)
                        '#64B5F6', // Capeg (Biru)
                        '#FFD54F', // Kontrak (Kuning)
                        '#E57373', // Outsource (Merah)
                        '#BA68C8'  // Lainnya (Ungu)
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                
                // --- KONFIGURASI CHART.JS V2 (Anti Undefined) ---
                legend: { 
                    display: false // Hide legend di sini (root options)
                },
                tooltips: {
                    mode: 'index',      // Tooltip pintar
                    intersect: false,   // Gak perlu pas kena batang
                    backgroundColor: '#fff',
                    titleFontColor: '#555',
                    titleFontFamily: systemFont,
                    bodyFontColor: '#666',
                    bodyFontFamily: systemFont,
                    borderColor: '#f0f0f0',
                    borderWidth: 1,
                    xPadding: 10,
                    yPadding: 10,
                    cornerRadius: 6,
                    displayColors: true,
                    callbacks: {
                        label: function(tooltipItem, data) {
                             // Logic ambil data v2
                             var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                             return ' Total: ' + value + ' Pegawai';
                        }
                    }
                },
                scales: {
                    yAxes: [{ // Pakai yAxes (Array) bukan y
                        ticks: {
                            beginAtZero: true,
                            precision: 0,
                            fontFamily: systemFont,
                            fontSize: 10
                        },
                        gridLines: {
                            borderDash: [5, 5],
                            drawBorder: false,
                            color: '#f2f2f2'
                        }
                    }],
                    xAxes: [{ // Pakai xAxes (Array) bukan x
                        gridLines: {
                            display: false
                        },
                        ticks: {
                            fontFamily: systemFont,
                            fontSize: 10
                        },
                        // Bikin batang lebih gemuk (v2 style)
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    }]
                }
            }
        });
    }
});
</script>