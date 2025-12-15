<?php
// --- BAGIAN 1: LOGIKA PHP (SERVER SIDE) ---
include "dist/koneksi.php";

// Cek Session
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$kode_cabang_session = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';

// Filter Query (Unit Kerja)
$where_unit = '';
if ($hak_akses === 'kepala') {
    $unit = mysqli_real_escape_string($conn, $kode_cabang_session);
    $where_unit = "AND j.unit_kerja = '$unit'";
}

// Inisialisasi Variabel
$jmlpegawai = 0;
$jmlpurna = 0;
$jmlpunishment = 0;
$jmldiklat = 0;
$tahun_sekarang = date('Y');

// --- PERBAIKAN UTAMA DISINI (QUERY PEGAWAI DISAMAKAN LOGIKANYA) ---
// Menggunakan JOIN tb_pegawai & tb_jabatan, serta status_aktif = 1
$sql_pegawai = "SELECT COUNT(DISTINCT p.id_peg) AS total 
                FROM tb_pegawai p 
                JOIN tb_jabatan j ON p.id_peg = j.id_peg 
                WHERE p.status_aktif = 1 
                AND j.status_jab = 'Aktif' 
                $where_unit";

$pegawai_q = mysqli_query($conn, $sql_pegawai);
if ($pegawai_q && $row = mysqli_fetch_assoc($pegawai_q)) {
    $jmlpegawai = $row['total'];
}

