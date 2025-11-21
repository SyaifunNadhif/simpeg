<?php
include "dist/koneksi.php";

// --- AMBIL DATA SESSION UNTUK FILTER ---
// Diasumsikan variabel ini sudah diambil dari session di file utama dashboard
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$kode_cabang_session = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';

// --- TENTUKAN KONDISI WHERE BERDASARKAN HAK AKSES ---
$where = '';

if ($hak_akses === 'kepala') {
    // Kepala: filter berdasarkan unit kerja/cabang
    $unit = mysqli_real_escape_string($conn, $kode_cabang_session);
    // Kita akan join ke tb_jabatan untuk memfilter unit kerja
    $where = "AND j.unit_kerja = '$unit'";
}
// Admin: $where tetap kosong, menampilkan semua data.

function get_masa_kerja_query($conn, $min_tahun, $max_tahun, $where_unit) {
    $where_kerja = "";
    
    // Logika Batas Masa Kerja
    if ($min_tahun > 0) {
        $where_kerja = "AND (YEAR(CURDATE()) - YEAR(p.tmt_kerja)) >= $min_tahun ";
    }
    if ($max_tahun > 0) {
        if ($min_tahun > 0 && $max_tahun > $min_tahun) {
            $where_kerja .= "AND (YEAR(CURDATE()) - YEAR(p.tmt_kerja)) <= $max_tahun ";
        } elseif ($max_tahun > 0 && $min_tahun == 0) {
             $where_kerja .= "AND (YEAR(CURDATE()) - YEAR(p.tmt_kerja)) <= $max_tahun ";
        }
    }
    
    // --- QUERY FIX: Tambahkan j.status_jab = 'Aktif' dan p.status_aktif=1 ---
    $query_sql = "
        SELECT COUNT(DISTINCT p.id_peg) AS total_pegawai_unik
        FROM tb_pegawai p
        JOIN tb_jabatan j ON p.id_peg = j.id_peg
        WHERE p.status_aktif=1 
          AND j.status_jab = 'Aktif' /* <--- KONDISI TAMBAHAN AGAR SAMA DENGAN JML PEGAWAI */
        $where_kerja 
        $where_unit
    ";
    
    $result = mysqli_query($conn, $query_sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['total_pegawai_unik'];
    }
    return 0;
}

// 1. Masa Kerja <= 10 Tahun (0 - 10)
// WHERE (YEAR(CURDATE()) - YEAR(tmt_kerja)) <= 10
$jml1 = get_masa_kerja_query($conn, 0, 10, $where);

// 2. Masa Kerja 10 - 20 Tahun
// WHERE (YEAR(CURDATE()) - YEAR(tmt_kerja)) > 10 AND <= 20
$jml2 = get_masa_kerja_query($conn, 11, 20, $where);

// 3. Masa Kerja 20 - 30 Tahun
// WHERE (YEAR(CURDATE()) - YEAR(tmt_kerja)) > 20 AND <= 30
$jml3 = get_masa_kerja_query($conn, 21, 30, $where);

// 4. Masa Kerja > 30 Tahun (min 31, max tak terbatas)
// WHERE (YEAR(CURDATE()) - YEAR(tmt_kerja)) > 30
$jml4 = get_masa_kerja_query($conn, 31, 0, $where);
?>

<style>
    /* Styling tetap sama */
    .info-box-text-wrap {
        white-space: normal !important; /* Agar teks panjang turun ke bawah */
        font-size: 14px;
        text-transform: uppercase;
        color: #6c757d;
        font-weight: 600;
        line-height: 1.2;
    }
    .info-box-number-custom {
        font-size: 24px;
        font-weight: 700;
        margin-top: 5px;
    }
    .card-outline-tabs {
        border-top: 3px solid #007bff;
    }
</style>

<div class="card shadow-sm card-outline-tabs">
    <div class="card-header border-0 ui-sortable-handle">
        <h3 class="card-title">
            <i class="fas fa-business-time mr-1"></i> Statistik Jumlah Pegawai Per Masa Kerja
        </h3>
    </div>
    
    <div class="card-body">
        <div class="row">
            <?php
            // Array Data untuk Looping
            $boxes = [
                ["label" => "Masa Kerja < 10 Tahun", "jumlah" => $jml1, "bg" => "info"],
                ["label" => "Masa Kerja 11 - 20 Tahun", "jumlah" => $jml2, "bg" => "success"], // Label disesuaikan
                ["label" => "Masa Kerja 21 - 30 Tahun", "jumlah" => $jml3, "bg" => "warning"], // Label disesuaikan
                ["label" => "Masa Kerja > 30 Tahun", "jumlah" => $jml4, "bg" => "danger"],
            ];

            foreach ($boxes as $b): 
            ?>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-<?= $b['bg'] ?> elevation-1">
                        <i class="fas fa-user-clock"></i>
                    </span>

                    <div class="info-box-content">
                        <span class="info-box-text info-box-text-wrap"><?= $b['label'] ?></span>
                        
                        <span class="info-box-number info-box-number-custom text-<?= $b['bg'] ?>">
                            <?= number_format($b['jumlah'], 0, ",", ".") ?> 
                            <small style="font-size: 14px; color: #999; font-weight:normal;">Pegawai</small>
                        </span>
                    </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>