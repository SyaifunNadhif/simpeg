<?php
// FILE: pages/report/ajax-detail.php

if (session_id() === '') session_start();
if (empty($_SESSION['id_user'])) {
    die('<div class="alert alert-danger">Sesi habis. Silakan login ulang.</div>');
}
include '../../dist/koneksi.php'; 

if (isset($_POST['diklat'])) {
    $diklat = mysqli_real_escape_string($conn, $_POST['diklat']);
    $tahun  = mysqli_real_escape_string($conn, $_POST['tahun']);
    $peny   = mysqli_real_escape_string($conn, $_POST['penyelenggara']);
    
    // --- KONFIGURASI PAGINATION ---
    $limit = 4; 
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $mulai = ($page > 1) ? ($page * $limit) - $limit : 0;

    // 1. HITUNG TOTAL DATA
    $sql_count = "SELECT COUNT(*) as total 
                  FROM tb_diklat d
                  JOIN tb_pegawai p ON d.id_peg = p.id_peg
                  WHERE d.diklat = '$diklat' 
                  AND d.tahun = '$tahun'
                  AND d.penyelenggara = '$peny'";
    $res_count = mysqli_query($conn, $sql_count);
    $row_count = mysqli_fetch_assoc($res_count);
    $total_data = $row_count['total'];
    $total_halaman = ceil($total_data / $limit);

    // 2. QUERY DATA
    $sql = "SELECT d.biaya, p.nama, p.id_peg, k.nama_kantor 
            FROM tb_diklat d
            JOIN tb_pegawai p ON d.id_peg = p.id_peg
            LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
            LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
            WHERE d.diklat = '$diklat' 
            AND d.tahun = '$tahun'
            AND d.penyelenggara = '$peny'
            ORDER BY p.nama ASC
            LIMIT $mulai, $limit";

    $q = mysqli_query($conn, $sql);
    
    // Info Halaman (Ukuran Kecil)
    echo '<div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <span class="badge badge-info px-2 py-1" style="font-size: 0.75rem;">Total: '.$total_data.' Peserta</span>
            <small class="text-muted font-weight-bold" style="font-size: 0.75rem;">Halaman '.$page.' dari '.$total_halaman.'</small>
          </div>';

    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-striped table-sm mb-0">';
    
    // Header Biru (Font Kecil 0.8rem)
    echo '<thead style="background: linear-gradient(45deg, #1e3c72, #2a5298); color: white; font-size: 0.8rem;">
            <tr>
                <th width="5%" class="text-center align-middle py-2">No</th>
                <th class="align-middle py-2">Nama Pegawai</th>
                <th class="align-middle py-2">Unit Kerja</th>
                <th class="text-right align-middle py-2">Biaya</th>
            </tr>
          </thead>';
    echo '<tbody style="font-size: 0.85rem;">'; // Isi Tabel Font 0.85rem (sekitar 13.6px)

    $no = $mulai + 1;

    if (mysqli_num_rows($q) > 0) {
        while ($r = mysqli_fetch_assoc($q)) {
            $unit = $r['nama_kantor'] ? $r['nama_kantor'] : '-';
            echo '<tr>';
            echo '<td class="text-center align-middle py-2">' . $no++ . '</td>';
            echo '<td class="align-middle py-2">
                    <div style="font-weight: 600; color: #333; line-height: 1.2;">' . htmlspecialchars($r['nama']) . '</div>
                    <small class="text-muted" style="font-size: 0.7rem;">NIP: ' . htmlspecialchars($r['id_peg']) . '</small>
                  </td>';
            echo '<td class="align-middle py-2 text-secondary">' . htmlspecialchars($unit) . '</td>'; 
            echo '<td class="text-right align-middle py-2" style="font-family: Consolas, monospace; font-weight: bold; color: #2c3e50;">Rp ' . number_format($r['biaya'], 0, ',', '.') . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="4" class="text-center text-muted py-3 small">Data tidak ditemukan.</td></tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    // 3. PAGINATION (Versi Mungil)
    if ($total_halaman > 1) {
        echo '<nav aria-label="Page navigation" class="mt-3">';
        echo '<ul class="pagination justify-content-center mb-0 align-items-center" style="font-size: 0.8rem;">';
        
        // --- PREV ---
        $disabled_prev = ($page <= 1) ? 'disabled' : '';
        $prev_page = $page - 1;
        $attr_prev = ($page <= 1) ? 'tabindex="-1" aria-disabled="true"' : 'href="#" data-page="'.$prev_page.'" data-diklat="'.htmlspecialchars($diklat).'" data-tahun="'.$tahun.'" data-peny="'.htmlspecialchars($peny).'"';
        
        // Padding tombol diperkecil (px-2 py-1)
        echo '<li class="page-item '.$disabled_prev.'">
                <a class="page-link page-nav rounded-pill px-3 py-1 mr-2 shadow-sm font-weight-bold" '.$attr_prev.' style="border: 1px solid #ddd; color: #1e3c72;">
                    <i class="fas fa-arrow-left mr-1" style="font-size: 0.7rem;"></i> Prev
                </a>
              </li>';

        // --- INFO TENGAH ---
        echo '<li class="page-item disabled">
                <span class="page-link border-0 bg-transparent text-secondary font-weight-bold mx-1" style="font-size: 0.8rem;">
                    '.$page.' / '.$total_halaman.'
                </span>
              </li>';

        // --- NEXT ---
        $disabled_next = ($page >= $total_halaman) ? 'disabled' : '';
        $next_page = $page + 1;
        $attr_next = ($page >= $total_halaman) ? 'tabindex="-1" aria-disabled="true"' : 'href="#" data-page="'.$next_page.'" data-diklat="'.htmlspecialchars($diklat).'" data-tahun="'.$tahun.'" data-peny="'.htmlspecialchars($peny).'"';
        
        echo '<li class="page-item '.$disabled_next.'">
                <a class="page-link page-nav rounded-pill px-3 py-1 ml-2 shadow-sm font-weight-bold" '.$attr_next.' style="border: 1px solid #ddd; color: #1e3c72;">
                    Next <i class="fas fa-arrow-right ml-1" style="font-size: 0.7rem;"></i>
                </a>
              </li>';
        
        echo '</ul>';
        echo '</nav>';
    }
}
?>