<?php
// FILE: pages/ref-biaya-pendidikan/ajax-get-detail-peserta.php

if (session_id() == '') session_start();
include "../../dist/koneksi.php"; 

ini_set('display_errors', 0);
while(ob_get_level()){ ob_end_clean(); } 
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $diklat        = mysqli_real_escape_string($conn, $_POST['diklat']);
    $penyelenggara = mysqli_real_escape_string($conn, $_POST['penyelenggara']);
    $tgl           = mysqli_real_escape_string($conn, $_POST['tgl']);
    
    // --- SETTING PAGINATION (Limit 3) ---
    $page  = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = 3; 
    $offset = ($page - 1) * $limit;

    // --- 1. HITUNG TOTAL REALISASI (KESELURUHAN) ---
    $sqlSummary = "SELECT 
                    COUNT(*) as total_orang,
                    SUM(biaya) as total_uang
                 FROM tb_diklat
                 WHERE diklat = '$diklat' 
                   AND penyelenggara = '$penyelenggara' 
                   AND date_reg = '$tgl'";
    
    $qSummary = mysqli_query($conn, $sqlSummary);
    $rSummary = mysqli_fetch_assoc($qSummary);
    
    $total_orang_real = (int)$rSummary['total_orang'];
    $total_uang_real  = (float)$rSummary['total_uang'];
    $total_pages      = ceil($total_orang_real / $limit);

    // --- 2. AMBIL DATA PESERTA (PER HALAMAN) ---
    $sql = "SELECT p.nama, p.id_peg, d.biaya 
            FROM tb_diklat d
            LEFT JOIN tb_pegawai p ON d.id_peg = p.id_peg
            WHERE d.diklat = '$diklat' 
              AND d.penyelenggara = '$penyelenggara' 
              AND d.date_reg = '$tgl'
            LIMIT $offset, $limit";

    $query = mysqli_query($conn, $sql);
    
    $rows = [];
    if ($query && mysqli_num_rows($query) > 0) {
        $no = $offset + 1;
        while ($r = mysqli_fetch_assoc($query)) {
            $biaya = isset($r['biaya']) ? (float)$r['biaya'] : 0;
            $rows[] = [
                'no'    => $no++,
                'nama'  => htmlspecialchars($r['nama']),
                'nip'   => htmlspecialchars($r['id_peg']),
                'biaya' => "Rp " . number_format($biaya, 0, ',', '.')
            ];
        }
    }

    // Return JSON
    echo json_encode([
        'status' => 'success',
        'data'   => $rows,
        // Data Footer (Realisasi)
        'summary' => [
            'total_orang' => $total_orang_real . " Orang",
            'total_biaya' => "Rp " . number_format($total_uang_real, 0, ',', '.')
        ],
        'pagination' => [
            'current_page' => $page,
            'total_pages'  => $total_pages,
            'total_data'   => $total_orang_real
        ]
    ]);
}
?>