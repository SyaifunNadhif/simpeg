<?php
/*********************************************************
 * FILE    : pages/pelanggaran/proses-upload-hukuman.php
 * MODULE  : Proses Import CSV (Auto Detect Delimiter)
 * VERSION : v2.0
 *********************************************************/

// 1. PERBAIKAN KONEKSI (Gunakan __DIR__ agar path absolut & anti error)
include_once __DIR__ . '/../../dist/koneksi.php';

// Fungsi ID Otomatis
function kdauto($conn, $tabel, $inisial){
    $struktur = mysqli_query($conn, "SELECT * FROM $tabel LIMIT 1");
    $field    = mysqli_fetch_field_direct($struktur, 0)->name;
    $qry      = mysqli_query($conn, "SELECT max(".$field.") as max_id FROM ".$tabel);
    $row      = mysqli_fetch_array($qry);
    if ($row['max_id'] == "") { $angka = 0; } 
    else { $angka = substr($row['max_id'], strlen($inisial)); }
    $angka++;
    return $inisial . str_pad($angka, 3, "0", STR_PAD_LEFT);
}

$berhasil = 0;
$gagal = 0;

if (isset($_POST['upload'])) {
    
    $file = $_FILES['file_csv']['tmp_name'];

    if (empty($file)) {
        echo "<script>alert('File kosong!'); window.history.back();</script>";
        exit;
    }

    $handle = fopen($file, "r");

    // 2. FITUR AUTO DETECT DELIMITER (PENTING!)
    // Cek baris pertama, apakah pakai titik koma (Excel Indo) atau koma (Excel US)
    $line = fgets($handle);
    $delimiter = (strpos($line, ';') !== false) ? ';' : ',';
    
    // Kembalikan pointer ke baris awal setelah pengecekan
    rewind($handle); 
    
    // Lewati baris Header (Judul Kolom)
    fgetcsv($handle, 1000, $delimiter);

    while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
        
        // 3. VALIDASI JUMLAH KOLOM (Agar tidak error Offset)
        // Pastikan baris ini punya minimal 7 kolom data
        if (count($data) < 7) {
            $gagal++;
            continue; // Skip baris ini
        }

        // Mapping Data
        $id_peg     = mysqli_real_escape_string($conn, trim($data[0]));
        $hukuman    = mysqli_real_escape_string($conn, trim($data[1]));
        $keterangan = mysqli_real_escape_string($conn, trim($data[2]));
        $pejabat_sk = mysqli_real_escape_string($conn, trim($data[3]));
        $jabatan_sk = mysqli_real_escape_string($conn, trim($data[4]));
        $no_sk      = mysqli_real_escape_string($conn, trim($data[5]));
        
        // Format Tanggal (Handle format dd/mm/yyyy atau yyyy-mm-dd)
        $raw_tgl    = trim($data[6]);
        $tgl_sk     = date('Y-m-d', strtotime(str_replace('/', '-', $raw_tgl)));

        // Data Otomatis
        $id_hukum   = kdauto($conn, "tb_hukuman", "H");
        $date_reg   = date('Y-m-d H:i:s');
        $created_by = 'Import Kolektif';

        // Cari Pangkat & Jabatan (Otomatis dari sistem)
        $gol = '-'; $pangkat = '-'; $eselon = '-';
        
        $tP = mysqli_query($conn, "SELECT gol, pangkat FROM tb_pangkat WHERE id_peg='$id_peg' AND status_pan='Aktif' ORDER BY tgl_sk DESC LIMIT 1");
        if ($tP && mysqli_num_rows($tP) > 0) { $gp = mysqli_fetch_array($tP); $gol = $gp['gol']; $pangkat = $gp['pangkat']; }

        $tJ = mysqli_query($conn, "SELECT eselon FROM tb_jabatan WHERE id_peg='$id_peg' AND status_jab='Aktif' ORDER BY tmt_jabatan DESC LIMIT 1");
        if ($tJ && mysqli_num_rows($tJ) > 0) { $esl = mysqli_fetch_array($tJ); $eselon = $esl['eselon']; }

        // Validasi: ID Pegawai harus ada di database
        $cekPegawai = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE id_peg='$id_peg'");
        
        if(mysqli_num_rows($cekPegawai) > 0 && !empty($hukuman)){
            $sql = "INSERT INTO tb_hukuman (
                id_hukum, id_peg, hukuman, keterangan, pejabat_sk, jabatan_sk, no_sk, tgl_sk, 
                gol, pangkat, eselon, date_reg, created_by
            ) VALUES (
                '$id_hukum', '$id_peg', '$hukuman', '$keterangan', '$pejabat_sk', '$jabatan_sk', '$no_sk', '$tgl_sk',
                '$gol', '$pangkat', '$eselon', '$date_reg', '$created_by'
            )";
            
            if(mysqli_query($conn, $sql)){
                $berhasil++;
            } else {
                $gagal++;
            }
        } else {
            $gagal++;
        }
    }
    
    fclose($handle);
}
?>
<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>body{font-family:sans-serif; background:#f4f6f9;}</style>
</head>
<body>
<script>
    Swal.fire({
        icon: 'info',
        title: 'Proses Selesai',
        html: 'Berhasil import: <b><?=$berhasil?></b> data.<br>Gagal/Skip: <b><?=$gagal?></b> data.',
        confirmButtonText: 'OK'
    }).then((result) => {
        // Perhatikan path redirect ini, pastikan kembali ke halaman view yang benar
        window.location.href = 'home-admin.php?page=form-view-data-pelanggaran';
    });
</script>
</body>
</html>