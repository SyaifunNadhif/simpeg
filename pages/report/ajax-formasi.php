<?php
// FILE: pages/report/ajax-formasi.php

// 1. KONEKSI (Mundur 2 folder ke root dist)
$path_koneksi = '../../dist/koneksi.php';
if (file_exists($path_koneksi)) {
    include $path_koneksi;
} else {
    die("<div class='alert alert-danger'>Error: Koneksi tidak ditemukan di $path_koneksi</div>");
}

// 2. AMBIL VARIABEL FILTER
$kode_cabang = isset($_POST['kode_cabang']) ? mysqli_real_escape_string($conn, $_POST['kode_cabang']) : '';

// 3. LOGIC FILTER (Query Builder)
// ------------------------------------------------------------------------------------
// A. Filter Lingkup Master Jabatan
if ($kode_cabang != '') {
    // Jika pilih cabang: Hanya tampilkan jabatan level Cabang (KC)
    $where_master_lingkup = "WHERE m.lingkup = 'KC'";
    
    // Filter unit kerja spesifik di tabel jabatan
    $join_filter_cabang = "AND j.unit_kerja = '$kode_cabang'";
} else {
    // Jika global: Hanya tampilkan jabatan Pusat (KP) dan Kanwil
    $where_master_lingkup = "WHERE m.lingkup IN ('KP', 'KANWIL')";
    
    // Tidak ada filter unit kerja spesifik (ambil semua yang sesuai jabatan)
    $join_filter_cabang = ""; 
}

// 4. QUERY UTAMA (DOUBLE LOCK VALIDATION)
$query = "SELECT 
            m.nama_jabatan, 
            m.kuota,
            -- Hitung Pegawai yang valid saja (DISTINCT untuk cegah duplikat aneh)
            COUNT(DISTINCT p.id_peg) AS terisi,
            (m.kuota - COUNT(DISTINCT p.id_peg)) AS kosong
          FROM tb_master_jabatan m
          
          -- JOIN 1: KE TABEL JABATAN
          -- Syarat 1: Nama Jabatan cocok
          -- Syarat 2: Status Jabatan WAJIB 'Aktif' (Biar jabatan masa lalu gak kehitung)
          -- Syarat 3: Filter Cabang (Kalau ada)
          LEFT JOIN tb_jabatan j ON j.jabatan = m.nama_jabatan 
                                 AND j.status_jab = 'Aktif' 
                                 $join_filter_cabang
          
          -- JOIN 2: KE TABEL PEGAWAI (KUNCI UTAMA BROTHER)
          -- Syarat: Status Pegawai WAJIB '1' (Aktif)
          -- Ini yang membuang data pegawai resign yang jabatannya 'lupa dimatikan'
          LEFT JOIN tb_pegawai p ON p.id_peg = j.id_peg AND p.status_aktif = '1'
          
          $where_master_lingkup
          
          GROUP BY m.nama_jabatan, m.kuota
          ORDER BY m.nama_jabatan ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("<div class='alert alert-danger'>Query Error: ".mysqli_error($conn)."</div>");
}
?>

<style>
    .table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-modern thead th { background: linear-gradient(45deg, #1e3c72, #2a5298); color: white; padding: 15px; border: none; text-transform: uppercase; font-size: 0.85rem; vertical-align: middle; }
    .table-modern td { padding: 12px 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .badge-modern { padding: 6px 12px; border-radius: 30px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
    .progress-thin { height: 6px; background-color: #e9ecef; border-radius: 10px; overflow: hidden; margin-top: 5px; width: 100px; }
    .progress-bar-custom { height: 100%; border-radius: 10px; }
</style>

<div class="card card-modern" style="border:none; box-shadow:0 5px 15px rgba(0,0,0,0.05); border-radius:12px; overflow:hidden;">
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
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_array($result)) {
                        $nama_jabatan = $row['nama_jabatan']; 
                        $kuota = $row['kuota'];
                        $terisi = $row['terisi'];
                        $kosong = $row['kosong']; 

                        // Visual Progress Bar
                        $persen = ($kuota > 0) ? round(($terisi / $kuota) * 100) : 0;
                        if ($persen > 100) $persen = 100;

                        // Logic Status Badge
                        if ($kosong > 0) {
                            $badgeClass = "background:#ffebee; color:#c62828;"; // Merah Soft
                            $icon = "fa-exclamation-circle";
                            $text = "Kurang $kosong";
                            $barColor = "background-color:#dc3545;";
                        } elseif ($kosong < 0) {
                            $over = abs($kosong);
                            $badgeClass = "background:#fff8e1; color:#f57f17;"; // Kuning Soft
                            $icon = "fa-users";
                            $text = "Over $over";
                            $barColor = "background-color:#ffc107;";
                        } else {
                            $badgeClass = "background:#e8f5e9; color:#2e7d32;"; // Hijau Soft
                            $icon = "fa-check-circle";
                            $text = "Terpenuhi";
                            $barColor = "background-color:#28a745;";
                        }
                ?>
                    <tr style="transition: background 0.2s;" onmouseover="this.style.background='#f8faff'" onmouseout="this.style.background='transparent'">
                        <td align="center" style="color:#888; font-weight:600;"><?= $no++ ?>.</td>
                        
                        <td style="font-weight: 500; color: #2c3e50;">
                            <?= $nama_jabatan ?>
                        </td>
                        
                        <td align="center">
                            <span style="background:#eee; padding:5px 10px; border-radius:6px; font-weight:bold; color:#555;">
                                <?= $kuota ?>
                            </span>
                        </td>
                        
                        <td align="center">
                            <div style="font-weight:bold; color:#444;"><?= $terisi ?></div>
                            <div class="progress-thin mx-auto" title="Terisi <?= $persen ?>%">
                                <div class="progress-bar-custom" style="width: <?= $persen ?>%; <?= $barColor ?>"></div>
                            </div>
                        </td>
                        
                        <td align="center" style="font-size:1.1rem; font-weight:bold; color: #555;">
                            <?= ($kosong < 0) ? '+' . abs($kosong) : $kosong ?>
                        </td>
                        
                        <td>
                            <div class="badge-modern" style="<?= $badgeClass ?>">
                                <i class="fas <?= $icon ?>"></i> <?= $text ?>
                            </div>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-5 text-muted'><i>Data formasi belum tersedia untuk filter ini.</i></td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>