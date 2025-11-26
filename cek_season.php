<?php
// ==========================================
// FILE: cek_session.php
// FUNGSI: MENGINTIP ISI SESSION LOGIN
// ==========================================

session_start(); // Wajib start dulu biar kebaca

echo "<h1>🕵️‍♂️ DETEKTIF SESSION</h1>";
echo "<hr>";

if (empty($_SESSION)) {
    echo "<h3 style='color:red'>⚠️ KOSONG! Tidak ada user yang login.</h3>";
    echo "<p>Silakan login dulu di aplikasi, baru refresh halaman ini.</p>";
} else {
    echo "<h3 style='color:green'>✅ ADA ISINYA! Berikut data session kamu:</h3>";
    
    echo "<pre style='background:#f4f4f4; padding:15px; border:1px solid #ccc; font-size:14px;'>";
    print_r($_SESSION);
    echo "</pre>";

    echo "<hr>";
    echo "<h4>👉 Kesimpulan untuk Codingan:</h4>";
    
    // Coba tebak variabel username yang umum
    $keys = array_keys($_SESSION);
    echo "Gunakan kode ini di file upload-data-diklat.php:<br><br>";
    
    echo "<code style='background:black; color:lime; padding:10px; display:block;'>";
    echo "\$current_user = \$_SESSION['" . $keys[0] . "']; // Ganti index array sesuai kebutuhan";
    echo "</code>";
}
?>