<?php
// ==== BASE URL GLOBAL ====
// untuk lokal
$BASE_URL = "http://localhost:8081/dummy/";

// untuk deploy nanti (tinggal ganti 1 baris)
// $BASE_URL = "https://domain.com/";

// helper
function base_url($path = '') {
    global $BASE_URL;
    return rtrim($BASE_URL, '/') . '/' . ltrim($path, '/');
}
?>
