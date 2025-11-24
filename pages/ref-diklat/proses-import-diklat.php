<?php
/*********************************************************
 * FILE    : pages/diklat/proses-import-diklat.php
 * MODULE  : Proses Import CSV (Clean UI)
 * VERSION : v1.0
 *********************************************************/

include_once __DIR__ . '/../../dist/koneksi.php';

if (session_id() == '') session_start();

$berhasil = 0;
$gagal = 0;

if (isset($_POST['upload'])) {
    
    $file = $_FILES['file_csv']['tmp_name'];

    if (empty($file)) {
        echo "<script>alert('File kosong!'); window.history.back();</script>";
        exit;
    }

    $handle = fopen($file, "r");

    // DETEKSI PEMISAH (, atau ;)
    $line = fgets($handle);
    $delimiter = (strpos($line, ';') !== false) ? ';' : ',';
    rewind($handle); 
    
    // LEWATI HEADER
    fgetcsv($handle, 1000, $delimiter);

    while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
        
        // Validasi jumlah kolom (Minimal 7)
        if (count($data) < 7) { $gagal++; continue; }

        $nip           = mysqli_real_escape_string($conn, trim($data[0]));
        $diklat        = mysqli_real_escape_string($conn, trim($data[1]));
        $penyelenggara = mysqli_real_escape_string($conn, trim($data[2]));
        $tempat        = mysqli_real_escape_string($conn, trim($data[3]));
        $angkatan      = mysqli_real_escape_string($conn, trim($data[4]));
        $tahun         = mysqli_real_escape_string($conn, trim($data[5]));
        
        // Format Tanggal (dd/mm/yyyy -> yyyy-mm-dd)
        $raw_tgl       = trim($data[6]);
        $date_reg      = date('Y-m-d', strtotime(str_replace('/', '-', $raw_tgl)));

        // Cari ID Pegawai berdasarkan NIP
        $qPeg = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE nip = '$nip'");
        
        if(mysqli_num_rows($qPeg) > 0 && !empty($diklat)){
            $rPeg = mysqli_fetch_assoc($qPeg);
            $id_peg = $rPeg['id_peg'];
            $created_by = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'admin';

            $sql = "INSERT INTO tb_diklat (
                id_peg, diklat, penyelenggara, tempat, angkatan, tahun, date_reg, created_by
            ) VALUES (
                '$id_peg', '$diklat', '$penyelenggara', '$tempat', '$angkatan', '$tahun', '$date_reg', '$created_by'
            )";
            
            if(mysqli_query($conn, $sql)){
                $berhasil++;
            } else {
                $gagal++;
            }
        } else {
            // Pegawai tidak ditemukan
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
      body { background-color: #f4f6f9; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; overflow: hidden; }
      .loading-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; width: 300px; }
      .loading-icon { color: #17a2b8; animation: spin 1s linear infinite; margin-bottom: 20px; }
      h4 { margin: 0; font-size: 18px; color: #333; }
      p { margin: 5px 0 0; font-size: 14px; color: #888; }
      @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
</head>
<body>

    <div class="loading-card">
        <i class="fas fa-circle-notch fa-3x loading-icon"></i>
        <h4>Sedang Memproses...</h4>
        <p>Mohon tunggu sebentar.</p>
    </div>

    <script>
        var berhasil = <?php echo $berhasil; ?>;
        var gagal    = <?php echo $gagal; ?>;
        
        // Redirect ke halaman master data diklat
        var redirectUrl = 'home-admin.php?page=master-data-diklat';

        if (berhasil > 0 || gagal > 0) {
            Swal.fire({
                icon: (berhasil > 0) ? 'success' : 'warning',
                title: 'Selesai',
                html: 'Berhasil import: <b>' + berhasil + '</b> data.<br>' +
                      'Gagal/Skip: <b>' + gagal + '</b> data.<br><small>(Gagal biasanya karena NIP tidak ditemukan)</small>',
                confirmButtonText: 'Kembali'
            }).then(() => { window.location.href = redirectUrl; });
        } else {
             Swal.fire({ icon: 'error', title: 'Gagal', text: 'File kosong atau format salah.' })
             .then(() => { window.history.back(); });
        }
    </script>
</body>
</html>