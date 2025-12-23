<?php
// pages/diklat/ajax-detail-diklat.php

// 1. SECURITY: CEK LOGIN DULU
if (session_id() === '') session_start();
if (empty($_SESSION['id_user'])) {
    die('<div class="alert alert-danger">Akses ditolak. Silakan login.</div>');
}

include '../../dist/koneksi.php'; 

if (isset($_POST['diklat'])) {
    // Sanitasi Input Database (Anti SQL Injection)
    $diklat = mysqli_real_escape_string($conn, $_POST['diklat']);
    $tahun  = mysqli_real_escape_string($conn, $_POST['tahun']);
    $peny   = mysqli_real_escape_string($conn, $_POST['penyelenggara']);

    // Query
    $sql = "SELECT d.biaya, p.nama, p.id_peg, k.nama_kantor 
            FROM tb_diklat d
            JOIN tb_pegawai p ON d.id_peg = p.id_peg
            LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
            LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
            WHERE d.diklat = '$diklat' 
            AND d.tahun = '$tahun'
            AND d.penyelenggara = '$peny'
            ORDER BY p.nama ASC";

    $q = mysqli_query($conn, $sql);
    
    // Tampilkan Tabel
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-striped table-sm">';
    echo '<thead class="thead-light">
            <tr>
                <th width="5%">No</th>
                <th>Nama Pegawai</th>
                <th>Unit Kerja</th>
                <th class="text-right">Biaya Individu</th>
            </tr>
          </thead>';
    echo '<tbody>';

    $no = 1;
    $total = 0;

    if (mysqli_num_rows($q) > 0) {
        while ($r = mysqli_fetch_assoc($q)) {
            $total += $r['biaya'];
            $unit = $r['nama_kantor'] ? $r['nama_kantor'] : '-';
            
            // [SECURITY] Sanitasi Output HTML (Anti XSS)
            // Semua data dari DB wajib dibungkus htmlspecialchars
            
            echo '<tr>';
            echo '<td class="text-center">' . $no++ . '</td>';
            echo '<td>
                    <strong>' . htmlspecialchars($r['nama']) . '</strong><br>
                    <small class="text-muted">NIP: ' . htmlspecialchars($r['id_peg']) . '</small>
                  </td>';
            echo '<td>' . htmlspecialchars($unit) . '</td>'; 
            echo '<td class="text-right">Rp ' . number_format($r['biaya'], 0, ',', '.') . '</td>';
            echo '</tr>';
        }
        
        // Baris Total
        echo '<tr class="font-weight-bold bg-light">';
        echo '<td colspan="3" class="text-right">Total Biaya Kegiatan Ini:</td>';
        echo '<td class="text-right text-primary">Rp ' . number_format($total, 0, ',', '.') . '</td>';
        echo '</tr>';

    } else {
        echo '<tr><td colspan="4" class="text-center text-muted">Data peserta tidak ditemukan.</td></tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
?>