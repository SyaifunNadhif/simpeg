<?php
include_once "dist/koneksi.php";

// --- 1. FILTER UNIT KERJA ---
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

while ($data = mysqli_fetch_array($hasil)) {
    $status_db = strtoupper(trim($data['status_kepeg']));
    $jumlah = $data['total'];

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

// --- 4. SIAPKAN DATA CHART ---
$labels_chart = "";
$data_chart   = "";

foreach ($kategori_final as $label => $nilai) {
    if ($nilai > 0) { 
        $labels_chart .= "'$label', ";
        $data_chart   .= "$nilai, ";
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
    const ctxStatus = document.getElementById('chartStatusPeg');
    
    if(ctxStatus) {
        new Chart(ctxStatus, {
            type: 'bar',
            data: {
                labels: [<?= $labels_chart; ?>],
                datasets: [{
                    label: 'Jumlah Pegawai',
                    data: [<?= $data_chart; ?>],
                    backgroundColor: [
                        '#81C784', // Tetap (Hijau)
                        '#64B5F6', // Capeg (Biru)
                        '#FFD54F', // Kontrak (Kuning)
                        '#E57373', // Outsource (Merah)
                        '#BA68C8'  // Lainnya (Ungu)
                    ],
                    borderRadius: 6,
                    // --- BIKIN BAR LEBIH GEMUK DI SINI ---
                    barPercentage: 0.7, 
                    categoryPercentage: 0.8,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                
                // --- INI SOLUSINYA BROTHER! (Smart Hover) ---
                interaction: {
                    mode: 'index',     // Tooltip muncul per kolom (bukan per pixel)
                    intersect: false,  // Mouse TIDAK HARUS kena batang warna, cukup di area lurusnya
                },
                // -------------------------------------------

                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#555',
                        bodyColor: '#666',
                        borderColor: '#f0f0f0', borderWidth: 1, padding: 10,
                        callbacks: {
                            label: function(ctx) {
                                return ' Total: ' + ctx.raw + ' Pegawai';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], drawBorder: false },
                        ticks: { precision: 0, font: {size: 10} }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: {size: 10} }
                    }
                }
            }
        });
    }
});
</script>