// Query 2: Non Aktif (Purna/Keluar tahun ini)
$purna = mysqli_query($conn, "SELECT COUNT(DISTINCT a.id_peg) AS total 
    FROM tb_pegawai a 
    JOIN tb_mutasi b ON a.id_peg = b.id_peg 
    JOIN tb_jabatan j ON a.id_peg = j.id_peg 
    WHERE a.status_aktif = 3 
    AND YEAR(b.tgl_mutasi) = YEAR(CURRENT_DATE()) 
    $where_unit");
if ($purna && $row = mysqli_fetch_assoc($purna)) $jmlpurna = $row['total'];

// Query 3: Pelanggaran
$punishment = mysqli_query($conn, "SELECT COUNT(DISTINCT h.id_peg) AS total 
    FROM tb_hukuman h 
    JOIN tb_jabatan j ON h.id_peg = j.id_peg 
    WHERE YEAR(h.tgl_sk) = YEAR(CURRENT_DATE()) 
    $where_unit");
if ($punishment && $row = mysqli_fetch_assoc($punishment)) $jmlpunishment = $row['total'];

// Query 4: Diklat
$diklat = mysqli_query($conn, "SELECT COUNT(DISTINCT d.diklat) AS total 
    FROM tb_diklat d 
    JOIN tb_jabatan j ON d.id_peg = j.id_peg 
    WHERE d.tahun = YEAR(CURRENT_DATE()) 
    $where_unit");
if ($diklat && $row = mysqli_fetch_assoc($diklat)) $jmldiklat = $row['total'];
?>

<div class="row">
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-hover border-0 shadow-sm h-100 animate-fade-up" style="animation-delay: 0.1s;">
            <div class="card-body p-4 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="icon-box bg-soft-primary text-primary">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <div class="text-end">
                        <h2 class="fw-bold mb-0 text-dark counter-value" data-target="<?= $jmlpegawai ?>">0</h2>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted text-uppercase fw-bold small ls-1 mb-0">Total Pegawai</h6>
                </div>
                <a href="home-admin.php?page=form-view-data-pegawai" class="stretched-link text-decoration-none d-flex align-items-center small fw-bold text-primary action-link">
                    Lihat Detail <i class="fas fa-arrow-right ms-2"></i>
                </a>
                <div class="shape-bg bg-primary"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-hover border-0 shadow-sm h-100 animate-fade-up" style="animation-delay: 0.2s;">
            <div class="card-body p-4 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="icon-box bg-soft-danger text-danger">
                        <i class="fas fa-user-times fa-lg"></i>
                    </div>
                    <div class="text-end">
                        <h2 class="fw-bold mb-0 text-dark counter-value" data-target="<?= $jmlpurna ?>">0</h2>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted text-uppercase fw-bold small ls-1 mb-0">Non Aktif (<?= $tahun_sekarang ?>)</h6>
                </div>
                <a href="home-admin.php?page=form-view-data-mutasi" class="stretched-link text-decoration-none d-flex align-items-center small fw-bold text-danger action-link">
                    Lihat Detail <i class="fas fa-arrow-right ms-2"></i>
                </a>
                <div class="shape-bg bg-danger"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-hover border-0 shadow-sm h-100 animate-fade-up" style="animation-delay: 0.3s;">
            <div class="card-body p-4 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="icon-box bg-soft-warning text-warning">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                    <div class="text-end">
                        <h2 class="fw-bold mb-0 text-dark counter-value" data-target="<?= $jmlpunishment ?>">0</h2>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted text-uppercase fw-bold small ls-1 mb-0">Pelanggaran (<?= $tahun_sekarang ?>)</h6>
                </div>
                <a href="home-admin.php?page=form-view-data-pelanggaran" class="stretched-link text-decoration-none d-flex align-items-center small fw-bold text-warning action-link">
                    Lihat Detail <i class="fas fa-arrow-right ms-2"></i>
                </a>
                <div class="shape-bg bg-warning"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-hover border-0 shadow-sm h-100 animate-fade-up" style="animation-delay: 0.4s;">
            <div class="card-body p-4 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="icon-box bg-soft-success text-success">
                        <i class="fas fa-chalkboard-teacher fa-lg"></i>
                    </div>
                    <div class="text-end">
                        <h2 class="fw-bold mb-0 text-dark counter-value" data-target="<?= $jmldiklat ?>">0</h2>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted text-uppercase fw-bold small ls-1 mb-0">Diklat (<?= $tahun_sekarang ?>)</h6>
                </div>
                <a href="home-admin.php?page=master-data-diklat" class="stretched-link text-decoration-none d-flex align-items-center small fw-bold text-success action-link">
                    Lihat Detail <i class="fas fa-arrow-right ms-2"></i>
                </a>
                <div class="shape-bg bg-success"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .card-hover {
        border-radius: 20px !important;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        background: #fff;
        z-index: 1;
    }
    .card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
    .icon-box {
        width: 55px;
        height: 55px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        transition: transform 0.4s ease;
    }
    .card-hover:hover .icon-box { transform: scale(1.1) rotate(5deg); }
    
    .bg-soft-primary { background-color: #e6f0ff; color: #007bff; }
    .bg-soft-danger  { background-color: #ffe6e9; color: #dc3545; }
    .bg-soft-warning { background-color: #fff8e1; color: #ffc107; }
    .bg-soft-success { background-color: #e6ffed; color: #28a745; }
    
    .shape-bg {
        position: absolute; bottom: -20px; right: -20px;
        width: 80px; height: 80px; border-radius: 50%;
        opacity: 0.08; z-index: -1; transition: all 0.5s ease;
    }
    .card-hover:hover .shape-bg { transform: scale(2.5); opacity: 0.1; }
    
    .ls-1 { letter-spacing: 1px; font-weight: 700; color: #adb5bd; }
    .action-link i { transition: transform 0.3s ease; }
    .card-hover:hover .action-link i { transform: translateX(6px); }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translate3d(0, 30px, 0); }
        to { opacity: 1; transform: translate3d(0, 0, 0); }
    }
    .animate-fade-up { animation-fill-mode: both; animation-duration: 0.8s; animation-name: fadeInUp; }
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const counters = document.querySelectorAll('.counter-value');
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const duration = 1500; 
        const increment = target / (duration / 16);
        let current = 0;
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.innerText = Math.ceil(current).toLocaleString('id-ID');
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = target.toLocaleString('id-ID');
            }
        };
        updateCounter();
    });
});
</script>