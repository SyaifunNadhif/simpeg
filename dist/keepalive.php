<?php
session_start();
$_SESSION['start_session'] = time(); // perpanjang waktu aktif
http_response_code(204); // tidak kirim konten
exit;
