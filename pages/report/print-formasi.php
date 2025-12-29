<?php
// FILE: pages/report/print-formasi.php
// VERSI: DIRECT DOWNLOAD PDF (Pake html2pdf.js)

include "../../dist/koneksi.php";

// Ambil Filter
$kode_cabang = isset($_GET['kode_cabang']) ? mysqli_real_escape_string($conn, $_GET['kode_cabang']) : '';

// 1. Judul Laporan
$nama_cabang_judul = "SEMUA KANTOR / GLOBAL (GABUNGAN)";
if ($kode_cabang == 'PUSAT') {
    $nama_cabang_judul = "KANTOR PUSAT & KANWIL";
} elseif ($kode_cabang != '') {
    $qCab = mysqli_query($conn, "SELECT nama_kantor FROM tb_kantor WHERE kode_kantor_detail = '$kode_cabang'");
    if($rCab = mysqli_fetch_assoc($qCab)){
        $nama_cabang_judul = strtoupper($rCab['nama_kantor']);
    }
}

// 2. Logic Query (SAMA PERSIS DENGAN VIEW UTAMA)
$where_master_lingkup = ""; 
$join_filter_cabang = "";

if ($kode_cabang == 'PUSAT') {
    $where_master_lingkup = "WHERE m.lingkup IN ('KP', 'KANWIL')";
    $join_filter_cabang   = ""; 
} elseif ($kode_cabang != '') {
    $where_master_lingkup = "WHERE m.lingkup IN ('KC', 'KK')";
    $join_filter_cabang   = "AND j.unit_kerja = '$kode_cabang'";
} else {
    $where_master_lingkup = ""; 
    $join_filter_cabang   = ""; 
}

// 3. Eksekusi Query
$query = "
    SELECT * FROM (
        SELECT 
            m.nama_jabatan, 
            m.kuota, 
            COUNT(DISTINCT p.id_peg) AS terisi, 
            (m.kuota - COUNT(DISTINCT p.id_peg)) AS kosong,
            'Master' as sumber
        FROM tb_master_jabatan m
        LEFT JOIN tb_jabatan j ON j.jabatan = m.nama_jabatan AND j.status_jab = 'Aktif' $join_filter_cabang
        LEFT JOIN tb_pegawai p ON p.id_peg = j.id_peg AND p.status_aktif = '1'
        $where_master_lingkup
        GROUP BY m.nama_jabatan, m.kuota

        UNION ALL

        SELECT 
            j.jabatan as nama_jabatan, 
            0 as kuota, 
            COUNT(DISTINCT p.id_peg) AS terisi, 
            (0 - COUNT(DISTINCT p.id_peg)) AS kosong,
            'NonMaster' as sumber
        FROM tb_jabatan j
        JOIN tb_pegawai p ON p.id_peg = j.id_peg AND p.status_aktif = '1'
        LEFT JOIN tb_master_jabatan m ON m.nama_jabatan = j.jabatan
        WHERE m.nama_jabatan IS NULL 
        AND j.status_jab = 'Aktif'
        $join_filter_cabang
        GROUP BY j.jabatan
    ) AS gabungan
    ORDER BY nama_jabatan ASC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Formasi - <?= $nama_cabang_judul ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 12px; padding: 20px; color: #000; }
        
        /* Tombol Download */
        .btn-download {
            background-color: #d32f2f; 
            color: white; 
            padding: 12px 25px; 
            font-weight: bold; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .btn-download:hover { background-color: #b71c1c; }

        /* Area Cetak */
        #content-to-pdf {
            padding: 10px;
            background: #fff;
        }

        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 5px 0; font-size: 18px; }
        .header h4 { margin: 5px 0; font-weight: normal; font-size: 14px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #444; padding: 6px 8px; font-size: 11px; }
        th { background-color: #eee; text-transform: uppercase; font-weight: bold; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>

    <div style="text-align: left;">
        <button onclick="generatePDF()" class="btn-download">
            <i class="fa fa-download"></i> DOWNLOAD PDF
        </button>
        <span style="margin-left: 10px; color: #666; font-style: italic;">*Klik tombol untuk menyimpan file PDF</span>
    </div>

    <div id="content-to-pdf">
        <div class="header">
            <h2>LAPORAN FORMASI PEGAWAI</h2>
            <h4>UNIT KERJA: <b><?= $nama_cabang_judul ?></b></h4>
            <small>Dicetak pada: <?= date('d F Y') ?></small>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">NO</th>
                    <th width="40%">JABATAN</th>
                    <th width="10%">KUOTA</th>
                    <th width="10%">TERISI</th>
                    <th width="10%">SISA</th>
                    <th width="25%">STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1; 
                $t_kuota = 0; $t_terisi = 0; $t_kosong = 0;

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $t_kuota += $row['kuota']; 
                        $t_terisi += $row['terisi']; 
                        $t_kosong += $row['kosong'];

                        $kosong = $row['kosong'];
                        if ($kosong > 0) {
                            $status = "KURANG $kosong";
                        } elseif ($kosong < 0) {
                            $status = "OVER " . abs($kosong);
                        } else {
                            $status = "TERPENUHI";
                        }
                        
                        $nm = ($row['sumber'] == 'NonMaster') ? ' *' : '';

                        echo "<tr>
                                <td class='text-center'>".$no++."</td>
                                <td>".$row['nama_jabatan'].$nm."</td>
                                <td class='text-center bold'>".$row['kuota']."</td>
                                <td class='text-center bold'>".$row['terisi']."</td>
                                <td class='text-center'>".(($kosong < 0) ? '+' . abs($kosong) : $kosong)."</td>
                                <td class='text-center'>$status</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>Data tidak ditemukan.</td></tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #ddd;">
                    <td colspan="2" class="text-right bold">GRAND TOTAL</td>
                    <td class="text-center bold"><?= $t_kuota ?></td>
                    <td class="text-center bold"><?= $t_terisi ?></td>
                    <td class="text-center bold"><?= ($t_kosong < 0) ? '+' . abs($t_kosong) : $t_kosong ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        
        <br>
        <small><i>* Data berdasarkan sistem kepegawaian terkini.</i></small>
    </div>

    <script>
        function generatePDF() {
            // Ambil elemen yang mau dicetak
            var element = document.getElementById('content-to-pdf');
            
            // Nama File
            var filename = 'Laporan_Formasi_<?= date("Ymd_His") ?>.pdf';

            // Konfigurasi html2pdf
            var opt = {
                margin:       [10, 10, 10, 10], // Margin (Atas, Kiri, Bawah, Kanan)
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 }, // Scale 2 biar teks tajam
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // Eksekusi
            html2pdf().set(opt).from(element).save();
        }
        
        // Opsional: Otomatis download saat dibuka (hilangkan // jika mau otomatis)
        // window.onload = function() { generatePDF(); };
    </script>

</body>
</html>