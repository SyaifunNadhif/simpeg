<?php
// FILE: pages/report/formasi.php
// VERSI: FINAL LENGKAP + FIX ORDER (Non-Master Paling Bawah)

if (session_id() == '') session_start();
include "dist/koneksi.php";

// --- 1. LOGIC FILTER ---
$kode_cabang = isset($_GET['kode_cabang']) ? mysqli_real_escape_string($conn, $_GET['kode_cabang']) : '';

$where_master_lingkup = "";
$join_filter_cabang = "";

if ($kode_cabang == 'PUSAT') {
    $where_master_lingkup = "WHERE m.lingkup IN ('KP', 'KANWIL')";
    $join_filter_cabang   = ""; 
} 
elseif ($kode_cabang != '') {
    $where_master_lingkup = "WHERE m.lingkup IN ('KC', 'KK')";
    $join_filter_cabang   = "AND j.unit_kerja = '$kode_cabang'";
} 
else {
    $where_master_lingkup = ""; 
    $join_filter_cabang   = ""; 
}

// --- 2. QUERY UTAMA ---
$query = "SELECT * FROM (
            -- BAGIAN 1: JABATAN DARI MASTER (Normal)
            SELECT 
                m.nama_jabatan, 
                m.kuota,
                COUNT(DISTINCT p.id_peg) AS terisi,
                (m.kuota - COUNT(DISTINCT p.id_peg)) AS kosong,
                'Master' as sumber,
                1 as urutan_prioritas  -- Prioritas UTAMA (Tampil Duluan)
            FROM tb_master_jabatan m
            
            LEFT JOIN tb_jabatan j ON j.jabatan = m.nama_jabatan 
                                   AND j.status_jab = 'Aktif' 
                                   $join_filter_cabang
            
            LEFT JOIN tb_pegawai p ON p.id_peg = j.id_peg AND p.status_aktif = '1'
            
            $where_master_lingkup
            
            GROUP BY m.nama_jabatan, m.kuota

            UNION ALL

            -- BAGIAN 2: JABATAN NON-MASTER (Anomali / Tidak Terdaftar)
            SELECT 
                j.jabatan as nama_jabatan, 
                0 as kuota, 
                COUNT(DISTINCT p.id_peg) AS terisi,
                (0 - COUNT(DISTINCT p.id_peg)) AS kosong,
                'NonMaster' as sumber,
                2 as urutan_prioritas  -- Prioritas KEDUA (Tampil Belakangan)
            FROM tb_jabatan j
            JOIN tb_pegawai p ON p.id_peg = j.id_peg AND p.status_aktif = '1'
            LEFT JOIN tb_master_jabatan m ON m.nama_jabatan = j.jabatan
            WHERE m.nama_jabatan IS NULL 
            AND j.status_jab = 'Aktif'
            $join_filter_cabang
            GROUP BY j.jabatan
          ) AS gabungan
          
          -- ORDER BY yang sudah diperbaiki:
          -- 1. Berdasarkan Prioritas (Master=1, NonMaster=2)
          -- 2. Berdasarkan Abjad Nama Jabatan
          ORDER BY urutan_prioritas ASC, nama_jabatan ASC";

