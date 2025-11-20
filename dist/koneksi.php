<?php
// dist/koneksi.php

// Gunakan 127.0.0.1 supaya selalu pakai TCP (bukan socket)
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'masq2971_simpeg_dummy';
$port = 3307;  // Sesuaikan dengan port MySQL Anda (3306/3307)

// Buat koneksi MySQLi procedural
$conn = mysqli_connect($host, $user, $pass, $db, $port);

// Jika koneksi gagal
if (!$conn) {
    die('Koneksi ke Database Gagal: (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
}

// Set charset agar huruf tampil normal
mysqli_set_charset($conn, "utf8");
?>
