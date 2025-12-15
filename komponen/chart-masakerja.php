<?php
// --- LOGIKA PHP (SERVER SIDE) ---
include "dist/koneksi.php";

$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$kode_cabang_session = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';

// Menggunakan nama variabel $where_unit agar konsisten dengan file atas
$where_unit = '';
if ($hak_akses === 'kepala') {
    $unit = mysqli_real_escape_string($conn, $kode_cabang_session);
    $where_unit = "AND j.unit_kerja = '$unit'";
}

function get_masa_kerja_query($conn, $min_tahun, $max_tahun, $where_unit) {
    $where_kerja = "";
    if ($min_tahun > 0) $where_kerja = "AND (YEAR(CURDATE()) - YEAR(p.tmt_kerja)) >= $min_tahun ";
    if ($max_tahun > 0) {
        if ($min_tahun > 0 && $max_tahun > $min_tahun) $where_kerja .= "AND (YEAR(CURDATE()) - YEAR(p.tmt_kerja)) <= $max_tahun ";
        elseif ($max_tahun > 0 && $min_tahun == 0) $where_kerja .= "AND (YEAR(CURDATE()) - YEAR(p.tmt_kerja)) <= $max_tahun ";
    }
    
    // QUERY STRICT (Pegawai Aktif + Jabatan Aktif)
    $query_sql = "SELECT COUNT(DISTINCT p.id_peg) AS total_pegawai_unik 
                  FROM tb_pegawai p 
                  JOIN tb_jabatan j ON p.id_peg = j.id_peg 
                  WHERE p.status_aktif = 1 
                  AND j.status_jab = 'Aktif' 
                  $where_kerja 
                  $where_unit";
    
    $result = mysqli_query($conn, $query_sql);
    if ($result && $row = mysqli_fetch_assoc($result)) return $row['total_pegawai_unik'];
    return 0;
}

// Hitung Data (Menggunakan variabel $where_unit)
$jml1 = get_masa_kerja_query($conn, 0, 10, $where_unit);
$jml2 = get_masa_kerja_query($conn, 11, 20, $where_unit);
$jml3 = get_masa_kerja_query($conn, 21, 30, $where_unit);
$jml4 = get_masa_kerja_query($conn, 31, 0, $where_unit);

// Setup Array Data untuk Tampilan
$stats = [
    ["label" => "< 10 Tahun", "jml" => $jml1, "color" => "info", "icon" => "fa-user-clock", "bar" => "25%"],
    ["label" => "11 - 20 Tahun", "jml" => $jml2, "color" => "success", "icon" => "fa-user-check", "bar" => "50%"],
    ["label" => "21 - 30 Tahun", "jml" => $jml3, "color" => "warning", "icon" => "fa-user-tie", "bar" => "75%"],
    ["label" => "> 30 Tahun", "jml" => $jml4, "color" => "danger", "icon" => "fa-user-shield", "bar" => "100%"],
];
?>

<div class="card border-0 shadow-sm mt-4 rounded-lg overflow-hidden">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title fw-bold text-dark mb-0">
            <i class="fas fa-chart-pie text-primary me-2"></i> Statistik Masa Kerja
        </h5>
    </div>
    
    <div class="card-body bg-light-gray">
        <div class="row">
            <?php foreach ($stats as $index => $s): ?>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-0 h-100 animate-slide-up" style="animation-delay: <?= ($index * 0.1) ?>s;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-uppercase fw-bold text-muted small ls-1 mb-1"><?= $s['label'] ?></p>
                                    <h3 class="fw-bolder mb-0 text-dark counter-stat" data-target="<?= $s['jml'] ?>">0</h3>
                                    <span class="small text-muted">Pegawai</span>
                                </div>
                                <div class="icon-shape bg-soft-<?= $s['color'] ?> text-<?= $s['color'] ?>">
                                    <i class="fas <?= $s['icon'] ?>"></i>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar bg-<?= $s['color'] ?>" role="progressbar" 
                                         style="width: <?= $s['bar'] ?>;" aria-valuenow="<?= $s['jml'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
    /* Styling Card Utama */
    .rounded-lg { border-radius: 15px; }
    .bg-light-gray { background-color: #f8f9fa; }

    /* Styling Stat Card (Kotak Kecil) */
    .stat-card {
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.08);
    }

    /* Icon Shape (Kotak di Kanan Atas) */
    .icon-shape {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.2rem;
        transition: all 0.3s ease;
    }
    
    .bg-soft-info { background-color: rgba(23, 162, 184, 0.1); color: #17a2b8; }
    .bg-soft-success { background-color: rgba(40, 167, 69, 0.1); color: #28a745; }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }

    .stat-card:hover .icon-shape {
        transform: scale(1.1) rotate(10deg);
    }

    .ls-1 { letter-spacing: 0.5px; font-size: 0.7rem; }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide-up {
        animation: slideUp 0.6s ease-out forwards;
        opacity: 0; 
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const statCounters = document.querySelectorAll('.counter-stat');
    statCounters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const duration = 1000; 
        const increment = target / (duration / 16);
        let current = 0;
        
        const updateStat = () => {
            current += increment;
            if (current < target) {
                counter.innerText = Math.ceil(current).toLocaleString('id-ID');
                requestAnimationFrame(updateStat);
            } else {
                counter.innerText = target.toLocaleString('id-ID');
            }
        };
        updateStat();
    });
});
</script>