$result = mysqli_query($conn, $query);
$params_export = "kode_cabang=" . $kode_cabang;
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    .modern-wrapper { font-family: 'Poppins', sans-serif; color: #444; font-size: 0.9rem; }
    
    .card-modern { border: none; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); background: #fff; margin-bottom: 20px; }
    .form-control-modern { border-radius: 8px; border: 1px solid #ddd; padding: 10px 15px; font-size: 0.9rem; }
    
    .btn-modern { 
        border-radius: 8px; padding: 10px 20px; font-weight: 600; font-size: 0.9rem; border: none; 
        display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); color: #fff !important; 
    }
    .btn-modern:hover { transform: translateY(-2px); text-decoration: none; box-shadow: 0 4px 10px rgba(0,0,0,0.15); color: #fff !important; filter: brightness(110%); }

    .btn-filter-m { background-color: #2a5298 !important; }
    .btn-pdf-m    { background-color: #d32f2f !important; }
    .btn-excel-m  { background-color: #1D6F42 !important; }

    .table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-modern thead th { background: linear-gradient(45deg, #1e3c72, #2a5298); color: white; padding: 15px; border: none; text-transform: uppercase; vertical-align: middle; }
    .table-modern tbody tr:hover { background-color: #f8f9fa; }
    .table-modern td { padding: 12px 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
    
    .total-row { background-color: #eef2ff !important; font-weight: 700; color: #1e3c72; border-top: 2px solid #2a5298; }
    .progress-thin { height: 6px; background-color: #e9ecef; border-radius: 10px; overflow: hidden; margin-top: 5px; width: 100px; }
    .progress-bar-custom { height: 100%; border-radius: 10px; }
    .badge-modern { padding: 6px 12px; border-radius: 30px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
</style>

<div class="modern-wrapper">
    <section class="content-header pt-3 pb-3">
        <div class="container-fluid">
            <h1 style="font-weight: 700; color: #2c3e50; font-size: 1.5rem;">
                <i class="fas fa-chart-pie mr-2 text-primary"></i> Laporan Formasi Pegawai
            </h1>
        </div>
    </section>

    <section class="content">
        <div class="card card-modern">
            <div class="card-body py-3">
                <form method="GET" action="home-admin.php">
                    <input type="hidden" name="page" value="formasi"> 

                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-building text-muted"></i></span>
                                </div>
                                <select name="kode_cabang" class="form-control form-control-modern border-left-0 pl-0">
                                    <option value="" <?= $kode_cabang == '' ? 'selected' : '' ?>>
                                        -- SEMUA KANTOR / GLOBAL (GABUNGAN) --
                                    </option>
                                    <option value="PUSAT" <?= $kode_cabang == 'PUSAT' ? 'selected' : '' ?>>
                                        KANTOR PUSAT (KP & KANWIL)
                                    </option>
                                    <?php
                                    $qc = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor WHERE level = 'KC' ORDER BY nama_kantor");
                                    while ($c = mysqli_fetch_array($qc)) {
                                        $sel = ($kode_cabang == $c['kode_kantor_detail']) ? 'selected' : '';
                                        echo "<option value='".$c['kode_kantor_detail']."' $sel>".$c['nama_kantor']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-7 text-md-right mt-3 mt-md-0">
                            <button type="submit" class="btn btn-modern btn-filter-m">
                                <i class="fas fa-filter"></i> Terapkan
                            </button>
                            <a href="pages/report/print-formasi.php?<?= $params_export ?>" target="_blank" class="btn btn-modern btn-pdf-m">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </a>
                            <a href="pages/report/export-formasi.php?<?= $params_export ?>" class="btn btn-modern btn-excel-m">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-modern">
            <div class="card-body p-0 table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="35%">Jabatan</th>
                            <th width="10%" class="text-center">Kuota</th>
                            <th width="15%" class="text-center">Terisi</th>
                            <th width="10%" class="text-center">Sisa</th>
                            <th width="25%">Status Formasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $total_kuota = 0; $total_terisi = 0; $total_kosong = 0;

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $nama_jabatan = $row['nama_jabatan']; 
                                $kuota = $row['kuota'];
                                $terisi = $row['terisi'];
                                $kosong = $row['kosong'];
                                $sumber = $row['sumber'];
                                
                                $total_kuota += $kuota; $total_terisi += $terisi; $total_kosong += $kosong;

                                $persen = ($kuota > 0) ? round(($terisi / $kuota) * 100) : 0;
                                if($persen > 100) $persen = 100; 

                                if ($kosong > 0) {
                                    $badgeStyle = "background:#ffebee; color:#c62828;"; 
                                    $icon = "fa-exclamation-circle";
                                    $text = "Kurang $kosong";
                                    $barColor = "background-color:#dc3545;";
                                } elseif ($kosong < 0) {
                                    $over = abs($kosong);
                                    $badgeStyle = "background:#fff8e1; color:#f57f17;"; 
                                    $icon = "fa-users";
                                    $text = "Over $over";
                                    $barColor = "background-color:#ffc107;";
                                } else {
                                    $badgeStyle = "background:#e8f5e9; color:#2e7d32;"; 
                                    $icon = "fa-check-circle";
                                    $text = "Terpenuhi";
                                    $barColor = "background-color:#28a745;";
                                }
                                
                                $warningIcon = ($sumber == 'NonMaster') ? '<i class="fas fa-exclamation-triangle text-warning mr-1" title="Jabatan Tidak Terdaftar di Master"></i>' : '';
                                
                                // Style baris khusus untuk anomali biar makin kelihatan
                                $rowStyle = ($sumber == 'NonMaster') ? "background-color: #fffbf0;" : "";
                        ?>
                            <tr style="<?= $rowStyle ?>">
                                <td align="center" style="font-weight: 600; color:#888;"><?= $no++ ?>.</td>
                                <td style="font-weight: 500; color: #2c3e50;"><?= $warningIcon ?> <?= $nama_jabatan ?></td>
                                <td align="center"><span style="background:#eee; padding:5px 10px; border-radius:6px; font-weight:bold; color:#555;"><?= $kuota ?></span></td>
                                <td align="center">
                                    <div style="font-weight:bold; color:#444;"><?= $terisi ?></div>
                                    <?php if($kuota > 0): ?>
                                    <div class="progress-thin mx-auto" title="Terisi <?= $persen ?>%">
                                        <div class="progress-bar-custom" style="width: <?= $persen ?>%; <?= $barColor ?>"></div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td align="center" style="font-size:1.1rem; font-weight:bold; color: #555;"><?= ($kosong < 0) ? '+' . abs($kosong) : $kosong ?></td>
                                <td><div class="badge-modern" style="<?= $badgeStyle ?>"><i class="fas <?= $icon ?>"></i> <?= $text ?></div></td>
                            </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-5 text-muted'><i>Data formasi belum tersedia.</i></td></tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="2" class="text-right text-uppercase" style="padding-right: 20px;">Grand Total</td>
                            <td align="center" style="font-size: 1.1rem;"><?= number_format($total_kuota) ?></td>
                            <td align="center" style="font-size: 1.1rem;"><?= number_format($total_terisi) ?></td>
                            <td align="center" style="font-size: 1.1rem; color: <?= ($total_kosong > 0) ? '#dc3545' : '#28a745' ?>">
                                <?= ($total_kosong < 0) ? '+' . abs($total_kosong) : $total_kosong ?>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </section>
</div>