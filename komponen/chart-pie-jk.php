<?php
include "dist/koneksi.php";

// --- 1. FILTER UNIT KERJA ---
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$kode_cabang_session = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';

$where_unit = '';
if ($hak_akses === 'kepala') {
    $unit = mysqli_real_escape_string($conn, $kode_cabang_session);
    $where_unit = "AND j.unit_kerja = '$unit'";
}

// --- 2. QUERY & AGREGASI DATA (PERBAIKAN LABEL GANDA) ---
// Kita ambil datanya, tapi hitung manual di PHP biar tidak ada label kembar
$sql = "SELECT p.jk, COUNT(DISTINCT p.id_peg) as total 
        FROM tb_pegawai p
        JOIN tb_jabatan j ON p.id_peg = j.id_peg
        WHERE p.status_aktif=1 
        AND j.status_jab = 'Aktif' 
        $where_unit 
        GROUP BY p.jk";

$hasil = mysqli_query($conn, $sql);

// Inisialisasi Hitungan
$total_l = 0;
$total_p = 0;

while ($data = mysqli_fetch_array($hasil)) {
    // Bersihkan data (trim spasi & uppercase)
    $jk_db = strtoupper(trim($data['jk']));
    
    // Logika Pengelompokan Pasti
    if ($jk_db == 'L' || $jk_db == 'LAKI-LAKI') {
        $total_l += $data['total'];
    } else {
        // Semua yang bukan L masuk ke Perempuan (termasuk P, Wanita, atau typo)
        $total_p += $data['total'];
    }
}

// Hitung Total Seluruh
$total_seluruh = $total_l + $total_p;

// Format Data untuk Chart.js (Hardcode Array biar urutan warna PASTI SAMA)
// Urutan: [Laki-laki, Perempuan]
$data_chart = "$total_l, $total_p"; 
?>

<div class="card border-0 shadow-sm rounded-lg" style="min-height: 380px;">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title fw-bold text-dark mb-0">
            <i class="fas fa-venus-mars text-pastel-blue me-2"></i> Komposisi Gender
        </h5>
    </div>

    <div class="card-body d-flex flex-column align-items-center justify-content-center pt-0">
        <div class="chart-container position-relative" style="height: 220px; width: 220px;">
            <canvas id="doughnutChartJK"></canvas>
            
            <div class="center-text">
                <h2 class="fw-bold mb-0 text-dark counter-value" data-target="<?= $total_seluruh ?>"><?= $total_seluruh ?></h2>
                <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Total</small>
            </div>
        </div>

        <div class="mt-4 w-100 px-3">
            <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                <span class="d-flex align-items-center text-muted small fw-bold">
                    <span class="dot bg-pastel-blue me-2"></span> Laki-laki
                </span>
                <span class="fw-bold text-dark small"><?= $total_l ?> Pegawai</span> 
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center text-muted small fw-bold">
                    <span class="dot bg-pastel-pink me-2"></span> Perempuan
                </span>
                <span class="fw-bold text-dark small"><?= $total_p ?> Pegawai</span>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded-lg { border-radius: 15px; }
    
    /* Center Text */
    .center-text {
        position: absolute; top: 55%; left: 50%;
        transform: translate(-50%, -50%); text-align: center;
        pointer-events: none; z-index: 0;
    }
    
    /* WARNA PASTEL SOFT (Modern) */
    /* Biru Langit Lembut */
    .bg-pastel-blue { background-color: #90CAF9 !important; } 
    .text-pastel-blue { color: #90CAF9 !important; }
    
    /* Pink Lembut (Dusty Pink) */
    .bg-pastel-pink { background-color: #F48FB1 !important; } 
    
    .dot { height: 12px; width: 12px; border-radius: 4px; display: inline-block; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('doughnutChartJK').getContext('2d');
    
    // Data Kita Paksa Fix 2 Kategori: [Laki, Perempuan]
    // Biar warnanya nggak ketukar
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Laki-laki', 'Perempuan'], 
            datasets: [{
                data: [<?= $data_chart; ?>], // Output: misal 50, 40
                backgroundColor: [
                    '#90CAF9', // Biru Soft (Laki-laki)
                    '#F48FB1'  // Pink Soft (Perempuan)
                ],
                hoverBackgroundColor: [
                    '#64B5F6', // Biru agak gelap pas hover
                    '#F06292'  // Pink agak gelap pas hover
                ],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%', 
            plugins: {
                legend: { display: false }, // Kita pakai legend manual di HTML biar rapi
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#555',
                    bodyColor: '#666',
                    borderColor: '#eee',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            var value = context.raw;
                            var total = <?= $total_seluruh > 0 ? $total_seluruh : 1 ?>;
                            var persen = ((value / total) * 100).toFixed(1) + '%';
                            return ' ' + context.label + ': ' + value + ' (' + persen + ')';
                        }
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
});
</script>